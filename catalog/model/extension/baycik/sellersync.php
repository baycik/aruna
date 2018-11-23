<?php
class ModelExtensionBaycikSellersync extends Model{
    public function getSellerSyncSites(){
        
    }
    
    
    
   
    public function parse_happywear ($sync_id, $tmpfile){
        $presql = "
            DELETE FROM ".DB_PREFIX ."baycik_sync_entries WHERE sync_id = '$sync_id'
            ";
        $this->db->query($presql);        
        $sql="
            LOAD DATA INFILE 
                '$tmpfile'
            INTO TABLE 
                ".DB_PREFIX ."baycik_sync_entries
            CHARACTER SET 'cp1251'
            FIELDS TERMINATED BY '\;'
                (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18)
            SET
                sync_id = '$sync_id',
                category_lvl1 = @col1,    
                category_lvl2 = @col2,      
                category_lvl3 = @col4,      
                product_name = '', 
                model = @col3, 
                filter1 = @col5,             
                filter2 = @col6,             
                manufacturer = @col7,  
                origin_country = @col8,                     
                option1 = @col9, 
                option2 = '', 
                option3 = '', 
                url = @col10, 
                image = @col11, 
                description = @col12, 
                min_order_size = @col15, 
                price1 = @col13, 
                price2 = @col7, 
                price3 = @col18, 
                price4 = ''
            ";
        $this->db->query($sql); 
        unlink($tmpfile);
}
    
    
    public function check_tables (){
        
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
    
    public function importCategories ($data){
        $sql = "
            SELECT 
                bse.*,
                product_id,
                GROUP_CONCAT(option1 SEPARATOR ',') AS option_group1,
                GROUP_CONCAT(price1 SEPARATOR ',') AS price_group1,
                MIN(price1) AS min_price
            FROM
                ".DB_PREFIX ."baycik_sync_entries AS bse
                LEFT JOIN 
                ".DB_PREFIX ."product USING(model)
            WHERE
                    category_lvl1 = '$data->category_lvl1'
                    AND category_lvl2 = '$data->category_lvl2'
                    AND category_lvl3 = '$data->category_lvl3'
            GROUP BY model
            ";
        $rows = $this->db->query($sql);
        foreach ($rows->rows as $row){
            if($row['product_id']){
               $this->importProductUpdate($this->composeProductObject($row, $data->destination_category)); 
            } else {
               $this->importProductAdd($this->composeProductObject($row, $data->destination_category)); 
            }
            
        }
    }
    
    private function composeProductObject($row,$dest_category){
        $options_option_group = explode(',', $row['option_group1']);
        $options_price_group = explode(',', $row['price_group1']);
        
        
        $product_option_value=[];
        foreach ($options_option_group as $i=>$option){
            
            $product_option_value[]=[
                    'product_option_value_id' => '',
                    'option_value_id' => '48',
                    'quantity' => '0',
                    'subtract' => '0',
                    'price' => $options_price_group[$i]- $row['min_price'],
                    'price_prefix' => '+',
                    'points' => 0,
                    'points_prefix' => '+',
                    'weight' => 0.00000000,
                    'weight_prefix' => '+'
                ];
        }
        
        $product_description = array(
            [],
            [],
            [ 
            'name'=> $row['category_lvl3'].' '.$row['manufacturer'].' '.$row['filter1'].' '.$row['filter2'],
            'description'=> $row['description'],
            'meta_title'=> $row['category_lvl3'].' '.$row['manufacturer'],
            'meta_description'=> '',
            'meta_keyword'=> '',
            'tag'=> '',
            ]   
        );
        $product_option = array(
            [
            'product_option_id' => '',
            'product_option_value' => array(
                array(
                    'product_option_value_id' => '',
                    'option_value_id' => '48',
                    'quantity' => '0',
                    'subtract' => '0',
                    'price' => $options_price_group[2]- $row['min_price'],
                    'price_prefix' => '+',
                    'points' => 0,
                    'points_prefix' => '+',
                    'weight' => 0.00000000,
                    'weight_prefix' => '+'
                ),
                array(
                    'product_option_value_id' => '',
                    'option_value_id' => '47',
                    'quantity' => '0',
                    'subtract' => '0',
                    'price' => $options_price_group[1]-$row['min_price'],
                    'price_prefix' => '+',
                    'points' => 0,
                    'points_prefix' => '+',
                    'weight' => 0.00000000,
                    'weight_prefix' => '+'
                ),
                array(
                    'product_option_value_id' => '',
                    'option_value_id' => '46',
                    'quantity' => '0',
                    'subtract' => '0',
                    'price' => $options_price_group[0] - $row['min_price'],
                    'price_prefix' => '+',
                    'points' => 0,
                    'points_prefix' => '+',
                    'weight' => 0.00000000,
                    'weight_prefix' => '+'
                )
            ),
            'option_id' => '11',
            'name' => 'Size',
            'type' => 'select',
            'value' => '',
            'required' => 1
            ]    
        );
        $product_attribute = array(
            
        );
        $row['price'] = $row['min_price'];
        $row['product_description'] = $product_description;
        $row['product_attribute'] = array( 'attribute1' => $row['filter1'], 'attribute2' => $row['filter2']);
        $row['product_option'] = $product_option;
        $row['product_category'] = [$dest_category];
        $row['product_image'] = array(
            [
            'product_image_id' => '',
            'product_id' => '',
            'image' => $row['image'],
            'sort_order' => 0
             ]   
        );
        $row['quantity'] = 1;
        $row['stock_status_id'] = 5;
        $row['store_id'] = 0;
        $row['product_store'] = array(0);
        $row['status'] = 1;
        $row['viewed'] = 1;
        return $row;
    }
    
    private function importRouteProduct($item,$seller_id) {
        
    }
    
    public function importProductAdd($item) {
        $this->load_admin_model('catalog/product');
        return $this->model_catalog_product->addProduct($item);
    }
    
    public function importProductUpdate($item) {
       $this->load_admin_model('catalog/product');
       return $this->model_catalog_product->editProduct($item['product_id'], $item);
    }
    
    public function importProductClean($data) {
        
    }
    
    
    protected function load_admin_model($route) {
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
    
    public function insert_parsed_row1 ($sync_id, $row){
        $sql = "
            INSERT INTO 
                ".DB_PREFIX ."baycik_sync_entries
            SET
                sync_id = '$sync_id',
                category_lvl1 = '{$row['category_lvl1']}',    
                category_lvl2 = '{$row['category_lvl2']}',      
                category_lvl3 = '{$row['category_lvl3']}',      
                product_name = '{$row['product_name']}', 
                model = '{$row['model']}', 
                filter1 = '{$row['filter1']}',             
                filter2 = '{$row['filter2']}',             
                manufacturer = '{$row['manufacturer']}',  
                origin_country = '{$row['origin_country']}',                     
                option1 = '{$row['option1']}', 
                option2 = '{$row['option2']}', 
                option3 = '{$row['option3']}', 
                url = '{$row['url']}', 
                img = '{$row['img']}', 
                description = '{$row['description']}', 
                min_order_size = '{$row['min_order_size']}', 
                price1 = '{$row['price1']}', 
                price2 = '{$row['price2']}', 
                price3 = '{$row['price3']}', 
                price4 = '{$row['price4']}'
            ";
                
        $this->db->query($sql); 
    }
}