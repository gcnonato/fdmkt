jQuery(document).ready(function($){

    "use strict";

    $('.cp-form-wrapper').addClass('down');

    $(window).on('click', function() {
		$('.cp-form-wrapper').addClass('down');
	});

	$('.cp-form-3 .cp-form-3-header').on('click', function() {
		$('.cp-form-wrapper').removeClass('down');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-form-wrapper').removeClass('down');
	    }
	    else {
	        $('.cp-form-wrapper').addClass('down');
	    }
	});

	$('.cp-form-wrapper').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-form-3a').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-form-3a').addClass('cp-form-3-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-form-3-success').addClass('cp-form-3-success-show');

	});

});