jQuery(document).ready(function($){

    "use strict";

    $('.cp-advertising, .cp-advertising-close').addClass('out');

    $(window).on('click', function() {
		$('.cp-advertising, .cp-advertising-close').addClass('out');
	});

	$(window).scroll(function() {
	    if ($(window).scrollTop() > 100) {
	        $('.cp-advertising, .cp-advertising-close').removeClass('out');
	    }
	    else {
	        $('.cp-advertising, .cp-advertising-close').addClass('out');
	    }
	});

	$('.cp-advertising, .cp-advertising-close').on('click', function(event){
		event.stopPropagation();
	});

});