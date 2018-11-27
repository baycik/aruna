<?php

class ModelExtensionArunaSetup extends Model {

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

    public function check_get_cat_list($filter_data) {

	$where = "WHERE 1";
	$order = '';
	$limit = '';

	if (isset($filter_data['filter_name'])) {
	    $where .= " AND (category_lvl1 LIKE '%{$filter_data['filter_name']}%' OR category_lvl2 LIKE '%{$filter_data['filter_name']}%' OR category_lvl3 LIKE '%{$filter_data['filter_name']}%')";
	}


	if (isset($filter_data['sort'])) {
	    $order = "ORDER BY {$filter_data['sort']} {$filter_data['order']}";
	}

	if (isset($filter_data['start'])) {
	    $limit = "LIMIT {$filter_data['start']} , {$filter_data['limit']}";
	}
	/*
	  if(isset($filter_data['seller_id'])){
	  $where .= " AND seller_id =  '{$filter_data['seller_id']}'";
	  } */
	$sql = "
                SELECT 
                    category_lvl1,
                    category_lvl2,
                    category_lvl3,
                    COUNT(DISTINCT model) AS products_total
                FROM 
                    " . DB_PREFIX . "baycik_sync_entries
                $where
                GROUP BY CONCAT(category_lvl1, '/', category_lvl2, '/', category_lvl3)  
                $order
                $limit
                ";

	$rows = $this->db->query($sql);
	return $rows->rows;
    }

}
