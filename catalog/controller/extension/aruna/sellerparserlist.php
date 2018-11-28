<?php

class ControllerExtensionArunaSellerParserList extends Controller {

    private $error = array();

    public function index() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellerparserlist', '', true);

	    $this->response->redirect($this->url->link('account/login', '', true));
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    $this->response->redirect($this->url->link('account/account', '', true));
	}
	$this->load->language('baycik/sellersync');

	$this->document->setTitle($this->language->get('heading_title'));

	$this->load->model('extension/aruna/parse');
	$this->load->model('extension/aruna/setup');


	$data['back'] = $this->url->link('extension/aruna/sellerparserlist', '', true);
        
       
	$url = '';
        

	$data['breadcrumbs'] = array();

	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('text_home'),
	    'href' => $this->url->link('common/home', '', true)
	);

	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title'),
	    'href' => $this->url->link('extension/aruna/sellersync', $url, true)
	);
        
	$data['heading_title'] = $this->language->get('heading_title');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['column_right'] = $this->load->controller('common/column_right');
	$data['content_top'] = $this->load->controller('common/content_top');
	$data['content_bottom'] = $this->load->controller('common/content_bottom');
	$data['footer'] = $this->load->controller('common/footer');
	$data['header'] = $this->load->controller('common/header');
	$data['seller_id'] = $this->customer->getId();
	//$this->syncWithHappywear();
        
        

	$this->response->setOutput($this->load->view('extension/aruna/sellersync', $data));
    }

    public function syncWithHappywear() {
	set_time_limit(300);
	$sync_id = 1;
	$tmpfile = tempnam("/tmp", "tmp_");
	copy("https://happywear.ru/exchange/xml/price-list.csv", $tmpfile);
	$this->load->model('extension/aruna/parse');
	$this->model_extension_aruna_parse->parse_happywear($sync_id, addslashes($tmpfile));
    }


}
