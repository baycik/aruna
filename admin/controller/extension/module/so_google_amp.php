<?php
class ControllerExtensionModuleSogoogleamp extends Controller {
	private $error = array();
	private $data = array();

	public function index() {
		$this->load->language('extension/module/so_google_amp');
		
		$data['objlang'] = $this->language;

		// Load breadcrumbs
		$data['breadcrumbs'] = $this->_breadcrumbs();

		// Load model
		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		$this->load->model('localisation/language');

		$this->document->setTitle($this->language->get('heading_title'));

		/*===== Load CSS & JS ========== */
		$this->document->addScript('view/javascript/bs-colorpicker/js/colorpicker.js');
		$this->document->addStyle('view/javascript/bs-colorpicker/css/colorpicker.css');

		
		// Get module id new 
		$module_id = '';
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$action = isset($this->request->post["action"]) ? $this->request->post["action"] : "";
			unset($this->request->post['action']);
			
			$params = $this->request->post['so_google_amp'];
			$this->model_setting_setting->editSetting('so_google_amp', $params);

			$params_module = array_merge($params, array('name'=>$params['so_google_amp_name'], 'status'=>$params['so_google_amp_status']));
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('so_google_amp', $params_module);
				$module_id = $this->db->getLastId();
			}
			else {
				$module_id = $this->request->get['module_id'];
				$this->model_setting_module->editModule($this->request->get['module_id'], $params_module);
			}

			$this->session->data['success'] = $this->language->get('text_success');
			if($action == "save_edit") {
				$this->response->redirect($this->url->link('extension/module/so_google_amp', 'user_token=' . $this->session->data['user_token'] . '&module_id='.$module_id, 'SSL'));
			
			}else{
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}
		
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/so_google_amp', 'user_token=' . $this->session->data['user_token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/so_google_amp', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		
		

		// Save and Stay --------------------------------------------------------------
		$data['error']= $this->error;
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		$data['text_layout'] = sprintf($this->language->get('text_layout'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], 'SSL'));

		// ---------------------------Load module --------------------------------------------
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST') || $this->request->server['REQUEST_METHOD'] == 'POST' && !$this->validate() && isset($this->request->get['module_id'])) {
			$module_info = $this->model_setting_setting->getSetting('so_google_amp');
		}
		else $module_info = $this->model_setting_setting->getSetting('so_google_amp');
		$params = isset($this->request->post['so_google_amp']) ? $this->request->post['so_google_amp'] : array();
		
		if (isset($params['name'])) {
			$data['name'] = $params['so_google_amp_name'];
		} elseif (!empty($module_info)) {
			$data['name'] = isset($module_info['so_google_amp_name']) ? $module_info['so_google_amp_name'] : '';
		} else {
			$data['name'] = '';
		}
		
		if (isset($params['status'])) {
			$data['status'] = $params['so_google_amp_status'];
		} elseif (!empty($module_info)) {
			$data['status'] = isset($module_info['so_google_amp_status']) ? $module_info['so_google_amp_status'] : '';
		} else {
			$data['status'] = '';
		}
		
		if (isset($params['logowidth'])) {
			$data['logowidth'] = $params['lso_google_amp_ogowidth'];
		} elseif (!empty($module_info)) {
			$data['logowidth'] = isset($module_info['so_google_amp_logowidth']) ? $module_info['so_google_amp_logowidth'] : '';
		} else {
			$data['logowidth'] = '100';
		}
		
		if (isset($params['logoheight'])) {
			$data['logoheight'] = $params['so_google_amp_logoheight'];
		} elseif (!empty($module_info)) {
			$data['logoheight'] = isset($module_info['so_google_amp_logoheight']) ? $module_info['so_google_amp_logoheight'] : '';
		} else {
			$data['logoheight'] = '100';
		}
		
		if (isset($params['thumbwidth'])) {
			$data['thumbwidth'] = $params['so_google_amp_thumbwidth'];
		} elseif (!empty($module_info)) {
			$data['thumbwidth'] = isset($module_info['so_google_amp_thumbwidth']) ? $module_info['so_google_amp_thumbwidth'] : '';
		} else {
			$data['thumbwidth'] = '100';
		}
		
		if (isset($params['thumbheight'])) {
			$data['thumbheight'] = $params['so_google_amp_thumbheight'];
		} elseif (!empty($module_info)) {
			$data['thumbheight'] = isset($module_info['so_google_amp_thumbheight']) ? $module_info['so_google_amp_thumbheight'] : '';
		} else {
			$data['thumbheight'] = '100';
		}
		
		if (isset($params['relatedproduct'])) {
			$data['status_relatedproduct'] = $params['so_google_amp_relatedproduct'];
		} elseif (!empty($module_info)) {
			$data['status_relatedproduct'] = isset($module_info['so_google_amp_relatedproduct']) ? $module_info['so_google_amp_relatedproduct'] : '';
		} else {
			$data['status_relatedproduct'] = '';
		}
		
		if (isset($params['linkcolor'])) {
			$data['linkcolor'] = $params['so_google_amp_linkcolor'];
		} elseif (!empty($module_info)) {
			$data['linkcolor'] = isset($module_info['so_google_amp_linkcolor']) ? $module_info['so_google_amp_linkcolor'] : '';
		} else {
			$data['linkcolor'] = '#e831e8';
		}
		
		if (isset($params['headerbg'])) {
			$data['headerbg'] = $params['so_google_amp_headerbg'];
		} elseif (!empty($module_info)) {
			$data['headerbg'] = isset($module_info['so_google_amp_headerbg']) ? $module_info['so_google_amp_headerbg'] : '';
		} else {
			$data['headerbg'] = '#e831e8';
		}
		
		if (isset($params['buttonbg'])) {
			$data['buttonbg'] = $params['so_google_amp_buttonbg'];
		} elseif (!empty($module_info)) {
			$data['buttonbg'] = isset($module_info['so_google_amp_buttonbg']) ? $module_info['so_google_amp_buttonbg'] : '';
		} else {
			$data['buttonbg'] = '#e831e8';
		}
		//--------------------------------Load Data -------------------------------------------
		
		//Get Data Default
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
	
		$this->response->setOutput($this->load->view('extension/module/so_google_amp', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/so_google_amp')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		$params	= $this->request->post['so_google_amp'];
		if ((utf8_strlen($params['so_google_amp_name']) < 3) ) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if (!empty($params['so_google_amp_logowidth'])) {
			if (!is_numeric($params['so_google_amp_logowidth'])) {
				$this->error['error_width'] = $this->language->get('error_width');
			}
		}

		if (!empty($params['so_google_amp_logoheight'])) {
			if (!is_numeric($params['so_google_amp_logoheight'])) {
				$this->error['error_height'] = $this->language->get('error_height');
			}
		}
		
		if (!empty($params['so_google_amp_thumbwidth'])) {
			if (!is_numeric($params['so_google_amp_thumbwidth'])) {
				$this->error['error_thumbwidth'] = $this->language->get('error_width');
			}
		}

		if (!empty($params['so_google_amp_thumbheight'])) {
			if (!is_numeric($params['so_google_amp_logoheight'])) {
				$this->error['error_thumbheight'] = $this->language->get('error_height');
			}
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		return !$this->error;
	}
	
	public function install() {
		$this->load->model('setting/setting');
		$this->load->model('setting/module');

		$data	= array(
			'so_google_amp_name' 					=> 'SO Google Amp',
			'so_google_amp_action' 					=> '',
			'so_google_amp_status'					=> '1',
			'so_google_amp_logowidth'					=> '136',
			'so_google_amp_logoheight'				=> '36',
			'so_google_amp_thumbwidth'				=> '320',
			'so_google_amp_thumbheight'				=> '260',
			'so_google_amp_relatedproduct'		=> '1',
			'so_google_amp_linkcolor'		=> '#e3b80d',
			'so_google_amp_headerbg'		=> '#7d707d',
			'so_google_amp_buttonbg'		=> '#e3b80d'
		);
		$data_module = array_merge($data, array('name'=>$data['so_google_amp_name'], 'status'=>$data['so_google_amp_status']));
		$this->model_setting_setting->editSetting('so_google_amp', $data);
		$this->model_setting_module->addModule('so_google_amp', $data_module);
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		$this->model_setting_setting->deleteSetting('so_google_amp');
		$this->model_setting_module->deleteModulesByCode('so_google_amp');
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
				'href' => $this->url->link('extension/module/so_google_amp', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$this->data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_google_amp', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);
		}
		return $this->data['breadcrumbs'];
	}
}
