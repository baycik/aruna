    $(function () {
	$('img').on('error', function () {
	    $(this).parent().hide();
	    console.log('404 image not found. hiding...');
	});
    });
