jQuery(document).ready(function($){

    "use strict";

    $('.cp-newsletter-3-wrapper').addClass('out');

    $(window).on('click', function() {
		$('.cp-newsletter-3-wrapper').addClass('out');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-newsletter-3-wrapper').removeClass('out');
	    }
	    else {
	        $('.cp-newsletter-3-wrapper').addClass('out');
	    }
	});

	$('.cp-newsletter-3-wrapper').on('click', function(event){
		event.stopPropagation();
	});

	$('#cp-newsletter-3a').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-newsletter-3a').addClass('cp-newsletter-3-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-newsletter-3-success').addClass('cp-newsletter-3-success-show');

	});

});