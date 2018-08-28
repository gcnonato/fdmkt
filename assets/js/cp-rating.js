jQuery(document).ready(function($){

    "use strict";

    $('#cp-rating input[type=radio]').on('change', function() {
	    $(this).closest("form").submit();
	});

	$('#cp-rating').ajaxForm(function() { // bind form using 'ajaxForm'

		// Hide form on submit
		$('#cp-rating').addClass('cp-rating-hide');

        // Form has been received; here we add 'cp-form-success-show' class to target element
        $('.cp-rating-result').addClass('cp-rating-result-show');

	});

});