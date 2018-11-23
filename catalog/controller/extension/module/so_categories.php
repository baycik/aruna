<?php
class ControllerExtensionModuleSocategories extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/so_categories');
		$data['heading_title'] = $setting['name'];
		$data['text_tax'] = $this->language->get('text_tax');
		$this->document->addStyle('catalog/view/javascript/so_categories/css/so-categories.css');
		if ($setting['theme'] == 'theme4') {
			$this->document->addScript('catalog/view/javascript/so_categories/js/jquery.imagesloaded.js');
			$this->document->addScript('catalog/view/javascript/so_categories/js/jquery.so_accordion.js');
		}
		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('extension/module/so_categories');
		
		$default = array(
			'objlang'				=> $this->language,
			'name' 					=> '',
			'module_description'	=> array(),
			'disp_title_module'		=> '1',
			'status'				=> '1',
			'deviceclass_sfx'		=> '',
			'category_column0'		=> '4',
			'category_column1'		=> '4',
			'category_column2'		=> '3',
			'category_column3'		=> '2',
			'category_column4'		=> '1',
			'item_link_target'		=> '_blank',
			'theme'					=> 'theme1',
			'accmouseenter'			=> 'click',
			'categorys'				=> array(),
			'category'				=> array(),
			'child_category'		=> '1',
			'category_depth'		=> '1',
			'source_limit'			=> '6',
			'cat_title_display'		=> '1',
			'cat_title_maxcharacs'	=> '20',
			'cat_sub_title_display'	=> '1',
			'cat_sub_title_maxcharacs'	=> '20',
			'cat_all_product'		=> '1',
			'product_image'			=> '1',
			'width'					=> '200',
			'height'				=> '200',
			'placeholder_path'		=> 'nophoto.png',
			'post_text'				=> '',
			'pre_text'				=> '',
			'use_cache'				=> '1',
			'cache_time'			=> '3600'
		);
		$data =  array_merge($default,$setting);//check data empty setting
		$data['columnclass_sfx'] 	= 'preset01-'.$setting['category_column0'].' preset02-'.$setting['category_column1'].' preset03-'.$setting['category_column2'].' preset04-'.$setting['category_column3'].' preset05-'.$setting['category_column4'];
		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['head_name'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['head_name'], ENT_QUOTES, 'UTF-8');
		}else{
			
			$data['head_name']              = reset($setting['module_description'])['head_name'];

		}

		if (isset($setting['pre_text']) && !empty($setting['pre_text'])) {
			$data['pre_text'] = html_entity_decode($setting['pre_text'], ENT_QUOTES, 'UTF-8');
		}else{
			$data['pre_text']  = '';
		}

		if (isset($setting['post_text']) && !empty($setting['post_text'])) {
			$data['post_text'] = html_entity_decode($setting['post_text'], ENT_QUOTES, 'UTF-8');
		}else{
			$data['post_text']  = '';
		}

		// Leader :Check folter Module
		$folder_so_deal = DIR_TEMPLATE.$this->config->get('theme_default_directory').'/template/extension/module/so_categories/';
		if(file_exists($folder_so_deal)) $data['theme_config'] = $this->config->get('theme_default_directory');
		else $data['theme_config'] = 'default';

		$data['uniqued'] 		= 'so_categories_' . rand() . time();
		
		//Default
		$catids = $setting['category'];
		$list = array();
		$cats = array();
		$data['list'] = array();
		$_catids = (array)self::processCategory($catids);
		foreach($_catids as $category_id){
			$category_info = $this->model_catalog_category->getCategory($category_id);
			if ($category_info['image'] != null) {
				$image = $this->model_tool_image->resize($category_info['image'], $setting['width'], $setting['height']);
			}else {
				$url = file_exists("image/".$setting['placeholder_path']);

				if ($url) {
					$image_name = $setting['placeholder_path'];
				} else {
					$image_name = "no_image.png";
				}
				$image = $this->model_tool_image->resize($image_name, $setting['width'], $setting['height']);
			}
			$title_maxlength = (($setting['cat_title_maxcharacs'] != 0 && strlen($category_info['name']) > $setting['cat_title_maxcharacs']) ? utf8_substr(strip_tags(html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8')), 0, $setting['cat_title_maxcharacs']) . '..' : $category_info['name']);
			if(isset($category_info['name']))
			{
				$data['list'][] = array(
					'title' 	=> html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8'),
					'title_maxlength' =>$title_maxlength,
					'image'		=> $image,
					'link'  	=> $this->url->link('product/category', 'path=' . $category_id),
					'child_cat' => self::getCategoryson($category_id,$setting),
					'product_image' => $setting['product_image'],
				);
			}
		}
		
		// caching
		$use_cache = (int)$setting['use_cache'];
		$cache_time = (int)$setting['cache_time'];
		$folder_cache = DIR_CACHE.'so/Categories/';
		if(!file_exists($folder_cache))
			mkdir ($folder_cache, 0777, true);
		if (!class_exists('Cache_Lite'))
			require_once (DIR_SYSTEM . 'library/so/categories/Cache_Lite/Lite.php');

		$options = array(
			'cacheDir' => $folder_cache,
			'lifeTime' => $cache_time
		);
		$Cache_Lite = new Cache_Lite($options);
		if ($use_cache){
			
			$this->hash = md5( serialize(array($this->config->get('config_language_id'), $this->session->data['currency'], $setting)));
			$_data = $Cache_Lite->get($this->hash);
			if (!$_data) {
				$_data = $this->load->view('extension/module/so_categories/default', $data);
				$Cache_Lite->save($_data);
				return  $_data;
			} else {
				return  $_data;
			}
		}else{
			if(file_exists($folder_cache))
				$Cache_Lite->_cleanDir($folder_cache);
			return $this->load->view('extension/module/so_categories/default', $data);
		}
		
	}
	public function getCategoryson($category_id, $setting){
		$checkCategory = $this->model_extension_module_so_categories->checkCategory($category_id);
		$categoryss = array();
		if(isset($checkCategory) && $checkCategory[0]['status'] == 1 && $checkCategory != null && $setting['child_category'] ==1){
			$filter_data = array(
				'category_id'  		=> $category_id,
				'limit'        		=> $setting['source_limit'],
				'start' 	   		=> 0,
				'width'        		=> $setting['width'],
				'height'       		=> $setting['height'],
				'placeholder_path' 	=> $setting['placeholder_path'],
				'category_depth' 	=> $setting['category_depth']
			);
			
			$categoryss = $this->model_extension_module_so_categories->getCategories_son_categories($filter_data);
		}
		return $categoryss;
	}
	
	private function processCategory($catids){
		$catpubid = array();
		if (empty($catids)) return;
		foreach ($catids as $i => $cid) {
			$category = $this->model_catalog_category->getCategory($cid);
			$cats[$i] = $category;
			if (empty($category)) {
				unset($cats[$i]);
			} else {
				$catpubid[] = $category['category_id'];
			}
		}
		return $catpubid;
	}
}