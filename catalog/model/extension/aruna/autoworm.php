<?php
class ModelExtensionArunaAutoWorm extends Model {
    
    public function init($sync_id){
        $product_code_list = $this->getProductCodes($sync_id);
        $count = 0;
        for($i=0; $i<count($product_code_list);$i++){
            $count = $count + 1; 
            $mpn = $product_code_list[$i]['mpn'];
            if(strpos($product_code_list[$i]['model'], 'Код') > -1){
                continue;
            }
            $product_object = $this->getProductInfo($product_code_list[$i]['model']);
            if($product_object){
                $this->updateEntry($product_object,$sync_id, $mpn);
            }
        }
        return $count; 
    }
    
    private function getProductCodes($sync_id){
        $sql = "
            SELECT
                LPAD(model, 6, '0') as model,
                mpn
            FROM 
                baycik_tmp_current_sync
            WHERE
                sync_id = '$sync_id'
            ";
       $query = $this->db->query($sql);
       return $query->rows;
    }
    
    private function updateEntry($data, $sync_id, $mpn) {
        if(isset($data['category_path'])){
            if(isset($data['category_path']['category_lvl2'])){
                $category_lvl2 = $data['category_path']['category_lvl2'];
            } else {
                $category_lvl2 = '';
            }
            if(isset($data['category_path']['category_lvl3'])){
                $category_lvl3 = $data['category_path']['category_lvl3'];
            } else {
                $category_lvl3 = '';
            }
        }
        if(isset($data['attribute_group'])){
            if(isset($data['attribute_group'])){
                $attribute_group = $data['attribute_group'];
            } else {
                $attribute_group = '';
            }
        }
        if(isset($data['prices_wholesale'])){
            if(isset($data['prices_wholesale'][0])){
                $price2 = $data['prices_wholesale'][0];
            } else {
                $price2 = '';
            }
            if(isset($data['prices_wholesale'][2])){
                $price3 = $data['prices_wholesale'][2];
            } else {
                $price3 = '';
            }
            if(isset($data['prices_wholesale'][4])){
                $price4 = $data['prices_wholesale'][4];
            } else {
                $price4 = '';
            }
        }
        if(isset($data['image'])){
            $image = $data['image'];
        } else {
            $image = '';
        }  
        if(isset($data['description'])){
            $description = $data['description'].$data['compatability'];
        } else {
            $description = '';
        }  
        $sql = "
            UPDATE
                baycik_tmp_current_sync
            SET
                category_lvl2 = '".$category_lvl2."',
                category_lvl3 = '".$category_lvl3."',
                url = '{$data['url']}',
                description = '".$description."',
                attribute_group = '".$attribute_group."',
                price2 = '".$price2."',
                price3 = '".$price3."',
                price4 = '".$price4."',
                image = '".$image."'
            WHERE  sync_id = '".$sync_id."' AND mpn = '".$mpn."'      
            ";
       $this->db->query($sql);      
    }
    
    private function getProductInfo($product_code) {
        header('Content-Type: text/plain');
        $url = 'http://www.autoopt.ru';
        $index_page = file_get_contents($url.'/search/catalog/?maker_id=&q='.$product_code);
        $product_obj = [
            'order_code' => $product_code
        ];
        preg_match('/\/.+iblock.*\.jpg/', $index_page, $match_img);
        if($match_img){
           $image_url =  $url.$match_img[0];
           $image = file_get_contents($image_url);
           $product_obj['image'] = $image_url;
           //header('Content-type: image/jpeg');
           //echo $image;
        }else {
            //echo 'not found =(';
        }
        preg_match_all('/(?=(price_info_WHOLESALE[0-9]{1}">)([0-9.]*))/', $index_page, $match_prices);
        if($match_prices[2]){
            $product_obj['prices_wholesale'] = array_filter($match_prices[2]);
        }
        
        preg_match('/(\/catalog\/'.$product_code.'.*\/")/', $index_page, $match_url);
        if($match_url){
            $product_obj['url'] = rtrim($match_url[0], '"');
        } else {
            return false;
        }
        $product_page = file_get_contents($url.$product_obj['url']);
        
        $product_page = iconv('WINDOWS-1251', 'UTF-8', $product_page);
        
        $attributes = $this->getProductAttributes($product_page);
        $product_obj['attribute_group'] = implode('|',$attributes['attribute_group']);
        $product_obj['category_path'] = $attributes['category_path'];
        $product_obj['articuls'] = $attributes['articuls'];
        $product_obj['name'] = $this->getName($product_page);
        if($attributes['brand']){
            $product_obj['brand'] = $attributes['brand'];
        } else {
            $product_obj['brand'] = $this->getManufacturer($product_obj['name']);
        }
        
        $product_obj['target_auto'] = $this->getTargetAuto($product_obj['name']);
        
        $product_obj['description'] = str_replace('"','\"',$this->getDescription($product_page));
        $product_obj['compatability'] = str_replace('"','\"',$this->getCompatability($product_page));
        return $product_obj;
    }
    
    
    
    public function getProductAttributes($product_page) {
        $result_object = [
            'attributes' => [],
            'category_path' => [
                'category_lvl2' => '',
                'category_lvl3' => ''
            ],
            'articuls' => '',
            'brand' => ''
        ];
        $temp_object = [];
        $attributes_start = strpos($product_page, '<table class="detail_top">');
        $attributes_end = strpos($product_page, '<h2>Сертификаты</h2>');
        $attributes_length = $attributes_end-$attributes_start;
        $division = explode('<div class="dp_left"><span>', substr($product_page, $attributes_start, $attributes_length));
        unset($division[0]);
        
        $this->load_admin_model('setting/setting');
        
        $attribute_list = $this->model_setting_setting->getSetting('auto_attribute_list');
        if(count($attribute_list)<1){
            $this->model_setting_setting->editSetting('auto_attribute_list', ['auto_attribute_list'=>[]]);
            $attribute_list = $this->model_setting_setting->getSetting('auto_attribute_list');
        }
        
        $attribute_group = array_fill(0, count($attribute_list['auto_attribute_list']),'');
        for($i = 1; $i<count($division); $i++){
            $division[$i] = explode('<div class="dp_right"><span>',$division[$i]);
            $temp_object[$i]['attribute_name'] = rtrim(strip_tags($division[$i][0]));
            $temp_object[$i]['value'] = rtrim(strip_tags($division[$i][1]));
            if($temp_object[$i]['attribute_name'] == 'Торговая марка' || $temp_object[$i]['attribute_name'] == 'Код для заказа' || $temp_object[$i]['attribute_name'] == 'Ширина, м' || $temp_object[$i]['attribute_name'] == 'Высота, м' || $temp_object[$i]['attribute_name'] == 'Длина, м' || $temp_object[$i]['attribute_name'] == 'Вес, кг' || $temp_object[$i]['attribute_name'] == 'Срок, при поставке под заказ' || $temp_object[$i]['attribute_name'] == 'Доп. скидка по дисконтным картам' || $temp_object[$i]['attribute_name'] == 'Запрет добавления в корзину'){
                continue;
            } else if ($temp_object[$i]['attribute_name'] == 'Артикул'){
                $result_object['articuls'] = $temp_object[$i]['value'];
            } else if ($temp_object[$i]['attribute_name'] == 'Артикул доп.'){
                $result_object['articuls'] .= ', '.$temp_object[$i]['value'];
            } else if ($temp_object[$i]['attribute_name'] == 'Бренд (ТМ)'){
                $result_object['brand'] .= $temp_object[$i]['value'];
            } else if ($temp_object[$i]['attribute_name'] == 'Каталожная группа'){
                $paths = explode('..', $temp_object[$i]['value']);
                if(isset($paths[0])){
                    $category_lvl2 = $paths[0];
                } else {
                    $category_lvl2 = 'Другое';
                }
                if(isset($paths[1])){
                    $category_lvl3 = $paths[1];
                } else {
                    $category_lvl3 = 'Другое';
                }
                $result_object['category_path']['category_lvl2'] = $category_lvl2;
                $result_object['category_path']['category_lvl3'] = $category_lvl3;
            } else {
                $attribute_index = $this->getAttributeIndex($attribute_list['auto_attribute_list'], $temp_object[$i]['attribute_name']);
                
                if($attribute_index === 'not_found'){
                   $new_attribute = $this->addAttribute($attribute_list['auto_attribute_list'],$temp_object[$i]['attribute_name']); 
                   $attribute_group[$new_attribute] = $temp_object[$i]['value'];
                } else {
                    $attribute_group[$attribute_index] = $temp_object[$i]['value'];
                }
            }
        }
        $result_object['attribute_group'] = $attribute_group;
        
        return $result_object;
    }
    
    private function addAttribute($array, $entry){
        $this->load_admin_model('setting/setting');
        array_push($array, $entry);
        $this->model_setting_setting->editSetting('auto_attribute_list', ['auto_attribute_list'=>$array]);
        $new_list = $this->model_setting_setting->getSetting('auto_attribute_list');
        return count($new_list['auto_attribute_list'])-1;
    }
    
    private function getAttributeIndex($haystack, $needle) {
        for($i = 0; $i<count($haystack); $i++){
            if($haystack[$i] == $needle){
                return $i;
            } else {
                continue;
            }
        }
         return 'not_found';
    }
    
    
        
    public function getName($product_page) {
        $name_start = strpos($product_page, '<h1 id="pagetitle">');
        $name_end = strpos($product_page, '</h1>');
        $name_length = $name_end-$name_start;
        $name = strip_tags(substr($product_page, $name_start, $name_length));
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
            return $target = ['Other'];
        } else {
            return implode(',', $target);
        }    
    }
    
    public function getManufacturer($product_name) {
        preg_match('/[^0-9,\W]*([A-ZА-Я ]*)$/', $product_name, $matches);
        if($matches){
            return  rtrim($matches[1]);
        } else {
            return 'Other';
        }
    }
   
    
    public function getDescription($product_page) {
        if(strpos($product_page, '<p itemprop="description">') > -1){
            $description_start = strpos($product_page, '<p itemprop="description">');
            $description_end = strpos($product_page, '<p>Использована информация:');
            $description_length = $description_end-$description_start;
            $html = substr($product_page, $description_start, $description_length);
            $post_start = strpos($html, '<ul>');
            $post_end = strpos($html, '<br>');
            $post_length = $post_end-$post_start;
            return  substr($html, $post_start, $post_length);
        } else {
            return;
        }
    }

    public function getCompatability($product_page) {
        if(strpos($product_page, '<h2>Применяемость</h2><noindex>') > -1){
            $compatability_start = strpos($product_page, '<h2>Применяемость</h2><noindex>');
            $compatability_end = strpos($product_page, 'Где еще применяется запчасть</a>');
            $compatability_end_length = strlen('Где еще применяется запчасть</a>');
            $compatability_length = $compatability_end-$compatability_start+$compatability_end_length;
            $html = substr($product_page, $compatability_start, $compatability_length);
            $post_start = strpos($html, '<table class="main test">');
            $post_end = strpos($html, '</table>');
            $post_end_length = strlen('</table>');
            $post_length = $post_end-$post_start+$post_end_length;
            $result = substr($html, $post_start, $post_length);
            $result = str_replace('<td class="th" nowrap>Наименование по автокаталогу</td>', '', $result);
            return preg_replace('/<td><a.*<\/a><\/td>/', '', $result) ;
        } else {
            return;
        }
    }
    
    private function load_admin_model($route) {
	$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
	$file = realpath(DIR_APPLICATION . '../admin/model/' . $route . '.php');
	if (is_file($file)) {
	    include_once($file);
            $correct_route = str_replace('_', '', $route);
	    $modelName = str_replace('/', '', ucwords("Model/" . $correct_route, "/"));
	    $proxy = new $modelName($this->registry);
	    $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
	} else {
	    throw new \Exception('Error: Could not load model ' . $route . '!');
	}
    }    
}

