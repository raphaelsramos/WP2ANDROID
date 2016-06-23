jQuery(document).ready( function() {
	// Create sanitary variable for use in the JavaScript conditional.
	if( WP2ANDROIDAdminInterface.is_reseted == 'true' ) {
		var reset_popup = jQuery( '#wp2android-popup-reset' );
		reset_popup.fadeIn();
		window.setTimeout(function() {
			   reset_popup.fadeOut();
			}, 2000);
	}

	//Update Message popup
	jQuery.fn.center = function () {
		this.animate({"top":( jQuery(window).height() - this.height() - 200 ) / 2+jQuery(window).scrollTop() + "px"},100);
		this.css( "left", 250 );
		return this;
	}

	jQuery( '#wp2android-popup-save' ).center();
	jQuery( '#wp2android-popup-reset' ).center();
	jQuery(window).scroll(function() {

		jQuery( '#wp2android-popup-save' ).center();
		jQuery( '#wp2android-popup-reset' ).center();

	});

	//Save everything else
	jQuery( '#wp2androidform' ).submit(function() {

			function newValues() {
			  var serializedValues = jQuery( "#wp2androidform *").not( '.ignore').serialize();
			  return serializedValues;
			}
			jQuery( ":checkbox, :radio").click(newValues);
			jQuery( "select").change(newValues);
			jQuery( '.ajax-loading-img').fadeIn();
			var serializedReturn = newValues();

			var data = {
				type: 'options',
				
				action: 'wp2android_ajax_post_action',
				data: serializedReturn,
				_ajax_nonce: WP2ANDROIDAdminInterface.nonce_value
			};

			jQuery.post(ajaxurl, jQuery( "#wp2androidform").serializeArray(), function(response) {

				var success = jQuery( '#wp2android-popup-save' );
				var loading = jQuery( '.ajax-loading-img' );
				loading.fadeOut();
				success.fadeIn();
				window.setTimeout(function() {
				   success.fadeOut();
				}, 2000);
			});
			return false;
		});
	});