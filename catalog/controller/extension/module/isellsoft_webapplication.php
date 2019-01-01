<?php

class ControllerExtensionModuleIsellsoftWebApplication extends controller{
    public function index(){
    }
    
    public function manifest(){
        $this->load->model('setting/setting');
	$config=$this->model_setting_setting->getSetting('config');
        $data=[
            'name'=>$config['config_meta_title'],
            'short_name'=>$config['config_name'],
            'start_url'=>HTTPS_SERVER,
            "display"=> "standalone",
            "background_color"=> "#00f",
            "theme_color"=>"#f00",
            "description"=> $config['config_meta_description'],
            "icons"=> [
                [
                    "src"=> $config['config_icon']
                ]
            ]
        ];
	header("Content-type: application/json");
        exit(json_encode($data));
    }
    public function service_worker(){
        header("Content-type:text/javascript");
        $cacheVersionNumber=date('z');
	?>
	var cacheVersion="iSellSoftCache<?php echo $cacheVersionNumber?>";
	var autoupdate=0;
	var resourcePattern=new RegExp(".+(.jpg|.jpeg|.png|.gif|.css|.js)$");
	
	this.addEventListener('install', function (event) {
	    console.log('iSellSoft Service Worker installed!!!');
	});
	this.addEventListener('message', function(event){
	    var message=event.data;
	    if( message==='clear_cache' ){
		caches.delete(cacheVersion).then(function(){
		    console.log('iSellSoftCache cleared!');
		});
	    }
	});
	this.addEventListener('fetch', function (event) {
	    event.respondWith(
		caches.match(event.request).then(function (cached_response) {
                    if ( cached_response && resourcePattern.test(event.request.url) ) {
                        return cached_response;
                    
                    }
		    var fetched_response=fetch(event.request).then(function (response) {
                        var resp2 = response.clone();
                        caches.open(cacheVersion).then(function (cache) {
                            cache.put(event.request, resp2);
                        });
			return response;
		    }).catch(function (e) {
			console.log('isell-error',e);
			return new Response('');
		    });
		    return cached_response || fetched_response;
		})
	    );
	});
	<?php
        exit;
    }
}