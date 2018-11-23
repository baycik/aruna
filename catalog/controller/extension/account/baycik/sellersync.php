<?php
class ControllerExtensionAccountBaycikSellersync extends Controller{
	private $error = array();
	public function index(){
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/account/baycik/sellersync', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
		$store_detail = $this->customer->isSeller();
		if(!isset($store_detail['store_status'])){
			$this->response->redirect($this->url->link('account/account', '', true));
		}
		$this->load->language('baycik/sellersync');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/baycik/sellersync');
		
		$this->getList();
	}    
}