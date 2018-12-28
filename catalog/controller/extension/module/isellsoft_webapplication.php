<?php

class ControllerExtensionModuleIsellsoftWebApplication extends controller{
    public function index(){
        echo '------';
    }
    
    public function manifest(){
        $this->load->model('setting/setting');
        $logo=$this->model_setting_setting->getSettingValue('config_logo');
        
        $data=[
            'name'=>$this->model_setting_setting->getSettingValue('config_meta_title'),
            'start_url'=>HTTPS_SERVER,
            "display"=> "standalone",
            "background_color"=> "#00f",
            "description"=> $this->model_setting_setting->getSettingValue('config_meta_description'),
            "icons"=> [
                [
                    "src"=> $logo,
                    "sizes"=> "48x48",
                    "type"=> "image/png"
                ]
            ]
        ];
        exit(json_encode($data));
    }
}