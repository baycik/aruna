<?php

class ControllerExtensionModuleIssRelated extends Controller {

    public function index() {
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string) $this->request->get['path']);
        } else {
            $parts = array();
        }

        $category_id = end($parts);
//
//        $this->load->language('extension/module/iss_related');
//
//        $url = '';
//
//        if (isset($this->request->get['sort'])) {
//            $url .= '&sort=' . $this->request->get['sort'];
//        }
//
//        if (isset($this->request->get['order'])) {
//            $url .= '&order=' . $this->request->get['order'];
//        }
//
//        if (isset($this->request->get['limit'])) {
//            $url .= '&limit=' . $this->request->get['limit'];
//        }
//
//        if ($category_id) {
//            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));
//        } else {
//            $search=empty($this->request->get['search'])?"":$this->request->get['search'];
//            $data['action'] = str_replace('&amp;', '&', $this->url->link('product/search', 'search=' .$search));
//        }
//
//        if (isset($this->request->get['filter'])) {
//            $data['filter_category'] = explode(',', $this->request->get['filter']);
//        } else {
//            $data['filter_category'] = array();
//        }
//        

        $this->load->model('extension/module/iss_related');
        $data['filter'] = $this->model_extension_module_iss_related->getProductFilters();
        if( !$data['filter'] ){
            return;
        }


        
        return $this->load->view('extension/module/iss_related', $data);
    }

}
