jQuery(document).ready(function($){

    "use strict";

    $(window).on('click', function() {
		$('.cp-newsletter-2-wrapper').removeClass('in');
	});

	$('.cp-newsletter-2-wrapper .cp-newsletter-2-icon').on('click', function() {
		$('.cp-newsletter-2-wrapper').addClass('in');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-newsletter-2-wrapper').addClass('in');
	    }
	    else {
	        $('.cp-newsletter-2-wrapper').removeClass('in');
	    }
	});

	$('.cp-newsletter-2-wrapper').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-newsletter-2a').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-newsletter-2a').addClass('cp-newsletter-2-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-newsletter-2-success').addClass('cp-newsletter-2-success-show');

	});

});