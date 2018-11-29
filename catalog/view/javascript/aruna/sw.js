this.addEventListener('install', function (event) {
    console.log('Hello new version');
});

this.addEventListener('message', function(event){
    var message=event.data;
    if( message==='clear_cache' ){
	caches.delete('arunaCache').then(function(){
	    console.log('arunaCache cleared!');
	});
    }
});
function postClientMessage( msg ){
    clients.matchAll().then(function(clients){
	clients.forEach(function(client){
	    client.postMessage(msg);
	});
    });
}
this.addEventListener('fetch', function (event) {
    event.respondWith(
	caches.match(event.request).then(function (resp) {
	    if (resp) {//use cached version
		return resp;
	    } else {
		return fetch(event.request).then(function (response) {
		    if( response.status!=200 ){
			postClientMessage( {
			    url: event.request.url,
			    status: response.status
			});
		    } else 
		    if ( /.jpg|.jpeg|.png|.gif|.css|.js|/.test(event.request.url) ) {
			var resp2 = response.clone();
			caches.open('arunaCache').then(function (cache) {
			    console.log(" saved to arunaCache: " + event.request.url);
			    cache.put(event.request, resp2);
			});
		    }
		    return response;
		}).catch(function (e) {
		    postClientMessage( {
			url: event.request.url,
			status: 408
		    });
		    console.log('arunaCache-error',e);
		    return new Response('');
		});
	    }
	})
    );
});