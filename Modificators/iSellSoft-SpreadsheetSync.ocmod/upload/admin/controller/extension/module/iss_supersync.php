<?php

class ControllerExtensionModuleISSSupersync extends Controller {

    private $error = array();

    public function index() {
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
        
	$this->load->language('extension/module/iss_supersync');
	$this->document->setTitle($this->language->get('heading_title'));
        
	$data = [];
	$data['breadcrumbs'] = array();
	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('text_home'),
	    'href' => $this->url->link('common/home', '', true)
	);
        
        $data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title_superparserlist'),
	    'href' => $this->url->link('extension/module/iss_superparserlist', $url, true)
	);
        
	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title_supersync'),
	    'href' => $this->url->link('extension/module/iss_supersync', $url, true)
	);

        $data['back_link']=$this->url->link('extension/module/iss_superparserlist', $url, true);
        
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
        
        $this->load->model('extension/module/iss_supersync/parse');
	$data['back'] = $this->url->link('extension/module/iss_supersync', '', true);
	$data['sort'] = $sort;
	$data['order'] = $order;
	$data['heading_title'] = $this->language->get('heading_title');
        $data['user_token'] = $this->session->data['user_token'];
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
	$this->load->model('extension/module/iss_supersync/setup');
	$data['categories_total'] = $categories_total = $this->model_extension_module_iss_supersync_setup->getCategoriesTotal($filter_data);
	$data['categories'] = $categories = $this->model_extension_module_iss_supersync_setup->getCategoryList($filter_data);
        
        
	$data['all_categories'] = $this->getAllCategories();
	$data['filter_name'] = $filter_name;
	
	$data['url']=$this->url->link('extension/module/iss_supersync', $url, true);

	$pagination = new Pagination();
	$pagination->total = $categories_total;
	$pagination->page = $page;
	$pagination->limit = $this->config->get('config_limit_admin');
	$pagination->url = $this->url->link('extension/module/iss_supersync', $url . '&page={page}', true);
	$data['pagination'] = $pagination->render();
	$data['results'] = sprintf($this->language->get('text_pagination'), ($categories_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($categories_total - $this->config->get('config_limit_admin'))) ?
		$categories_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $categories_total, ceil($categories_total / $this->config->get('config_limit_admin')));
	$this->response->setOutput($this->load->view('extension/module/iss_supersync', $data));
    }
    public function getAllCategories() {
        $this->load->model('catalog/category');
	$list = $this->model_catalog_category->getCategories();
	return $list;
    }

    public function saveImportPrefs() {
        $this->load->model('extension/module/iss_supersync/setup');
	$data = $this->request->post['data'];
	$decoded_text = html_entity_decode($data);
	$item = json_decode($decoded_text, true);
        $ok=$this->model_extension_module_iss_supersync_setup->saveCategoryPrefs($item);
        if( !$ok ){
            die("Save of category failed");
        }
	echo 1;
    }
    
    public function importUserProducts() {
        $sync_id=$this->request->request['sync_id'];
        $group_id=$this->request->request['group_id'];
        $this->load->model('extension/module/iss_supersync/import');
	$ok=$this->model_extension_module_iss_supersync_import->importStart($sync_id, $group_id,0);
        die($ok);
    }
    
    public function deleteAbsentProducts(){
        $seller_id = $this->customer->getId();
        $this->load->model('extension/module/iss_supersync/import');
	$ok=$this->model_extension_module_iss_supersync_import->deleteAbsentProducts();
	die($ok);
    }
    
    public function getTotalImportCategories() {
        $sync_id=$this->request->post['sync_id'];
        $this->load->model('extension/module/iss_supersync/import');
	$total_caegories=$this->model_extension_module_iss_supersync_import->getTotalImportCategories($sync_id);
        
        echo(json_encode($total_caegories));
    }
    
    public function autocompleteCategories() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
