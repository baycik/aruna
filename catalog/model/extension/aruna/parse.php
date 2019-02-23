<?php
class ModelExtensionArunaParse extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }
    
    public function initParser($sync_id,$mode='detect_unchanged_entries'){
        set_time_limit(300);
        $sync=$this->db->query("SELECT * FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$sync_id'")->row;
        if( !$sync ){
            return false;
        }
	$sync_id = $sync['sync_id'];
	$this->prepare_parsing($sync_id);
	
        $parser_method='parse_'.$sync['sync_parser_name'];
        $this->$parser_method($sync);
	
	$this->finish_parsing($sync_id,$mode);
	
        $this->db->query("UPDATE " . DB_PREFIX . "baycik_sync_list SET sync_last_started=NOW() WHERE sync_id='{$sync['sync_id']}'");
        return true;
    }
    
    private function prepare_parsing($sync_id){
	$this->db->query("DROP TEMPORARY TABLE IF EXISTS baycik_tmp_previous_sync");#TEMPORARY
	$this->db->query("CREATE TEMPORARY TABLE baycik_tmp_previous_sync AS (SELECT * FROM " . DB_PREFIX . "baycik_sync_entries WHERE sync_id='$sync_id')");

	$this->db->query("DROP TEMPORARY TABLE IF EXISTS baycik_tmp_current_sync");#TEMPORARY
	$this->db->query("CREATE TEMPORARY TABLE baycik_tmp_current_sync LIKE ".DB_PREFIX."baycik_sync_entries");
    }
    private function finish_parsing($sync_id,$mode){
	$clear_previous_sync_sql = "DELETE FROM ".DB_PREFIX."baycik_sync_entries WHERE sync_id = '$sync_id'";
	$this->db->query($clear_previous_sync_sql);
        $fill_entries_table_sql = "
            INSERT INTO 
                ".DB_PREFIX."baycik_sync_entries 
                    (`is_changed`,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` ,  `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10` , `attribute11`,`image` , `image1` , `image2` , `image3` , `image4` , `image5` ,`option_group1`,`price_group1`,`price`)
                SELECT          1,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` ,  `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10`, `attribute11` , `image` , `image1` , `image2` , `image3` , `image4` , `image5`,
                    GROUP_CONCAT(option1 SEPARATOR '|') AS `option_group1`,
                    GROUP_CONCAT(price1 SEPARATOR '|') AS `price_group1`,
                    MIN(price1) AS `price`
                FROM 
                    baycik_tmp_current_sync
                GROUP BY CONCAT(`category_lvl1`,'/',`category_lvl2`,'/',`category_lvl3`), model
                HAVING price>0 AND price IS NOT NULL";
	$this->db->query($fill_entries_table_sql);
        $this->groupEntriesByCategories($sync_id);
        if( $mode=='detect_unchanged_entries' ){
            $change_finder_sql="
                UPDATE
                    ".DB_PREFIX."baycik_sync_entries bse
                        JOIN
                    baycik_tmp_previous_sync bps USING (`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` , `attribute6` , `attribute7` , `attribute8` , `attribute9` , `attribute10`, `attribute11`,`option1` , `option2` , `option3` , `image` , `image1` , `image2` , `image3` , `image4` , `image5` , `price1` , `price2` , `price3` , `price4`)
                SET
                    bse.is_changed=0
                WHERE sync_id='$sync_id'";
            $this->db->query($change_finder_sql);
        }
    }
    
    
    
    private function parse_happywear($sync) {
        $source_file="https://happywear.ru/exchange/xml/price-list.csv";
        //$source_file="/price-list1.csv";
	$tmpfile = './happy_exchange'.rand(0,1000);//tempnam("/tmp", "tmp_");
	if(!copy($source_file, $tmpfile)){
            die("Downloading failed");
        };
	$sync_id = $sync['sync_id'];
	$sql = "
            LOAD DATA LOCAL INFILE 
                '$tmpfile'
            INTO TABLE 
                baycik_tmp_current_sync
            CHARACTER SET 'cp1251'
            FIELDS TERMINATED BY '\;'
                (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22)
            SET
                sync_id = '$sync_id',
                is_changed=1,
                category_lvl1 = @col1,    
                category_lvl2 = @col2,      
                category_lvl3 = '',      
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
	$this->db->query($sql);
	unlink($tmpfile);
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
                        baycik_tmp_current_sync
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
                            baycik_tmp_current_sync
                        SET
                            sync_id = '$sync_id',
                            is_changed=1,     
                            product_name = CONCAT(UCASE(MID('". (string)$product->typePrefix."',1,1)),MID('". (string)$product->typePrefix."',2),' ".(string)$product->model."'), 
                            model = 'ÇAR".$product_model."', 
                            mpn= '".$product_model."',
                            manufacturer = CONCAT(UCASE(MID('".(string)$product->vendor."',1,1)),MID('".(string)$product->vendor."',2)),  
                            origin_country = 'Россия',
                            url = '" . (string)$product->url."', 
                            description = '" . (string)$product->description." Длина изделия: <br>".(string)$additional_description."', 
                            min_order_size = '', 
                            stock_status='14 дней',
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
    
     public function parse_fason($sync) {
        $source_file="https://fason-m.com.ua/upload/yandex_xml/xml_files/fason.xml";
	$sync_id = $sync['sync_id'];
        $xml=simplexml_load_file($source_file);
        $categories = $xml->shop->categories;
        $product_list = $xml->shop->offers;
        $path = [];
        function fasonGetPath ($path,$product_category_id, $categories){
            for($i = 0; $i < count($categories->category); $i++ ){
                $category = $categories->category[$i]->attributes();
                if( $product_category_id == (int)$category->id){
                    array_unshift($path,(string)$categories->category[$i]);
                    if (isset($category->parentId[0])){
                        return fasonGetPath($path, (int)$category->parentId, $categories);
                    } else {
                        return $path;
                    }
                } 
            }
            return [];
        }
        foreach ($product_list->offer as $product){
            $product_category_id = (int)$product->categoryId;
            $path = fasonGetPath([],$product_category_id, $categories);
            $product_model = (string)$product->attributes()->id;
            $product_attribute_1 = '';
            $product_attribute_2 = '';
            $product_attribute_3 = '';
            $product_attribute_4 = '';
            for($i = 0; $i < count($product->param); $i++){
                if ($product->param[$i]->attributes()->name == 'Материал'){
                    $product_attribute_1 = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Цвет'){
                    $product_color = $product->param[$i];    
                } else if ($product->param[$i]->attributes()->name == 'Длина'){
                    $product_attribute_2 = $product->param[$i];
                } else if ($product->param[$i]->attributes()->name == 'Стиль'){
                    $product_attribute_3 = $product->param[$i];    
                } else if ($product->param[$i]->attributes()->name == 'Сезон'){
                    $product_attribute_4 = $product->param[$i];    
                } else if ($product->param[$i]->attributes()->name == 'Размеры'){
                    $product_option = $product->param[$i];
                }
            }
            foreach (explode(', ',$product_option) as $option){            
                $sql = "
                    INSERT INTO  
                        baycik_tmp_current_sync
                    SET
                        sync_id = '$sync_id',
                        is_changed=1,    
                        category_lvl1 = '".(!empty($path[0])?$path[0]:'')."',
                        category_lvl2 = '".(!empty($path[1])?$path[1]:'')."',
                        category_lvl3 = '".(!empty($path[2])?$path[2]:'')."',
                        product_name = '".(string)$product->name." ".$product_color."',
                        model = 'FAS".$product_model." ".$product_color."', 
                        mpn= '".$product_model."',
                        manufacturer = 'Fason',  
                        origin_country = 'Украина',
                        url = '', 
                        description = '" . (string)$product->description."', 
                        min_order_size = '', 
                        stock_status='14 дней',
                        stock_count=0,
                        attribute1 = '" . (string)$product_attribute_1."',
                        attribute2 = '" . (string)$product_attribute_2."',
                        attribute3 = '" . (string)$product_attribute_3."',
                        attribute4 = '" . (string)$product_attribute_4."',
                        attribute5 = '',
                        option1 = '$option', 
                        option2 = '', 
                        option3 = '',  
                        price1 = '".(string)$product->price."', 
                        price2 = '', 
                        price3 = '', 
                        price4 = ''
                    ";
                    for ($i = 0; $i < count($product->picture); $i++) {
                        if ($i > 5) {
                            break;
                        }
                        if (!empty((string) $product->picture[$i])) {
                            if ($i == 0) {
                                $sql .= ", image = '" . (string) $product->picture[$i] . "'";
                            } else {
                                $sql .= ", image{$i} = '" . (string) $product->picture[$i] . "'";
                            }
                        }
                    }
                $this->db->query($sql);
            }
        }
    } 
    
    public function parse_isell($sync) {
        $source_file="http://91.210.179.105:2080/public/isell_export.csv";
        //$source_file="/price-list1.csv";
	$tmpfile = './isell_exchange'.rand(0,1000);//tempnam("/tmp", "tmp_");
	if(!copy($source_file, $tmpfile)){
            die("Downloading failed");
        };
	$sync_id = $sync['sync_id'];
	$sql = "
            LOAD DATA LOCAL INFILE 
                '$tmpfile'
            INTO TABLE 
                baycik_tmp_current_sync
            CHARACTER SET 'cp1251'
            FIELDS TERMINATED BY '\;'
                (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22,@col23,@col24,@col25,@col26,@col27,@col28,@col29,@col30,@col31,@col32)
            SET
                sync_id = '$sync_id',
                is_changed=1,
                category_lvl1 = @col1,    
                category_lvl2 = @col2,      
                category_lvl3 = @col3,      
                product_name = @col5, 
                model = @col4, 
                mpn=@col10,
                manufacturer = @col6,  
                origin_country = @col7,
                url = '', 
                description = '', 
                min_order_size = '1', 
                stock_status=IF(@col8>0,'в наличии','под заказ'),
                stock_count=@col8,
                attribute1 = @col21,
                attribute2 = @col22,
                attribute3 = @col23,
                attribute4 = @col24,
                attribute5 = @col25,
                attribute6 = @col26,
                attribute7 = @col27,
                attribute8 = @col28,
                attribute9 = @col29,
                attribute10 = @col30,
                attribute11 = @col31,
                attribute12 = @col32,
                option1 = '', 
                option2 = '', 
                option3 = '', 
                image = @col16,
                image1 = '', 
                image2 = '', 
                image3 = '', 
                image4 = '', 
                image5 = '', 
                price1 = @col17, 
                price2 = @col18, 
                price3 = '', 
                price4 = ''
            ";
	$this->db->query($sql);
	unlink($tmpfile);
    }
    
    
    public function addSync($seller_id, $sync_source){
        $sql = "
            INSERT INTO " . DB_PREFIX . "baycik_sync_list
                seller_id, sync_source,sync_comission,sync_last_improted
            ON DUPLICATE KEY UPDATE  
                seller_id = $seller_id,
                sync_source = $sync_source,
                sync_comission = '',
                sync_last_improted = ''
            ";
        $this->db->query($sql);
    }


    private function groupEntriesByCategories ($sync_id){
        if( !isset($sync_id) ){
            return;
        }
        $presql = "
            UPDATE " . DB_PREFIX . "baycik_sync_groups
            SET total_products = 0 
            ";
        $this->db->query($presql);
        $sql = "
            INSERT INTO
                " . DB_PREFIX . "baycik_sync_groups ( sync_id, category_lvl1, category_lvl2, category_lvl3, category_path, total_products )
            SELECT * FROM
                (SELECT 
                    sync_id, category_lvl1, category_lvl2, category_lvl3, CONCAT(category_lvl1,'/',category_lvl2 , '/' , category_lvl3), COUNT(model) AS tp
                FROM 	
                    " . DB_PREFIX . "baycik_sync_entries AS bse    
                WHERE bse.sync_id = '$sync_id'
                GROUP BY bse.category_lvl1, bse.category_lvl2, bse.category_lvl3) hello_vasya
            ON DUPLICATE KEY UPDATE  total_products = tp
            ";
        $this->db->query($sql);
        $clear_empty="
            DELETE FROM 
                " . DB_PREFIX . "baycik_sync_groups 
            WHERE sync_id='$sync_id' AND total_products=0;
            ";
        $this->db->query($clear_empty);
    }

    
}
