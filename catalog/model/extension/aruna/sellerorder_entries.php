<?php
class ModelExtensionArunaSellerOrderEntries extends Model {

        public function updateSellerOrderProduct ($data){
            $this->db->query("
                UPDATE 
                    " . DB_PREFIX . "order_product
                SET
                    quantity = '".$data['product_quantity']."',
                    price = '".$data['product_price']."',
                    total = '".$data['total']."'
                WHERE 
                    order_product_id = '".$data['order_product_id']."' AND order_id = '".$data['order_id']."' AND product_id = '".$data['product_id']."'  
            ");
            
            $this->db->query("
                UPDATE " . DB_PREFIX . "order 
                SET 
                    total = (SELECT 
                            SUM(total) AS order_total
                        FROM
                            oc_order_product
                        WHERE
                            order_id = '".$data['order_id']."'),
                    date_modified = NOW()
                WHERE
                    order_id = '".$data['order_id']."'
            ");
            
            $this->db->query("
                UPDATE " . DB_PREFIX . "order_total 
                SET 
                    value = (SELECT 
                            SUM(total) AS order_total
                        FROM
                            oc_order_product
                        WHERE
                            order_id = '".$data['order_id']."')
                WHERE
                    order_id = '".$data['order_id']."' AND code = 'sub_total'
            ");
            
            $sub_total_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total WHERE order_id = '".$data['order_id']."' AND code = 'sub_total'");
            $sub_total = $sub_total_query->row['value'];
            $shipping_query = $this->db->query("SELECT value FROM " . DB_PREFIX . "order_total WHERE order_id = '".$data['order_id']."' AND code = 'shipping'");
            $shipping = $shipping_query->row['value'];
            $total = (int)$sub_total + (int)$shipping;
            return $this->db->query("UPDATE " . DB_PREFIX . "order_total SET value = '". $total ."' WHERE order_id = '".$data['order_id']."' AND code = 'total'");
        }
        
        public function deleteSellerOrderProduct ($data){
            $this->updateSellerOrderProduct($data);
            $this->db->query("DELETE FROM " . DB_PREFIX . "order_product  WHERE order_product_id = '".$data['order_product_id']."' AND order_id = '".$data['order_id']."' AND product_id = '".$data['product_id']."'  ");
            $order_products = $this->db->query("SELECT total FROM " . DB_PREFIX . "order WHERE order_id = '".$data['order_id']."'");
            if((int)$order_products->row['total'] == 0){
                $this->db->query("DELETE FROM " . DB_PREFIX . "order_total WHERE order_id = '".$data['order_id']."'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "order WHERE order_id = '".$data['order_id']."'");
            }
            return $this->db->query("DELETE FROM " . DB_PREFIX . "order_option WHERE order_product_id = '".$data['order_product_id']."' AND order_id = '".$data['order_id']."'");
        }
        
        public function addSellerOrderProduct ($data){
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_product 
                                SET
                                order_id = '".$data['order_id']."',
                                product_id = '".$data['product_id']."',
                                name = (SELECT pd.name FROM oc_product_description pd WHERE product_id = '".$data['product_id']."'),
                                model = (SELECT p.model FROM oc_product p WHERE product_id = '".$data['product_id']."'),
                                quantity = '".$data['product_quantity']."',
                                price = '".$data['product_price']."',
                                total = '".$data['total']."',
                                tax = 0.0000,
                                reward = 0");
            $data['order_product_id'] = $this->db->getLastId();
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_option 
                                SET 
                                 order_product_id = '".$data['order_product_id']."',
                                 order_id = '".$data['order_id']."',
                                 product_option_id = '".$data['product_option_id']."',
                                 product_option_value_id = '".$data['product_option_value_id']."',
                                 name = (SELECT name FROM oc_option_description JOIN oc_product_option USING(option_id) WHERE product_option_id = ".$data['product_option_id']."),
                                 value = (SELECT name FROM oc_option_value_description JOIN oc_product_option_value using(option_value_id) WHERE product_option_value_id = ".$data['product_option_value_id']."),
                                 type = (SELECT type FROM oc_option JOIN oc_product_option using(option_id) WHERE product_option_id = ".$data['product_option_id'].")");
            
            return $this->updateSellerOrderProduct($data);
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
                SELECT DISTINCT
                    order_id
                FROM 
                    `" . DB_PREFIX . "order_product` op 
                WHERE 
                    (SELECT DISTINCT pvo.seller_id FROM oc_purpletree_vendor_orders pvo WHERE op.order_id = pvo.order_id) = '".$seller_id."'
                ";
            $query = $this->db->query($sql);
            return $query->rows;
        }
        
        public function getSellerCustomers($seller_id){
		$query = $this->db->query("SELECT DISTINCT customer_id, CONCAT(firstname, ' ', lastname) as customer_name FROM`" . DB_PREFIX . "order` JOIN `" . DB_PREFIX . "purpletree_vendor_orders` USING(order_id) WHERE seller_id = '" . (int)$seller_id . "'");

		return $query->rows;
	}
        
	public function getSellerOrderProducts($data = array()) {
		$sql = "SELECT DISTINCT
                            op.*,
                            oo.name AS option_name,
                            oo.value AS option_value,
                            (SELECT distinct pvo.order_status_id FROM oc_purpletree_vendor_orders pvo WHERE op.order_id = pvo.order_id) as seller_order_status_id,
                            (SELECT name FROM oc_order_status os WHERE seller_order_status_id = os.order_status_id) as seller_order_status_name,
                            bse.sync_id,
                            bsl.sync_name AS sync_name,
                            o.order_id,
                            CONCAT(o.firstname, ' ', o.lastname) as customer_name,
                            o.currency_code,
                            o.currency_value,
                            o.date_added,
                            o.date_modified,
                            o.shipping_code
                        FROM
                            " . DB_PREFIX . "order_product op
                                JOIN
                            " . DB_PREFIX . "order_option oo ON (oo.order_product_id = op.order_product_id)
                                JOIN
                            " . DB_PREFIX . "order o  ON (op.order_id = o.order_id)
                                LEFT JOIN
                            " . DB_PREFIX . "baycik_sync_entries bse USING (model)
                                LEFT JOIN
                            " . DB_PREFIX . "baycik_sync_list bsl ON (bsl.sync_id = bse.sync_id)
                             ";
                $sql .= "";
                
                  if (isset($data['filter_seller_id']) && $data['filter_seller_id'] != '*' ){
                        $seller_id = $data['filter_seller_id'];
                        $sql .= " WHERE (SELECT DISTINCT pvo.seller_id FROM oc_purpletree_vendor_orders pvo WHERE op.order_id = pvo.order_id) = '".$seller_id."' AND";
                } else {
                        $sql .= " WHERE ";
                }
                
                if (isset($data['filter_sync_id']) && $data['filter_sync_id'] != '*' ){
                        $sync_id = $data['filter_sync_id'];
                        $sql .= " bsl.sync_id  = '".(int)$sync_id . "' AND";
                } else {
                        $sql .= "  ";
                }
                
               
                
                 if (isset($data['filter_customer_id']) && $data['filter_customer_id'] != '*'){
                        $customer_id = $data['filter_customer_id'];
                        $sql .= " o.customer_id = '".(int)$customer_id . "'";
                } else {
                        $sql .= " o.customer_id > '0'";
                }
                
                if (isset($data['filter_order_id']) && $data['filter_order_id'] != '*' ){
                        $order_id = $data['filter_order_id'];
                        $sql .= " AND op.order_id = '".(int)$order_id . "'";
                } else {
                        $sql .= " AND op.order_id > '0'";
                }
                
                
		if (isset($data['filter_seller_order_status'])  && $data['filter_seller_order_status'] != '*') {
			$implode = array();

			$seller_order_statuses = explode(',', $data['filter_seller_order_status']);

			foreach ($seller_order_statuses as $seller_order_status_id) {
				$implode[] = "(SELECT distinct pvo.order_status_id FROM oc_purpletree_vendor_orders pvo WHERE op.order_id = pvo.order_id) = '" . (int)$seller_order_status_id . "'";
			}

			if ($implode) {
				$sql .= " AND (" . implode(" OR ", $implode) . ")";
			}
		} else {
			$sql .= " AND (SELECT distinct pvo.order_status_id FROM oc_purpletree_vendor_orders pvo WHERE op.order_id = pvo.order_id) > '0'";
		}
                
		if (!empty($data['filter_date_from'])) {
			$sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_from']) . "')";
		}

		if (!empty($data['filter_date_to'])) {
			$sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_to']) . "')";
		}
		if(empty($data['filter_date_from']) && empty($data['filter_date_to'])){
			$end_date = date('Y-m-d', strtotime("-30 days"));
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

			$sql .= " LIMIT " . $data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $array = [
                    'results' => $query->rows,
                    'total' => $query->num_rows
                ];
	}	
	public function getSellerOrdersTotal($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "purpletree_order_total WHERE seller_id = '".(int)$seller_id."' AND order_id = '".(int)$order_id."' AND code='sub_total'");

		return $query->row;
	}
	public function getTotalllseller($seller_id,$order_id){
		$query = $this->db->query("SELECT value AS total  FROM " . DB_PREFIX . "purpletree_order_total WHERE seller_id = '".(int)$seller_id."' AND order_id = '".(int)$order_id."' AND code='sub_total'");

		return $query->row;
	} 
}