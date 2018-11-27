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
		
                $this->load->model('extension/baycik/parse');
                $this->load->model('extension/baycik/setup');
		
                
                $data['back'] = $this->url->link('extension/account/baycik/sellersync', '', true);
                
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
                
                if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
                
                $url = '';
                
                $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home','',true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/account/baycik/sellersync',  $url, true)
		);
                
		$data['sort'] = $sort;
		$data['order'] = $order;
                $data['heading_title'] =  $this->language->get('heading_title');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');	
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
                $data['seller_id'] = $this->customer->getId();	
                //$this->syncWithHappywear();
                
                $filter_data = [
                    'filter_name'	  => '',
                    'filter_model'	  => '',
                    'sort'                => $sort,
                    'order'               => $order,
                    'start'               => ($page - 1) * $this->config->get('config_limit_admin'),
                    'limit'               => $this->config->get('config_limit_admin'),
                    'seller_id'		  => $this->customer->getId()	
                ];
                
                $data['categories'] =  $categories = $this->model_extension_baycik_setup->check_get_cat_list($filter_data);
                $data['destination_categories'] = $this->getDestCategories();
		//$this->getList();
                
                
                if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

                if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
                $pagination = new Pagination();
                $pagination->total = count($data['categories']);
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/account/baycik/sellersync', $url . '&page={page}', true);
                
                $data['pagination'] = $pagination->render();
                
                $data['results'] = sprintf($this->language->get('text_pagination'), (count($data['categories'])) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > (count($data['categories']) - $this->config->get('config_limit_admin'))) ? count($data['categories']) : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), count($data['categories']), ceil(count($data['categories']) / $this->config->get('config_limit_admin')));
                
                
                $this->response->setOutput($this->load->view('account/baycik/sellersync', $data));
            
	} 
        
        private $data = array(
            "category_lvl1"=>"Одежда",
            "category_lvl2"=>"Свитшоты, толстовки",
            "category_lvl3"=>"Толстовка для мальчика",
            "category_comission"=>"1.33",
            "destination_category_id"=>"27"
            );
        public function testImport() {
	    $this->load->model('extension/baycik/import');
	    return $this->model_extension_baycik_import->importCategories(json_decode(json_encode($this->data)));
        }
        
        public function syncWithHappywear(){
	    $sync_id=1;
            $tmpfile = tempnam("/tmp", "tmp_");
            copy("https://happywear.ru/exchange/xml/price-list.csv", $tmpfile);
            $this->load->model('extension/baycik/parse');
            $this->model_extension_baycik_parse->parse_happywear($sync_id, addslashes($tmpfile));
	}
        
        public function getDestCategories (){
            $list = $this->config->get('module_purpletree_multivendor_allow_category');
            $new_list = [];
            $keys = array_keys($list);
            $values = array_values($list);
            for($i=0; $i<count($list); $i++){
                $new_key = array(
                    'category_path'=>$keys[$i],
                    'category_id'=>$values[$i]
                );
                array_push($new_list, $new_key);
            }
            return $new_list;  
        }
        
        public function importUserData (){
            $this->load->model('extension/baycik/import');
            $data = $this->request->post['data'];
            
            $seller_id = (int) $this->request->post['seller_id'];
            $decoded_text = html_entity_decode($data);
            $import_array = json_decode($decoded_text, true);
            foreach($import_array as $item){
                $this->model_extension_baycik_import->importCategories(json_decode(json_encode($item)), $seller_id);
            }
        }
        
        
        
        public function parse2 (){
            if (($handle = fopen("https://happywear.ru/exchange/xml/price-list.csv", "r")) !== FALSE) {
                header('Content-Type: text/html; charset=UTF-8');
                $sync_id = $this->request->get['sync_id'];
                $this->load->model('extension/baycik/sellersync');
                $i=0;
                while (($data = fgetcsv($handle, 5000, ";")) !== FALSE && $i++<1000) {
                    $row=[
                        'category_lvl1'=> addslashes($data[0]),
                        'category_lvl2'=>addslashes($data[1]),
                        'model'=> addslashes($data[2]),
                        'category_lvl3'=>addslashes($data[3]),
                        'filter1'=>addslashes($data[4]),
                        'filter2'=>addslashes($data[5]),
                        'manufacturer'=>addslashes($data[6]),
                        'origin_country'=>addslashes($data[7]),
                        'option1'=>addslashes($data[8]),
                        'url'=>addslashes($data[9]),
                        'img'=>addslashes($data[10]),
                        'description'=>addslashes($data[11]),
                        'price1'=>addslashes($data[12]),
                        'price2'=>addslashes($data[16]),
                        'price3'=>addslashes($data[17]),
                        'min_order'=>addslashes($data[14])
                    ];
                    if( $row['model'] ){
                        $this->model_extension_baycik_sellersync->insert_parsed_row($sync_id, $row); 
                    }

                }
                fclose($handle);
            }
        }
        
        public function autocomplete() {
            $json = array();

            if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {

                    $this->load->model('extension/purpletree_multivendor/sellerproduct');

                    if (isset($this->request->get['filter_name'])) {
                            $filter_name = $this->request->get['filter_name'];
                    } else {
                            $filter_name = '';
                    }

                    if (isset($this->request->get['limit'])) {
                            $limit = $this->request->get['limit'];
                    } else {
                            $limit = 5;
                    }

                            $seller_id = $this->customer->getId();

                    $filter_data = array(
                            'filter_name'  => $filter_name,
                            'start'        => 0,
                            'limit'        => $limit,
                            'seller_id' => $seller_id
                    );

                    $results = $this->model_extension_purpletree_multivendor_sellerproduct->getProducts($filter_data);

                    foreach ($results as $result) {
                            

                            $json[] = array(
                                    'product_id' => $result['product_id'],
                                    'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                                    'model'      => $result['model'],
                                    'option'     => $option_data,
                                    'price'      => $result['price']
                            );
                    }
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
        
        
        
}
