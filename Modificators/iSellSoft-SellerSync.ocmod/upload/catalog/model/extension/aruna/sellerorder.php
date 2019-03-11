<?php
class ModelExtensionArunaSellerOrder extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");
                
		if ($order_query->num_rows) {
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'email'                   => $order_query->row['email'],
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;
		}
	}
        
       
        public function getSellerCustomers($seller_id){
		$query = $this->db->query("SELECT DISTINCT customer_id, CONCAT(firstname, ' ', lastname) as customer_name FROM`" . DB_PREFIX . "order` JOIN `" . DB_PREFIX . "purpletree_vendor_orders` USING(order_id) WHERE seller_id = '" . (int)$seller_id . "'");

		return $query->rows;
	}
        
        public function getTotalSellerOrders($data= array()){
		$sql = "SELECT COUNT(DISTINCT(pvo.order_id)) AS total FROM `" . DB_PREFIX . "order` o JOIN " . DB_PREFIX . "purpletree_vendor_orders pvo ON(pvo.order_id=o.order_id)";
		
		if (isset($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "pvo.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " WHERE pvo.order_status_id > '0'";
		}
		if (isset($data['filter_admin_order_status'])) {
			$implode1 = array();

			$order_statuses1 = explode(',', $data['filter_admin_order_status']);

			foreach ($order_statuses1 as $order_status_id) {
				$implode1[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode1) {
				$sql .= " AND (" . implode(" OR ", $implode1) . ")";
			}
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		
		if(!empty($data['seller_id'])){
			$sql .= " AND pvo.seller_id ='".(int)$data['seller_id']."'";
		}
		
		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(!isset($data['filter_date_from']) && !isset($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-60 days"));
			$sql .= " AND DATE(o.date_added) >= '".$end_date."'";
			$sql .= " AND DATE(o.date_added) <= '".date('Y-m-d')."'";
		}
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
        
        public function updateOrderStatus($data){
            $sql = "
                UPDATE 
                    " . DB_PREFIX . "purpletree_vendor_orders 
                SET
                    order_status_id = '".$data['order_status_id']."'
                WHERE 
                    order_id = '".$data['order_id']."'    
                ";
           return $this->db->query($sql);
        }

	public function getSellerOrders($data = array()) {
		$sql = "SELECT DISTINCT
                            o.order_id,
                            (SELECT distinct pvo.order_status_id FROM oc_purpletree_vendor_orders pvo WHERE o.order_id = pvo.order_id) as seller_order_status_id,
                            (SELECT name FROM oc_order_status os WHERE seller_order_status_id = os.order_status_id) as seller_order_status_name,
                            CONCAT(o.firstname, ' ', o.lastname) AS customer,
                            o.shipping_code,
                            o.currency_code,
                            o.currency_value,
                            o.total,
                            o.date_added,
                            o.date_modified
                        FROM 
                            `" . DB_PREFIX . "order` o
                             ";
                
                $sql .= " WHERE ";
                  if (isset($data['filter_seller_id']) && $data['filter_seller_id'] != '*' ){
                        $seller_id = $data['filter_seller_id'];
                        $sql .= " (SELECT DISTINCT pvo.seller_id FROM oc_purpletree_vendor_orders pvo WHERE o.order_id = pvo.order_id LIMIT 1) = '".$seller_id."'";
                } else {
                        $sql .= " ";
                }
                
                if (isset($data['filter_customer_id']) && $data['filter_customer_id'] != '*' ){
                        $customer_id = $data['filter_customer_id'];
                        $sql .= " AND o.customer_id = '".(int)$customer_id . "'";
                } else {
                        $sql .= " AND o.customer_id > '-1' ";
                }
                
                
		if (isset($data['filter_seller_order_status'])  && $data['filter_seller_order_status'] != '*') {
			$implode = array();

			$seller_order_statuses = explode(',', $data['filter_seller_order_status']);

			foreach ($seller_order_statuses as $seller_order_status_id) {
				$implode[] = " (SELECT distinct pvo.order_status_id FROM oc_purpletree_vendor_orders pvo WHERE o.order_id = pvo.order_id) = '" . (int)$seller_order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " ";
		}
		
		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(empty($data['filter_date_from']) && empty($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-90 days"));
			$sql .= " AND DATE(o.date_added) >= '".$end_date."'";
			$sql .= " AND DATE(o.date_added) <= '".date('Y-m-d')."'";
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.date_modified',
			'op.total',
                        'op.price'
		);
		
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $this->db->escape($data['sort']);
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		} 
		$query = $this->db->query($sql);
                return $array = [
                    'results' => $query->rows,
                    'total' => $query->num_rows
                ];
	}
       
	
	public function getSellerOrdersTotal($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "order_total ot WHERE (SELECT DISTINCT pvo.seller_id FROM oc_purpletree_vendor_orders pvo WHERE ot.order_id = pvo.order_id LIMIT 1) = '".(int)$seller_id."' AND ot.order_id = '".(int)$order_id."' AND ot.code='sub_total'");

		return $query->row;
	}
	public function getTotalllseller($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "order_total ot WHERE (SELECT DISTINCT pvo.seller_id FROM oc_purpletree_vendor_orders pvo WHERE ot.order_id = pvo.order_id LIMIT 1) = '".(int)$seller_id."' AND ot.order_id = '".(int)$order_id."' AND ot.code='sub_total'");

		return $query->row;
	}
        public function getSellerOrdersCommissionTotal($order_id,$seller_id=NULL){
		
		$sql = "SELECT SUM(commission) AS total_commission  FROM " . DB_PREFIX . "purpletree_vendor_commissions WHERE order_id = '".(int)$order_id."'";
		
		if(!empty($seller_id)){
			$sql .= " AND seller_id = '".(int)$seller_id."'";
		}
		
		$query = $this->db->query($sql);

		return $query->row;
	}
        
	public function getOrderProduct($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->row;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");

		return $query->rows;
	}

	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}

	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}
}