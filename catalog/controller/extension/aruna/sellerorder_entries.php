<?php
class ControllerExtensionArunaSellerOrderEntries extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/aruna/sellerorder_entries', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$url = '';
                
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/aruna/sellerorder_entries', $url, true)
		);
                
                
                $seller_id = $this->customer->getId();
                
                $this->load->model('extension/aruna/sellerorder_entries');
                $this->load->model('extension/aruna/sellerorder_entries');
                

                $this->load->model('extension/localisation/order_status');
                
		$data['seller_order_statuses'] = $this->model_extension_localisation_order_status->getOrderStatuses();
                $data['seller_order_ids'] = $this->model_extension_aruna_sellerorder_entries->getOrderIds($seller_id);
                $data['seller_sync_list'] = $this->model_extension_aruna_sellerorder_entries->getSyncList($seller_id);
                $data['seller_customer_list'] = $this->model_extension_aruna_sellerorder_entries->getSellerCustomers($seller_id);
                
                $this->load->language('purpletree_multivendor/sellerorder');
                
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_missing'] = $this->language->get('text_missing');
		$data['text_all'] = $this->language->get('text_all');
		$data['button_view'] = $this->language->get('button_view');
		$data['entry_date_from'] = $this->language->get('entry_date_from');
		$data['entry_date_to'] = $this->language->get('entry_date_to');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_admin_order_status'] = $this->language->get('entry_admin_order_status');     
                
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
                
                
                $data['seller_order_entries_list'] = $this->load->view('extension/aruna/sellerorder_entries_list', $data);
                
                $this->response->setOutput($this->load->view('extension/aruna/sellerorder_entries', $data));
	}

        public function renderList() {
          
            if (isset($this->request->get['page'])) {
                    $page = $this->request->get['page'];
                    
            } else if (isset($this->request->post['start'])){
                    $page = $this->request->post['page'];
            }else{
                    $page = 1;
            }
            
            $seller_id = $this->customer->getId();
            if ($this->request->post){
                $filter_data = $this->request->post;
            } else {
                $filter_data = array();
            }
            
            
            if (isset($this->request->post['filter_order_id'])) {
                $filter_data['filter_order_id'] = $this->request->post['filter_order_id'];    
            } else {
                $filter_data['filter_order_id'] = $this->request->get['order_id'];
            }
             
            $data = [];
            $filter_data['limit'] = $this->config->get('config_limit_admin');
            $filter_data['filter_seller_id'] = $seller_id;
            $filter_data['start'] = ((int)$page-1)*$filter_data['limit'];
            
            $this->load->model('extension/aruna/sellerorder_entries');
            
            $list = $this->model_extension_aruna_sellerorder_entries->getSellerOrderProducts($filter_data);
            $results = $list['results'];
            $order_entries_total = $list['total'];;
                $total_sale = 0;
		foreach ($results as $result) {
			 $total = 0;
			 $totalall = 0;
				$product_totals  = $order_entries_total;
				if(is_array($this->model_extension_aruna_sellerorder_entries->getTotalllseller($seller_id,$result['order_id']))) {
					if(isset($this->model_extension_aruna_sellerorder_entries->getTotalllseller($seller_id,$result['order_id'])['total'])) {
						$totalall  = $this->model_extension_aruna_sellerorder_entries->getTotalllseller($seller_id,$result['order_id'])['total'];
					}
				};

				if(isset($product_totals['total'])){
					$total = $product_totals['total'];
				} else {
					$total = 0;
				}
				
			$total_sale+= $total;
			$orderstatus = 5;
 	  			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
					$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
				} else {
				$data['error_warning'] = $this->language->get('module_purpletree_multivendor_commission_status_warning');
			}  

			$data['seller_orders_entries_list'][] = array(
                                'order_product_id'      => $result['order_product_id'],
                                'product_id'      => $result['product_id'],
				'order_id'      => $result['order_id'],
				'customer_name'      => $result['customer_name'],
                                'product_name'      => $result['name'].' <b>'. $result['model'].'</b> ' . '<br>' .$result['option_name'].' '. $result['option_value'],
                                'model'      => $result['model'],
                                'quantity'      => $result['quantity'],
				'order_status'  => $result['seller_order_status_id'],
                                'order_status_name'  => $result['seller_order_status_name'],
                                'price'      => number_format(intval($result['price']),2, '.', ''),
                                'total_price'      => number_format(intval($result['total']),2, '.', ''),
                                'sync_name'      => $result['sync_name'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'shipping_code' => $result['shipping_code'],
				'delete'          => $this->url->link('extension/aruna/sellerorder_entries/delete', 'order_product_id=' . $result['order_product_id'], true),
			);
		}
                $pagination = new Pagination();
		$pagination->total = $order_entries_total;
		$pagination->page = $page;
		$pagination->limit = 50;
		$pagination->url = $this->url->link('extension/aruna/sellerorder_entries', 'page={page}', true);

		$data['pagination'] = $pagination->render();
                
		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_entries_total) ? (($page - 1) * 50) + 1 : 0, ((($page - 1) * 50) > ($order_entries_total - 50)) ? $order_entries_total : ((($page - 1) * 50) + 50), $order_entries_total, ceil($order_entries_total / 50));
                
                if($this->request->post){
                    echo $this->load->view('extension/aruna/sellerorder_entries_list', $data);
                    exit();
                }
                
                return $this->load->view('extension/aruna/sellerorder_entries_list', $data);
        }
     
    public function updateSellerOrderProduct(){
        if ($this->request->post){
            $data = [
                'product_id' => $this->request->post['product_id'],
                'order_product_id' => $this->request->post['order_product_id'],
                'order_id' => $this->request->post['order_id'],
                'product_price' => $this->request->post['product_price'],
                'product_quantity' => $this->request->post['product_quantity'],
                'total' => $this->request->post['product_price']*$this->request->post['product_quantity'],
                'seller_id' => $this->customer->getId()
            ];
        } else {
            return;
        }
        $this->load->model('extension/aruna/sellerorder_entries');
        
        return $this->model_extension_aruna_sellerorder_entries->updateSellerOrderProduct($data);
    }
    
    public function deleteSellerOrderProduct(){
        if ($this->request->post){
            $data = [
                'product_id' => $this->request->post['product_id'],
                'order_product_id' => $this->request->post['order_product_id'],
                'order_id' => $this->request->post['order_id'],
                'product_price' => $this->request->post['product_price'],
                'product_quantity' => $this->request->post['product_quantity'],
                'total' => $this->request->post['product_price']*$this->request->post['product_quantity'],
                'seller_id' => $this->customer->getId()
            ];
        } else {
            return;
        }
        $this->load->model('extension/aruna/sellerorder_entries');
        return $this->model_extension_aruna_sellerorder_entries->deleteSellerOrderProduct($data);
    }
    
    public function addSellerOrderProduct(){
        if ($this->request->post){
            $data = [
                'product_id' => $this->request->post['product_id'],
                'order_id' => $this->request->post['order_id'],
                'product_price' => $this->request->post['product_price'],
                'product_quantity' => $this->request->post['product_quantity'],
                'product_option_id' => $this->request->post['product_option_id'],
                'product_option_value_id' => $this->request->post['product_option_value_id'],
                'total' => $this->request->post['product_price']*$this->request->post['product_quantity'],
                'seller_id' => $this->customer->getId()
            ];
        } else {
            return;
        }
       
        $this->load->model('extension/aruna/sellerorder_entries');
        return $this->model_extension_aruna_sellerorder_entries->addSellerOrderProduct($data);
    }
    
    public function productAutocomplete() {
		$json = array();

		if (isset($this->request->post['filter_name']) || isset($this->request->post['filter_model'])) {
			$this->load->model('extension/aruna/sellerproduct');
			$this->load_admin_model('catalog/option');
                        $this->load->model('extension/aruna/product');

			if (isset($this->request->post['filter_name'])) {
				$filter_name = $this->request->post['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->post['filter_model'])) {
				$filter_model = $this->request->post['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->post['limit'])) {
				$limit = $this->request->post['limit'];
			} else {
				$limit = 5;
			}
			$filter_data = array(
                                'seller_id'  => $this->customer->getId(),
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);
			$results = $this->model_extension_aruna_sellerproduct->getSellerProducts($filter_data);
                       
			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_extension_aruna_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'options'     => $option_data,
					'price'      => number_format(intval($result['price']),2, '.', '')
				);
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
    
	
    private function load_admin_model($route) {
	$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
	$file = realpath(DIR_APPLICATION . '../admin/model/' . $route . '.php');
	if (is_file($file)) {
	    include_once($file);
            $correct_route = str_replace('_', '', $route);
	    $modelName = str_replace('/', '', ucwords("Model/" . $correct_route, "/"));
	    $proxy = new $modelName($this->registry);
	    $this->registry->set('model_' . str_replace('/', '_', (string) $route), $proxy);
	} else {
	    throw new \Exception('Error: Could not load model ' . $route . '!');
	}
    }    
}