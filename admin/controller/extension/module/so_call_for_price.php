<?php

class ControllerExtensionModuleSoCallForPrice extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/so_call_for_price');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		$this->load->model('extension/module/so_call_for_price');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$action = isset($this->request->post["action"]) ? $this->request->post["action"] : "";
			unset($this->request->post['action']);

			$this->model_setting_setting->editSetting('module_so_call_for_price', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if($action == "save_edit") {
				$this->response->redirect($this->url->link('extension/module/so_call_for_price', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else {
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/so_call_for_price', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/so_call_for_price', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['module_so_call_for_price_status'])) {
			$data['status'] = $this->request->post['module_so_call_for_price_status'];
		} else {
			$data['status'] = $this->config->get('module_so_call_for_price_status');
		}

		if (isset($this->request->post['module_so_call_for_price_hide_cart'])) {
			$data['hide_cart'] = $this->request->post['module_so_call_for_price_hide_cart'];
		} else {
			$data['hide_cart'] = $this->config->get('module_so_call_for_price_hide_cart');
		}

		if (isset($this->request->post['module_so_call_for_price_replace_cart'])) {
			$data['replace_cart'] = $this->request->post['module_so_call_for_price_replace_cart'];
		} else {
			$data['replace_cart'] = $this->config->get('module_so_call_for_price_replace_cart');
		}

		if (isset($this->request->post['module_so_call_for_price_send_mail_to'])) {
			$data['send_mail_to'] = $this->request->post['module_so_call_for_price_send_mail_to'];
		} else {
			$data['send_mail_to'] = $this->config->get('module_so_call_for_price_send_mail_to');
		}

		if (isset($this->request->post['module_so_call_for_price_send_mail_customer'])) {
			$data['send_mail_customer'] = $this->request->post['module_so_call_for_price_send_mail_customer'];
		} else {
			$data['send_mail_customer'] = $this->config->get('module_so_call_for_price_send_mail_customer');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/so_call_for_price', $data));
	}
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/so_call_for_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/module/so_call_for_price');
		$this->load->model('setting/setting');		
		$setting_array = array(
			'module_so_call_for_price_status'					=> 1,
			'module_so_call_for_price_hide_cart'				=> 0,
			'module_so_call_for_price_replace_cart'				=> 0,
			'module_so_call_for_price_send_mail_to'				=> 'dulv@ytcvn.com',
			'module_so_call_for_price_send_mail_customer'		=> 1
		);
		$this->model_setting_setting->editSetting('module_so_call_for_price', $setting_array);
	}

	public function uninstall() {
		$this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_so_call_for_price');
	}
}