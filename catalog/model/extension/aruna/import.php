<?php

class ModelExtensionArunaImport extends Model {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->language_id = (int) $this->config->get('config_language_id');
        $this->store_id = (int) $this->config->get('config_store_id');
    }

    private function createNeededProductProperties($sync_id) {
        $result = $this->db->query("SELECT sync_config FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$sync_id'");
        if (!$result->row || !$result->row['sync_config']) {
            return false;
        }
        $this->sync_config = json_decode($result->row['sync_config']);
        if (isset($this->sync_config->attributes)) {
            $this->load_admin_model('catalog/attribute_group');
            foreach ($this->sync_config->attributes as &$attribute) {
                $row = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name='{$attribute->name}'")->row;
                if ($row && $row['attribute_group_id']) {
                    $attribute->attribute_group_id = $row['attribute_group_id'];
                } else {
                    $data = [
                        'sort_order' => 1,
                        'attribute_group_description' => [
                            $this->language_id => [
                                'name' => $attribute->name
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

    public function importUserProducts($sync_id) {
        $this->createNeededProductProperties($sync_id);
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
            ";
        $result = $this->db->query($sql);
        foreach ($result->rows as $row) {
            $ok = $this->importCategory($row);
            if (!$ok) {
                return false;
            }
        }
        return true;
    }

    public function importCategory($data) {
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
                category_lvl1 = '{$data['category_lvl1']}'
                AND category_lvl2 = '{$data['category_lvl2']}'
                AND category_lvl3 = '{$data['category_lvl3']}'
            GROUP BY model
            ";
        $rows = $this->db->query($sql);
        foreach ($rows->rows as $row) {
            $product = $this->composeProductObject($row, $data['comission'], $data['destination_category_id']);
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
            'quantity' => $row['stock_count'],
            'stock_status_id' => $this->composeStockStatus($row['stock_status']),
            'product_store' => [$this->store_id],
            'status' => 1
        ];
        
        //print_r($product);die();
        
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
            $modelName = str_replace(['/', '_'], '', ucwords("Model/" . $route, "/"));
            $proxy = new $modelName($this->registry);
            $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
        } else {
            throw new \Exception('Error: Could not load model ' . $route . '!');
        }
    }

}
