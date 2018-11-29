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
	$this->load->language('aruna/sellersync');

	$this->document->setTitle($this->language->get('heading_title'));

	$this->load->model('extension/aruna/parse');


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
        $data['parser_list'] = $this->model_extension_aruna_parse->getParserList($seller_id);
        $data['parser_name'] = 'happywear.ru';

	$this->response->setOutput($this->load->view('extension/aruna/sellerparserlist', $data));
    }
    
    public function startParsing(){
        $parsername = $this->request->post['parsername'];
        if(!$parsername){
            echo "Source hasn't been selected";
            return;
        }
        //$seller_id = $this->customer->getId();
        //$this->load->model('extension/aruna/parse');
	//$this->model_extension_aruna_parse->addSync($seller_id, $parsername);
        
        if($parsername == 'happywear.ru'){
            echo $this->syncWithHappywear();
        } 
    }
    
    public function syncWithHappywear() {
	set_time_limit(300);
	$sync_id = 1;
	$tmpfile = tempnam("/tmp", "tmp_");
	if(!copy("https://happywear.ru/exchange/xml/price-list.csv", $tmpfile)){
            return "Downloading failed";
        };
        $this->load->model('extension/aruna/parse');
        if (!$this->model_extension_aruna_parse->parse_happywear($sync_id, addslashes($tmpfile))){
            return "Parse Error";
        };
        return 1;
    }


}
