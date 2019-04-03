<?php
class ModelExtensionArunaAutoWorm extends Model {
    private $sync_id=0;
    private $timelimit=100;
    private $proxy = './imgproxy/?url='; 
    private $auto_worm_config = [
        'csv_columns' => ['product_name','model','mpn','leftovers','manufacturer','price1'],
        'required_field' => 'url',
        'name'=>'Digger Worm',
        'manufacturer'=>'manufacturer',
        'options'=>[],
        'attributes'=>[
            [
                'field'=>'mpn',
                'name'=>'Оригинальный каталожный номер (OEM)',
                'group_description'=>'Свойства товара'
            ],
            [
                'field'=>'manufacturer',
                'name'=>'Производитель',
                'group_description'=>'Свойства товара'
            ]
        ],
        'filters'=>[
            [
                'field'=>'manufacturer',
                'name'=>'Производитель',
                'index'=>0
            ],
            [
                'field'=>'stock_status',
                'name'=>'Срок доставки',
                'index'=>0
            ]
        ]
    ];
    private $attribute_blacklist=[
        'Торговая марка',
        'Код для заказа',
        'Срок, при поставке под заказ',
        'Доп. скидка по дисконтным картам',
        'Запрет добавления в корзину',
        'Срока поиска',
        'Нормализованный Артикул для связи с АК'
    ];
    private $filter_whitelist=[
        'Область применения'=>'Область применения',
        'Авто совместимость'=>'Авто совместимость',
        'Высота искрогасителя, мм'=>'Высота, мм',
        'Высота подхвата, мм'=>'Высота, мм',
        'Высота подъема, мм'=>'Высота, мм',
        'Высота, мм'=>'Высота, мм',
        'Высота, м'=>'Высота, м',
        'Выходное напряжение В'=>'Напряжение, В',
        'Вольтаж, V'=>'Напряжение, В',
        'Выходное напряжение, В'=>'Напряжение, В',
        'Диаметр входного отверстия искрогасителя, мм'=>'Диаметр, мм',
        'Диаметр искрогасителя, мм'=>'Диаметр, мм',
        'Диаметр обода, мм'=>'Диаметр, мм',
        'Диаметр посадочный, мм'=>'Диаметр, мм',
        'Диаметр прутка, мм'=>'Диаметр, мм',
        'Диаметр слива, мм'=>'Диаметр, мм',
        'Диаметр стартера, мм'=>'Диаметр, мм',
        'Диаметр цилиндра, мм'=>'Диаметр, мм',
        'Диаметр, мм'=>'Диаметр, мм',
        'Диаметр,мм'=>'Диаметр, мм',
        'Внешний диаметр, мм'=>'Внешний диаметр, мм',
        'Внутренний диамтр щеточного диска, мм'=>'Внутренний диаметр, мм',
        'Внутренний диаметр, мм'=>'Внутренний диаметр, мм',
        'Двигатель, тип'=>'Тип двигателя',
        'Длина балонного ключа, мм'=>'Длина, мм',
        'Длина болтореза, мм'=>'Длина, мм',
        'Длина гвоздодера, мм'=>'Длина, мм',
        'Длина жгута для ремонта бк шин, мм'=>'Длина, мм',
        'Длина зажимов, мм'=>'Длина, мм',
        'Длина заплатки, мм'=>'Длина, мм',
        'Длина изоленты, мм'=>'Длина, мм',
        'Длина стартера, мм'=>'Длина, мм',
        'Длина щетки, мм'=>'Длина, мм',
        'Длина, мм'=>'Длина, мм',
        'Длина, м'=>'Длина, м',
        'Емкость АКБ, А·ч'=>'Емкость А/ч',
        'Емкость тестируемых батарей, А/ч'=>'Емкость А/ч',
        'Емкость, Ач'=>'Емкость А/ч',
        'Емкость, л'=>'Объем, л',
        'Коробка передач (КПП), тип'=>'Тип коробки передач',
        'Крутящий момент, Н·м,'=>'Крутящий момент',
        'Максимальный крутящий момент Н/м.'=>'Максимальный крутящий момент',
        'Максимальный крутящий момент, Н·м (кгс·м)'=>'Максимальный крутящий момент',
        'Литраж, л'=>'Объем, л',
        'Максимальная ёмкость АКБ А/ч'=>'Максимальная емкость',
        'Максимальный ток зажимов, А'=>'Максимальный ток, А',
        'Максимальный ток запуска, А'=>'Максимальный ток, А',
        'Максимальный ток зарядки, А'=>'Максимальный ток, А',
        'Максимальный ток потребления, А'=>'Максимальный ток, А',
        'Материал'=>'Материал',
        'Мощность генератора, Вт'=>'Мощность, Вт',
        'Мощность паяльника, Вт'=>'Мощность, Вт',
        'Мощность, Вт'=>'Мощность, Вт',
        'Мощность, W'=>'Мощность, Вт',
        'Напряжение питания, В'=>'Напряжение, В',
        'Напряжение, В'=>'Напряжение, В',
        'Номинальное напряжение аккумулятора, В'=>'Напряжение, В',
        'Номинальное напряжение, В'=>'Напряжение, В',
        'Объем бачка, л'=>'Объем, л',
        'Объем, л'=>'Объем, л',
        'Размер, мм'=>'Размер, мм',
        'Стартер, тип'=>'Тип стартера',
        'Сцепление, тип'=>'Тип сцепления',
        'Тип амортизатора'=>'Тип амортизатора',
        'Тип домкрата'=>'Тип домкрата',
        'Тип ключей в наборе'=>'Тип ключей в наборе',
        'Тип колесного диска'=>'Тип колесного диска',
        'Тип крепления'=>'Тип крепления',
        'Тип крепления грузика'=>'Тип крепления грузика',
        'Тип ламп'=>'Тип ламп',
        'Тип насоса'=>'Тип насоса',
        'Тип перекачиваемой жидкости'=>'Тип перекачиваемой жидкости',
        'Тип сенсора'=>'Тип сенсора',
        'Тип тормозной камеры'=>'Тип тормозной камеры',
        'Тип цоколя'=>'Цоколь',
        'Тип электрооборудования'=>'Тип электрооборудования',
        'Тип-применяемость клапана'=>'Тип клапана',
        'Тип/материал предохранителя'=>'Тип предохранителя',
        'Ток втягивающей обмотки, А'=>'Сила тока, А',
        'Ток выпрямляемый, не менее, А'=>'Сила тока, А',
        'Ток удерживающей обмотки, А'=>'Сила тока, А',
        'Ток холодного пуска DIN, А'=>'Сила тока, А',
        'Ток холодного пуска EN, А'=>'Сила тока, А',
        'Топливо, тип'=>'Тип топлива',
        'Ход поршня, мм'=>'Ход поршня, мм',
        'Цвет'=>'Цвет',
        'Цвет основной'=>'Цвет',
        'Цветовая температура, K'=>'Цветовая температура, K',
        'Частота вращения, об/мин'=>'Частота вращения, об/мин',
        'Число и расположение цилиндров'=>'Число и расположение цилиндров',
        'Число цилиндров'=>'Число цилиндров',
        'Ширина заплатки, мм'=>'Ширина, мм',
        'Ширина ленты, мм'=>'Ширина, мм',
        'Ширина шины'=>'Ширина, мм',
        'Ширина, мм'=>'Ширина, мм',
        'Вес, кг'=>'Вес, кг',
        'Ширина, м'=>'Ширина, м'
    ];
    
    public function init($sync_id){
        $this->start=time();
        $this->sync_id=$sync_id;
        $this->loadConfig();
        $this->startDigging();
        
    }
    
    private function loadConfig(){
        $result=$this->db->query("SELECT * FROM " . DB_PREFIX . "baycik_sync_list WHERE sync_id='$this->sync_id'");
        if( !$result ){
            die("Sync config not found");
        }
        
        $manual_attributes=$this->auto_worm_config['attributes'];
        $db_sync_config=json_decode($result->row['sync_config']);
        if( isset($db_sync_config->attributes) ){
            $this->auto_worm_config['attributes']= $db_sync_config->attributes;
            foreach($manual_attributes as $mattribute){
                $this->addAttribute( $mattribute['name'] );
            }
        }
    }
    
    private function saveConfig(){
        $this->copyWhitelistedFilters();
        $parser_config= json_encode($this->auto_worm_config, JSON_UNESCAPED_UNICODE);
        $this->db->query("UPDATE " . DB_PREFIX . "baycik_sync_list SET sync_config='$parser_config' WHERE sync_id='$this->sync_id'");
    }
    
    private function copyWhitelistedFilters(){
        $this->auto_worm_config['filters'] = [];
        foreach( $this->auto_worm_config['attributes'] as $attribute ){
            echo "\n<br> $attribute->name :";
            if( isset($this->filter_whitelist[$attribute->name]) ){
                $attribute->delimiter=',';
                $attribute->name=$this->filter_whitelist[$attribute->name];
                $attribute->delimeter=',';
                $this->auto_worm_config['filters'][]=$attribute;
                echo "is filter & attribute!";
            } else {
                echo "attribute";
            }
        }
    }
    
    private function startDigging(){
        while( $next_product_model = $this->getNextProductModel() ){
            $product_info = $this->digProductInfo($next_product_model);
            $this->fillEntry($product_info, $next_product_model);
            $this->saveConfig();
            
            if( time()-$this->start > $this->timelimit ){
                break;
            }
            //print_r($product_info);
        }
        $this->load->model('extension/aruna/parse');
        $this->model_extension_aruna_parse->groupEntriesByCategories ($this->sync_id);
    }
    private function getNextProductModel(){
        $sql = "
            SELECT
                LPAD(model, 6, '0') as model
            FROM 
                ".DB_PREFIX."baycik_sync_entries
            WHERE
                sync_id = '$this->sync_id'
                AND attribute_group IS NULL
            LIMIT 1";
       $result = $this->db->query($sql);
       if(isset($result->row['model'])){
           return $result->row['model'];
       }
       return null;
    }
    
    private function digProductInfo($product_model) {
        ///////////////////////////////
        //PARSING SEARCH PAGE
        ///////////////////////////////
        $url = 'http://www.autoopt.ru';
        $index_page = file_get_contents($url.'/search/catalog/?maker_id=&q='.$product_model);
        preg_match('/(\/catalog\/'.$product_model.'.*\/")/', $index_page, $match_url);
        if( !$match_url ){//not found
            return [
                'url'=>''
            ];
        }
        $product_obj=[
            'url'=>$url.rtrim($match_url[0], '"')
        ];
        
        preg_match('/\/.+iblock.*\.jpg/', $index_page, $match_img);
        if($match_img){
           $image_url =  $url.$match_img[0];
           $product_obj['image'] = $image_url;
        }
        preg_match_all('/(?=(price_info_WHOLESALE[0-9]{1}">)([0-9.]*))/', $index_page, $match_prices);
        if($match_prices[2]){
            $product_obj['prices_wholesale'] = array_filter($match_prices[2]);
        }
        ///////////////////////////////
        //PARSING PRODUCT PAGE
        ///////////////////////////////
        $product_page_html_raw = file_get_contents($product_obj['url']);
        $product_page_html = iconv('WINDOWS-1251', 'UTF-8', $product_page_html_raw);
        
        $product_obj['name'] = $this->getName($product_page_html);
        $details = $this->parseDetailsSection($product_page_html, $product_obj['name']);
        $product_obj['attribute_group'] = implode('|',$details['attribute_group']);
        $product_obj['category_path'] = $details['category_path'];
        $product_obj['articles'] = $details['articles'];
        $product_obj['target_auto'] = $this->getTargetAuto($product_obj['name']);
        $product_obj['description'] = $this->getDescription($product_page_html);
        $product_obj['compatability'] = $this->getCompatability($product_page_html);
        
        if($details['brand']){
            $product_obj['brand'] = $details['brand'];
        } else {
            $product_obj['brand'] = $this->getManufacturer($product_obj['name']);
        }
        preg_match_all('/\/product_pictures\/big\/[a-zA-Z0-9\/_]*\.jpg/', $product_page_html, $secondary_images);
        if($secondary_images[0]){
            for($i=1; $i<count(array_unique($secondary_images[0])); $i++){
                if($i == 6){
                    break;
                }
                $product_obj['image'.$i] = $url.$secondary_images[0][$i];
            }
        }
        return $product_obj;
    }    
    
    public function parseDetailsSection($product_page_html, $product_name) {
        $result_object = [
            'attribute_group' => '',
            'category_path' => [
                'category_lvl1' => '',
                'category_lvl2' => '',
                'category_lvl3' => ''
            ],
            'articles' => '',
            'brand' => ''
        ];
        $temp_object = [];
        $details_start = strpos($product_page_html, '<table class="detail_top">');
        $details_end = strpos($product_page_html, '<h2>Сертификаты</h2>');
        $details_length = $details_end-$details_start;
        $division = explode('<div class="dp_left"><span>', substr($product_page_html, $details_start, $details_length));
        unset($division[0]);

        $attribute_group = array_fill(0, count($this->auto_worm_config['attributes']),'');
        
        for($i = 1; $i<count($division); $i++){
            $division[$i] = explode('<div class="dp_right"><span>',$division[$i]);
            $temp_object[$i]['attribute_name'] = rtrim(strip_tags($division[$i][0]));
            $temp_object[$i]['value'] = rtrim(strip_tags($division[$i][1]));
            if( in_array($temp_object[$i]['attribute_name'], $this->attribute_blacklist) ){
                continue;
            }
            switch($temp_object[$i]['attribute_name']){
                case 'Артикул':
                    $result_object['articles'] = $temp_object[$i]['value'];
                    break;
                case 'Артикул доп.':
                    $result_object['articles'] .= ', '.$temp_object[$i]['value'];
                    break;
                case 'Бренд (ТМ)':
                    $result_object['brand'] .= $temp_object[$i]['value'];
                    break;
                case 'Каталожная группа':
                    $paths = explode('..', $temp_object[$i]['value']);
                    if(isset($paths[0])){
                        $category_lvl1 = $paths[0];
                    } else {
                        $category_lvl1 = 'Другое';
                    }
                    if(isset($paths[1])){
                        $category_lvl2 = $paths[1];
                    } else {
                        $category_lvl2 = 'Другое';
                    }
                    $result_object['category_path']['category_lvl1'] = $category_lvl1;
                    $result_object['category_path']['category_lvl2'] = $category_lvl2;
                    break;
                default:
                    $attribute_name=trim(str_replace('  ',' ',$temp_object[$i]['attribute_name']));
                    $attribute_index = $this->getAttributeIndex($attribute_name);
                    if( $attribute_index === 'not_found' ){
                        $attribute_index = $this->addAttribute($attribute_name); 
                    }
                    
            
                    $attribute_group[$attribute_index] = ucwords($temp_object[$i]['value']);
                    break;
            }
        }
        $target_auto_index = $this->getAttributeIndex('Авто совместимость');
        if( $target_auto_index === 'not_found' ){
            $target_auto_index = $this->addAttribute('Авто совместимость'); 
        }
        $attribute_group[$target_auto_index] = $this->getTargetAuto($product_name);
        $result_object['attribute_group'] = $attribute_group;
        return $result_object;
    }
    
    private function addAttribute( $attribute_name ){
        $attribute_object=new stdClass();
        $attribute_object->field='attribute_group';
        $attribute_object->name=$attribute_name;
        $attribute_object->index=count($this->auto_worm_config['attributes']);
        $attribute_object->group_description='Свойства товара';

        $this->auto_worm_config['attributes'][]=$attribute_object;
        return $attribute_object->index;
    }
    
    private function getAttributeIndex($needle) {
        $haystack=$this->auto_worm_config['attributes'];
        for($i = 0; $i<count($haystack); $i++){
            if( $haystack[$i]->name == $needle ){
                return $haystack[$i]->index;
            }
        }
        return 'not_found';
    }
    
    
    public function getName($product_page_html) {
        $name_start = strpos($product_page_html, '<h1 id="pagetitle">');
        $name_end = strpos($product_page_html, '</h1>');
        $name_length = $name_end-$name_start;
        $name = strip_tags(substr($product_page_html, $name_start, $name_length));
        return $name;
    }
    
    public function getTargetAuto($product_name) {
        $target = [];
        include 'autos_list.php';
        foreach($target_autos as $auto=>$value){
            if(strpos($product_name, $auto)){
                array_push($target, $value);
            } else{
                continue;
            }
        }
        if(count($target)<1){
            return 'Прочее';
        } else {
            return implode(',', $target);
        }    
    }
    
    public function getManufacturer($product_name) {
        preg_match('/[^0-9,\W]*([A-ZА-Я ]*)$/', $product_name, $matches);
        if($matches){
            return  trim($matches[1]);
        } else {
            return 'Other';
        }
    }
    
    public function getDescription($product_page_html) {
        if(strpos($product_page_html, '<p itemprop="description">') > -1){
            $description_start = strpos($product_page_html, '<p itemprop="description">');
            $description_end = strpos($product_page_html, '<p>Использована информация:');
            $description_length = $description_end-$description_start;
            $html = substr($product_page_html, $description_start, $description_length);
            $post_start = strpos($html, '<ul>');
            if(strpos($html, '<h2>Статьи о товаре</h2>') > -1){
                $post_end = strpos($html, '<h2>Статьи о товаре</h2>');
                $post_length = $post_end-$post_start;
                $html = (substr($html, $post_start, $post_length));
            } 
            if(strpos($html, '<img src=') > -1){
                $post_end = strpos($html, '<img src=');
                $post_length = $post_end-$post_start;
                $html = (substr($html, $post_start, $post_length));
            } 
            $html = preg_replace('/\<\/[brlip]*\>/', '-|-', $html);
            $html = strip_tags($html);
            $html = str_replace('-|-', '</br>', $html);
            return $this->db->escape($html);
        } else {
            return;
        }
    }

    public function getCompatability($product_page_html) {
        if(strpos($product_page_html, '<h2>Применяемость</h2><noindex>') > -1){
            $compatability_start = strpos($product_page_html, '<h2>Применяемость</h2><noindex>');
            $compatability_end = strpos($product_page_html, 'Где еще применяется запчасть</a>');
            $compatability_end_length = strlen('Где еще применяется запчасть</a>');
            $compatability_length = $compatability_end-$compatability_start+$compatability_end_length;
            $html = substr($product_page_html, $compatability_start, $compatability_length);
            $post_start = strpos($html, '<table class="main test">');
            $post_end = strpos($html, '</table>');
            $post_end_length = strlen('</table>');
            $post_length = $post_end-$post_start+$post_end_length;
            $result = substr($html, $post_start, $post_length);
            $result = str_replace('<td class="th" nowrap>Наименование по автокаталогу</td>', '', $result);
            $html=preg_replace('/<td><a.*<\/a><\/td>/', '', $result);
            return $this->db->escape($html);
        }
        return null;
    }
      
    private function fillEntry($data, $product_model) {
        $category_lvl1 = '';
        $category_lvl2 = '';
        $attribute_group = '';
        $price2 = '';
        $price3 = '';
        $price4 = '';
        $description = '';
        
        $image=!empty($data['image'])?$this->proxy.$data['image']:'';
        $image1=!empty($data['image1'])?$this->proxy.$data['image1']:'';
        $image2=!empty($data['image2'])?$this->proxy.$data['image2']:'';
        $image3=!empty($data['image3'])?$this->proxy.$data['image3']:'';
        $image4=!empty($data['image4'])?$this->proxy.$data['image4']:'';
        $image5=!empty($data['image5'])?$this->proxy.$data['image5']:'';
        
        $stock_count=0;
        $stock_status='7-9 дней';
        $url=$data['url'];
        
        if( isset($data['category_path']) ){
            if(isset($data['category_path']['category_lvl1'])){
                $category_lvl1 = $data['category_path']['category_lvl1'];
            }
            if(isset($data['category_path']['category_lvl2'])){
                $category_lvl2 = $data['category_path']['category_lvl2'];
            }
        }
        if(isset($data['attribute_group'])){
            if(isset($data['attribute_group'])){
                $attribute_group = addslashes($data['attribute_group']);
            }
        }
        if(isset($data['prices_wholesale'])){
            if(isset($data['prices_wholesale'][0])){
                $price2 = $data['prices_wholesale'][0];
            }
            if(isset($data['prices_wholesale'][2])){
                $price3 = $data['prices_wholesale'][2];
            }
            if(isset($data['prices_wholesale'][4])){
                $price4 = $data['prices_wholesale'][4];
            }
        }
        if(isset($data['description'])){
            $description = stripslashes(addslashes($data['description'].$data['compatability']));
        }
        $sql = "
            UPDATE
                ".DB_PREFIX."baycik_sync_entries
            SET
                category_lvl1 = '$category_lvl1',
                category_lvl2 = '$category_lvl2',
                category_lvl3 = '',    
                url = '$url',
                description = '$description',
                attribute_group = '$attribute_group',
                stock_count = '$stock_count',
                stock_status = '$stock_status',
                price2 = '$price2',
                price3 = '$price3',
                price4 = '$price4',
                image = '$image',
                image1 = '$image1',
                image2 = '$image2',
                image3 = '$image3',
                image4 = '$image4',
                image5 = '$image5'
            WHERE sync_id = '$this->sync_id' AND model = TRIM(LEADING '0' FROM '$product_model')      
            ";     
        $this->db->query($sql);
    }
}