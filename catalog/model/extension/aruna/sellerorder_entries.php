<?php
class ModelExtensionArunaSellerOrderEntries extends Model {

        public function updateSellerOrderProduct ($data){
            $sql = "
                UPDATE 
                    baycik_aruna.oc_purpletree_vendor_orders pvo
                            JOIN
                    baycik_aruna.oc_order o USING(order_id)  
                           JOIN
                    baycik_aruna.oc_order_product op USING(product_id)
                SET
                    pvo.quantity = '".$data['product_quantity']."',
                    op.quantity = '".$data['product_quantity']."',
                    pvo.unit_price = '".$data['product_price']."',
                    op.price = '".$data['product_price']."',
                    pvo.total_price = '".$data['total']."',
                    op.total = '".$data['total']."',
                    o.total = '".$data['total']."'
                WHERE 
                    pvo.seller_id = '".$data['seller_id']."' AND pvo.order_id = '".$data['order_id']."' AND pvo.product_id = '".$data['product_id']."'  
            ";
            echo $sql;
            die;
            return $this->db->query($sql);
        }
    
        public function getSyncList ($seller_id){
            $sql="
                SELECT 
                    sync_id, 
                    sync_name 
                FROM 
                    `" . DB_PREFIX . "baycik_sync_list` 
                WHERE 
                    seller_id = $seller_id
                ";
            $query = $this->db->query($sql);

            return $query->rows;
        }
        
        public function getOrderIds ($seller_id){
            $sql="
                SELECT 
                    order_id
                FROM 
                    `" . DB_PREFIX . "purpletree_vendor_orders` 
                WHERE 
                    seller_id = $seller_id
                ";
            $query = $this->db->query($sql);

            return $query->rows;
        }
        
        public function getSellerCustomers($seller_id){
		$query = $this->db->query("SELECT DISTINCT customer_id, CONCAT(firstname, ' ', lastname) as customer_name FROM`" . DB_PREFIX . "order` JOIN `" . DB_PREFIX . "purpletree_vendor_orders` USING(order_id) WHERE seller_id = '" . (int)$seller_id . "'");

		return $query->rows;
	}
        
	public function getSellerOrderProducts($data = array()) {
		$sql = "SELECT 
                            op.order_product_id,
                            pvo.order_status_id AS seller_order_status_id,
                            os.name AS seller_order_status_name,
                            o.order_status_id AS admin_order_status_id,  
                            CONCAT(o.firstname, ' ', o.lastname) AS customer_name,  
                            bse.sync_id,
                            bsl.sync_name AS sync_name,
                            o.order_id, pvo.product_id,  
                            oo.name AS option_name, oo.value AS option_value,
                            pvo.quantity, op.name AS product_name, op.model AS model, 
                            o.shipping_code, op.total, op.price, o.currency_code, o.currency_value, o.date_added, o.date_modified 
                        FROM
                            `" . DB_PREFIX . "purpletree_vendor_orders` pvo
                                JOIN
                            `" . DB_PREFIX . "order_product` op USING(order_id)
                                        JOIN
                            `" . DB_PREFIX . "baycik_sync_entries` bse USING(model)
                                        JOIN
                            `" . DB_PREFIX . "order` o USING(order_id)
                                        JOIN
                            `" . DB_PREFIX . "order_option` oo ON(oo.order_product_id = op.order_product_id)
                                        JOIN
                            `" . DB_PREFIX . "baycik_sync_list` bsl ON(bsl.sync_id = bse.sync_id)
                                        JOIN
                            `" . DB_PREFIX . "order_status` os ON (os.order_status_id = pvo.order_status_id)
                             ";
                $sql .= "";
                if (isset($data['filter_sync_id']) && $data['filter_sync_id'] != '*' ){
                        $sync_id = $data['filter_sync_id'];
                        $sql .= "WHERE bsl.sync_id  = '".(int)$sync_id . "' AND";
                } else {
                        $sql .= "WHERE";
                }
                
                 if (isset($data['filter_seller_id']) && $data['filter_seller_id'] != '*' ){
                        $seller_id = $data['filter_seller_id'];
                        $sql .= " pvo.seller_id = '".(int)$seller_id . "'";
                } else {
                        $sql .= "pvo.seller_id > 0 ";
                }
                
                 if (isset($data['filter_order_id']) && $data['filter_order_id'] != '*' ){
                        $order_id = $data['filter_order_id'];
                        $sql .= " AND pvo.order_id = '".(int)$order_id . "'";
                } else {
                        $sql .= " AND pvo.order_id > '0'";
                }
                
                 if (isset($data['filter_customer_id']) && $data['filter_customer_id'] != '*'){
                        $customer_id = $data['filter_customer_id'];
                        $sql .= " AND o.customer_id = '".(int)$customer_id . "'";
                } else {
                        $sql .= " AND o.customer_id > '0'";
                }
                
                
		if (isset($data['filter_order_status'])  && $data['filter_order_status'] != '*') {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "pvo.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " AND pvo.order_status_id > '0'";
		}
		if (isset($data['filter_admin_order_status'])  && $data['filter_admin_order_status'] != '*') {
			$implode1 = array();

			$order_statuses1 = explode(',', $data['filter_admin_order_status']);

			foreach ($order_statuses1 as $order_status_id) {
				$implode1[] = "o.order_status_id > '" . (int)$order_status_id . "'";
			}

			if ($implode1) {
				$sql .= " AND (" . implode(" OR ", $implode1) . ")";
			}
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(pvo.created_at) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(pvo.created_at) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(empty($data['filter_date_from']) && empty($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-30 days"));
			$sql .= " AND DATE(pvo.created_at) >= '".$end_date."'";
			$sql .= " AND DATE(pvo.created_at) <= '".date('Y-m-d')."'";
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

			$sql .= " LIMIT " . $data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
                echo $sql;
                die;
		return $query->rows;
	}
        
        public function getSellerOrdersProductTotal($seller_id){
                $sql = "
                    SELECT 
                        *  
                    FROM 
                        " . DB_PREFIX . "purpletree_vendor_orders pvo 
                    WHERE 
                        seller_id = '".(int)$seller_id."'
                    ";
		$query = $this->db->query($sql);
		return $query->num_rows;
	}
	
	public function getSellerOrdersTotal($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "purpletree_order_total WHERE seller_id = '".(int)$seller_id."' AND order_id = '".(int)$order_id."' AND code='sub_total'");

		return $query->row;
	}
	public function getTotalllseller($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "purpletree_order_total WHERE seller_id = '".(int)$seller_id."' AND order_id = '".(int)$order_id."' AND code='sub_total'");

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