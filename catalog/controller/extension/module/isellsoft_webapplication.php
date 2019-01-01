<?php

class ControllerExtensionModuleIsellsoftWebApplication extends controller{
    public function index(){
        echo '------';
    }
    
    public function manifest(){
        $this->load->model('setting/setting');
	$config=$this->model_setting_setting->getSetting('config');
        $data=[
            'name'=>$config['config_meta_title'],
            'start_url'=>HTTPS_SERVER,
            "display"=> "standalone",
            "background_color"=> "#00f",
            "description"=> $config['config_meta_description'],
            "icons"=> [
                [
                    "src"=> $config['config_logo']
                ]
            ]
        ];
	header("Content-type: application/json");
        exit(json_encode($data));
    }
}