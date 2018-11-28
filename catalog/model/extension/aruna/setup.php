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
	if(isset($filter_data['seller_id'])){
	  $where .= " AND seller_id =  '{$filter_data['seller_id']}'";
	} */
	$sql = "
                SELECT * FROM 
                    " . DB_PREFIX . "baycik_sync_groups
                $where
                $order
                $limit
                ";
	$rows = $this->db->query($sql);
	return $rows->rows;
    }
    public function getCategoriesTotal ($filter_data){
        $where = "WHERE sync_id = '{$filter_data['sync_id']}'";
        if (isset($filter_data['filter_name'])) {
	    $where .= " AND category_path LIKE '%{$filter_data['filter_name']}%'";
	}

        $sql = "
            SELECT COUNT(*) AS num FROM 
               " . DB_PREFIX . "baycik_sync_groups  
            $where
            ";
        $row = $this->db->query($sql);
        return $row->row['num'];        
    }

}
