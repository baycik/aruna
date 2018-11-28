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
	$this->load->language('baycik/sellersync');

	$this->document->setTitle($this->language->get('heading_title'));

	$this->load->model('extension/aruna/parse');
	$this->load->model('extension/aruna/setup');


	$data['back'] = $this->url->link('extension/aruna/sellersync', '', true);
        
        if (isset($this->request->get['filter_name'])) {
	    $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
	}

	if (isset($this->request->get['page'])) {
	    $url .= '&page=' . $this->request->get['page'];
	}

	if (isset($this->request->get['sort'])) {
	    $sort = $this->request->get['sort'];
	} else {
	    $sort = 'category_lvl1, category_lvl2, category_lvl3';
	}

	if (isset($this->request->get['order'])) {
	    $order = $this->request->get['order'];
	} else {
	    $order = 'ASC';
	}

        if (isset($this->request->get['filter_name'])) {
                     $filter_name = $this->request->get['filter_name'];
             } else {
                     $filter_name = null;
        }
        
	if (isset($this->request->get['page'])) {
	    $page = $this->request->get['page'];
	} else {
	    $page = 1;
	}

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
        
        
	$data['sort'] = $sort;
	$data['order'] = $order;
        $data['filter_name'] = $filter_name;
	$data['heading_title'] = $this->language->get('heading_title');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['column_right'] = $this->load->controller('common/column_right');
	$data['content_top'] = $this->load->controller('common/content_top');
	$data['content_bottom'] = $this->load->controller('common/content_bottom');
	$data['footer'] = $this->load->controller('common/footer');
	$data['header'] = $this->load->controller('common/header');
	$data['seller_id'] = $this->customer->getId();
	//$this->syncWithHappywear();
        
        
	$filter_data = [
	    'filter_name' => $data['filter_name'],
	    'filter_model' => '',
	    'sort' => $sort,
	    'order' => $order,
	    'start' => ($page - 1) * $this->config->get('config_limit_admin'),
	    'limit' => $this->config->get('config_limit_admin'),
	    'sync_id' => 1
	];
        $data['categories_total'] = $categories_total = $this->model_extension_aruna_setup->getCategoriesTotal($filter_data);
	$data['categories'] = $categories = $this->model_extension_aruna_setup->check_get_cat_list($filter_data);
	$data['destination_categories'] = $this->getDestCategories();
	//$this->getList();


         if (isset($this->request->get['filter_name'])) {
                     $filter_name = $this->request->get['filter_name'];
             } else {
                     $filter_name = null;
        }
        
	if (isset($this->request->get['page'])) {
	    $page = $this->request->get['page'];
	} else {
	    $page = 1;
	}
        
        
	$pagination = new Pagination();
	$pagination->total = $categories_total;
	$pagination->page = $page;
	$pagination->limit = $this->config->get('config_limit_admin');
	$pagination->url = $this->url->link('extension/aruna/sellersync', $url . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();

	$data['results'] = sprintf($this->language->get('text_pagination'), ($categories_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
                ((($page - 1) * $this->config->get('config_limit_admin')) > ($categories_total - $this->config->get('config_limit_admin'))) ?
                $categories_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')),
                $categories_total, ceil($categories_total / $this->config->get('config_limit_admin')));
//	$data['results'] = sprintf($this->language->get('text_pagination'), ($categories_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
//                ((($page - 1) * $this->config->get('config_limit_admin')) > ($categories_total - $this->config->get('config_limit_admin'))) ?
//                $categories_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')),
//                $categories_total, ceil($categories_total / $this->config->get('config_limit_admin')));


	$this->response->setOutput($this->load->view('extension/aruna/sellersync', $data));
    }

    private $data = array(
	"category_lvl1" => "Одежда",
	"category_lvl2" => "Свитшоты, толстовки",
	"category_lvl3" => "Толстовка для мальчика",
	"category_comission" => "1.33",
	"destination_category_id" => "27"
    );

    public function testImport() {
	$this->load->model('extension/aruna/import');
	return $this->model_extension_aruna_import->importCategories(json_decode(json_encode($this->data)));
    }
   
    public function getDestCategories() {
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
	$this->load->model('extension/aruna/import');
	$data = $this->request->post['data'];
	$decoded_text = html_entity_decode($data);
	$import_array = json_decode($decoded_text, true);
	foreach ($import_array as $item) {
	    $this->model_extension_aruna_import->saveCategoryPrefs($item, 1);
	}
    }
    
    public function importUserProducts() {
	$this->load->model('extension/aruna/import');
	$this->model_extension_aruna_import->getImportList(1);
    }
}
