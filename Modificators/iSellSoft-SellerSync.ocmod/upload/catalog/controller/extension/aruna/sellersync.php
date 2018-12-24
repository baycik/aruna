<?php

class ControllerExtensionArunaSellersync extends Controller {

    private $error = array();

    public function index() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);

	    $this->response->redirect($this->url->link('account/login', '', true));
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    $this->response->redirect($this->url->link('account/account', '', true));
	}
        
	$url = '';
        if ( isset($this->request->get['sync_id']) ) {
            $sync_id=$this->request->get['sync_id'];
	    $url .= '&sync_id=' . $this->request->get['sync_id'];
	} else {
            die('No sync selected!');
        }
        
        
        if (isset($this->request->get['filter_name'])) {
	    $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
	}
        
	$this->load->language('aruna/sellersync');
	$this->document->setTitle($this->language->get('heading_title_sellersync'));
	$data = [];
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
	    'text' => $this->language->get('heading_title_sellerparser'),
	    'href' => $this->url->link('extension/aruna/sellerparserlist', $url, true)
	);
        
        $data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title_sellersync'),
	    'href' => $this->url->link('extension/aruna/sellersync', $url, true)
	);

	if (isset($this->request->get['sort'])) {
	    $sort = $this->request->get['sort'];
	} else {
	    $sort = 'category_path';
	}

	if (isset($this->request->get['order'])) {
	    $order = $this->request->get['order'];
	} else {
	    $order = 'ASC';
	}

	if (isset($this->request->post['filter_name'])) {
	    $filter_name = $this->request->post['filter_name'];
	} else {
	    $filter_name = null;
	}

	if (isset($this->request->get['page'])) {
	    $page = $this->request->get['page'];
	} else {
	    $page = 1;
	}
        //$url .= '&page=' . $page;
        
        $this->load->model('extension/aruna/parse');
	$data['back'] = $this->url->link('extension/aruna/sellersync', '', true);
	$data['sort'] = $sort;
	$data['order'] = $order;
	$data['heading_title'] = $this->language->get('heading_title_sellersync');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['column_right'] = $this->load->controller('common/column_right');
	$data['content_top'] = $this->load->controller('common/content_top');
	$data['content_bottom'] = $this->load->controller('common/content_bottom');
	$data['footer'] = $this->load->controller('common/footer');
	$data['header'] = $this->load->controller('common/header');
	$data['seller_id'] = $this->customer->getId();
        $data['sync_id']=$sync_id;
	$filter_data = [
	    'filter_name' => $filter_name,
	    'filter_model' => '',
	    'sort' => $sort,
	    'order' => $order,
	    'start' => ($page - 1) * $this->config->get('config_limit_admin'),
	    'limit' => $this->config->get('config_limit_admin'),
	    'sync_id' => $sync_id
	];
	$this->load->model('extension/aruna/setup');
	$data['categories_total'] = $categories_total = $this->model_extension_aruna_setup->getCategoriesTotal($filter_data);
	$data['categories'] = $categories = $this->model_extension_aruna_setup->getCategoryList($filter_data);
        
	$data['destination_categories'] = $this->getDestCategories();
	$data['filter_name'] = $filter_name;
	
	$data['url']=$this->url->link('extension/aruna/sellersync', $url, true);

	$pagination = new Pagination();
	$pagination->total = $categories_total;
	$pagination->page = $page;
	$pagination->limit = $this->config->get('config_limit_admin');
	$pagination->url = $this->url->link('extension/aruna/sellersync', $url . '&page={page}', true);
	$data['pagination'] = $pagination->render();
	$data['results'] = sprintf($this->language->get('text_pagination'), ($categories_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($categories_total - $this->config->get('config_limit_admin'))) ?
		$categories_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $categories_total, ceil($categories_total / $this->config->get('config_limit_admin')));
	$this->response->setOutput($this->load->view('extension/aruna/sellersync', $data));
    }
    public function getDestCategories() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);

	    die('Access denied');
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    die('Access denied');
	}
	$list = $this->config->get('module_purpletree_multivendor_allow_category');
	$new_list = [];
	$keys = array_keys($list);
	$values = array_values($list);
	for ($i = 0; $i < count($list); $i++) {
	    $new_key = array(
		'category_path' => $keys[$i],
		'category_id' => $values[$i]
	    );
	    array_push($new_list, $new_key);
	}
	return $new_list;
    }

    public function saveImportPrefs() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);
	    die('Access denied');
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    die('Access denied');
	}
        
        $this->load->model('extension/aruna/setup');
	$data = $this->request->post['data'];
	$decoded_text = html_entity_decode($data);
	$item = json_decode($decoded_text, true);
        $ok=$this->model_extension_aruna_setup->saveCategoryPrefs($item);
        if( !$ok ){
            die("Save of category failed");
        }
	echo 1;
    }

    public function importUserProducts() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);

	    die('Access denied');
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    die('Access denied');
	}
        
        $sync_id=$this->request->post['sync_id'];
        $group_id=$this->request->post['group_id'];
	$seller_id = $this->customer->getId();
        $this->load->model('extension/aruna/import');
	$ok=$this->model_extension_aruna_import->importSellerProduct($seller_id,$sync_id, $group_id);
        die($ok);
    }
    
    public function deleteAbsentSellerProducts(){
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);

	    die('Access denied');
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    die('Access denied');
	}

        $seller_id = $this->customer->getId();
        $this->load->model('extension/aruna/import');
	$ok=$this->model_extension_aruna_import->deleteAbsentSellerProducts($seller_id);
	die($ok);
    }
    
    public function getTotalImportCategories() {
	if (!$this->customer->isLogged()) {
	    $this->session->data['redirect'] = $this->url->link('extension/aruna/sellersync', '', true);

	    die('Access denied');
	}
	$store_detail = $this->customer->isSeller();
	if (!isset($store_detail['store_status'])) {
	    die('Access denied');
	}
        
        $sync_id=$this->request->post['sync_id'];
        $this->load->model('extension/aruna/import');
	$total_caegories=$this->model_extension_aruna_import->getTotalImportCategories($sync_id);
        
        echo(json_encode($total_caegories));
    }

}
