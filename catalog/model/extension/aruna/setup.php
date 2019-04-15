<?php

class ModelExtensionArunaSetup extends Model {

    public function __construct($registry) {
	parent::__construct($registry);
	$this->language_id = (int) $this->config->get('config_language_id');
	$this->store_id = (int) $this->config->get('config_store_id');
    }

    private $parser_registry=[
        'happywear'=>[
            'exclusive_owner'=>[2],
            'name'=>'Сайт одежды happywear.ru',
            'download_images'=>0,
            'attributes'=>[
                [
                    'field'=>'origin_country',
                    'name'=>'Страна',
                    'group_description'=>'Произведено'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель',
                    'group_description'=>'Произведено'
                ],
                [
                    'field'=>'attribute1',
                    'name'=>'Расцветка',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute2',
                    'name'=>'Состав',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute3',
                    'name'=>'Тип',
                    'group_description'=>'Свойства товара'
                ]
            ],
            'options'=>[
                [
                    'name'=>'Размер',
                    'option_type'=>'radio',
                    'value_group_field'=>'option_group1',
                    'price_group_field' => 'price_group1',
                    'price_base_field' => 'price'
                ]
            ],
            'filters'=>[
                [
                    'field'=>'option_group1',
                    'name'=>'Размер',
                    'delimeter'=>['|',' ','.','-','_','/',',']
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель'
                ]
            ],
            'manufacturer'=>'manufacturer'
        ],
        'glem'=>[
            'exclusive_owner'=>[2],
            'name'=>'Сайт женской одежды glem.com',
            'download_images'=>1,
            'attributes'=>[
                [
                    'field'=>'origin_country',
                    'name'=>'Страна',
                    'group_description'=>'Произведено'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель',
                    'group_description'=>'Произведено'
                ]
            ],
            'options'=>[
                [
                    'name'=>'Размер',
                    'option_type'=>'radio',
                    'value_group_field'=>'option_group1',
                    'price_group_field' => 'price_group1',
                    'price_base_field' => 'price'
                ]
            ],
            'filters'=>[
                [
                    'field'=>'option_group1',
                    'name'=>'Размер',
                    'delimeter'=>['|',' ','.','-','_','/',',']
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель'
                ]
            ],
            'manufacturer'=>'manufacturer'
        ],
        'charutti'=>[
            'exclusive_owner'=>[2],
            'name'=>'Сайт женской одежды charutti.ru',
            'download_images'=>1,
            'attributes'=>[
                [
                    'field'=>'attribute1',
                    'name'=>'Тип ткани',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute2',
                    'name'=>'Состав',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна',
                    'group_description'=>'Произведено'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель',
                    'group_description'=>'Произведено'
                ]
            ],
            'options'=>[
                [
                    'name'=>'Размер',
                    'option_type'=>'radio',
                    'value_group_field'=>'option_group1',
                    'price_group_field' => 'price_group1',
                    'price_base_field' => 'price'
                ]
            ],
            'filters'=>[
                [
                    'field'=>'option_group1',
                    'name'=>'Размер',
                    'delimeter'=>['|',' ','.','-','_','/',',']
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель'
                ]
            ],
            'manufacturer'=>'manufacturer'
        ],
        'fason'=>[
            'exclusive_owner'=>[2],
            'name'=>'Сайт женской одежды fason.ua',
            'download_images'=>1,
            'attributes'=>[
                [
                    'field'=>'attribute1',
                    'name'=>'Тип ткани',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute2',
                    'name'=>'Длина',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute3',
                    'name'=>'Стиль',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'attribute4',
                    'name'=>'Сезон',
                    'group_description'=>'Свойства товара'
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна',
                    'group_description'=>'Произведено'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель',
                    'group_description'=>'Произведено'
                ]
            ],
            'options'=>[
                [
                    'name'=>'Размер',
                    'option_type'=>'radio',
                    'value_group_field'=>'option_group1',
                    'price_group_field' => 'price_group1',
                    'price_base_field' => 'price'
                ]
            ],
            'filters'=>[
                [
                    'field'=>'option_group1',
                    'name'=>'Размер',
                    'delimeter'=>['|',' ','.','-','_','/',',']
                ],
                [
                    'field'=>'origin_country',
                    'name'=>'Страна'
                ],
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель'
                ]
            ],
            'manufacturer'=>'manufacturer'
        ],
        'isell'=>[
            'name'=>'Электротовары NilsonCrimea',
            'download_images'=>1,
            'options'=>[],
            'attributes'=>[
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель',
                    'group_description'=>'Произведено'
                ]
            ],
            'filters'=>[
                [
                    'field'=>'manufacturer',
                    'name'=>'Производитель'
                ]
            ],
            'manufacturer'=>'manufacturer'
        ],
        'csv'=>[
            'exclusive_owner'=>[51],
            'csv_columns' => ['product_name','model','mpn','leftovers','manufacturer','price1'],
            'required_field' => 'url',
            'name'=>'Импорт CSV Автоальянс',
            'attributes'=>[],
            'options'=>[],
            'filters'=>[],
            'manufacturer'=>''
        ]
    ];

    public function addParser($seller_id,$parser_id){
        $parser_object=$this->parser_registry[$parser_id];
        $parser_config= json_encode($parser_object, JSON_UNESCAPED_UNICODE);
        $this->db->query("INSERT INTO " . DB_PREFIX . "baycik_sync_list SET seller_id='$seller_id', sync_parser_name='{$parser_id}', sync_name='{$parser_object['name']}',sync_config='$parser_config'");
    }
    public function deleteParser($seller_id,$sync_id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id=".(int) $sync_id." AND seller_id=".(int)$seller_id );
        $this->db->query("DELETE FROM " . DB_PREFIX . "baycik_sync_groups WHERE sync_id=".(int) $sync_id );
        $this->db->query("DELETE FROM " . DB_PREFIX . "baycik_sync_entries WHERE sync_id=".(int) $sync_id );
    }
    public function updateParserConfig($sync_id){
	$sql="SELECT sync_parser_name FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$sync_id'";
	$parser_id=$this->db->query($sql)->row['sync_parser_name'];
	if( $parser_id && $sync_id ){
	    $parser_object=$this->parser_registry[$parser_id];
	    $parser_config= json_encode($parser_object, JSON_UNESCAPED_UNICODE);
	    $this->db->query("UPDATE " . DB_PREFIX . "baycik_sync_list SET sync_config='$parser_config' WHERE sync_id='$sync_id'");
	}
    }
    public function getParserList($seller_id){
        $added_parsers=$this->getSyncList($seller_id);
        $allowed_parsers=[];
        foreach($this->parser_registry as $parser_id=>$available){
            if( isset($available['exclusive_owner']) && !in_array($seller_id, $available['exclusive_owner']) ){
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
    public function getSyncList ($seller_id){
        $sql="SELECT * FROM " . DB_PREFIX . "baycik_sync_list WHERE seller_id='$seller_id'";
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

    public function getCategoriesTotal($filter_data) {
	$where = "WHERE sync_id = '{$filter_data['sync_id']}'";
	if ( isset($filter_data['filter_name']) ) {
	    $where .= " AND category_path LIKE '%{$filter_data['filter_name']}%'";
	}
	$sql = "SELECT 
		COUNT(*) AS num 
	    FROM 
               " . DB_PREFIX . "baycik_sync_groups  
            $where
            ORDER BY category_lvl1,category_lvl2,category_lvl3";
	$row = $this->db->query($sql);
	return $row->row['num'];
    }
    
    public function saveCategoryPrefs ($data){
        $sql = "
            UPDATE 
             " . DB_PREFIX . "baycik_sync_groups
            SET
                comission = ". (int) $data['category_comission']. ",
                destination_category_id = ". (int) $data['destination_category_id']. " 
                    
            WHERE group_id = ". (int) $data['group_id'];
        return $this->db->query($sql);
    }

}
