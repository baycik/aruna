<?php

class ModelExtensionModuleIssRelated extends Model {

    public function getRelated($product_id) {
        $this->load->model('catalog/product');
        $product = $this->model_catalog_product->getProduct($product_id);
        $search_query=[
            'filter_name'=>$product['name'],
            'start'=>0,
            'limit'=>6,
            'sort'=>''
            ];
        
            print_r($search_query);
        
        
        $matches=$this->model_catalog_product->getProducts( $search_query );
        $related=[];
        foreach($matches as $match){
            if( $match['product_id']==$product_id ){
                continue;
            }
            $related[]=$match;
        }
        return $related;
    }

}