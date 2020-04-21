<?php
class ModelExtensionModuleISSSupersyncParse extends Model {
    private $sync_config = '';
    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }
    
    public function initParser($sync_id,$mode='detect_unchanged_entries'){
        set_time_limit(300);
        $sync=$this->db->query("SELECT * FROM " . DB_PREFIX . "iss_sync_list WHERE sync_id='$sync_id'")->row;
        if( !$sync ){
            return false;
        }
        
        $this->sync_config = json_decode($sync['sync_config']);
	$this->prepare_parsing($sync_id);
	
        $parser_method = 'parse_'.$sync['sync_parser_name'];
        if($this->$parser_method($sync)){
            
            $this->finish_parsing($sync_id,$mode);
	
            $this->db->query("UPDATE " . DB_PREFIX . "iss_sync_list SET sync_last_started=NOW() WHERE sync_id='{$sync['sync_id']}'");
            return true;
        } else {
            die('Error while parsing '.$parser_method.'!');
        };       
    }
    
    private function prepare_parsing($sync_id){
	$this->db->query("DROP TEMPORARY TABLE IF EXISTS iss_tmp_previous_sync");#TEMPORARY
	$this->db->query("CREATE TEMPORARY TABLE iss_tmp_previous_sync AS (SELECT * FROM " . DB_PREFIX . "iss_sync_entries WHERE sync_id='$sync_id')");

	$this->db->query("DROP TEMPORARY TABLE IF EXISTS iss_tmp_current_sync");#TEMPORARY
	$this->db->query("CREATE TEMPORARY TABLE iss_tmp_current_sync LIKE ".DB_PREFIX."iss_sync_entries");
    }
    private function finish_parsing($sync_id,$mode){
        if(isset($this->sync_config->csv_columns)){
            $changed_columns = $this->sync_config->csv_columns;
            $changed_columns[] = 'sync_entry_id';
            $sync_entries_structure = $this->db->query("SELECT * FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '".DB_PREFIX."iss_sync_entries'")->rows;
            $sync_entries_columns = [];
            foreach($sync_entries_structure as $column){
                array_push($sync_entries_columns, $column['COLUMN_NAME']);
            }
            $unchanged_columns = array_diff($sync_entries_columns, $changed_columns);
            foreach($unchanged_columns as &$column){
                $column = 'cse.'.$column.' = bse.'.$column;
            }
            $set = implode(', ', $unchanged_columns);
            $sql = "
                UPDATE      
                    iss_tmp_current_sync cse
                                JOIN
                    ".DB_PREFIX."iss_sync_entries bse USING (`model`)
                SET $set
                WHERE bse.sync_id='$sync_id'"; 
            $this->db->query($sql);
        } 
	$clear_previous_sync_sql = "DELETE FROM ".DB_PREFIX."iss_sync_entries WHERE sync_id = '$sync_id'";
	$this->db->query($clear_previous_sync_sql);
        $fill_entries_table_sql = "
            INSERT INTO 
                ".DB_PREFIX."iss_sync_entries 
                    (`is_changed`,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `leftovers`,`stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` ,  `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10` , `attribute11` , `attribute12` ,`attribute_group` ,  `image` , `image1` , `image2` , `image3` , `image4` , `image5` , `price1` , `price2` , `price3` , `price4` ,product_name1, product_name2, product_name3, `option_group1`,`price_group1`,`price`)
                SELECT          1,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `leftovers` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` ,  `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10` , `attribute11` , `attribute12` ,`attribute_group` , `image` , `image1` , `image2` , `image3` , `image4` , `image5` , `price1` , `price2` , `price3` , `price4` ,product_name1, product_name2, product_name3 ,
                    GROUP_CONCAT(option1 SEPARATOR '|') AS `option_group1`,
                    GROUP_CONCAT(price1 SEPARATOR '|') AS `price_group1`,
                    MIN(price1) AS `price`
                FROM 
                    iss_tmp_current_sync
                GROUP BY CONCAT(`category_lvl1`,'/',`category_lvl2`,'/',`category_lvl3`), model
                #HAVING price>0 AND price IS NOT NULL";
	$this->db->query($fill_entries_table_sql);
        $this->groupEntriesByCategories($sync_id);
        if( $mode=='detect_unchanged_entries' ){
            $change_finder_sql="
                UPDATE
                    ".DB_PREFIX."iss_sync_entries bse
                        JOIN
                    iss_tmp_previous_sync bps USING (`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `url` , `description` , `min_order_size` , `leftovers` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` , `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10`, `attribute11` ,`attribute_group`,`option1` , `option2` , `option3` , `image` , `image1` , `image2` , `image3` , `image4` , `image5` , `price1` , `price2` , `price3` , `price4`,product_name1, product_name2, product_name3)
                SET
                    bse.is_changed=0
                WHERE sync_id='$sync_id'";
            $this->db->query($change_finder_sql);
        }
    }
    
    
    
    private function parse_happywear($sync) {
        $source_file="https://happywear.ru/exchange/xml/price-list.csv";
        //$source_file="/price-list1.csv";
        $tmp_prefix = 'happywear_';
        $files = $this->optimizeFileSize($source_file, $tmp_prefix);
	$sync_id = $sync['sync_id'];
        foreach($files as $file){
            $sql = "
                LOAD DATA LOCAL INFILE 
                    '$file'
                INTO TABLE 
                    iss_tmp_current_sync
                CHARACTER SET 'cp1251'
                FIELDS TERMINATED BY '\;'
                    (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22)
                SET
                    sync_id = '$sync_id',
                    is_changed=1,
                    category_lvl1 = @col1,    
                    category_lvl2 = @col2,      
                    category_lvl3 = @col4,      
                    product_name = CONCAT(@col4,' ',@col7,' ',@col5), 
                    model = CONCAT(@col3,' ',@col5), 
                    mpn=@col14,
                    manufacturer = @col7,  
                    origin_country = @col8,
                    url = @col10, 
                    description = CONCAT(@col12,' <hr> ',@col22), 
                    min_order_size = @col15, 
                    stock_status='7-9 дней',
                    stock_count=0,
                    attribute1 = TRIM(REPLACE(REPLACE(REPLACE(@col5,',',', '),'  ',' '),'  ',' ')),
                    attribute2 = TRIM(REPLACE(REPLACE(REPLACE(@col6,',',', '),'  ',' '),'  ',' ')),
                    attribute3 = '',
                    attribute4 = '',
                    attribute5 = '',
                    option1 = TRIM(@col9), 
                    option2 = '', 
                    option3 = '', 
                    image = @col11,
                    image1 = REPLACE(@col11,'_front','_1'), 
                    image2 = REPLACE(@col11,'_front','_2'), 
                    image3 = REPLACE(@col11,'_front','_3'), 
                    image4 = REPLACE(@col11,'_front','_4'), 
                    image5 = REPLACE(@col11,'_front','_5'), 
                    price1 = @col13, 
                    price2 = @col7, 
                    price3 = @col18, 
                    price4 = ''
                ";
            if($this->db->query($sql)){
                continue;
            } else {
                return false;
            }
        }
        if($this->removeTempDir($tmp_prefix)){
            return true;
        } else {
            return false;
        }
    }
    
    private function optimizeFileSize($source_file, $tmp_prefix = ''){
        $tmpfile = './'.$tmp_prefix.'exchange'.rand(0,1000).'.csv';
        if(!copy($source_file, $tmpfile)){
            die("Downloading failed");
        };
        $file_size = filesize($tmpfile);
        $max_file_size = 40 * 1000000;
        if($file_size > $max_file_size){
            return $this->splitCSV($tmpfile, ceil($file_size/$max_file_size), $file_size, $tmp_prefix );
        } else {
            return $this->splitCSV($tmpfile, 1, $file_size, $tmp_prefix );
        }
    }

    private function splitCSV($tmpfile, $parts_quantity, $file_size, $tmp_prefix){
        $store_path = './'.$tmp_prefix.'tmp_files';
        $file_handle = fopen($tmpfile,'r');
        $buffer = $file_size / $parts_quantity;
        $file_parts = array();
        mkdir($store_path);
        $file_name = basename($tmpfile, '.csv');
        
        $store_path .= '/';
        $result = [];
        
        for($i=0;$i<$parts_quantity;$i++){
            //read buffer sized amount from file
            $file_part = fread($file_handle, $buffer);
            //the filename of the part
            $file_part_path = $store_path.$file_name."part$i.csv";
            $result[] = $file_part_path;
            //open the new file [create it] to write
            $file_new = fopen($file_part_path,'w+');
            //write the part of file
            fwrite($file_new, $file_part);
            //close the part file handle
            fclose($file_new);
        }    
        
        //close the main file handle
        fclose($file_handle);

        unlink($tmpfile);
        return $result;
    }
    
    public function parse_csv($sync) {
        $this->load->model('tool/upload');
        $source_file = $this->model_tool_upload->getUploadByCode($_FILES[0]);
        $filename = DIR_UPLOAD . $source_file['filename'];
        //$source_file="/price-list1.csv";
	$sync_id = $sync['sync_id'];
        $tmp_prefix = 'csv_';
        $files = $this->optimizeFileSize($filename, $tmp_prefix);
        foreach($files as $file){
            $sql = "
                LOAD DATA LOCAL INFILE 
                    '$file'
                INTO TABLE 
                    iss_tmp_current_sync
                CHARACTER SET 'utf8'
                FIELDS TERMINATED BY '\;'
                    (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10, 
                     @col11, @col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20, 
                     @col21, @col22,@col23,@col24,@col25,@col26,@col27,@col28)
                SET
                    sync_id = '$sync_id',  
                    category_lvl1 = @col2, 
                    category_lvl2 = @col3,
                    category_lvl3 = @col4,
                    product_name = @col7, 
                    product_name1 = @col8, 
                    model = @col6, 
                    mpn = @col23,
                    leftovers = 1,
                    manufacturer = 'Nilson', 
                    price1 = REPLACE(REPLACE(@col10, ' ', ''), ',', '.'),
                    origin_country = 'Turkey',
                    min_order_size = @col114, 
                    image = CONCAT('/image/catlog/products/',@col6,'.jpg')
                ";
            
            $this->db->query($sql);
        }
        
        if(!$this->validateData()){
            print_r('Неверный порядок полей! Проверьте: Код товара, Артикул, Название товара, Производитель, Количество, Цена');
            die; 
        };
        if($this->removeTempDir($tmp_prefix)){
            return true;
        } else {
            return false;
        }
    }  
    
    public function validateData(){
        $presql = "
            DELETE FROM
                 iss_tmp_current_sync
            WHERE
                manufacturer REGEXP '[0-9\",]+'
            ";
        $this->db->query($presql);
        $sql = "
            SELECT *
            FROM
                iss_tmp_current_sync
            WHERE 
		product_name REGEXP '.+' AND product_name != ''
                AND model REGEXP '[0-9]+' AND model != ''
                AND mpn REGEXP '.+' AND mpn != ''
                AND leftovers NOT REGEXP '[A-Za-zА-Яа-я.-]+' AND leftovers != ''
		AND manufacturer NOT REGEXP '[0-9\",]+' AND manufacturer != ''
                AND price1 NOT REGEXP '[A-Za-zА-Яа-я-]+'  AND price1 != ''
            LIMIT 5
            OFFSET 0
            ";
        if($this->db->query($sql)->num_rows>0){
            return true;
        }
        return false;
    }
    
    public function parse_glem($sync) {
        $source_file="https://glem.com.ua/eshop/xml.php?user=54d71a1bb5b13bb04f18565d4a4bc121";
	$sync_id = $sync['sync_id'];
        $xml=simplexml_load_file($source_file);
        $categories = $xml->shop->categories;
        $product_list = $xml->shop->offers;
        function getPath ($path,$product_category_id, $categories){
            for($i = 0; $i < count($categories->category); $i++ ){
                $category = $categories->category[$i]->attributes();
                if( $product_category_id == (int)$category->id){
                    array_unshift($path,(string)$categories->category[$i]);
                    if (isset($category->parentId[0])){
                        return getPath($path, (int)$category->parentId, $categories);
                    } else {
                        return $path;
                    }
                } 
            }
            return [];
        }
        $path = [];
        foreach ($product_list->offer as $product){
            $product_category_id = (int)$product->categoryId;
            $path = getPath([],$product_category_id, $categories);
            $path[2] = implode('/', array_splice($path, 2 )); 
            $path =  array_splice($path, 0, 3 );
            $product_model = (string)$product->attributes()->id;
            foreach (explode(',', (string)$product->param[0]) as $option){                
                $sql = "
                    INSERT INTO  
                        iss_tmp_current_sync
                    SET
                        sync_id = '$sync_id',
                        is_changed=1,
                        category_lvl1 = '{$path[0]}',    
                        category_lvl2 = '{$path[1]}',      
                        category_lvl3 = '{$path[2]}',      
                        product_name = CONCAT(UCASE(MID('". (string)$product->model."',1,1)),MID('". (string)$product->model."',2),' ".(string)$product->param[1]."'), 
                        model = '".(string)$product->vendor."".$product_model."', 
                        mpn= '".$product_model."',
                        manufacturer = '" . (string)$product->vendor."',  
                        origin_country = '" . (string)$product->country_of_origin."',
                        url = '" . (string)$product->url."', 
                        description = '" . (string)$product->description."', 
                        min_order_size = '', 
                        stock_status='14 дней',
                        stock_count=0,
                        attribute1 = '',
                        attribute2 = '',
                        attribute3 = '',
                        attribute4 = '',
                        attribute5 = '',
                        option1 = '$option', 
                        option2 = '', 
                        option3 = '',  
                        price1 = '".(string)$product->price."', 
                        price2 = '', 
                        price3 = '', 
                        price4 = ''
                    ";
                for ($i = 0; $i < count($product->picture); $i++ )  {
                    if($i > 5){
                        break;
                    }
                    if ( !empty((string)$product->picture[$i]) )  {
                        if($i == 0){
                            $sql .= ", image = '".(string)$product->picture[$i]."'";
                        } else {
                        $sql .= ", image{$i} = '".(string)$product->picture[$i]."'";
                        }                    
                    }
                }       
            $this->db->query($sql);
            }
        }
    } 
    
    public function parse_charutti($sync) {
        $source_file="http://charutti.ru/upload/catalogFull.xml";
	$sync_id = $sync['sync_id'];
        $xml=simplexml_load_file($source_file);
        $categories = $xml->shop->categories;
        $product_list = $xml->shop->offers;
        function charuttiGetPath ($path,$product_category_id, $categories){
            for($i = 0; $i < count($categories->category); $i++ ){
                $category = $categories->category[$i]->attributes();
                if( $product_category_id == (int)$category->id){
                    array_unshift($path,(string)$categories->category[$i]);
                    if (isset($category->parentId[0])){
                        return charuttiGetPath($path, (int)$category->parentId, $categories);
                    } else {
                        return $path;
                    }
                } 
            }
            return [];
        }
        $path = [];
        foreach ($product_list->offer as $product){
            $product_category_id = (int)$product->categoryId;
            $path = charuttiGetPath([],$product_category_id, $categories);
            $product_model = (string)$product->attributes()->id;
            for($i = 0; $i < count($product->param); $i++){
                if ($product->param[$i]->attributes()->name == 'Прочие фотографии'){
                    $product_pictures = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Ткань'){
                    $product_attribute_1 = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Состав'){
                    $product_attribute_2 = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Длина'){
                    $product_subdescription = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Размер'){
                    $product_option = $product->param[$i];
                }
            }
            $additional_description = str_replace(';', '<br>', $product_subdescription);
            foreach (explode('; ',$product_option) as $option){            
                    $sql = "
                        INSERT INTO  
                            iss_tmp_current_sync
                        SET
                            sync_id = '$sync_id',
                            is_changed=1,     
                            product_name = CONCAT(UCASE(MID('". addslashes((string)$product->typePrefix)."',1,1)),MID('". addslashes((string)$product->typePrefix)."',2),' ".addslashes((string)$product->model)."'), 
                            model = '?AR".$product_model."', 
                            mpn= '".$product_model."',
                            manufacturer = CONCAT(UCASE(MID('".(string)$product->vendor."',1,1)),MID('".(string)$product->vendor."',2)),  
                            origin_country = 'Россия',
                            url = '" . (string)$product->url."', 
                            description = '" . (string)$product->description." Длина изделия: <br>".(string)$additional_description."', 
                            min_order_size = '', 
                            stock_status='8-10 дней',
                            stock_count=0,
                            attribute1 = '" . (string)$product_attribute_1."',
                            attribute2 = '" . (string)$product_attribute_2."',
                            attribute3 = '',
                            attribute4 = '',
                            attribute5 = '',
                            image = '".(string)$product->picture."',
                            option1 = '$option', 
                            option2 = '', 
                            option3 = '',  
                            price1 = '".(string)$product->price."', 
                            price2 = '', 
                            price3 = '', 
                            price4 = '',
                            category_lvl1 = '".(!empty($path[0])?$path[0]:'')."',
                            category_lvl2 = '".(!empty($path[1])?$path[1]:'')."',
                            category_lvl3 = '".(!empty($path[2])?$path[2]:'')."'
                        ";
                    $additional_pics = explode(';', $product_pictures);        
                    for ($i = 0; $i < count($additional_pics); $i++ )  {
                        if($i > 5){
                            break;
                        }
                        if ( !empty((string)$additional_pics[$i]) )  {
                            $sql .= ", image".($i + 1)." = '".(string)$additional_pics[$i]."'";                  
                        }
                    }
                    $this->db->query($sql);
            }
        }
    } 
    
    private function copyIsellConfig($sync_id) {
        $isell_config = json_decode(file_get_contents('http://91.210.179.105:2080/public/attribute_config.json'));
        $this->sync_config->attributes = $this->verifyIsellConfig($this->sync_config->attributes, $isell_config->attributes);
        $this->sync_config->filters = $this->verifyIsellConfig($this->sync_config->filters, $isell_config->filters);
        $this->db->query("UPDATE " . DB_PREFIX . "iss_sync_list SET sync_config = '".json_encode($this->sync_config, JSON_UNESCAPED_UNICODE )."' WHERE sync_id='$sync_id'");
        return;
    }
    
    private function verifyIsellConfig($db_config, $isell_config){
        if(empty($db_config)){
            die ('sync_not_found!');
        }
        foreach($db_config as $db_config_key){
            foreach($isell_config as $isell_config_key){
                if($db_config_key->name == $isell_config_key->name){
                    continue;
                }
                array_push($db_config,$isell_config_key);
            }
        }
        return $db_config;
    }
    
    public function addSync($seller_id, $sync_source){
        $sql = "
            INSERT INTO " . DB_PREFIX . "iss_sync_list
                seller_id, sync_source,sync_comission,sync_last_improted
            ON DUPLICATE KEY UPDATE  
                seller_id = $seller_id,
                sync_source = $sync_source,
                sync_comission = '',
                sync_last_improted = ''
            ";
        $this->db->query($sql);
    }


    public function groupEntriesByCategories ($sync_id){
        if( !isset($sync_id) ){
            return;
        }
        $presql = "
            UPDATE " . DB_PREFIX . "iss_sync_groups
            SET total_products = 0 
            WHERE sync_id = '$sync_id'
            ";
        $this->db->query($presql);
        $sql = "
            INSERT INTO
                " . DB_PREFIX . "iss_sync_groups ( sync_id, category_lvl1, category_lvl2, category_lvl3, category_path, total_products )
            SELECT * FROM
                (SELECT 
                    sync_id, category_lvl1, category_lvl2, category_lvl3, CONCAT(category_lvl1,'/',category_lvl2 , '/' , category_lvl3), COUNT(model) AS tp
                FROM 	
                    " . DB_PREFIX . "iss_sync_entries AS bse    
                WHERE bse.sync_id = '$sync_id'
                GROUP BY bse.category_lvl1, bse.category_lvl2, bse.category_lvl3) hello_vasya
            ON DUPLICATE KEY UPDATE  total_products = tp
            ";
        $this->db->query($sql);
        $clear_empty="
            DELETE FROM 
                " . DB_PREFIX . "iss_sync_groups 
            WHERE sync_id='$sync_id' AND total_products=0;
            ";
        $this->db->query($clear_empty);
    }
    
    private function removeTempDir($tmp_prefix){
        $dir = './'.$tmp_prefix.'tmp_files';
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
          (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir); 
    }
}
