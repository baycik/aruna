<?php
$create_table="CREATE TABLE `oc_baycik_sync_entries` (
  `sync_entry_id` INT NOT NULL AUTO_INCREMENT,
  `sync_id` INT NULL,
  `category_lvl1` VARCHAR(45) NULL,
  `category_lvl2` VARCHAR(45) NULL,
  `category_lvl3` VARCHAR(45) NULL,
  `product_name` VARCHAR(255) NULL,
  `model` VARCHAR(64) NULL,
  `filter1` VARCHAR(100) NULL,
  `filter2` VARCHAR(100) NULL,
  `manufacturer` VARCHAR(45) NULL,
  `origin_country` VARCHAR(45) NULL,
  `option1` VARCHAR(45) NULL,
  `option2` VARCHAR(45) NULL,
  `option3` VARCHAR(45) NULL,
  `url` VARCHAR(512) NULL,
  `image` VARCHAR(512) NULL,
  `description` VARCHAR(2048) NULL,
  `min_order_size` VARCHAR(45) NULL,
  `price1` FLOAT NULL,
  `price2` FLOAT NULL,
  `price3` FLOAT NULL,
  `price4` FLOAT NULL,
  PRIMARY KEY (`sync_entry_id`))
ENGINE = MyISAM;
";





class ModelExtensionBaycikSellersync extends Model{
    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id=(int)$this->config->get('config_language_id');
        $this->store_id=(int)$this->config->get('config_store_id');
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
                model = CONCAT(@col3,' ',@col5), 
                filter1 = @col5,             
                filter2 = @col6,             
                manufacturer = @col7,  
                origin_country = @col8,                     
                option1 = @col9, 
                option2 = '', 
                option3 = '', 
                url = @col10, 
                image = @col11,
                image_2 = REPLACE(@col11,'_front','_1'), 
                image_3 = REPLACE(@col11,'_front','_2'), 
                image_4 = REPLACE(@col11,'_front','_3'), 
                image_5 = REPLACE(@col11,'_front','_4'), 
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
            LIMIT 4
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
        //$this->reorderOptions();
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
                    $existing_option=$this->db->query($sql)->row;
                    if( !$existing_option ){
                        $sql="INSERT INTO 
                                ".DB_PREFIX ."option_value
                            SET
                                option_id='$option_id',
                                sort_order='$i'
                            ";
                        $this->db->query($sql);
                        $option_value_id=$this->db->getLastId();
                        $sql="INSERT INTO 
                                ".DB_PREFIX ."option_value_description
                            SET
                                option_value_id='$option_value_id',
                                option_id='$option_id',
                                name='$value',
                                language_id='$this->language_id' 
                            ";
                        $this->db->query($sql);
                        $existing_option=[
                            'option_value_id'=>$option_value_id
                        ];
                    }
                    $this->optionsCache[$value]= $existing_option;
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
    
    private $manufacturerCache=[];
    private function composeProductManufacturer($manufacturer_name){
        if( !$manufacturer_name ){
            return 0;
        }
	if( isset($this->manufacturerCache[$manufacturer_name]) ){
            return $this->manufacturerCache[$manufacturer_name];
        }
        $this->load_admin_model('catalog/manufacturer');
        
        $search_data=['filter_name'=>$manufacturer_name,'limit'=>1,'start'=>0];
        $manufacturer=$this->model_catalog_manufacturer->getManufacturers($search_data);
        if( $manufacturer && isset($manufacturer[0]) ){
            $this->manufacturerCache[$manufacturer_name]=$manufacturer[0]['manufacturer_id'];
            return $this->manufacturerCache[$manufacturer_name];
        }
        
        $data=[
            'name'=>$manufacturer_name,
            'sort_order'=>1,
            'manufacturer_store'=>[$this->store_id],
            'manufacturer_seo_url'=>[
                $this->store_id=>[
                    $this->language_id=>[$manufacturer_name]
                ]
            ]
        ];
        $this->manufacturerCache[$manufacturer_name]=$this->model_catalog_manufacturer->addManufacturer($data);
        return $this->manufacturerCache[$manufacturer_name];
    }
    
    private function composeProductImageObject($row){
        return $product_image = 
            [
                [
                    'product_image_id' => '',
                    'product_id' => '',
                    'image' => $row['image_2'],
                    'sort_order' => '1'
                ],
                [
                    'product_image_id' => '',
                    'product_id' => '',
                    'image' => $row['image_3'],
                    'sort_order' => '2'
                ],
                [
                    'product_image_id' => '',
                    'product_id' => '',
                    'image' => $row['image_4'],
                    'sort_order' => '3'
                ],
                [
                    'product_image_id' => '',
                    'product_id' => '',
                    'image' => $row['image_5'],
                    'sort_order' => '4'
                ]
            ];
    }
    
    
    private $attributeCache=[];
    private function composeProductAttributeObject($attribute_id,$attribute_name){
       if( !$attribute_name ){
            return 0;
        }
	if( !isset($this->attributeCache[$attribute_id]) ){
	    $this->attributeCache[$attribute_id]=[
		'attribute_id'=>$attribute_id,
		'product_attribute_description'=>[
		    $this->language_id=>[
			'text'=>$attribute_name
		    ]
		]
	    ];
        }
	return $this->attributeCache[$attribute_id];	
    }
    
    
    
    private function composeProductObject($row,$category_comission,$destination_category_id){
	////////////////////////////////
	//OPTIONS SECTION
	////////////////////////////////
	
	//especially for happywear
	$option_id=13;//'Размер'
	$option_type='radio';
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
	//MANUFACTURER SECTION
	////////////////////////////////
        $manufacturer_id=$this->composeProductManufacturer($row['manufacturer']);
	////////////////////////////////
	//ATTRIBUTE SECTION
	////////////////////////////////
	
	
	//TO DO attributes as options
        $product_attribute = [];
	//especially for happywear
	$attribute_id=12;//'Страна'
	$attribute_text=$row['origin_country'];
	$product_attribute[]=$this->composeProductAttributeObject($attribute_id,$attribute_text);
        
	$attribute_id=12;//'Страна'
	$attribute_text=$row['origin_country'];
	$product_attribute[]=$this->composeProductAttributeObject($attribute_id,$attribute_text);

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
	    'location'=>$row['origin_country'],
	    'minimum'=>0,
	    'subtract'=>'',
	    'date_available'=>'',
	    'manufacturer_id'=>$manufacturer_id,
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
            'product_image'=>$this->composeProductImageObject($row),
	    'product_description'=>$product_description,
	    'product_attribute'=>$product_attribute,
	    'product_option'=>[$product_option],
	    'product_category'=>[$destination_category_id],
	    'shipping'=>1,
	    'quantity'=>1,
	    'stock_status_id'=>5,
	    'product_store'=>[$this->store_id],
	    'status'=>1
	];
        return $product;
    }
    
    
    
    
    
    private function importRouteProduct($item,$seller_id) {
        
    }
    
    
    public function importProductAdd($item) {
        $this->load_admin_model('catalog/product');
        $product_id = $this->model_catalog_product->addProduct($item);
        $sql="
            INSERT INTO
                ".DB_PREFIX ."purpletree_vendor_products
            SET
                id = '',
                seller_id = '2',
                product_id = '$product_id',
                is_approved = '0',
                created_at = NOW(),
                updated_at = NOW()
            ";
         return $this->db->query($sql);
    }
    
    public function importProductUpdate($item) {
       $this->load_admin_model('catalog/product');
       return $this->model_catalog_product->editProduct($item['product_id'], $item);
    }
    
    public function importProductClean($data) {
        
    }
    
    public function reorderOptions (){
        $sql = "
            SET @i:=0;

            UPDATE
                 oc_option_value
            SET sort_order=@i:=@i+1
            WHERE option_value_id IN (SELECT option_value_id FROM
            oc_option_value_description ORDER BY oc_option_value_description.name)
            ";
        $this->db->query($sql); 
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