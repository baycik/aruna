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
                
		$data['order_statuses'] = $this->model_extension_localisation_order_status->getOrderStatuses();
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
                
                
                $data['seller_order_entries_list'] = $this->renderList();
                
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
            };
            
            $data = [];
            $filter_data['limit'] = $this->config->get('config_limit_admin');
            $filter_data['seller_id'] = $seller_id;
            $filter_data['start'] = ((int)$page-1)*$filter_data['limit'];
            
            $this->load->model('extension/aruna/sellerorder_entries');
            $order_entries_total = $this->model_extension_aruna_sellerorder_entries->getSellerOrdersProductTotal($seller_id);
            $results = $this->model_extension_aruna_sellerorder_entries->getSellerOrderProducts($filter_data);
                $total_sale = 0;
		$total_commission = 0;
                
		foreach ($results as $result) {
			 $total = 0;
			 $totalall = 0;
				$product_totals  = $this->model_extension_aruna_sellerorder_entries->getSellerOrdersProductTotal($seller_id);
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
				
				$product_commission  = $this->model_extension_aruna_sellerorder_entries->getSellerOrdersCommissionTotal($result['order_id'],$seller_id);
			
			$total_sale+= $total;
			$orderstatus = 5;
 	  			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
					$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
				} else {
				$data['error_warning'] = $this->language->get('module_purpletree_multivendor_commission_status_warning');
			}  

			if($result['admin_order_status_id'] == $result['seller_order_status_id']   && $result['seller_order_status_id'] == $orderstatus && $result['admin_order_status_id'] == $orderstatus ) {
				$total_payable += $total;
				$total_commission+= $product_commission['total_commission'];
			}
	
			$data['seller_orders_entries_list'][] = array(
                                'product_id'      => $result['product_id'],
				'order_id'      => $result['order_id'],
				'customer_name'      => $result['customer_name'],
                                'product_name'      => $result['product_name']. '<br>' .$result['option_name'].' '. $result['option_value'],
                                'model'      => $result['model'],
                                'quantity'      => $result['quantity'],
				'admin_order_status'      => $result['admin_order_status_id'],
				'order_status'  => $result['seller_order_status_id'],
                                'order_status_name'  => $result['seller_order_status_name'],
                                'price'      => number_format(intval($result['price']),2),
                                'total_price'      => number_format(intval($result['total']),2),
                                'sync_name'      => $result['sync_name'],
				'commission'         => $this->currency->format($product_commission['total_commission'], $result['currency_code'], $result['currency_value']),
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