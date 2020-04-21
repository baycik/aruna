<?php

class ControllerExtensionModuleIssFilter extends Controller {

    public function index() {
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = array();
        }

        $category_id = end($parts);

        $this->load->language('extension/module/iss_filter');

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        if ($category_id) {
            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));
        } else {
            $search=empty($this->request->get['search'])?"":$this->request->get['search'];
            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/search', 'search=' .$search));
        }

        if (isset($this->request->get['filter'])) {
            $data['filter_category'] = explode(',', $this->request->get['filter']);
        } else {
            $data['filter_category'] = array();
        }
        

        $this->load->model('extension/module/iss_filter');
        $data['filter'] = $this->model_extension_module_iss_filter->getProductFilters();
        if( !$data['filter'] ){
            return;
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['price_title'] = $this->language->get('price_title');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['heading_title'] = $this->language->get('heading_title');
        
        if ( isset($this->request->get['min']) && $this->request->get['min']>=$data['filter']['min_price_available'] ) {
            $data['filter_min']=$this->request->get['min'];
        } else {
            $data['filter_min']=$data['filter']['min_price_available'];
        }
        if ( isset($this->request->get['max']) && $this->request->get['max']<=$data['filter']['max_price_available'] ) {
            $data['filter_max']=$this->request->get['max'];
        } else {
            $data['filter_max']=$data['filter']['max_price_available'];
        }
        $data['min_price_available'] = $data['filter']['min_price_available'];
        $data['max_price_available'] = $data['filter']['max_price_available'];
        return $this->load->view('extension/module/iss_filter', $data);
    }

}
