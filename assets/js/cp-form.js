jQuery(document).ready(function($){

    "use strict";

    $('.cp-form').addClass('down');

    $(window).on('click', function() {
		$('.cp-form').addClass('down');
	});

	$('.cp-form .cp-title').on('click', function() {
		$('.cp-form').removeClass('down');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-form').removeClass('down');
	    }
	    else {
	        $('.cp-form').addClass('down');
	    }
	});

	$('.cp-form').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-form').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-form').addClass('cp-form-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-form-success').addClass('cp-form-success-show');

	});

});