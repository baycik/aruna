	var cacheVersion="iSellSoftCache";
	var autoupdate=1;
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
		    if( cached_response && !autoupdate ){
			return cached_response;
		    }
		    var fetched_response=fetch(event.request).then(function (response) {
			if ( resourcePattern.test(event.request.url) ) {
			    var resp2 = response.clone();
			    caches.open('iSellSoftCache').then(function (cache) {
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