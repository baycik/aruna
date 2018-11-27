<?php

class ModelExtensionArunaImport extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }

    private $globalSyncConfig = [
	'attributes' => [
	    [
		'attribute_group_id' => 8,
		'field' => 'origin_country'
	    ],
	    [
		'attribute_group_id' => 7,
		'field' => 'attribute1'
	    ],
	    [
		'attribute_group_id' => 9,
		'field' => 'attribute2'
	    ]
	],
	'options' => [
	    [
		'option_id' => 13,
		'option_type' => 'radio',
		'value_group_field' => 'option_group1',
		'price_group_field' => 'price_group1',
		'price_base_field' => 'price'
	    ]
	]
    ];

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

    public function check_get_cat_list() {
	$sql = "
            SELECT 
                category_lvl1,
                category_lvl2,
                category_lvl3,
                COUNT(DISTINCT model) AS products_total
            FROM 
                " . DB_PREFIX . "baycik_sync_entries
            GROUP BY CONCAT(category_lvl1, '/', category_lvl2, '/', category_lvl3)    
            ";
	$rows = $this->db->query($sql);
	return $rows->rows;
    }

    public function importCategories($data) {
	$sql = "
            SELECT 
                bse.*,
                product_id,
                GROUP_CONCAT(option1 SEPARATOR '|') AS option_group1,
                GROUP_CONCAT(price1 SEPARATOR '|') AS price_group1,
                MIN(price1) AS price
            FROM
                " . DB_PREFIX . "baycik_sync_entries AS bse
                LEFT JOIN 
                " . DB_PREFIX . "product USING(model)
            WHERE
                category_lvl1 = '$data->category_lvl1'
                AND category_lvl2 = '$data->category_lvl2'
                AND category_lvl3 = '$data->category_lvl3'
            GROUP BY model
            ";
	$rows = $this->db->query($sql);
	foreach ($rows->rows as $row) {
	    $product = $this->composeProductObject($row, $data->category_comission, $data->destination_category_id);
	    if ($row['product_id']) {
		$product['product_id'] = $row['product_id'];
		$this->importProductUpdate($product); //is this right???
	    } else {

		$this->importProductAdd($product);
	    }
	}
	$this->reorderOptions();
	return true;
    }

    private function composeProductImageObject($row) {
	return $product_image = [
	    [
		'product_image_id' => '',
		'product_id' => '',
		'image' => $row['image1'],
		'sort_order' => '1'
	    ],
	    [
		'product_image_id' => '',
		'product_id' => '',
		'image' => $row['image2'],
		'sort_order' => '2'
	    ],
	    [
		'product_image_id' => '',
		'product_id' => '',
		'image' => $row['image3'],
		'sort_order' => '3'
	    ],
	    [
		'product_image_id' => '',
		'product_id' => '',
		'image' => $row['image4'],
		'sort_order' => '4'
	    ],
	    [
		'product_image_id' => '',
		'product_id' => '',
		'image' => $row['image5'],
		'sort_order' => '5'
	    ]
	];
    }

    private $optionsCache = [];

    private function getProductOption($option_id, $option_type, $option_value, $price = 0, $option_price = '', $category_comission = 0) {
	$product_option = [
	    'option_id' => $option_id,
	    'product_option_id' => '',
	    'product_option_value' => '',
	    'type' => $option_type,
	    'value' => '',
	    'required' => 1
	];
	if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
	    $option_value = explode('|', $option_value);
	    $option_prices = explode('|', $option_price);
	    $product_option_values = [];
	    foreach ($option_value as $i => $value) {
		if (!isset($this->optionsCache[$value])) {
		    $sql = "SELECT *
                    FROM
                        " . DB_PREFIX . "option_value_description ovd
                    WHERE 
                        option_id='$option_id' 
                        AND ovd.name='$value' 
                        AND ovd.language_id='$this->language_id' 
                    LIMIT 1";
		    $existing_option = $this->db->query($sql)->row;
		    if (!$existing_option) {
			$sql = "INSERT INTO 
                                " . DB_PREFIX . "option_value
                            SET
                                option_id='$option_id',
                                sort_order='$i'
                            ";
			$this->db->query($sql);
			$option_value_id = $this->db->getLastId();
			$sql = "INSERT INTO 
                                " . DB_PREFIX . "option_value_description
                            SET
                                option_value_id='$option_value_id',
                                option_id='$option_id',
                                name='$value',
                                language_id='$this->language_id' 
                            ";
			$this->db->query($sql);
			$existing_option = [
			    'option_value_id' => $option_value_id
			];
		    }
		    $this->optionsCache[$value] = $existing_option;
		}
		if (!$this->optionsCache[$value]) {
		    continue;
		}
		$product_option_values[] = [
		    'product_option_value_id' => '',
		    'option_value_id' => $this->optionsCache[$value]['option_value_id'],
		    'quantity' => '0',
		    'subtract' => '0',
		    'price' => round(($option_prices[$i] - $price) * $category_comission, 2),
		    'price_prefix' => '+',
		    'points' => 0,
		    'points_prefix' => '+',
		    'weight' => 0.00000000,
		    'weight_prefix' => '+'
		];
	    }
	    $product_option['product_option_value'] = $product_option_values;
	} else {
	    $product_option['value'] = $option_value;
	}
	return $product_option;
    }

    private function composeProductOptionsObject($row, $category_comission) {
	$product_options = [];
	if ($this->globalSyncConfig['options']) {
	    foreach ($this->globalSyncConfig['options'] as $optionConfig) {
		$option_price = $row[$optionConfig['price_group_field']];
		$option_value = $row[$optionConfig['value_group_field']];
		$price = $row[$optionConfig['price_base_field']];
		$product_options[] = $this->getProductOption($optionConfig['option_id'], $optionConfig['option_type'], $option_value, $price, $option_price, $category_comission);
	    }
	}
	return $product_options;
    }

    private $manufacturerCache = [];

    private function composeProductManufacturer($manufacturer_name) {
	if (!$manufacturer_name) {
	    return 0;
	}
	if (isset($this->manufacturerCache[$manufacturer_name])) {
	    return $this->manufacturerCache[$manufacturer_name];
	}
	$this->load_admin_model('catalog/manufacturer');

	$search_data = ['filter_name' => $manufacturer_name, 'limit' => 1, 'start' => 0];
	$manufacturer = $this->model_catalog_manufacturer->getManufacturers($search_data);
	if ($manufacturer && isset($manufacturer[0])) {
	    $this->manufacturerCache[$manufacturer_name] = $manufacturer[0]['manufacturer_id'];
	    return $this->manufacturerCache[$manufacturer_name];
	}

	$data = [
	    'name' => $manufacturer_name,
	    'sort_order' => 1,
	    'manufacturer_store' => [$this->store_id],
	    'manufacturer_seo_url' => [
		$this->store_id => [
		    $this->language_id => [$manufacturer_name]
		]
	    ]
	];
	$this->manufacturerCache[$manufacturer_name] = $this->model_catalog_manufacturer->addManufacturer($data);
	return $this->manufacturerCache[$manufacturer_name];
    }

    private $attributeCache = [];

    private function getProductAttributeId($attribute_name, $attribute_group_id) {
	if (!$attribute_name) {
	    return 0;
	}
	if (isset($this->attributeCache[$attribute_name])) {
	    return $this->attributeCache[$attribute_name];
	}
	$this->load_admin_model('catalog/attribute');

	$search_data = ['filter_name' => $attribute_name, 'limit' => 1, 'start' => 0];
	$attribute = $this->model_catalog_attribute->getAttributes($search_data);
	if ($attribute && isset($attribute[0])) {
	    $this->attributeCache[$attribute_name] = $attribute[0]['attribute_id'];
	    return $this->attributeCache[$attribute_name];
	}

	$newattribute = [
	    'attribute_group_id' => $attribute_group_id,
	    'sort_order' => 1,
	    'attribute_description' => [
		$this->language_id => [
		    'name' => $attribute_name
		]
	    ]
	];
	$this->attributeCache[$attribute_name] = $this->model_catalog_attribute->addAttribute($newattribute);
	return $this->attributeCache[$attribute_name];
    }

    private function composeProductAttributeObject($row) {
	$product_attribute = [];
	if ($this->globalSyncConfig['attributes']) {
	    foreach ($this->globalSyncConfig['attributes'] as $attributeConfig) {
		$attribute_name = $row[$attributeConfig['field']];
		$product_attribute[] = [
		    'attribute_id' => $this->getProductAttributeId($attribute_name, $attributeConfig['attribute_group_id']),
		    'product_attribute_description' => [
			$this->language_id => [
			    'text' => $attribute_name
			]
		    ]
		];
	    }
	}
	return $product_attribute;
    }

    private function composeProductCategory($destination_category_id) {
	$query = $this->db->query("
                SELECT path_id AS category
                FROM " . DB_PREFIX . "category_path
                WHERE category_id = '" . (int) $destination_category_id . "'");
	$categories = array();
	foreach ($query->rows as $row) {
	    array_push($categories, $row['category']);
	}
	return $categories;
    }

    private function composeProductObject($row, $category_comission, $destination_category_id) {
	////////////////////////////////
	//DESCRIPTION SECTION
	////////////////////////////////
	$product_description = [
	    $this->language_id => [
		'name' => $row['product_name'],
		'description' => $row['description'],
		'meta_title' => $row['category_lvl3'] . ' ' . $row['manufacturer'],
		'meta_description' => $row['description'],
		'meta_keyword' => $row['product_name'],
		'tag' => '',
	    ]
	];
	////////////////////////////////
	//COMPOSING SECTION
	////////////////////////////////
	$product = [
	    'model' => $row['model'],
	    'sku' => '',
	    'upc' => '',
	    'ean' => '',
	    'jan' => '',
	    'isbn' => '',
	    'mpn' => '',
	    'location' => $row['origin_country'],
	    'minimum' => 0,
	    'subtract' => '',
	    'date_available' => '',
	    'price' => round($row['price'] * $category_comission, 2),
	    'points' => 0,
	    'weight' => 0,
	    'weight_class_id' => 0,
	    'length' => 0,
	    'width' => 0,
	    'height' => 0,
	    'length_class_id' => 0,
	    'tax_class_id' => 0,
	    'sort_order' => 1,
	    'name' => $row['product_name'],
	    'image' => $row['image'],
	    'manufacturer_id' => $this->composeProductManufacturer($row['manufacturer']),
	    'product_image' => $this->composeProductImageObject($row),
	    'product_attribute' => $this->composeProductAttributeObject($row),
	    'product_category' => $this->composeProductCategory($destination_category_id),
	    'product_option' => $this->composeProductOptionsObject($row, $category_comission),
	    'product_description' => $product_description,
	    'shipping' => 1,
	    'quantity' => 1,
	    'stock_status_id' => 5,
	    'product_store' => [$this->store_id],
	    'status' => 1
	];
	return $product;
    }

    public function importProductAdd($item) {
	$this->load_admin_model('catalog/product');
	$product_id = $this->model_catalog_product->addProduct($item);
	$sql = "
            INSERT INTO
                " . DB_PREFIX . "purpletree_vendor_products
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

    private function reorderOptions() {
	$this->db->query("SET @i:=0;");
	$sql = "
            UPDATE
                 " . DB_PREFIX . "option_value
            SET sort_order=@i:=@i+1
            WHERE option_value_id IN (SELECT option_value_id FROM
            " . DB_PREFIX . "option_value_description ORDER BY name)
            ";
	$this->db->query($sql);
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

}
