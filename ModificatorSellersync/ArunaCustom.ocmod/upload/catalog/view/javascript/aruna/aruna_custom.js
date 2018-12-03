if ( localStorage.getItem("disableSW")!=1 && ('serviceWorker' in navigator) ) {
    navigator.serviceWorker.register('?route=service_worker', {scope: './'})
    .then(function(reg) {
	//console.log('serviceWorker Registration succeeded. Scope is ' + reg.scope);
    }).catch(function(error) {
	console.log('serviceWorker Registration failed with ' + error);
    });
    navigator.serviceWorker.addEventListener('message',function(event){
	var msg=event.data;
	console.log(msg);
    });
}