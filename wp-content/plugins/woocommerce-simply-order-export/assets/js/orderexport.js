jQuery(document).ready(function() {

	jQuery('.wpg-datepicker').datepicker({
			dateFormat : 'yy-mm-dd',
			timeFormat:  "HH:mm"
		}
	);

	/**
	 * Export csv ajax
	 */
	jQuery('.wpg-order-export').on('click', function(e){

		e.preventDefault();
		var element = jQuery(this);
		var data = jQuery(this).parents('form').serialize();
		jQuery(element).parent().find('.spinner').css('visibility', 'visible');

		jQuery.post( ajaxurl, data, function(response){

			jQuery(element).parent().find('.spinner').css('visibility', 'hidden');
			response = jQuery.parseJSON(response);
			if( response.error === false ) {
				window.location = window.location.href+'&filename='+response.msg+'&downloadname='+response.downloadname+'&oe=1';
			}else{
				jQuery('.wpg-response-msg').html( response.msg ).addClass('wpg-error');
			}

		});
	});

	/**
	 * Advanced options
	 */
	jQuery('#woo-soe-advanced').on('click', function(e){
		e.preventDefault();
		jQuery('.woo-soe-advanced').slideToggle();
	});

//	//If cookie is set, open a new window to download settings file.
//	jQuery(window).on('load', function(){
//
//		if(wsoe_getCookie('wsoe_setting_export')){
//			window.open(location.href, '_blank');
//
//			var d = new Date();
//			d.setTime(d.getTime() - ( 1 * 24 * 60 * 60 * 1000));
//			var expires = "expires="+d.toUTCString();
//			wsoe_setCookie( 'wsoe_setting_export', '1', expires );
//		}
//	});
//	
});

/**
 * Sets the cookie.
 * @param {string} cname
 * @param {mixed} cvalue
 * @param {int} exdays
 */
function wsoe_setCookie(cname, cvalue, expires) {
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

/**
 * Reads the cookie
 * @param cname Cookie Name
 * @returns String - Cookie value
 */
function wsoe_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
