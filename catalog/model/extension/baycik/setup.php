<?php

class ModelExtensionBaycikSetup extends Model{
    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id=(int)$this->config->get('config_language_id');
        $this->store_id=(int)$this->config->get('config_store_id');
    }    
    
    private function load_admin_model($route) {
        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
        $file = realpath(DIR_APPLICATION. '../admin/model/' . $route . '.php');
        if (is_file($file)) {
            include_once($file);
            $modelName = str_replace('/', '', ucwords("Model/" . $route, "/"));
            $proxy = new $modelName($this->registry);
            $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
        } else {
            throw new \Exception('Error: Could not load model ' . $route . '!');
        }
    }
    
    public function check_get_cat_list (){
        $sql = "
            SELECT 
                category_lvl1,
                category_lvl2,
                category_lvl3,
                COUNT(DISTINCT model) AS products_total
            FROM 
                ".DB_PREFIX ."baycik_sync_entries
            GROUP BY CONCAT(category_lvl1, '/', category_lvl2, '/', category_lvl3)    
            ";
        $rows = $this->db->query($sql);
        return $rows->rows;
    }
}