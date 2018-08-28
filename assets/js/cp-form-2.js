jQuery(document).ready(function($){

    "use strict";

    $('.cp-form-2').addClass('down');

    $(window).on('click', function() {
		$('.cp-form-2').addClass('down');
	});

	$('.cp-form-2 .cp-form-2-header').on('click', function() {
		$('.cp-form-2').removeClass('down');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-form-2').removeClass('down');
	    }
	    else {
	        $('.cp-form-2').addClass('down');
	    }
	});

	$('.cp-form-2').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-form-2').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-form-2').addClass('cp-form-2-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-form-2-success').addClass('cp-form-2-success-show');

	});

});