<?php

class ModelExtensionArunaImport extends Model {
    private $sync_id;
    private $meta_description_prefix = "Купить в Симферополе ";
    private $meta_keyword_prefix = "Крым,Симферополь,купить,";
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->language_id = (int) $this->config->get('config_language_id');
        $this->store_id = (int) $this->config->get('config_store_id');
        $this->start = microtime(1);
        
        
        $this->load->model('extension/aruna/product');
        
        
        
    }

    private function profile($msg) {
        echo "\n $msg " . round(microtime(1) - $this->start, 5);
    }

    public function importSellerProduct($seller_id, $sync_id, $group_id = null) {
        $this->seller_id = $seller_id;
        $this->sync_id = $sync_id;
        $group_filter = '';
        if ($group_id) {
            $group_filter = "AND group_id = '$group_id'"; //if group_id is defined there will be only one row!
        }
        $sql = "
            SELECT 
                category_lvl1,
                category_lvl2,
                category_lvl3,
                (comission+100)/100 comission,
                destination_category_id
            FROM
               " . DB_PREFIX . "baycik_sync_groups
            WHERE
                destination_category_id IS NOT NULL
                AND destination_category_id != 0
                AND sync_id = '$sync_id'
                $group_filter  
            ";
        $result = $this->db->query($sql);
        $this->profile("select group ");
        if (!$result->num_rows) {
            return false;
        }
        foreach ($result->rows as $group_data) {
            $this->importSellerProductGroup($seller_id, $group_data);
        }
        $this->profile("finish");
        return true;
    }

    private function importSellerProductGroup($seller_id, $group_data) {
        $sql = "
            SELECT 
                bse.*,
		(SELECT product_id FROM " . DB_PREFIX . "product p JOIN " . DB_PREFIX . "purpletree_vendor_products USING(product_id) WHERE p.model=bse.model AND seller_id='$seller_id' LIMIT 1) AS product_id
            FROM
                " . DB_PREFIX . "baycik_sync_entries AS bse
            WHERE
                is_changed
                AND category_lvl1 = '{$group_data['category_lvl1']}'
                AND category_lvl2 = '{$group_data['category_lvl2']}'
                AND category_lvl3 = '{$group_data['category_lvl3']}'
            ";
        $rows = $this->db->query($sql)->rows;
        $this->profile("select entries");
        if (!count($rows)) {
            return 1;
        }
        $this->createNeededProductProperties($this->sync_id);
        foreach ($rows as $row) {
            $product = $this->composeProductObject($row, $group_data['comission'], $group_data['destination_category_id']);
            if ($row['product_id']) {
                $product['product_id'] = $row['product_id'];
                $this->productUpdate($product);
            } else {
                $this->productAdd($product);
            }
            $this->db->query("UPDATE " . DB_PREFIX . "baycik_sync_entries SET is_changed=0 WHERE sync_entry_id='{$row['sync_entry_id']}'");
        }
        $this->profile("import entries");
        $this->reorderOptions();
        $this->assignFiltersToCategory($product['product_category']);
        return 1;
    }

    private function productAdd($item) {
        $product_id = $this->model_extension_aruna_product->addProduct($item);
        $sql = "
            INSERT INTO
                " . DB_PREFIX . "purpletree_vendor_products
            SET
                seller_id = '$this->seller_id',
                product_id = '$product_id',
                is_approved = '1',
                created_at = NOW(),
                updated_at = NOW()
            ";
        return $this->db->query($sql);
    }

    private function productUpdate($item) {
        return $this->model_extension_aruna_product->liteEditProduct($item['product_id'], $item);
    }
    
    private function productDelete($product_id){
        $this->model_extension_aruna_product->deleteProduct($product_id);
    }

    public function deleteAbsentSellerProducts($seller_id) {
        set_time_limit(300);
        $sql = "SELECT 
		    p.product_id
		FROM
		    " . DB_PREFIX . "product p
			JOIN
		    " . DB_PREFIX . "purpletree_vendor_products vp ON p.product_id=vp.product_id AND vp.seller_id = '$seller_id'
			LEFT JOIN
		    " . DB_PREFIX . "baycik_sync_entries bse USING(model)
			LEFT JOIN
		    " . DB_PREFIX . "baycik_sync_groups AS bsg USING(category_lvl1,category_lvl2,category_lvl3)
		WHERE
		    bse.sync_id IS NULL
		    OR destination_category_id=0
		";
        $result = $this->db->query($sql);
        if (!$result->num_rows) {
            return true;
        }
        
        $this->load->model('extension/aruna/product');
        foreach ($result->rows as $product) {
            $this->productDelete($product['product_id']);
        }
        $this->deleteAbsentFiltersAndAttributes();
        return true;
    }

    private function deleteAbsentFiltersAndAttributes() {
        $sql_clean_filters = "DELETE FROM " . DB_PREFIX . "filter WHERE filter_id NOT IN (SELECT filter_id FROM " . DB_PREFIX . "product_filter)";
        $sql_clean_filters_description = "DELETE FROM " . DB_PREFIX . "filter_description WHERE filter_id NOT IN (SELECT filter_id FROM " . DB_PREFIX . "filter)";
        $sql_clean_category_filters = "DELETE FROM " . DB_PREFIX . "category_filter WHERE filter_id NOT IN (SELECT filter_id FROM " . DB_PREFIX . "filter)";
        $sql_clean_attributes = "DELETE a,ad FROM " . DB_PREFIX . "attribute a JOIN " . DB_PREFIX . "attribute_description ad USING(attribute_id) WHERE attribute_id NOT IN (SELECT attribute_id FROM " . DB_PREFIX . "product_attribute);";
        $sql_clean_attributes_groups = "DELETE ag,agd FROM " . DB_PREFIX . "attribute_group ag JOIN " . DB_PREFIX . "attribute_group_description agd USING(attribute_group_id) WHERE attribute_group_id NOT IN (SELECT attribute_group_id FROM " . DB_PREFIX . "attribute);";
        $sql_clean_option_values = "DELETE FROM " . DB_PREFIX . "option_value WHERE option_value_id NOT IN (SELECT option_value_id FROM " . DB_PREFIX . "product_option_value)";
        $sql_clean_option_value_description = "DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_value_id NOT IN (SELECT option_value_id FROM " . DB_PREFIX . "product_option_value)";
        $sql_clean_manufacturer = "DELETE FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id NOT IN (SELECT DISTINCT manufacturer_id FROM " . DB_PREFIX . "product)";

        $this->db->query($sql_clean_filters);
        $this->db->query($sql_clean_filters_description);
        $this->db->query($sql_clean_category_filters);
        $this->db->query($sql_clean_attributes);
        $this->db->query($sql_clean_attributes_groups);
        $this->db->query($sql_clean_option_values);
        $this->db->query($sql_clean_option_value_description);
        $this->db->query($sql_clean_manufacturer);
    }

    private $filterCategoryIds = [];

    private function assignFiltersToCategory($category_ids) {
        $filter_ids = array_keys($this->filterCategoryIds);
        if (count($filter_ids) > 0) {
            $insert_values = '';
            foreach ($category_ids as $category_id) {
                foreach ($filter_ids as $filter_id) {
                    $insert_values .= ",($category_id,$filter_id)";
                }
            }
            $insert_values = substr($insert_values, 1);
            $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "category_filter (category_id, filter_id) VALUES $insert_values");
        }
        $this->filterCategoryIds = [];
    }

    private $filterCache = [];

    private function composeProductFilters($row) {
        $product_filters = [];
        if ($this->sync_config->filters) {
            foreach ($this->sync_config->filters as $filterConfig) {
                $filter_group_id = $filterConfig->filter_group_id;
                if (isset($filterConfig->delimeter)) {
                    $filter_value = $row[$filterConfig->field];
                    if (is_array($filterConfig->delimeter)) {
                        $filter_value = str_replace($filterConfig->delimeter, '|', $filter_value);
                        $delimeter = '|';
                    } else {
                        $delimeter = $filterConfig->delimeter;
                    }
                    $filter_names = explode($delimeter, $filter_value);
                } else {
                    $filter_names = [$row[$filterConfig->field]];
                }
                foreach ($filter_names as $filter_name) {
                    if (!$filter_name) {
                        continue;
                    }
                    if (!isset($this->filterCache[$filter_group_id . '_' . $filter_name])) {
                        $filter_row = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "filter_description WHERE filter_group_id='{$filter_group_id}' AND name='{$filter_name}'")->row;
                        if ($filter_row && $filter_row['filter_id']) {
                            $filter_id = $filter_row['filter_id'];
                        } else {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "filter SET filter_group_id='{$filter_group_id}'");
                            $filter_id = $this->db->getLastId();
                            $this->db->query("INSERT INTO " . DB_PREFIX . "filter_description SET filter_group_id='{$filter_group_id}', language_id='{$this->language_id}',filter_id='{$filter_id}',name='$filter_name'");
                        }
                        $this->filterCache[$filter_group_id . '_' . $filter_name] = $filter_id;
                    }
                    $product_filters[] = $this->filterCache[$filter_group_id . '_' . $filter_name];
                    $this->filterCategoryIds[$this->filterCache[$filter_group_id . '_' . $filter_name]] = 1;
                }
            }
        }
        return array_unique($product_filters);
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
                    'price' => round(($option_prices[$i] - $price) * $category_comission, 0),
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
        if ($this->sync_config->options) {
            foreach ($this->sync_config->options as $optionConfig) {
                $option_price = $row[$optionConfig->price_group_field];
                $option_value = $row[$optionConfig->value_group_field];
                $price = $row[$optionConfig->price_base_field];
                $product_options[] = $this->getProductOption($optionConfig->option_id, $optionConfig->option_type, $option_value, $price, $option_price, $category_comission);
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
        if ($this->sync_config->attributes) {
            foreach ($this->sync_config->attributes as $attributeConfig) {
                $attribute_name = $row[$attributeConfig->field];
                if (!$attribute_name) {
                    continue;
                }
                $product_attribute[] = [
                    'attribute_id' => $this->getProductAttributeId($attributeConfig->name, $attributeConfig->attribute_group_id),
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
                SELECT 
		    DISTINCT path_id AS category
                FROM 
		    " . DB_PREFIX . "category_path
                WHERE 
		    category_id = '" . (int) $destination_category_id . "'
		ORDER BY level DESC
		LIMIT 2");
        $categories = array();
        foreach ($query->rows as $row) {
            array_push($categories, $row['category']);
        }
        return $categories;
    }

    private $sstatusCache = [];

    private function composeStockStatus($stock_status) {
        if (!isset($this->sstatusCache[$stock_status])) {
            $result = $this->db->query("SELECT stock_status_id FROM " . DB_PREFIX . "stock_status WHERE name='" . (string) $stock_status . "' AND language_id='{$this->language_id}'");
            $stock_status_id = $result->row['stock_status_id'];
            if (!$stock_status_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "stock_status SET name='" . (string) $stock_status . "',language_id='{$this->language_id}'");
                $stock_status_id = $this->db->getLastId();
            }
            $this->sstatusCache[$stock_status] = $stock_status_id;
        }
        return $this->sstatusCache[$stock_status];
    }

    private function filenamePrepare($str) {
        $translit = array(
            "А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ё" => "e", "Ж" => "zh", "З" => "z", "И" => "i", "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "shch", "Ъ" => "", "Ы" => "y", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya",
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "e", "ж" => "zh", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "shch", "ъ" => "", "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            "A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h", "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p", "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x", "Y" => "y", "Z" => "z"
        );
        $result = strtr($str, $translit);
        $result = preg_replace("/[^a-zA-Z0-9_]/i", "-", $result);
        $result = preg_replace("/\-+/i", "-", $result);
        $result = preg_replace("/(^\-)|(\-$)/i", "", $result);
        return $result;
    }

    private function remoteFileExists($url){
        stream_context_set_default(
            [
                'http' => [
                    'method' => 'HEAD'
                ]
            ]
        );
        $headers = get_headers($url);
        return strpos($headers[0],'200')!==false;
    }
    
    private function getImage($url, $name = null) {
        if( empty($this->sync_config->download_images) ){
            return $this->remoteFileExists($url)?$url:null;
        }
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $basename = pathinfo($url, PATHINFO_BASENAME);
        $dest = "catalog/Seller_{$this->seller_id}/sync/";
        if( !file_exists(DIR_IMAGE .$dest) ){
            mkdir(DIR_IMAGE .$dest, 0777, true);
        }
        if ($name) {
            $dest .= $this->filenamePrepare($name).".$ext";
        } else {
            $dest .= $basename;
        }
        if( file_exists(DIR_IMAGE .$dest) ){
            return $dest;
        }
        if ( @copy($url, DIR_IMAGE . $dest) ) {
            return $dest;
        }
        return null;
    }
    
    private function composeProductImage($row) {
        return $this->getImage($row['image'],$row['model'].$row['product_name']);
    }

    private function composeProductImageObject($row) {
        $product_images = [];
        for ($i = 1; $i <= 5; $i++) {
            $img=$this->getImage($row['image' . $i], $row['model'].$row['product_name'] . "_$i");
            if( $img ){
                $product_images[] = ['image' => $img,'sort_order'=>$i];
            }
        }
        return $product_images;
    }

    private function composeProductObject($row, $category_comission, $destination_category_id) {
        $product_is_new=!$row['product_id'];
        ////////////////////////////////
        //DESCRIPTION SECTION
        ////////////////////////////////
        $row['description'] = preg_replace('/{{\w+}}/', '', $row['description']);
        $product_description = [
            $this->language_id => [
                'name' => $row['product_name'],
                'description' => $row['description'],
                'meta_title' => strip_tags($row['category_lvl3'] . ' ' . $row['manufacturer']),
                'meta_description' => mb_substr($this->meta_description_prefix . strip_tags($row['description']), 0, 500),
                'meta_keyword' => $this->meta_keyword_prefix . str_replace(' ', ',', strip_tags($row['product_name'])),
                'tag' => '',
            ]
        ];
        ////////////////////////////////
        //COMPOSING SECTION
        ////////////////////////////////
        if( isset($row['sort_order']) ){
            $sort_order = $row['sort_order'];
        } else {
            $sort_order = 1700000000 - time();//new products sort to start
        }
        
        $product = [
            'model' => $row['model'],
            'sku' => '',
            'upc' => '',
            'ean' => '',
            'jan' => '',
            'isbn' => '',
            'mpn' => $row['mpn'],
            'location' => '',
            'minimum' => $row['min_order_size'],
            'subtract' => '',
            'date_available' => '',
            'price' => round($row['price'] * $category_comission, 0),
            'points' => 0,
            'weight' => 0,
            'weight_class_id' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => 0,
            'tax_class_id' => 0,
            'sort_order' => $sort_order,
            'name' => $row['product_name'],
            'manufacturer_id' => $this->composeProductManufacturer($row['manufacturer']),
            'product_attribute' => $this->composeProductAttributeObject($row),
            'product_category' => $this->composeProductCategory($destination_category_id),
            'product_option' => $this->composeProductOptionsObject($row, $category_comission),
            'product_filter' => $this->composeProductFilters($row),
            'product_description' => $product_description,
            'shipping' => 1,
            'quantity' => $row['stock_count'],
            'stock_status_id' => $this->composeStockStatus($row['stock_status']),
            'product_store' => [$this->store_id],
            'status' => 1
        ];
        if ( $product_is_new ) {
            $product['image'] =         $this->composeProductImage($row);
            $product['product_image'] = $this->composeProductImageObject($row);
        }
        //print_r($product);die("$category_comission-");
        return $product;
    }

    private function reorderOptions() {
        $this->db->query("SET @i:=0;");
        $sql = "
	    UPDATE " . DB_PREFIX . "option_value 
	    JOIN (SELECT * FROM " . DB_PREFIX . "option_value_description ORDER BY `name`*1,`name`) AS t USING(option_value_id)
	    SET sort_order = @i:=@i + 1";
        $this->db->query($sql);

        $this->db->query("SET @i:=0;");
        $sql = "
	    UPDATE " . DB_PREFIX . "attribute 
	    JOIN (SELECT * FROM " . DB_PREFIX . "attribute_description ORDER BY `name`*1,`name`) AS t USING(attribute_id)
	    SET sort_order = @i:=@i + 1";
        $this->db->query($sql);

        $this->db->query("SET @i:=0;");
        $sql = "
	    UPDATE " . DB_PREFIX . "filter 
	    JOIN (SELECT * FROM " . DB_PREFIX . "filter_description ORDER BY `name`*1,`name`) AS t USING(filter_id)
	    SET sort_order = @i:=@i + 1";
        $this->db->query($sql);
    }

    private function load_admin_model($route) {
        $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
        $file = realpath(DIR_APPLICATION . '../admin/model/' . $route . '.php');
        if (is_file($file)) {
            include_once($file);
            $modelName = str_replace(['/', '_'], '', ucwords("Model/" . $route, "/"));
            $proxy = new $modelName($this->registry);
            $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
        } else {
            throw new \Exception('Error: Could not load model ' . $route . '!');
        }
    }

    private function createNeededProductProperties($sync_id) {
        $result = $this->db->query("SELECT sync_config FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$sync_id'");
        if (!$result->row || !$result->row['sync_config']) {
            return false;
        }
        $this->sync_config = json_decode($result->row['sync_config'], false, 512, JSON_UNESCAPED_UNICODE);
        if (isset($this->sync_config->filters)) {
            $this->load_admin_model('catalog/filter');
            foreach ($this->sync_config->filters as &$filter) {
                $row = $this->db->query("SELECT filter_group_id FROM " . DB_PREFIX . "filter_group_description WHERE name='{$filter->name}'")->row;
                if ($row && $row['filter_group_id']) {
                    $filter->filter_group_id = $row['filter_group_id'];
                } else {
                    $data = [
                        'sort_order' => 1,
                        'filter_group_description' => [
                            $this->language_id => [
                                'name' => $filter->name
                            ]
                        ]
                    ];
                    $filter->filter_group_id = $this->model_catalog_filter->addFilter($data);
                }
            }
        }
        if (isset($this->sync_config->attributes)) {
            $this->load_admin_model('catalog/attribute_group');
            foreach ($this->sync_config->attributes as &$attribute) {
                $row = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name='{$attribute->group_description}'")->row;
                if ($row && $row['attribute_group_id']) {
                    $attribute->attribute_group_id = $row['attribute_group_id'];
                } else {
                    $data = [
                        'sort_order' => 1,
                        'attribute_group_description' => [
                            $this->language_id => [
                                'name' => $attribute->group_description
                            ]
                        ]
                    ];
                    $attribute->attribute_group_id = $this->model_catalog_attribute_group->addAttributeGroup($data);
                }
            }
        }
        if (isset($this->sync_config->options)) {
            $this->load_admin_model('catalog/option');
            foreach ($this->sync_config->options as &$option) {
                $row = $this->db->query("SELECT o.option_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE od.language_id = '{$this->language_id}' AND od.name='{$option->name}'")->row;
                if ($row && $row['option_id']) {
                    $option->option_id = $row['option_id'];
                } else {
                    $data = [
                        'sort_order' => 1,
                        'type' => $option->option_type,
                        'option_description' => [
                            $this->language_id => [
                                'name' => $option->name
                            ]
                        ]
                    ];
                    $option->option_id = $this->model_catalog_option->addOption($data);
                }
            }
        }
    }

    public function getTotalImportCategories($sync_id) {
        $sql = "
            SELECT  
                group_id,
                destination_category_id
            FROM
               " . DB_PREFIX . "baycik_sync_groups
            WHERE
                destination_category_id IS NOT NULL
                AND destination_category_id != 0
                AND sync_id = '$sync_id'
            ";
        $result = $this->db->query($sql);
        $total = [
            'total_rows' => $result->num_rows,
            'groups' => []
        ];
        foreach ($result->rows as $row) {
            array_push($total['groups'], $row['group_id']);
        }
        return $total;
    }

}
