<?php

class ModelExtensionModuleIssFilter extends Model {

    public function getProductFilters() {
        $this->language_id = (int) $this->config->get('config_language_id');
        if( empty($this->registry->matches_filled) ){
            return false;//must be called after model_catalog_product->getProducts();
        }
        $sql_available_filters = "
                SELECT 
                    fd.filter_id,
                    fd.name filter_name,
                    fd.filter_group_id,
                    (SELECT name FROM " . DB_PREFIX . "filter_group_description fgd WHERE fgd.filter_group_id=fd.filter_group_id AND fgd.language_id = {$this->language_id}) filter_group_name,
                    SUM(IF(price_match AND filter_match,1,0)) product_count
                FROM 
                    " . DB_PREFIX . "filter_description fd
                        JOIN
                    " . DB_PREFIX . "product_filter pf USING(filter_id)
                        JOIN
                    tmp_matches USING(product_id)
                WHERE
                    fd.language_id={$this->language_id}
                GROUP BY filter_id
                ORDER BY filter_group_name,SUM(IF(price_match AND filter_match,1,0))=0,fd.name*1,fd.name";
        $result = $this->db->query($sql_available_filters);
        if (!$result->num_rows) {
            return [];
        }
        $filter_group_data = [];
        foreach ($result->rows as $filter_data) {
            $filter_group_id = $filter_data['filter_group_id'];
            if (!isset($filter_group_data[$filter_group_id])) {
                $filter_group_data[$filter_group_id] = [
                    'filter_group_id' => $filter_group_id,
                    'name' => $filter_data['filter_group_name'],
                    'filter' => []
                ];
            }
            $filter_group_data[$filter_group_id]['filter'][] = [
                'filter_id' => $filter_data['filter_id'],
                'name' => $filter_data['filter_name'] . ($filter_data['product_count'] > 0 ? " ({$filter_data['product_count']})" : ""),
                'count' => $filter_data['product_count']
            ];
        }
        $filter=[
            'filter_groups'=>$filter_group_data,
            'min_price_available'=>0,
            'max_price_available'=>0
        ];
        $sql_minmax="
            SELECT
               MIN(IF(discount,discount,price)) AS min_price_available,
               MAX(IF(discount,discount,price)) AS max_price_available
            FROM
                tmp_matches
            WHERE
                filter_match=1";
        $result_minmax=$this->db->query($sql_minmax);
        if( $result_minmax->row ){
            $filter['min_price_available']=$result_minmax->row['min_price_available'];
            $filter['max_price_available']=$result_minmax->row['max_price_available'];
        }
        return $filter;
    }

}
