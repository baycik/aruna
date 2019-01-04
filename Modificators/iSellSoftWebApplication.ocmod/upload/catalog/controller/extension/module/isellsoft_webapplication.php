<?php

class ControllerExtensionModuleIsellsoftWebApplication extends controller{
    public function index(){
    }
    
    public function manifest(){
        $this->load->model('setting/setting');
	$config=$this->model_setting_setting->getSetting('iss_webapp');
	header("Content-type: application/manifest+json");
        if( !isset($config['iss_webapp_status']) || !$config['iss_webapp_status'] ){
            exit;
        }
        $data=[
            'name'=>$config['iss_webapp_name'],
            'short_name'=>$config['iss_webapp_shortname'],
            'start_url'=>$config['iss_webapp_starturl'],
            "display"=> $config['iss_webapp_display'],
            "background_color"=> $config['iss_webapp_bgcolor'],
            "theme_color"=>$config['iss_webapp_themecolor'],
            "description"=> $config['iss_webapp_description'],
            "icons"=> [
                [
                    "src"=> "image/".$config['iss_webapp_icon'],
                    "type"=> $config['iss_webapp_icon_mime'],
                    "sizes"=> $config['iss_webapp_icon_size']
                ]
            ]
        ];
        exit(json_encode($data));
    }
    public function service_worker(){
        header("Content-type:text/javascript");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        $this->load->model('setting/setting');
	$config=$this->model_setting_setting->getSetting('iss_webapp');
        $dayPeriod=floor(date('z')/$config['iss_webapp_swclear']);
        $cacheVersionNumber="_v".$config['iss_webapp_swversion'].".".$dayPeriod;
        $staticPattern=(isset($config['iss_webapp_swpattern']) && $config['iss_webapp_swpattern'])?"new RegExp( '{$config['iss_webapp_swpattern']}' )":'null';
        
        if( !$config['iss_webapp_swpattern'] && $config['iss_webapp_swdynamic']=="disabled" ){
            exit;
        }
	?>
	var cacheVersion="iSellSoftCache<?php echo $cacheVersionNumber?>";
	var cacheDynamicFiles="<?php echo $config['iss_webapp_swdynamic']?>";
	var staticPattern=<?php echo $staticPattern?>;
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
                    if ( cached_response && staticPattern && staticPattern.test(event.request.url) ) {
                        return cached_response;
                    }
		    var fetched_response=fetch(event.request).then(function (response) {
                        if ( event.request.method !== 'GET' || event.request.url.indexOf('/admin')>-1 ){
                            return response;
                        }
                        if( cacheDynamicFiles=="autoupdate" || staticPattern && staticPattern.test(event.request.url) ){
                            var resp2 = response.clone();
                            caches.open(cacheVersion).then(function (cache) {
                                console.log('iSellSoftSW Cached!',cacheVersion,event.request.url);
                                cache.put(event.request, resp2);
                            });
                        }
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