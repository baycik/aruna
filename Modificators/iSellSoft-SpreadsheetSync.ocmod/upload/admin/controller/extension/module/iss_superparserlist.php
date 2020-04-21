<?php

class ControllerExtensionModuleISSSuperparserlist extends Controller {
    
    private $error = array();

    public function index() {
	$this->load->language('extension/module/iss_superparserlist');

	$this->document->setTitle($this->language->get('heading_title'));
        
        if(!$this->checkDatabase()) {
            $this->installDatabase();
        }
        
	$data['back'] = $this->url->link('extension/module/iss_superparserlist', '', true);
               
	$url = '';
        
	$data['breadcrumbs'] = array();

	$data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

	$data['breadcrumbs'][] = array(
	    'text' => $this->language->get('heading_title'),
	    'href' => $this->url->link('extension/module/iss_superparserlist', $url, true)
	);
        $user_id = $this->customer->getId();
        $data['user_token'] = $this->session->data['user_token'];
	$data['heading_title'] = $this->language->get('heading_title');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['column_right'] = $this->load->controller('common/column_right');
	$data['content_top'] = $this->load->controller('common/content_top');
	$data['content_bottom'] = $this->load->controller('common/content_bottom');
	$data['footer'] = $this->load->controller('common/footer');
	$data['header'] = $this->load->controller('common/header');
	$data['user_id'] = $user_id;
        
	$this->load->model('extension/module/iss_supersync/setup');
        $data['sync_list'] = $this->model_extension_module_iss_supersync_setup->getSyncList($user_id);
        $data['parser_list'] = $this->model_extension_module_iss_supersync_setup->getParserList($user_id);
       
	$this->response->setOutput($this->load->view('extension/module/iss_superparserlist', $data));
    }
       
    public function startParsing(){
        if( isset($this->request->post['code']) ){
            $_FILES[0] = $this->request->post['code'];
        
        }
        
        $sync_id = $this->request->post['sync_id'];
        if( !$sync_id ){
            echo "Source hasn't been selected";
            return;
        }
        
        if( !isset($this->request->post['code']) ){
            $this->load->model('extension/module/iss_supersync/setup');
            echo $this->model_extension_module_iss_supersync_setup->updateParserConfig($sync_id);
        }
        
        $this->load->model('extension/module/iss_supersync/parse');
        echo $this->model_extension_module_iss_supersync_parse->initParser($sync_id,'update_all_entries');
    }
    
       
    public function addParser(){
        $parser_id = $this->request->post['parser_id'];
        if( !$parser_id ){
            echo "No parser selected";
            return;
        }
        $seller_id = $this->customer->getId();
        $this->load->model('extension/module/iss_supersync/setup');
        $this->model_extension_module_iss_supersync_setup->addParser($seller_id,$parser_id);
        $this->response->redirect($this->url->link('extension/module/iss_superparserlist', 'user_token=' . $this->session->data['user_token'], true));
    }
    public function deleteParser(){

        $sync_id = $this->request->post['sync_id'];
        if( !$sync_id ){
            echo "No sync selected";
            return;
        }
        $seller_id = $this->customer->getId();
        $this->load->model('extension/module/iss_supersync/setup');
        $this->model_extension_module_iss_supersync_setup->deleteParser($seller_id,$sync_id);
    }
    public function checkDatabase() {
        $this->load->model('extension/module/iss_supersync/setup');
        $database_found = $this->model_extension_module_iss_supersync_setup->validateTable();
        if(!$database_found) {
            return false;
        } 

        return true;
    }
    public function installDatabase() {
        $this->load->model('extension/module/iss_supersync/setup');
        $this->model_extension_module_iss_supersync_setup->createTables();
        return true;
    }
}
