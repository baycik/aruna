    $(function () {
	$('img').on('error', function () {
	    $(this).parent().hide();
	});
    });
