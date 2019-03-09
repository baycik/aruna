<?php
class ControllerExtensionArunaSellerOrder extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/aruna/sellerorder', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		
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
			'href' => $this->url->link('extension/aruna/sellerorder', true)
		);
		
                $this->load->language('purpletree_multivendor/sellerorder');
                
		$this->load->model('extension/aruna/sellerorder');
                
                $this->load->model('extension/localisation/order_status');
                $seller_id = $this->customer->getId();
                $data['seller_customer_list'] = $this->model_extension_aruna_sellerorder->getSellerCustomers($seller_id);
                $data['seller_order_statuses'] = $this->model_extension_localisation_order_status->getOrderStatuses();
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_missing'] = $this->language->get('text_missing');
		$data['text_all'] = $this->language->get('text_all');
		$data['button_view'] = $this->language->get('button_view');
		$data['entry_date_from'] = $this->language->get('entry_date_from');
		$data['entry_date_to'] = $this->language->get('entry_date_to');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
                
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

                $data['seller_order_table'] = $this->renderList();
                 
		$this->response->setOutput($this->load->view('extension/aruna/sellerorder_list', $data));
	}
        
        public function renderList(){
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
                $filter_data['filter_seller_id'] = $seller_id;
                $filter_data['start'] = ((int)$page-1)*$filter_data['limit'];

		$data['seller_orders'] = array();

		$this->load->model('extension/aruna/sellerorder');
                $this->load->model('extension/localisation/order_status');
                
                $list = $this->model_extension_aruna_sellerorder->getSellerOrders($filter_data);
                $results = $list['results'];
                $order_total = $list['total'];
                $this->load->model('extension/localisation/order_status');
		foreach ($results as $result) {
			 $total = 0;
			 $totalall = 0;
				$product_totals  = $this->model_extension_aruna_sellerorder->getSellerOrdersTotal($seller_id,$result['order_id']);
				if(is_array($this->model_extension_aruna_sellerorder->getTotalllseller($seller_id,$result['order_id']))) {
					if(isset($this->model_extension_aruna_sellerorder->getTotalllseller($seller_id,$result['order_id'])['total'])) {
						$totalall  = $this->model_extension_aruna_sellerorder->getTotalllseller($seller_id,$result['order_id'])['total'];
					}
				};

				if(isset($product_totals['total'])){
					$total = $product_totals['total'];
				} else {
					$total = 0;
				}
				
			$orderstatus = 5;
 	  			if(null !== $this->config->get('module_purpletree_multivendor_commission_status')) {
					$orderstatus = $this->config->get('module_purpletree_multivendor_commission_status');
				} else {
				$data['error_warning'] = $this->language->get('module_purpletree_multivendor_commission_status_warning');
			}  
                        
                        $edit = '';
                        if (intval($result['total'])>0){
                            $edit = $this->url->link('extension/aruna/sellerorder_entries', '#' . $result['order_id']);
                        }
                        
                        
                        
			$data['seller_orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'seller_order_status_id'  => $result['seller_order_status_id'],
				'seller_order_status_name'  => $result['seller_order_status_name'],
                                'total_price'      => $result['total'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'shipping_code' => $result['shipping_code'],
				'view'          => $this->url->link('extension/aruna/sellerorder/info', 'order_id=' . $result['order_id'], true),
                                'edit'          => $edit
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('extension/aruna/sellerorder', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

                $this->load->model('extension/localisation/order_status');
                
                if($this->request->post){
                    echo $this->load->view('extension/aruna/sellerorder_list_table', $data);
                    exit();
                }
                return $this->load->view('extension/aruna/sellerorder_list_table', $data);
        }
        
        public function updateOrderStatus(){
            if ($this->request->post){
                $data = [
                    'order_status_id' => $this->request->post['order_status_id'],
                    'order_id' => $this->request->post['order_id']
                ];
            } else {
                return;
            }
            $this->load->model('extension/aruna/sellerorder');

            return $this->model_extension_aruna_sellerorder->updateOrderStatus($data);
            
        }

	public function info() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('extension/aruna/sellerorder', 'order_id=' . $order_id, true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('extension/aruna/sellerorder');
		$order_info = $this->model_extension_aruna_sellerorder->getOrder($order_id);
		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

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
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = $this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_extension_aruna_sellerorder->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_extension_aruna_sellerorder->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				$data['products'][] = array(
					'name'     => $product['name'].' <b>'.$product['model'].'</b> ',
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_extension_aruna_sellerorder->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();

			$totals = $this->model_extension_aruna_sellerorder->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_extension_aruna_sellerorder->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}
			$data['back'] = $this->url->link('extension/aruna/sellerorder', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('extension/aruna/sellerorder_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}
}