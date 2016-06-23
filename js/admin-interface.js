/**
 * WP2ANDROID-API Admin Interface JavaScript
 *
 * All JavaScript logic for the plugin options admin interface.
 * @since 1.7
 *
 */

(function ($) {

  wp2android_plugin_interface = {
 
 	toggle_nav_tabs: function () {
 		var flip = 0;
	
		$( '#expand_options' ).click( function(){
			if( flip == 0 ){
				flip = 1;
				$( '#wp2android_container #wp2android-nav' ).hide();
				$( '#wp2android_container #content' ).width( 785 );
				$( '#wp2android_container .group' ).add( '#wp2android_container .group h1' ).show();

				$(this).text( '[-]' );

			} else {
				flip = 0;
				$( '#wp2android_container #wp2android-nav' ).show();
				$( '#wp2android_container #content' ).width( 595 );
				$( '#wp2android_container .group' ).add( '#wp2android_container .group h1' ).hide();
				$( '#wp2android_container .group:first' ).show();
				$( '#wp2android_container #wp2android-nav li' ).removeClass( 'current' );
				$( '#wp2android_container #wp2android-nav li:first' ).addClass( 'current' );

				$(this).text( '[+]' );
			}
		});
 	},

/**
 * load_first_tab()
 * @since 1.7
 */
 	load_first_tab: function () {
 		$( '.group' ).hide();
 		$( '.group:has(".section"):first' ).fadeIn(); // Fade in the first tab containing options (not just the first tab).
 	},
 	
/**
 * open_first_menu()
 * @since 1.7
 */
 	open_first_menu: function () {
 		$( '#wp2android-nav li.current.has-children:first ul.sub-menu' ).slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' ).parents( 'li.has-children' ).addClass( 'open' );
 	},
 	
/**
 * toggle_nav_menus()
 * @since 1.7
 */
 	toggle_nav_menus: function () {
 		$( '#wp2android-nav li.has-children > a' ).click( function ( e ) {
 			if ( $( this ).parent().hasClass( 'open' ) ) { return false; }
 			
 			$( '#wp2android-nav li.top-level' ).removeClass( 'open' ).removeClass( 'current' );
 			$( '#wp2android-nav li.active' ).removeClass( 'active' );
 			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
 				$( '#wp2android-nav .sub-menu.open' ).removeClass( 'open' ).slideUp().parent().removeClass( 'current' );
 				$( this ).parent().addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' ).children( 'li:first' ).addClass( 'active' );
 			}
 			
 			// Find the first child with sections and display it.
 			var clickedGroup = $( this ).parent().find( '.sub-menu li a:first' ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
 				$( '.group' ).hide();
 				$( clickedGroup ).fadeIn();
 			}
 			return false;
 		});
 	},
 	
/**
 * toggle_collapsed_fields()
 * @since 1.7
 */
 	toggle_collapsed_fields: function () {
		$( '.group .collapsed' ).each(function(){
			$( this ).find( 'input:checked' ).parent().parent().parent().nextAll().each( function(){
				if ($( this ).hasClass( 'last' ) ) {
					$( this ).removeClass( 'hidden' );
					return false;
				}
				$( this ).filter( '.hidden' ).removeClass( 'hidden' );
				
				$( '.group .collapsed input:checkbox').click(function ( e ) {
					wp2android_plugin_interface.unhide_hidden( $( this ).attr( 'id' ) );
				});

			});
		});
 	},

/**
 * setup_nav_highlights()
 * @since 1.7
 */
 
 	setup_nav_highlights: function () {
	 	// Highlight the first item by default.
	 	$( '#wp2android-nav li.top-level:first' ).addClass( 'current' ).addClass( 'open' );
		
		// Default single-level logic.
		$( '#wp2android-nav li.top-level' ).not( '.has-children' ).find( 'a' ).click( function ( e ) {
			var thisObj = $( this );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( clickedGroup != '' ) {
				$( '#wp2android-nav .open' ).removeClass( 'open' );
				$( '.sub-menu' ).slideUp();
				$( '#wp2android-nav .active' ).removeClass( 'active' );
				$( '#wp2android-nav li.current' ).removeClass( 'current' );
				thisObj.parent().addClass( 'current' );
				
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
				
				return false;
			}
		});
		
		$( '#wp2android-nav li:not(".has-children") > a:first' ).click( function( evt ) {
			var parentObj = $( this ).parent( 'li' );
			var thisObj = $( this );
			
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( this ).parents( '.top-level' ).hasClass( 'open' ) ) {} else {
				$( '#wp2android-nav li.top-level' ).removeClass( 'current' ).removeClass( 'open' );
				$( '#wp2android-nav .sub-menu' ).removeClass( 'open' ).slideUp();
				$( this ).parents( 'li.top-level' ).addClass( 'current' );
			}
		
			$( '.group' ).hide();
			$( clickedGroup ).fadeIn();
		
			evt.preventDefault();
			return false;
		});
		
		// Sub-menu link click logic.
		$( '.sub-menu a' ).click( function ( e ) {
			var thisObj = $( this );
			var parentMenu = $( this ).parents( 'li.top-level' );
			var clickedGroup = thisObj.attr( 'href' );
			
			if ( $( '.sub-menu li a[href="' + clickedGroup + '"]' ).hasClass( 'active' ) ) {
				return false;
			}
			
			if ( clickedGroup != '' ) {
				parentMenu.addClass( 'open' );
				$( '.sub-menu li, .flyout-menu li' ).removeClass( 'active' );
				$( this ).parent().addClass( 'active' );
				$( '.group' ).hide();
				$( clickedGroup ).fadeIn();
			}
			
			return false;
		});
 	},

/**
 * setup_custom_ui_slider()
 * @since 1.7
 */
 
 	setup_custom_ui_slider: function () {

		$('div.ui-slide').each(function(i) {

			if( $(this).attr('min') != undefined && $(this).attr('max') != undefined ) {

				$(this).slider( { 
								min: parseInt($(this).attr('min')), 
								max: parseInt($(this).attr('max')), 
								value: parseInt($(this).next("input").val()),
								step: parseInt($(this).attr('inc')) ,
								slide: function( event, ui ) {
									$( this ).next("input").val(ui.value);
								}
							});

				$(this).removeAttr('min').removeAttr('max').removeAttr('inc');
			}

		});

 	},

/**
 * init_flyout_menus()
 * @since 1.7
 */
 
 	init_flyout_menus: function () {
 		// Only trigger flyouts on menus with closed sub-menus.
 		$( '#wp2android-nav li.has-children' ).each ( function ( i ) {
 			$( this ).hover(
	 			function () {
	 				if ( $( this ).find( '.flyout-menu' ).length == 0 ) {
		 				var flyoutContents = $( this ).find( '.sub-menu' ).html();
		 				var flyoutMenu = $( '<div />' ).addClass( 'flyout-menu' ).html( '<ul>' + flyoutContents + '</ul>' );
		 				$( this ).append( flyoutMenu );
	 				}
	 			}, 
	 			function () {
	 				// $( '#wp2android-nav .flyout-menu' ).remove();
	 			}
	 		);
 		});
 		
 		// Add custom link click logic to the flyout menus, due to custom logic being required.
 		$( '.flyout-menu a' ).live( 'click', function ( e ) {
 			var thisObj = $( this );
 			var parentObj = $( this ).parent();
 			var parentMenu = $( this ).parents( '.top-level' );
 			var clickedGroup = $( this ).attr( 'href' );
 			
 			if ( clickedGroup != '' ) {
	 			$( '.group' ).hide();
	 			$( clickedGroup ).fadeIn();
	 			
	 			// Adjust the main navigation menu.
	 			$( '#wp2android-nav li' ).removeClass( 'open' ).removeClass( 'current' ).find( '.sub-menu' ).slideUp().removeClass( 'open' );
	 			parentMenu.addClass( 'open' ).addClass( 'current' ).find( '.sub-menu' ).slideDown().addClass( 'open' );
	 			$( '#wp2android-nav li.active' ).removeClass( 'active' );
	 			$( '#wp2android-nav a[href="' + clickedGroup + '"]' ).parent().addClass( 'active' );
 			}
 			
 			return false;
 		});
 	}, // End init_flyout_menus()

/**
 * unhide_hidden()
 *
 * @since 1.7
 * @see toggle_collapsed_fields()
 */
 
 	unhide_hidden: function ( obj ) {
 		obj = $( '#' + obj ); // Get the jQuery object.
 		
		if ( obj.attr( 'checked' ) ) {
			obj.parent().parent().parent().nextAll().slideDown().removeClass( 'hidden' ).addClass( 'visible' );
		} else {
			obj.parent().parent().parent().nextAll().each( function(){
				if ( $( this ).filter( '.last' ).length ) {
					$( this ).slideUp().addClass( 'hidden' );
				return false;
				}
				$( this ).slideUp().addClass( 'hidden' );
			});
		}
 	}, // End unhide_hidden()
	
	styleSelectinit: function () {
      $( '.select_wrapper').each(function () {
		  if ($(this).hasClass('select_wrapper_multiple')){
		  }else{
          	$(this).prepend( '<span>' + $(this).find( '.wp2android-input option:selected').text() + '</span>' );
		  }
      });
      $( 'select.wp2android-input').live( 'change', function () {
		  if ($(this).parent().hasClass('select_wrapper_multiple')){
			  
		  }else{
			$(this).prev( 'span').replaceWith( '<span>' + $(this).find( 'option:selected').text() + '</span>' );
		  }
      });
      $( 'select.wp2android-input').bind($.browser.msie ? 'click' : 'change', function(event) {
		  if ($(this).parent().hasClass('select_wrapper_multiple')){
			  
		  }else{
        	$(this).prev( 'span').replaceWith( '<span>' + $(this).find( 'option:selected').text() + '</span>' );
		  }
      }); 
    }
  
  }; // End wp2android_plugin_interface Object // Don't remove this, or the sky will fall on your head.

/**
 * Execute the above methods in the wp2android_plugin_interface object.
 * @since 1.7
 */
	$(document).ready(function () {
		wp2android_plugin_interface.toggle_nav_tabs();
		wp2android_plugin_interface.load_first_tab();
		wp2android_plugin_interface.toggle_collapsed_fields();
		wp2android_plugin_interface.setup_nav_highlights();
		wp2android_plugin_interface.toggle_nav_menus();
		wp2android_plugin_interface.init_flyout_menus();
		wp2android_plugin_interface.open_first_menu();
		wp2android_plugin_interface.setup_custom_ui_slider();
		wp2android_plugin_interface.styleSelectinit();
	});
})(jQuery);