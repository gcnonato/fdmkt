jQuery(document).ready(function($){

    "use strict";

    $(window).on('click', function() {
		$('.cp-download').removeClass('in');
	});

	$('.cp-download .cp-newsletter-2-icon').on('click', function() {
		$('.cp-download').addClass('in');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-download').addClass('in');
	    }
	    else {
	        $('.cp-download').removeClass('in');
	    }
	});

	$('.cp-download').on('click', function(event){
		event.stopPropagation();
	});

});