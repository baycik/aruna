<?php
class ControllerExtensionModuleIssFilter extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/iss_filter');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module');
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                        $this->request->post['name'] = 'Search And Filter';
                        
			$this->model_setting_setting->editSetting('module_iss_filter', $this->request->post);
			if (!isset($this->request->post['module_id'])) {
				$this->model_extension_module->addModule('iss_filter', $this->request->post);
			} else {
				$this->model_extension_module->editModule($this->request->post['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
		}

                
                
                
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
                
                if( $this->isKeysInstalled() ){
                    $data['iskeysinstalled'] = $this->language->get('keys_installed');
                } else {
                    $data['error_warning'] .= $this->language->get('keys_uninstalled');
                }

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/iss_filter', 'token=' . $this->session->data['token'], true)
		);
                if(!empty($this->request->get['module_id'])){
                    $data['module_id'] = $this->request->get['module_id'];
                }
		$data['heading_title'] = $this->language->get('heading_title');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['install_keys'] = $this->language->get('install_keys');
		$data['uninstall_keys'] = $this->language->get('uninstall_keys');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_edit'] = $this->language->get('text_edit');
                
                
		$data['action'] = $this->url->link('extension/module/iss_filter', 'token=' . $this->session->data['token'], true);
		$data['installKeys'] = $this->url->link('extension/module/iss_filter/installKeys', 'token=' . $this->session->data['token'], true);
		$data['uninstallKeys'] = $this->url->link('extension/module/iss_filter/uninstallKeys', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		if (isset($this->request->post['module_filter_status'])) {
			$data['module_iss_filter_status'] = $this->request->post['module_iss_filter_status'];
		} else {
			$data['module_iss_filter_status'] = $this->config->get('module_iss_filter_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/iss_filter', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/iss_filter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
        
        private function checkKey($table,$key){
            $result=$this->db->query("SELECT COUNT(1) installed FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE table_schema=DATABASE() AND table_name='$table' AND index_name='$key'");
            return $result->row['installed']*1;
        }
        
        private function isKeysInstalled(){
            return  $this->checkKey('oc_filter_description', 'iss_index1') 
                    && $this->checkKey('oc_product_filter', 'iss_index1')
                    && $this->checkKey('oc_product', 'iss_fti1')
                    && $this->checkKey('oc_product_description', 'iss_fti2');
        }
        
        public function installKeys(){
            $this->load->language('extension/module/iss_filter');
            try{
                if( !$this->checkKey('oc_filter_description', 'iss_index1') ){
                    $this->db->query("ALTER TABLE `oc_filter_description` ADD INDEX `iss_index1` (`language_id` ASC);");
                }
                if( !$this->checkKey('oc_product_filter', 'iss_index1') ){
                    $this->db->query("ALTER TABLE `oc_product_filter` ADD INDEX `iss_index1` (`product_id` ASC);");
                }
                if( !$this->checkKey('oc_product', 'iss_fti1') ){
                    $this->db->query("ALTER TABLE `oc_product` ADD FULLTEXT INDEX `iss_fti1` (`model` ASC, `ean` ASC, `jan` ASC, `isbn` ASC, `mpn` ASC, `upc` ASC, `sku` ASC);");
                }
                if( !$this->checkKey('oc_product_description', 'iss_fti2') ){
                    $this->db->query("ALTER TABLE `oc_product_description` 
                                        ADD FULLTEXT INDEX `iss_fti2` (`name` ASC),
                                        ADD FULLTEXT INDEX `iss_fti3` (`description` ASC),
                                        ADD FULLTEXT INDEX `iss_fti4` (`tag` ASC);");
                }
            } 
            catch(Exception $ex){
                $this->error['warning']=$this->language->get('keys_error').'<br>'.($ex->getMessage());
            }
            $this->index();
        }
        
        public function uninstallKeys(){
            $this->load->language('extension/module/iss_filter');
            try{
                if( $this->checkKey('oc_filter_description', 'iss_index1') ){
                    $this->db->query("ALTER TABLE `oc_filter_description` DROP INDEX `iss_index1` ;");
                }
                if( $this->checkKey('oc_product_filter', 'iss_index1') ){
                    $this->db->query("ALTER TABLE `oc_product_filter` DROP INDEX `iss_index1`");
                }
                if( $this->checkKey('oc_product', 'iss_fti1') ){
                    $this->db->query("ALTER TABLE `oc_product` DROP INDEX `iss_fti1`");
                }
                if( $this->checkKey('oc_product_description', 'iss_fti2') ){
                    $this->db->query("ALTER TABLE `oc_product_description` 
                                    DROP INDEX `iss_fti2`,
                                    DROP INDEX `iss_fti3`,
                                    DROP INDEX `iss_fti4`;");
                }
            } 
            catch(Exception $ex){
                $this->error['warning']=$this->language->get('keys_error').'<br>'.($ex->getMessage());
            }
            $this->index();
        }
}