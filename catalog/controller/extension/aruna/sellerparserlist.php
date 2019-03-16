<?php

class ControllerExtensionArunaSellerparserList extends Controller {
    
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
	$this->load->language('aruna/sellersync');

	$this->document->setTitle($this->language->get('heading_title'));

        
  
	$data['back'] = $this->url->link('extension/aruna/sellerparserlist', '', true);
        
       
	$url = '';
        

	$data['breadcrumbs'] = array();

	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('text_home'),
	    'href' => $this->url->link('common/home', '', true)
	);
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_dashboard'),
            'href' => $this->url->link('extension/account/purpletree_multivendor/dashboardicons', $url, true)
        );    

	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title'),
	    'href' => $this->url->link('extension/aruna/sellerparserlist', $url, true)
	);
        $seller_id = $this->customer->getId();
        
	$data['heading_title'] = $this->language->get('heading_title');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['column_right'] = $this->load->controller('common/column_right');
	$data['content_top'] = $this->load->controller('common/content_top');
	$data['content_bottom'] = $this->load->controller('common/content_bottom');
	$data['footer'] = $this->load->controller('common/footer');
	$data['header'] = $this->load->controller('common/header');
	$data['seller_id'] = $seller_id;
        
	$this->load->model('extension/aruna/setup');
        $data['sync_list'] = $this->model_extension_aruna_setup->getSyncList($seller_id);
        $data['parser_list'] = $this->model_extension_aruna_setup->getParserList($seller_id);
       
	$this->response->setOutput($this->load->view('extension/aruna/sellerparserlist', $data));
    }
       
    public function startParsing(){
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellerparserlist', '', true);

	    $this->response->redirect($this->url->link('account/login', '', true));
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    $this->response->redirect($this->url->link('account/account', '', true));
	}
        
        if(isset($this->request->post['code'])){
            $_FILES[0] = $this->request->post['code'];
        
        }
        
        $sync_id = $this->request->post['sync_id'];
        if( !$sync_id ){
            echo "Source hasn't been selected";
            return;
        }
        $this->load->model('extension/aruna/setup');
        echo $this->model_extension_aruna_setup->updateParserConfig($sync_id);
	
        $this->load->model('extension/aruna/parse');
        echo $this->model_extension_aruna_parse->initParser($sync_id,'update_all_entries');
        
    }
    
    public function addParser(){
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellerparserlist', '', true);

	    $this->response->redirect($this->url->link('account/login', '', true));
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    $this->response->redirect($this->url->link('account/account', '', true));
	}

        $parser_id = $this->request->post['parser_id'];
        if( !$parser_id ){
            echo "No parser selected";
            return;
        }
        $seller_id = $this->customer->getId();
        $this->load->model('extension/aruna/setup');
        $this->model_extension_aruna_setup->addParser($seller_id,$parser_id);
        $this->response->redirect($this->url->link('extension/aruna/sellerparserlist', '', true));
    }
    public function deleteParser(){
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellerparserlist', '', true);

	    $this->response->redirect($this->url->link('account/login', '', true));
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    $this->response->redirect($this->url->link('account/account', '', true));
	}

        $sync_id = $this->request->post['sync_id'];
        if( !$sync_id ){
            echo "No sync selected";
            return;
        }
        $seller_id = $this->customer->getId();
        $this->load->model('extension/aruna/setup');
        $this->model_extension_aruna_setup->deleteParser($seller_id,$sync_id);
    }
}
