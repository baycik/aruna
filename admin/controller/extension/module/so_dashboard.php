<?php
class ControllerExtensionModuleSodashboard extends Controller {
	private $error = array();
	private $data = array();
	public function index() {
		// Load language
		$this->load->language('extension/module/so_dashboard');
		$data['objlang'] = $this->language;
		$data['text_yes'] 						= $this->language->get('text_yes');
		$data['text_no'] 						= $this->language->get('text_no');
	
		// Load breadcrumbs
		$data['breadcrumbs'] = $this->_breadcrumbs();

		// Load model
		$this->load->model('catalog/category');
		$this->load->model('setting/module');
		$this->load->model('extension/module/so_dashboard');

		$this->document->setTitle($this->language->get('heading_title'));

	
		// Get module id new 
		$moduleid_new= $this->model_extension_module_so_dashboard->getModuleId(); // Get module id
		$module_id = '';
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->request->post['moduleid'] = $moduleid_new[0]['Auto_increment'];
				$module_id = $moduleid_new[0]['Auto_increment'];
				$this->model_setting_module->addModule('so_dashboard', $this->request->post);

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
				$this->response->redirect($this->url->link('extension/module/so_dashboard', 'module_id='.$module_id.'&user_token=' . $this->session->data['user_token'], 'SSL'));
			}else{
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
		}
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$default = array(
			'name' 					=> 'SO Dashboard ',
			'action' 				=> '',
			'module_description'	=> array(),
			'status'				=> '1',
			'display_logowidth'				=> '100',
			'display_logoheight'				=> '100',
			'display_totalorder'				=> '1',
			'display_totalwishlist'				=> '1',
			'display_totalpewardPoints'				=> '1',
			'display_totaldownloads'				=> '1',
			'display_totaltransactions'				=> '1',
			'display_latestorder'				=> '1',
			'display_editaccount'				=> '1',
			'display_password'				=> '1',
			'display_address'				=> '1',
			'display_wishlist'				=> '1',
			'display_orderhistory'				=> '1',
			'display_downloads'				=> '1',
			'display_reward_points'				=> '1',
			'display_returns'				=> '1',
			'display_transactions'				=> '1',
			'display_payments'				=> '1',
			'display_newsletter'				=> '1',
			'display_account_login'				=> '1',
			'display_account_register'				=> '1',
			'display_account_forgotpassword'				=> '1',
			'display_account_myaccount'				=> '1',
			'display_account_editaccount'				=> '1',
			'display_account_password'				=> '1',
			'display_account_address'				=> '1',
			'display_account_wishlist'				=> '1',
			'display_account_newsletter'				=> '1',
			'display_account_logout'				=> '1',
			'display_order'				=> '1',
			'display_order_download'				=> '1',
			'display_order_payments'				=> '1',
			'display_order_reward'				=> '1',
			'display_order_returns'				=> '1',
			'display_order_transactions'				=> '1',
			
		);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST') || $this->request->server['REQUEST_METHOD'] == 'POST' && !$this->validate() && isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
			$module_info =  array_merge($default,$module_info);//check data empty database
			$data['action'] = $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
			$data['subheading'] = $this->language->get('text_edit_module') . $module_info['name'];
			$data['selectedid'] = $this->request->get['module_id'];
		} else {
			$module_info = $default;
			if($this->request->post != null)
			{
				$module_info = array_merge($module_info,$this->request->post);
			}
			
			$data['selectedid'] = 0;
			$data['action'] = $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL');
			$data['subheading'] = $this->language->get('text_create_new_module');
		}

		$data['user_token'] = $this->session->data['user_token'];
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['error']= $this->error;

		// Save and Stay --------------------------------------------------------------
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['text_layout'] = sprintf($this->language->get('text_layout'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], 'SSL'));

		// ---------------------------Load module --------------------------------------------
		$data['modules'] = array( 0=> $module_info );
		//$data['moduletabs'] = $this->model_setting_module->getModulesByCode( 'so_dashboard' );
		$data['link'] = $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'] . '', 'SSL');
		$data['linkremove'] = $this->url->link('extension/module/so_dashboard&user_token=' . $this->session->data['user_token']);

		//--------------------------------Load Data -------------------------------------------
		// Module description
		$data['module_description'] = $module_info['module_description'];
		//Get Data Default
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		
		// Remove cache
		$data['success_remove'] = $this->language->get('text_success_remove');
		
		$this->response->setOutput($this->load->view('extension/module/so_dashboard', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/so_dashboard')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (empty($this->request->post['display_logowidth'])  || !is_numeric($this->request->post['display_logowidth'])) {
				$this->error['error_width'] = $this->language->get('error_width');
		}
		
		if (empty($this->request->post['display_logoheight']) || !is_numeric ($this->request->post['display_logoheight'])) {
			$this->error['error_height'] = $this->language->get('error_height');
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
		
		
		
		if (!isset($this->request->get['module_id']) ) {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_dashboard', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}
		return $this->data['breadcrumbs'];
	}
}
