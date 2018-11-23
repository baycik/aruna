<?php
class ControllerExtensionModulesodashboard extends Controller {
	public function index($setting) {
		$this->document->addStyle('catalog/view/javascript/so_dashboard/style.css');
		
		
		$data['logged'] = $this->customer->isLogged();
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		
		// Dev custom Account
		$data['setting']    = $setting;
		
		$this->load->language('extension/module/so_dashboard');
		if ($this->customer->isLogged()) {
			
			$this->load->model('account/customer');
			$this->load->model('tool/image');
			
			$data['customer_info'] = $this->model_account_customer->getCustomer($this->customer->getId());
			if ($data['customer_info']['custom_field'] && !empty($data['customer_info']) && !empty(json_decode($data['customer_info']['custom_field'], true))) {
				
				$data['field_addimage'] = json_decode($data['customer_info']['custom_field'], true);
				$data['thumbUrl'] = $this->model_tool_image->resize($data['field_addimage']['profileimage'], $setting['display_logowidth'], $setting['display_logoheight']);
			} else {
				$data['thumbUrl'] = 'image/placeholder.png';
				
			}
		}
		
		return $this->load->view('extension/module/so_dashboard/default', $data);
	}
}