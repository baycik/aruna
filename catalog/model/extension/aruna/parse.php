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
                    (`is_changed`,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` , `image` , `image1` , `image2` , `image3` , `image4` , `image5` ,`option_group1`,`price_group1`,`price`)
                SELECT          1,`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `mpn`, `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` , `image` , `image1` , `image2` , `image3` , `image4` , `image5`,
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
                    baycik_tmp_previous_sync bps USING (`sync_id` , `category_lvl1` , `category_lvl2` , `category_lvl3` , `product_name` , `model` , `url` , `description` , `min_order_size` , `stock_count` , `stock_status` , `manufacturer` , `origin_country` , `attribute1` , `attribute2` , `attribute3` , `attribute4` , `attribute5` , `option1` , `option2` , `option3` , `image` , `image1` , `image2` , `image3` , `image4` , `image5` , `price1` , `price2` , `price3` , `price4`)
                SET
                    bse.is_changed=0
                WHERE sync_id='$sync_id'";
            $this->db->query($change_finder_sql);
        }
    }
    
    
    
    private function parse_happywear($sync) {
        $source_file="https://happywear.ru/exchange/xml/price-list1.csv";
        $source_file="/price-list1.csv";
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
            WHERE total_products=0;
            ";
        $this->db->query($clear_empty);
    }

    
}
