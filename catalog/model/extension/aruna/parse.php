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
  PRIMARY KEY (`sync_entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

class ModelExtensionBaycikParse extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }

    private function load_admin_model($route) {
	$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
	$file = realpath(DIR_APPLICATION . '../admin/model/' . $route . '.php');
	if (is_file($file)) {
	    include_once($file);
	    $modelName = str_replace('/', '', ucwords("Model/" . $route, "/"));
	    $proxy = new $modelName($this->registry);
	    $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
	} else {
	    throw new \Exception('Error: Could not load model ' . $route . '!');
	}
    }

    public function parse_happywear($sync_id, $tmpfile) {
	$presql = "
            DELETE FROM " . DB_PREFIX . "baycik_sync_entries WHERE sync_id = '$sync_id'
            ";
	$this->db->query($presql);
	$sql = "
            LOAD DATA INFILE 
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
                category_lvl3 = @col4,      
                product_name = CONCAT(@col4,' ',@col7,' ',@col5), 
                model = CONCAT(@col3,' ',@col5), 
                manufacturer = @col7,  
                origin_country = @col8,                     
                url = @col10, 
                description = @col12, 
                min_order_size = @col15, 
                attribute1 = @col5,
                attribute2 = @col6,
                attribute3 = '',
                attribute4 = '',
                attribute5 = '',
                option1 = @col9, 
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

}
