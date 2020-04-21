<?php

class ModelExtensionModuleISSSupersyncSetup extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }

    private $parser_registry=[
        
        'csv'=>[
            'csv_columns' => ['product_name','product_name1','model','mpn','leftovers','manufacturer','price1','category_lvl1','origin_country','min_order_size','image'],
            'name'=>'Импорт CSV',
            'attributes'=>[],
            'options'=>[],
            'filters'=>[],
            'manufacturer'=>'',
            'product_name_to_language'=>[
                'en-gb'  => 'product_name1',
                'tr-tr' => 'product_name' 
            ]
        ]
    ];
 


    public function addParser($user_id,$parser_id){
        $parser_object=$this->parser_registry[$parser_id];
        $parser_config= json_encode($parser_object, JSON_UNESCAPED_UNICODE);
        $this->db->query("INSERT INTO " . DB_PREFIX . "iss_sync_list SET user_id='$user_id', sync_parser_name='{$parser_id}', sync_name='{$parser_object['name']}',sync_config='$parser_config'");
    }
    public function deleteParser($user_id,$sync_id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "iss_sync_list WHERE sync_id=".(int) $sync_id." AND user_id=".(int)$user_id );
        $this->db->query("DELETE FROM " . DB_PREFIX . "iss_sync_groups WHERE sync_id=".(int) $sync_id );
        $this->db->query("DELETE FROM " . DB_PREFIX . "iss_sync_entries WHERE sync_id=".(int) $sync_id );
    }
    public function updateParserConfig($sync_id){
	$sql="SELECT sync_parser_name FROM " . DB_PREFIX . "iss_sync_list WHERE sync_id='$sync_id'";
	$parser_id=$this->db->query($sql)->row['sync_parser_name'];
	if( $parser_id && $sync_id ){
	    $parser_object=$this->parser_registry[$parser_id];
	    $parser_config= json_encode($parser_object, JSON_UNESCAPED_UNICODE);
	    $this->db->query("UPDATE " . DB_PREFIX . "iss_sync_list SET sync_config='$parser_config' WHERE sync_id='$sync_id'");
	}
    }
    public function getParserList($user_id){
        $added_parsers=$this->getSyncList($user_id);
        $allowed_parsers=[];
        foreach($this->parser_registry as $parser_id=>$available){
            if( isset($available['exclusive_owner']) && !in_array($user_id, $available['exclusive_owner']) ){
                continue;
            }
            foreach($added_parsers as $added){
                if( $added['sync_parser_name']==$parser_id ){
                    continue 2;
                }
            }
            $allowed_parsers[$parser_id]=$available;
        }
        return $allowed_parsers;
    }
    public function getSyncList ($user_id){
        $sql="SELECT * FROM " . DB_PREFIX . "iss_sync_list WHERE user_id='$user_id'";
        return $this->db->query($sql)->rows;
    }


    public function getCategoryList($filter_data) {

	$where = "WHERE sync_id = '{$filter_data['sync_id']}'";
	$order = '';
	$limit = '';

	if (isset($filter_data['filter_name'])) {
	    $where .= " AND category_path LIKE '%{$filter_data['filter_name']}%'";
	}


	if (isset($filter_data['sort'])) {
	    $order = "ORDER BY {$filter_data['sort']} {$filter_data['order']}";
	}

	if (isset($filter_data['start'])) {
	    $limit = "LIMIT {$filter_data['start']} , {$filter_data['limit']}";
	}
	/*
	  if(isset($filter_data['user_id'])){
	  $where .= " AND user_id =  '{$filter_data['user_id']}'";
	  } */
	$sql = "
                SELECT * FROM 
                    " . DB_PREFIX . "iss_sync_groups
                $where
                $order
                $limit
                ";
	$rows = $this->db->query($sql);
	return $rows->rows;
    }

    public function getCategoriesTotal($filter_data) {
	$where = "WHERE sync_id = '{$filter_data['sync_id']}'";
	if ( isset($filter_data['filter_name']) ) {
	    $where .= " AND category_path LIKE '%{$filter_data['filter_name']}%'";
	}
	$sql = "SELECT 
		COUNT(*) AS num 
	    FROM 
               " . DB_PREFIX . "iss_sync_groups  
            $where
            ORDER BY category_lvl1,category_lvl2,category_lvl3";
	$row = $this->db->query($sql);
	return $row->row['num'];
    }
    
    public function saveCategoryPrefs ($data){
        $this->db->query("
            UPDATE " . DB_PREFIX . "iss_sync_entries se
                JOIN " . DB_PREFIX . "iss_sync_groups sg ON 
                    se.category_lvl1 = sg.category_lvl1 
                    AND se.category_lvl2 = sg.category_lvl2 
                    AND se.category_lvl3 = sg.category_lvl3 
            SET 
                se.is_changed = 1
            WHERE
                sg.group_id = ". (int) $data['group_id']);
        $sql = "
            UPDATE 
             " . DB_PREFIX . "iss_sync_groups
            SET
                comission = ". (int) $data['category_comission']. ",
                destination_categories = '". $data['destination_categories']. "'
            WHERE group_id = ". (int) $data['group_id'];
        return $this->db->query($sql);
    } 
    public function validateTable() {
        $table_name = $this->db->escape('iss_sync');

        $table = DB_PREFIX . $table_name;

        $query = $this->db->query("SHOW TABLES LIKE '{$table}%'");

        return !empty($query->num_rows);      
    }
    public function createTables() {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS ". DB_PREFIX . "iss_sync_list (
                `sync_id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `sync_name` varchar(45) DEFAULT NULL,
                `sync_parser_name` varchar(45) DEFAULT NULL,
                `sync_config` text,
                `sync_last_started` datetime DEFAULT NULL,
                PRIMARY KEY (`sync_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS ". DB_PREFIX . "iss_sync_groups (
                `group_id` int(11) NOT NULL AUTO_INCREMENT,
                `sync_id` int(11) DEFAULT NULL,
                `category_path` varchar(602) DEFAULT NULL,
                `category_lvl1` varchar(200) DEFAULT NULL,
                `category_lvl2` varchar(200) DEFAULT NULL,
                `category_lvl3` varchar(200) DEFAULT NULL,
                `total_products` int(11) DEFAULT NULL,
                `comission` int(11) DEFAULT NULL,
                `retail_comission` int(11) DEFAULT NULL,
                `destination_categories` varchar(445) DEFAULT NULL,
                PRIMARY KEY (`group_id`),
                UNIQUE KEY `category_path_UNIQUE` (`category_path`,`sync_id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8"
        );

        $this->db->query(
            "CREATE TABLE IF NOT EXISTS ". DB_PREFIX . "iss_sync_entries (
                `sync_entry_id` int(11) NOT NULL AUTO_INCREMENT,
                `sync_id` int(11) DEFAULT NULL,
                `is_changed` int(3) DEFAULT '1',
                `category_lvl1` varchar(45) DEFAULT NULL,
                `category_lvl2` varchar(45) DEFAULT NULL,
                `category_lvl3` varchar(45) DEFAULT NULL,
                `product_name` varchar(255) DEFAULT NULL,
                `model` varchar(64) DEFAULT NULL,
                `mpn` varchar(45) DEFAULT NULL,
                `url` varchar(512) DEFAULT NULL,
                `description` varchar(2048) DEFAULT NULL,
                `weight` FLOAT DEFAULT NULL, 
                `min_order_size` varchar(45) DEFAULT NULL,
                `leftovers` varchar(45) DEFAULT NULL,
                `stock_count` varchar(45) DEFAULT NULL,
                `stock_status` varchar(45) DEFAULT NULL,
                `manufacturer` varchar(45) DEFAULT NULL,
                `origin_country` varchar(45) DEFAULT NULL,
                `attribute1` varchar(100) DEFAULT NULL,
                `attribute2` varchar(100) DEFAULT NULL,
                `attribute3` varchar(100) DEFAULT NULL,
                `attribute4` varchar(100) DEFAULT NULL,
                `attribute5` varchar(100) DEFAULT NULL,
                `attribute6` varchar(100) DEFAULT NULL,
                `attribute7` varchar(100) DEFAULT NULL,
                `attribute8` varchar(100) DEFAULT NULL,
                `attribute9` varchar(100) DEFAULT NULL,
                `attribute10` varchar(100) DEFAULT NULL,
                `attribute11` varchar(100) DEFAULT NULL,
                `attribute12` varchar(100) DEFAULT NULL,
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
                `option_group1` varchar(512) DEFAULT NULL,
                `price_group1` varchar(512) DEFAULT NULL,
                `attribute_group` text,
                `price` float DEFAULT NULL,
                `product_name1` varchar(225) DEFAULT NULL,
                `product_name2` varchar(225) DEFAULT NULL,
                `product_name3` varchar(225) DEFAULT NULL,
                PRIMARY KEY (`sync_entry_id`),
                KEY `index2` (`category_lvl1`,`category_lvl2`,`category_lvl3`),
                KEY `index3` (`model`)
            ) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8"
        );
    }
}
