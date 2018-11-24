<?php
class ModelExtensionBaycikSellersync extends Model{
    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id=(int)$this->config->get('config_language_id');
    }    
    
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
                product_name = CONCAT(@col4,' ',@col7,' ',@col5), 
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
                GROUP_CONCAT(option1 SEPARATOR '|') AS option_group1,
                GROUP_CONCAT(price1 SEPARATOR '|') AS price_group1,
                MIN(price1) AS price
            FROM
                ".DB_PREFIX ."baycik_sync_entries AS bse
                LEFT JOIN 
                ".DB_PREFIX ."product USING(model)
            WHERE
                    category_lvl1 = '$data->category_lvl1'
                    AND category_lvl2 = '$data->category_lvl2'
                    AND category_lvl3 = '$data->category_lvl3'
            GROUP BY model
	    
	    LIMIT 5
            ";
        $rows = $this->db->query($sql);
        foreach ($rows->rows as $row){
	    $product=$this->composeProductObject($row,$data->category_comission, $data->destination_category_id);
            if($row['product_id']){
		$product['product_id']=$row['product_id'];
		$this->importProductUpdate($product); //is this right???
            } else {
               $this->importProductAdd($product); 
            }
        }
	return true;
    }
    
    
    private $optionsCache=[];
    private function composeProductOptionsObject($option_id,$option_type,$option_value,$price=0,$option_price='',$category_comission=0){
        $product_option = [
            'option_id' => $option_id,
            'product_option_id' => '',
            'product_option_value' => '',
            'type' => $option_type,
            'value' => '',
            'required' => 1   
        ];
	if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image'){
	    $option_value = explode('|', $option_value);
	    $option_prices = explode('|', $option_price);
	    $product_option_values=[];
	    foreach ($option_value as $i=>$value){
		if( !isset($this->optionsCache[$value]) ){
		    $sql="SELECT *
			FROM
			    ".DB_PREFIX ."option_value_description ovd
			WHERE 
			    option_id='$option_id' 
			    AND ovd.name='$value' 
			    AND ovd.language_id='$this->language_id' 
			LIMIT 1";
		    $option_query=$this->db->query($sql);
		    $this->optionsCache[$value]=$option_query->row;
		}
		if( !$this->optionsCache[$value] ){
		    continue;
		}
		$product_option_values[]=[
		    'product_option_value_id' => '',
		    'option_value_id' => $this->optionsCache[$value]['option_value_id'],
		    'quantity' => '0',
		    'subtract' => '0',
		    'price' => round( ($option_prices[$i]- $price)*$category_comission,2),
		    'price_prefix' => '+',
		    'points' => 0,
		    'points_prefix' => '+',
		    'weight' => 0.00000000,
		    'weight_prefix' => '+'
		];
	    }
	    $product_option['product_option_value']=$product_option_values;
	} else {
	    $product_option['value']=$option_value;
	}
	return $product_option;
    }
    private function composeProductObject($row,$category_comission,$destination_category_id){
	////////////////////////////////
	//OPTIONS SECTION
	////////////////////////////////
	
	//especially for happywear
	$option_id=11;//'Размер'
	$option_type='select';
	$product_option=$this->composeProductOptionsObject($option_id,$option_type,$row['option_group1'],$row['price'],$row['price_group1'],$category_comission);
	////////////////////////////////
	//DESCRIPTION SECTION
	////////////////////////////////
        $product_description = [
	    $this->language_id=>[ 
            'name'=> $row['product_name'],
            'description'=> $row['description'],
            'meta_title'=> $row['category_lvl3'].' '.$row['manufacturer'],
            'meta_description'=> $row['description'],
            'meta_keyword'=> $row['product_name'],
            'tag'=> '',
            ]   
        ];
	////////////////////////////////
	//ATTRIBUTE SECTION
	////////////////////////////////
	
	
	//TO DO attributes as options
        $product_attribute = [];
	////////////////////////////////
	//COMPOSING SECTION
	////////////////////////////////
	$product=[
	    'model'=>$row['model'],
	    'sku'=>'',
	    'upc'=>'',
	    'ean'=>'',
	    'jan'=>'',
	    'isbn'=>'',
	    'mpn'=>'',
	    'location'=>'',
	    'minimum'=>0,
	    'subtract'=>'',
	    'date_available'=>'',
	    'manufacturer_id'=>'',
	    'price'=>round($row['price']*$category_comission,2),
	    'points'=>0,
	    'weight'=>0,
	    'weight_class_id'=>0,
	    'length'=>0,
	    'width'=>0,
	    'height'=>0,
	    'length_class_id'=>0,
	    'tax_class_id'=>0,
	    'sort_order'=>1,
	    'name'=>$row['product_name'],
	    'image'=>$row['image'],
	    'product_description'=>$product_description,
	    'product_attribute'=>$product_attribute,
	    'product_option'=>[$product_option],
	    'product_category'=>[$destination_category_id],
	    'shipping'=>1,
	    'quantity'=>1,
	    'stock_status_id'=>5,
	    'store_id'=>0,
	    'status'=>1
	];
        return $product;
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