<?php

class ControllerExtensionModuleIssWebapp extends controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/module/iss_webapp');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $old_data = $this->model_setting_setting->getSetting('module_iss_webapp');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->post['module_iss_webapp_icon']) && is_file(DIR_IMAGE . $this->request->post['module_iss_webapp_icon'])) {
                $icon_info = getimagesize(DIR_IMAGE . $this->request->post['module_iss_webapp_icon']);
                $this->request->post['module_iss_webapp_icon_mime'] = $icon_info['mime'];
                $this->request->post['module_iss_webapp_icon_size'] = $icon_info[0] . 'x' . $icon_info[1];
            }
            
            $this->model_setting_setting->editSetting('module_iss_webapp', $this->request->post);
            $data=$this->request->post;
            if( !isset($old_data['module_iss_webapp_status']) ){
                $old_data['module_iss_webapp_status']=0;
            }
            if( !isset($old_data['module_iss_webapp_ogstatus']) ){
                $old_data['module_iss_webapp_ogstatus']=0;
            }
            if( !isset($old_data['module_iss_webapp_themecolor']) ){
                $old_data['module_iss_webapp_themecolor']='#fff';
            }
            if( $old_data['module_iss_webapp_status']!=$data['module_iss_webapp_status'] || $old_data['module_iss_webapp_themecolor']!=$data['module_iss_webapp_themecolor'] ){
                $this->installWebappHeadTags( $data );
                $data['mod_refresh_url']=$this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true);
            }
            if( $old_data['module_iss_webapp_status']!=$data['module_iss_webapp_status'] || $old_data['module_iss_webapp_ogstatus']!=$data['module_iss_webapp_ogstatus'] ){
                $this->installWebappOpengraph( $data );
                $data['mod_refresh_url']=$this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true);
            }
            //$data['error_warning'] = $this->language->get('text_success');

            //$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        } else {
            $data=$old_data;
        }


        $config = $this->model_setting_setting->getSetting('config');

        if (!isset($data['module_iss_webapp_name'])) {
            $data['module_iss_webapp_name'] = $config['config_meta_title'];
        }
        if (!isset($data['module_iss_webapp_shortname'])) {
            $data['module_iss_webapp_shortname'] = $config['config_name'];
        }
        if (!isset($data['module_iss_webapp_description'])) {
            $data['module_iss_webapp_description'] = $config['config_meta_description'];
        }
        if (!isset($data['module_iss_webapp_starturl'])) {
            $data['module_iss_webapp_starturl'] = HTTPS_CATALOG;
        }
        $this->load->model('tool/image');
        
        if (isset($this->request->post['module_iss_webapp_icon'])) {
            $data['module_iss_webapp_icon'] = $this->request->post['module_iss_webapp_icon'];
        }
        
        if (isset($data['module_iss_webapp_icon']) && is_file(DIR_IMAGE . $data['module_iss_webapp_icon'])) {
            $data['module_iss_webapp_icon_thumb'] = $this->model_tool_image->resize($data['module_iss_webapp_icon'], 100, 100);
        } else {
            $data['module_iss_webapp_icon_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }
        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (isset($this->request->post['module_iss_webapp_status'])) {
            $data['module_iss_webapp_status'] = $this->request->post['module_iss_webapp_status'];
        } else {
            $data['module_iss_webapp_status'] = $this->config->get('module_iss_webapp_status');
        }


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/iss_webapp', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/iss_webapp', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/iss_webapp', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/iss_webapp')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    
    private function installWebappOpengraph( $webapp_data ){
        if( !$webapp_data['module_iss_webapp_status'] || !$webapp_data['module_iss_webapp_ogstatus'] ){
            $this->load->model('setting/modification');
            $code = 'iSellSoft-WebApp-Opengraph';

            // Check to see if the modification is already installed or not.
            $modification_info = $this->model_setting_modification->getModificationByCode($code);

            if ($modification_info) {
                $this->model_setting_modification->deleteModification($modification_info['modification_id']);
            }
            return true;
        }
        
        $result = $this->db->query("SELECT extension_install_id FROM `" . DB_PREFIX . "extension_path` WHERE path LIKE '%/iss_webapp.php' LIMIT 1");
        if( $result->row && $result->row['extension_install_id'] ){
            $extension_install_id=$result->row['extension_install_id'];
        } else {
            $extension_install_id=0;
        }
        
        $xml=<<<'EOT'
<modification>
    <name>iSellSoft WebApp OpenGraph 1.2</name>
    <code>iSellSoft-WebApp-OpenGraph 1.2</code>
    <version>1.0</version>
    <author>iSellSoft Team</author>
    <file path="catalog/view/theme/*/template/common/header.twig">
	<operation error="skip"> 
	    <search><![CDATA[
            </head>
            ]]></search>
	    <add offset="0" position="before"><![CDATA[
                {% if OpenGraphData %}
                <meta property="og:title" content="{{OpenGraphData.title}}" />
                <meta property="og:site_name" content="{{ title }}" />
                <meta property="og:type" content="{{OpenGraphData.type}}" />
                <meta property="og:url" content="{{OpenGraphData.url}}" />
                <meta property="og:image" content="{{OpenGraphData.image}}" />
                <meta property="og:description" content="{{OpenGraphData.description}}" />
                {% else %}
                <meta property="og:title" content="{{ title }}" />
                <meta property="og:site_name" content="{{ title }}" />
                <meta property="og:type" content="website" />
                <meta property="og:url" content="{{ home }}" />
                <meta property="og:image" content="{{ logo }}" />
                <meta property="og:description" content="{{ description }}" />
                {% endif %}
            ]]></add>
	</operation>
    </file>
    <file path="catalog/controller/product/product.php">
	<operation error="skip"> 
	    <search><![CDATA[
			if ($product_info['minimum']) {
            ]]></search>
	    <add offset="0" position="before"><![CDATA[
                        $og_title=$data['price'];
                        if(!empty($data['special'])){
                            $og_title=$data['special'];
                        }
                        if( !empty($product_option_value_data) ){
                            $og_title.=' ('.implode(', ',array_column($product_option_value_data, 'name')).')';
                        }
                        $og_title.=' '.$product_info['name'];
                        $this->registry->set('OpenGraphData', [
                            'image'=>$data['thumb'],
                            'title'=>$og_title,
                            'type'=>'website',
                            'url'=>$this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id']),
                            'description'=>$product_info['meta_description']
                        ]);
            ]]></add>
	</operation>
    </file>
    <file path="catalog/controller/common/header.php">
	<operation error="skip"> 
	    <search><![CDATA[
		$data['base'] = $server;
            ]]></search>
	    <add offset="0" position="before"><![CDATA[
                $data['OpenGraphData']=$this->registry->get('OpenGraphData');          
            ]]></add>
	</operation>
    </file>
</modification>
EOT;
        $this->installModification($extension_install_id, $xml);
    }
    
    private function installWebappHeadTags( $webapp_data ){
        if( empty($webapp_data['module_iss_webapp_status']) ){
            $this->load->model('setting/modification');
            $code = 'iSellSoft-WebApp-SW+Manifest';

            // Check to see if the modification is already installed or not.
            $modification_info = $this->model_setting_modification->getModificationByCode($code);

            if ($modification_info) {
                $this->model_setting_modification->deleteModification($modification_info['modification_id']);
            }
            return true;
        }
        
        $result = $this->db->query("SELECT extension_install_id FROM `" . DB_PREFIX . "extension_path` WHERE path LIKE '%/iss_webapp.php' LIMIT 1");
        if( $result->row && $result->row['extension_install_id'] ){
            $extension_install_id=$result->row['extension_install_id'];
        } else {
            $extension_install_id=0;
        }
        
        $xml=<<<EOT
<modification>
    <name>iSellSoft WebApp SW+Manifest 1.2</name>
    <code>iSellSoft-WebApp-SW+Manifest 1.2</code>
    <version>1.0</version>
    <author>iSellSoft Team</author>
    <file path="catalog/view/theme/*/template/common/header.twig">
	<operation error="skip"> 
	    <search><![CDATA[
            </head>
            ]]></search>
	    <add offset="0" position="before"><![CDATA[
                <meta name="theme-color" content="{$webapp_data['module_iss_webapp_themecolor']}">
                <link rel="manifest" href="?route=extension/module/isellsoft_webapplication/manifest">
		<script src="?route=extension/module/isellsoft_webapplication/service_worker" type="text/javascript"></script>
		<script type="text/javascript">
		if ( localStorage.getItem("disableSW")!=1 && ('serviceWorker' in navigator) ) {
		    navigator.serviceWorker.register('?route=extension/module/isellsoft_webapplication/service_worker', {scope: './'}).then(function(registration) {
                        registration.update();
                    }).catch(function(error) {
			console.log('serviceWorker Registration failed with ' + error);
		    });
		}
		</script>
            ]]></add>
	</operation>
    </file>
</modification>
EOT;
        $this->installModification($extension_install_id, $xml);
    }
    
    private function installModification($extension_install_id, $xml) {
        $this->load->language('marketplace/install');

        if (!$this->user->hasPermission('modify', 'marketplace/install')) {
            die($this->language->get('error_permission'));
        }

        if ($xml) {
            $this->load->model('setting/modification');
            try {
                $error='';
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadXml($xml);

                $name = $dom->getElementsByTagName('name')->item(0);

                if ($name) {
                    $name = $name->nodeValue;
                } else {
                    $name = '';
                }

                $code = $dom->getElementsByTagName('code')->item(0);

                if ($code) {
                    $code = $code->nodeValue;

                    // Check to see if the modification is already installed or not.
                    $modification_info = $this->model_setting_modification->getModificationByCode($code);

                    if ($modification_info) {
                        $this->model_setting_modification->deleteModification($modification_info['modification_id']);
                    }
                } else {
                    $error = $this->language->get('error_code');
                }

                $author = $dom->getElementsByTagName('author')->item(0);

                if ($author) {
                    $author = $author->nodeValue;
                } else {
                    $author = '';
                }

                $version = $dom->getElementsByTagName('version')->item(0);

                if ($version) {
                    $version = $version->nodeValue;
                } else {
                    $version = '';
                }

                $link = $dom->getElementsByTagName('link')->item(0);

                if ($link) {
                    $link = $link->nodeValue;
                } else {
                    $link = '';
                }

                if (!$error) {


                    $modification_data = array(
                        'extension_install_id' => $extension_install_id,
                        'name' => $name,
                        'code' => $code,
                        'author' => $author,
                        'version' => $version,
                        'link' => $link,
                        'xml' => $xml,
                        'status' => 1
                    );

                    $this->model_setting_modification->addModification($modification_data);
                }
            } catch (Exception $exception) {
                $error=sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
            }
            return $error;
        }
    }

}
