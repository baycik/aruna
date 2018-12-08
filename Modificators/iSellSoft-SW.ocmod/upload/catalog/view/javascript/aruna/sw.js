var disable_autoupdate_cache=0;
this.addEventListener('install', function (event) {
    console.log('iSellSoft ServiceWorker installed!!!');
});

this.addEventListener('message', function(event){
    var message=event.data;
    if( message==='clear_cache' ){
	caches.delete('iSellSoftCache').then(function(){
	    console.log('iSellSoftCache cleared!');
	});
    }
});
function postClientMessage( msg ){
    clients.matchAll().then(function(clients){
	clients.forEach(function(client){
	    client.postMessage(msg);
	});
    });
};
this.addEventListener('fetch', function (event) {
    event.respondWith(
	caches.match(event.request).then(function (cached_response) {
	    if( disable_autoupdate_cache && cached_response ){
		return cached_response;
	    }
	    var fetched_response=fetch(event.request).then(function (response) {
		if( response.status!=200 ){
		    postClientMessage( {
			url: event.request.url,
			status: response.status
		    });
		} else 
		if ( /[\w-_]+(.jpg|.jpeg|.png|.gif|.css|.js)/.test(event.request.url) ) {
		    var resp2 = response.clone();
		    caches.open('iSellSoftCache').then(function (cache) {
			//console.log(" saved to mobisellCache: " + event.request.url);
			cache.put(event.request, resp2);
		    });
		}
		return response;
	    }).catch(function (e) {
		postClientMessage( {
		    url: event.request.url,
		    status: 408
		});
		console.log('iSellSoft-error',e);
		return new Response('');
	    });
	    return cached_response || fetched_response;
	})
    );
});