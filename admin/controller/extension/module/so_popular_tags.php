<?php
class ControllerExtensionModuleSopopulartags extends Controller {
	private $error = array();
	public $data = array();
	public function index() {
	// Load language
		$this->load->language('extension/module/so_popular_tags');
		$data['objlang'] = $this->language;
		
	// Load breadcrumbs
		$data['breadcrumbs'] = $this->_breadcrumbs();
		
	// Load model
		$this->load->model('setting/module');
		$this->load->model('extension/module/so_popular_tags');
		$this->load->model('localisation/language');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
	// Delete Module
		if( isset($this->request->get['module_id']) && isset($this->request->get['delete']) ){
			$this->model_setting_module->deleteModule( $this->request->get['module_id'] );
			$this->response->redirect($this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}
	//Get Module Id New
		$moduleid_new= $this->model_extension_module_so_popular_tags->getModuleId(); // Get module id
		$module_id = '';		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->request->post['moduleid'] = $moduleid_new[0]['Auto_increment'];
				$module_id = $moduleid_new[0]['Auto_increment'];
				$this->model_setting_module->addModule('so_popular_tags', $this->request->post);
			} else {
				$module_id = $this->request->get['module_id'];
				$this->request->post['moduleid'] = $this->request->get['module_id'];
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$action = isset($this->request->post["action"]) ? $this->request->post["action"] : "";
			unset($this->request->post['action']);
		 	$data = $this->request->post;
			
			$this->session->data['success'] = $this->language->get('text_success');
			if($action == "save_edit") {
				$this->response->redirect($this->url->link('extension/module/so_popular_tags', 'module_id='.$module_id.'&user_token=' . $this->session->data['user_token'], 'SSL'));
			}elseif($action == "save_new"){
				$this->response->redirect($this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else{
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
		}
		//---------------------------------------------------------------------------------
		
		$default = array(
			'name'					=> '',
			'module_description'	=> array(),
			'disp_title_module'		=> '1',
			'item_link_target'		=> '_blank',
			'status'				=> '1',
			'class_suffix'			=> '',
			'limit_tags' 			=> 20,
			'min_font_size'			=> '9',
			'max_font_size'			=> '25',
			'font_weight'			=> array(),
			'post_text'				=> '',
			'pre_text'				=> '',
			'use_cache'				=> '0',
			'cache_time'			=> '3600'
			
		);
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST') || $this->request->server['REQUEST_METHOD'] == 'POST' && !$this->validate() && isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
			$module_info =  array_merge($default,$module_info);//check data empty database
			$data['action'] = $this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
			$data['subheading'] = $this->language->get('text_edit_module') . $module_info['name'];
			$data['selectedid'] = $this->request->get['module_id'];
		} else {
			$module_info = $default;
			if(isset($this->request->post))
			{
				$module_info = array_merge($module_info,$this->request->post);
			}				
			$data['selectedid'] = 0;
			$data['action'] = $this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'], 'SSL');
			$data['subheading'] = $this->language->get('text_create_new_module');
		}

		$data['cancel'] 	= $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['user_token'] = $this->session->data['user_token'];
		$data['languages'] 	= $this->model_localisation_language->getLanguages();
		$data['error']		= $this->error;
	// Save and Stay
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['text_layout'] = sprintf($this->language->get('text_layout'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	// ---------------------------Load module --------------------------------------------
		$data['modules'] = array( 0=> $module_info );
		$data['moduletabs'] = $this->model_setting_module->getModulesByCode( 'so_popular_tags' );
		$data['link'] = $this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'] . '', 'SSL');
		$data['linkremove'] = $this->url->link('extension/module/so_popular_tags&user_token=' . $this->session->data['user_token']);
	
	// ----------------------------Load Data------------------------------------------
		$data['item_link_targets'] = array(
			'_blank' => $this->language->get('value_blank'),
			'_self'  => $this->language->get('value_self'),
		);
		
		$data['font_weights'] = array(
			'lighter' 	=> $this->language->get('value_lighter'),
			'normal'  	=> $this->language->get('value_normal'),
			'bold'  	=> $this->language->get('value_bold'),
			'bolder'  	=> $this->language->get('value_bolder'),
		);
		
		// Module description
		$data['module_description'] = $module_info['module_description'];
		
		// Get data default
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		// Remove cache
		$data['success_remove'] = $this->language->get('text_success_remove');
		$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		if($is_ajax && isset($_REQUEST['is_ajax_cache_lite']) && $_REQUEST['is_ajax_cache_lite']){
			self::remove_cache();
		}
		$this->response->setOutput($this->load->view('extension/module/so_popular_tags', $data));
	}
	public function remove_cache()
	{
		/*$this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');*/
		$folder_cache = DIR_CACHE.'so/';
		if(file_exists($folder_cache))
		{
			self::mageDelTree($folder_cache);
		}
	}
	function mageDelTree($path) {
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					self::mageDelTree($path.'/'.$entry);
				}
			}
			@rmdir($path);
		} else {
			@unlink($path);
		}
	}
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/so_popular_tags')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	// Name
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
	// Language
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		foreach($languages as $language){
			$module_description = $this->request->post['module_description'];
			if ((utf8_strlen($module_description[$language['language_id']]['head_name']) < 3) || (utf8_strlen($module_description[$language['language_id']]['head_name']) > 64)) {
				$this->error['head_name'] = $this->language->get('error_head_name');
			}
		}
	// limit tags
		if ($this->request->post['limit_tags'] != '0' && !filter_var($this->request->post['limit_tags'],FILTER_VALIDATE_FLOAT) || $this->request->post['limit_tags'] < 0) {
			$this->error['limit_tags'] = $this->language->get('error_limit_tags');
		}
	// min font-size	
		if ($this->request->post['min_font_size'] != '0' && !filter_var($this->request->post['min_font_size'],FILTER_VALIDATE_FLOAT) || $this->request->post['min_font_size'] < 9) {
			$this->error['min_font_size'] = $this->language->get('error_min_font_size');
		}
	// max font-size	
		if ($this->request->post['max_font_size'] != '0' && !filter_var($this->request->post['max_font_size'],FILTER_VALIDATE_FLOAT) || $this->request->post['max_font_size'] < 0 || $this->request->post['max_font_size'] > 25) {
			$this->error['max_font_size'] = $this->language->get('error_max_font_size');
		}
	// warning
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
	}
	
	public function _breadcrumbs(){
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if (!isset($this->request->get['module_id'])) {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_popular_tags', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}
		return $this->data['breadcrumbs'];
	}
}
