<?php

class ModelExtensionModuleSoCallForPrice extends Model {

	public function sendData($data) {
		if ($this->config->get('module_so_call_for_price_send_mail_to')) {
			$this->load->language('extension/module/so_call_for_price');
			$this->load->model('catalog/product');
			$this->load->model('localisation/country');

			$product_info = $this->model_catalog_product->getProduct($data['product_id']);
			$country = $this->model_localisation_country->getCountry($data['country']);
			
			$mail = new Mail();
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8'));
			
			$message = "Hello, ".html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
			$message .= "\n";
			$message .= "Thank you for your interest in our ".html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8')." ".html_entity_decode($data['product_link'], ENT_QUOTES, 'UTF-8');
			$message .= "\n";
			$message .= "Your request has been forwarded to the concerned department and we will be in touch with you shorly";
			$message .= "\n\n\n";
			$message .= "Your information:";
			$message .= "\n";
			$message .= "Email: ".$data['email'];
			$message .= "\n";
			$message .= "Contact Number: ".$data['number'];
			$message .= "\n";
			$message .= "Country: ".$country['name'];
			$message .= "\n";
			$message .= "Inquiry Details: ".$data['message'];
			$message .= "\n\n\n";
			$message .= "Best Regards";
			$message .= "\n";
			$message .= $this->config->get('config_name');
			$message .= "\n";
			$message .= $data['shop_url'];

			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);

			$emails = explode(',', $this->config->get('module_so_call_for_price_send_mail_to'));
			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					if (!$mail->send()) {
						return false;
					}
					return true;
				}
			}

			if ($this->config->get('module_so_call_for_price_send_mail_customer')) {
				if ($email && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($data['email']);
					if (!$mail->send()) {
						return false;
					}
					return true;
				}
			}
		}
	}
}