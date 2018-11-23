<?php

class ControllerExtensionModuleSoCallForPrice extends Controller {
	public function index() {
		$data       = array();

        if (isset($this->request->get['product_id'])) {
            $data['product_id'] = (int)$this->request->get['product_id'];
        } else {
            $data['product_id'] = 0;
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['base'] = $this->config->get('config_ssl');
        } else {
            $data['base'] = $this->config->get('config_url');
        }

        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        $this->response->setOutput($this->load->view('extension/module/so_call_for_price/form', $data));
	}

    function sendData() {
        $this->load->language('extension/module/so_call_for_price');
        $json = array();
        if (isset($_POST['isAjax']) && $_POST['isAjax'] == 1 && isset($_POST['product_id'])) {
            if (!isset($_POST['name']) || trim($_POST['name']) == '') {
                $json['errors'][] = array('code'=>701, 'error'=>$this->language->get('error_name'));
                // $json['status'] = false;
            }

            if (!isset($_POST['email']) || trim($_POST['email']) == '' || (!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL))) {
                $json['errors'][] = array('code'=>702, 'error'=>$this->language->get('error_email'));
                // $json['status'] = false;
            }

            if (!isset($_POST['number']) || trim($_POST['number']) == '') {
                $json['errors'][] = array('code'=>703, 'error'=>$this->language->get('error_number'));
                // $json['status'] = false;
            }

            if (!isset($_POST['country']) || trim($_POST['country']) == '') {
                $json['errors'][] = array('code'=>704, 'error'=>$this->language->get('error_country'));
                // $json['status'] = false;
            }

            if (!isset($_POST['message']) || trim($_POST['message']) == '') {
                $json['errors'][] = array('code'=>705, 'error'=>$this->language->get('error_message'));
                // $json['status'] = false;
            }

            if (!isset($json['errors'])) {
                $this->load->model('extension/module/so_call_for_price');

                if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                    $base = $this->config->get('config_ssl');
                } else {
                    $base = $this->config->get('config_url');
                }

                $data = array(
                    'product_id'    => $_POST['product_id'],
                    'product_link'  => $this->url->link('product/product', 'product_id=' . $_POST['product_id']),
                    'shop_url'      => $base,
                    'name'          => trim($_POST['name']),
                    'email'         => trim($_POST['email']),
                    'number'        => trim($_POST['number']),
                    'country'       => trim($_POST['country']),
                    'message'       => trim($_POST['message'])
                );
                $sent = $this->model_extension_module_so_call_for_price->sendData($data);
                if ($sent) {
                    $json['status'] = 1;
                    $json['success'] = $this->language->get('text_success');
                }
                else {
                    $json['status'] = 2;
                    $json['error'] = $this->language->get('text_email_not_sent');
                }
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}