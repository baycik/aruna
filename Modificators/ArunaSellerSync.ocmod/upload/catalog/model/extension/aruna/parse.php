<?php

$create_table = "CREATE TABLE `oc_baycik_sync_entries` (
  `sync_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_id` int(11) DEFAULT NULL,
  `category_lvl1` varchar(45) DEFAULT NULL,
  `category_lvl2` varchar(45) DEFAULT NULL,
  `category_lvl3` varchar(45) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `min_order_size` varchar(45) DEFAULT NULL,
  `stock_count` varchar(45) DEFAULT NULL,
  `stock_status` varchar(45) DEFAULT NULL,
  `manufacturer` varchar(45) DEFAULT NULL,
  `origin_country` varchar(45) DEFAULT NULL,
  `attribute1` varchar(100) DEFAULT NULL,
  `attribute2` varchar(100) DEFAULT NULL,
  `attribute3` varchar(100) DEFAULT NULL,
  `attribute4` varchar(100) DEFAULT NULL,
  `attribute5` varchar(100) DEFAULT NULL,
  `option1` varchar(45) DEFAULT NULL,
  `option2` varchar(45) DEFAULT NULL,
  `option3` varchar(45) DEFAULT NULL,
  `image` varchar(512) DEFAULT NULL,
  `image1` varchar(512) DEFAULT NULL,
  `image2` varchar(512) DEFAULT NULL,
  `image3` varchar(512) DEFAULT NULL,
  `image4` varchar(512) DEFAULT NULL,
  `image5` varchar(512) DEFAULT NULL,
  `price1` float DEFAULT NULL,
  `price2` float DEFAULT NULL,
  `price3` float DEFAULT NULL,
  `price4` float DEFAULT NULL,
  PRIMARY KEY (`sync_entry_id`),
  KEY `index2` (`category_lvl1`,`category_lvl2`,`category_lvl3`),
  KEY `index3` (`model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `oc_baycik_sync_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_id` int(11) DEFAULT NULL,
  `category_path` varchar(602) DEFAULT NULL,
  `category_lvl1` varchar(200) DEFAULT NULL,
  `category_lvl2` varchar(200) DEFAULT NULL,
  `category_lvl3` varchar(200) DEFAULT NULL,
  `total_products` int(11) DEFAULT NULL,
  `comission` int(11) DEFAULT NULL,
  `destination_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `category_path_UNIQUE` (`category_path`,`sync_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `oc_baycik_sync_list` (
  `sync_id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) DEFAULT NULL,
  `sync_name` varchar(45) DEFAULT NULL,
  `sync_parser_name` varchar(45) DEFAULT NULL,
  `sync_config` text,
  `sync_last_started` datetime DEFAULT NULL,
  PRIMARY KEY (`sync_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





";

class ModelExtensionArunaParse extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }
    
    public function initParser($sync_id){
        $sync=$this->db->query("SELECT * FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$sync_id'")->row;
        if( !$sync ){
            return false;
        }
        $method='parse_'.$sync['sync_parser_name'];
        $this->$method($sync);
        $this->db->query("UPDATE " . DB_PREFIX . "baycik_sync_list SET sync_last_started=NOW() WHERE sync_id='{$sync['sync_id']}'");
        return true;
    }
    
    public function parse_happywear($sync) {
        set_time_limit(300);
	$tmpfile = './happy_exchange'.rand(0,1000);//tempnam("/tmp", "tmp_");
	if(!copy("https://happywear.ru/exchange/xml/price-list.csv", $tmpfile)){
            die("Downloading failed");
        };
        
	$sync_id = $sync['sync_id'];
        
	$presql = "
            DELETE FROM " . DB_PREFIX . "baycik_sync_entries WHERE sync_id = '$sync_id'
            ";
	$this->db->query($presql);
	$sql = "
            LOAD DATA LOCAL INFILE 
                '$tmpfile'
            INTO TABLE 
                " . DB_PREFIX . "baycik_sync_entries
            CHARACTER SET 'cp1251'
            FIELDS TERMINATED BY '\;'
                (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18)
            SET
                sync_id = '$sync_id',
                category_lvl1 = @col1,    
                category_lvl2 = @col2,      
                category_lvl3 = '',      
                product_name = CONCAT(@col4,' ',@col7,' ',@col5), 
                model = CONCAT(@col3,' ',@col5), 
                manufacturer = @col7,  
                origin_country = @col8,                     
                url = @col10, 
                description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(@col12,'{{emoji_183}}',''),'{{emoji_6}}',''),'{{emoji_9}}',''),'{{emoji_104}}',''),'{{emoji_223}}',''),'{{emoji_55}}',''),'{{emoji_271}}',''),'{{emoji_137}}',''),'{{emoji_147}}',''),'{{emoji_40}}',''),'{{emoji_66}}',''),'{{emoji_284}}',''),'{{emoji_239}}',''),'{{emoji_77}}',''),'{{emoji_129}}',''),'{{emoji_4}}',''), 
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
        $this->db->query("DELETE FROM baycik_aruna.oc_baycik_sync_entries WHERE url NOT LIKE 'http%' OR price1<1");//DELETING defective entries
        $this->groupEntriesByCategories($sync_id);
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
                    sync_id, category_lvl1, category_lvl2, category_lvl3, CONCAT(category_lvl1,'/',category_lvl2 , '/' , category_lvl3), COUNT(DISTINCT(model)) AS tp
                FROM 	
                    " . DB_PREFIX . "baycik_sync_entries AS bse    
                WHERE bse.sync_id = '$sync_id'
                GROUP BY bse.category_lvl1, bse.category_lvl2, bse.category_lvl3) hhh
            ON DUPLICATE KEY UPDATE  total_products = tp
            ";
        $this->db->query($sql);
        $clear_empty="DELETE FROM  " . DB_PREFIX . "baycik_sync_groups  WHERE total_products=0;";
        $this->db->query($clear_empty);
    }
}
