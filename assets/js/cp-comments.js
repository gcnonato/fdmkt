jQuery(document).ready(function($){

    "use strict";

    $(window).on('click', function() {
		$('.cp-comments').removeClass('in');
	});

	$('.cp-comments .cp-newsletter-2-icon').on('click', function() {
		$('.cp-comments').addClass('in');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-comments').addClass('in');
	    }
	    else {
	        $('.cp-comments').removeClass('in');
	    }
	});

	$('.cp-comments').on('click', function(event){
		event.stopPropagation();
	});

});