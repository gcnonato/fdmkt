jQuery(document).ready(function($){

    "use strict";

    $(window).on('click', function() {
		$('.cp-newsletter').removeClass('in');
	});

	$('.cp-newsletter .cp-newsletter-icon').on('click', function() {
		$('.cp-newsletter').addClass('in');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-newsletter').addClass('in');
	    }
	    else {
	        $('.cp-newsletter').removeClass('in');
	    }
	});

	$('.cp-newsletter').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-newsletter').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-newsletter').addClass('cp-newsletter-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-newsletter-success').addClass('cp-newsletter-success-show');

	});

});