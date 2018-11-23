<?php
class ModelExtensionModuleSonewlettercustompopup extends Model {
	public function subscribes($data) {
		$res = $this->db->query("select * from ". DB_PREFIX ."newsletter_custom_popup where news_email='".$data['email']."'");
		if($res->num_rows == 1)
		{
			return "Email Already Exist";
		}
		else
		{
			if($this->db->query("INSERT INTO " . DB_PREFIX . "newsletter_custom_popup(news_email,news_create_date,news_status) values ('".$data['email']."' , '".$data['createdate']."' , '".$data['status']."')"))
			{
				return "Subscription Successfull";
			}
			else
			{
				return "Subscription Fail";
			}
		}
	}
		
}