<?php
/**
 * Handles -API Settings Page
 *
 * @author      ThunderBear Design
 * @category    API
 * @package     WPAndroid/API
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP2ANDROID_Settings {
	/**
	 * Setup class
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct() {
		//Registering Settings Menu for WP for Android
		add_action('admin_menu', array( $this, 'settings_menu'));
		
		//Adding Meta Data for WP for Android
		add_action ('wp_head', array( $this, 'head_meta' ));
		
		//Displaying Plugin Errors
		add_action ('admin_menu', array( $this, 'check_environment' ));
		
		//Ajax Handler
		add_action( 'wp_ajax_wp2android_ajax_post_action', array ($this, 'wp2android_ajax_callback') );
	}
	
	public function head_meta (){
		echo "\n<!-- WP for Android v1 - Developed by ThunderBear Design -->\n";
	}
	
	public function settings_menu() {

		if ( isset( $_REQUEST['page'] ) ) {

			// Sanitize page being requested.
			$_page = '';
			$_page = sanitize_title( strtolower( trim( strip_tags( $_REQUEST['page'] ) ) ) );

			// Sanitize action being requested.
			$_action = '';

			if ( isset( $_REQUEST['wp2android_save'] ) ) {
				$_action = sanitize_title( strtolower( trim( strip_tags( $_REQUEST['wp2android_save'] ) ) ) );
			} 

			/* Perform settings reset.
  		------------------------------------------------------------*/

			if ( $_action == 'reset' ) {
				// Add nonce security check.
				if ( function_exists( 'check_ajax_referer' ) ) {
					check_ajax_referer( 'wp2android-theme-options-reset', '_ajax_nonce' );
				}

				switch ( $_page ) {

					case 'wp2android-settings':
						update_option('wp2android_settings', '');
						header( "Location: options-general.php?page=wp2android-settings&reset=true" );
						die;
						break;
					}
			}
		}

		$wp2android_settings_page = add_options_page('WP for Android Settings', 'WP for Android', 'manage_options', 'wp2android-settings', array($this, 'settings_page') );

		// Add framework functionaily to the head individually
		add_action( "admin_print_scripts-$wp2android_settings_page", array(&$this, 'wp2android_load_only') );

		// Load Framework CSS Files
		add_action( "admin_print_styles-$wp2android_settings_page", array(&$this, 'wp2android_load_css') );

		// Add the non-JavaScript "save" to the load of each of the screens.
		add_action( "load-$wp2android_settings_page", array(&$this, 'wp2android_nonajax_callback') );	
	}
	
	public function check_environment(){
		$data = '';
		if ( version_compare(get_bloginfo('version'), WP2ANDROID_REQUIRED_VERSION, '<')) $errors[] = __('Please install WordPress '.WP2ANDROID_REQUIRED_VERSION.' or higher to use <strong>WP for Android Plugin</strong>.','thunderbear');

		if (isset($errors) && sizeof($errors)>0) {
			$data .= '<div class=\"error\" style=\"padding:10px\"><strong>'.__('Environment errors:','thunderbear').'</strong>';
			foreach ($errors as $error) {
				$data .= '<p>'.$error.'</p>';
			}
			$data .= '</div>';
		}
		
		if (!empty($data)){
			add_action('admin_notices', create_function('', 'echo "' . $data . '";'));
		}
	}	
	
	function wp2android_load_css () {
		wp_register_style( 'wp2android-admin-interface', plugins_url( 'css/admin-style.css', WP2ANDROID_PLUGIN_FILE ), '', WP2ANDROID_PLUGIN_VERSION );
		wp_enqueue_style( 'wp2android-admin-interface' );
	}
	
	function wp2android_load_only() {
		wp_register_script( 'wp2android-admin-interface', plugins_url( 'js/admin-interface.js', WP2ANDROID_PLUGIN_FILE ), array( 'jquery' ), '5.3.5' );
		wp_register_script( 'wp2android-admin-actions',   plugins_url( 'js/admin-actions.js', WP2ANDROID_PLUGIN_FILE ), array( 'jquery' ) );
		
		$is_reset = 'false';
		if( isset( $_REQUEST['reset'] ) ) {
			$is_reset = $_REQUEST['reset'];
			$is_reset = strtolower( strip_tags( trim( $is_reset ) ) );
		} else {
			$is_reset = 'false';
		}
		
		$wp2android_nonce = '';
		if ( function_exists( 'wp_create_nonce' ) ) { $wp2android_nonce = wp_create_nonce( 'wp2android-theme-options-update' ); } 
		wp_localize_script( 'wp2android-admin-actions', 'WP2ANDROIDAdminInterface', array( 
			'is_reseted' => $is_reset,
			'nonce_value' => $wp2android_nonce,
			'tb_pathToImage' => includes_url() . 'js/thickbox/loadingAnimation.gif',
	    	'tb_closeImage' => includes_url() . 'js/thickbox/tb-close.png',
		) );
		
		wp_enqueue_script( 'wp2android-admin-interface' );
		wp_enqueue_script( 'wp2android-admin-actions' );
		wp_enqueue_script( 'jquery-ui-slider' );
	}
	
	function wp2android_ajax_callback() {

		add_action( 'wp_ajax_wp2android_ajax_post_action', 'wp2android_ajax_callback' );
		// check security with nonce.
		if ( function_exists( 'check_ajax_referer' ) ) { check_ajax_referer( 'wp2android-theme-options-update', '_ajax_nonce' ); } // End IF Statement
		$data = maybe_unserialize( $_POST['data'] );
		$output = $_POST;
		$output['app_thumbnail'] = array(
			'width'		=> intval($output['app_thumbnail']['width']),
			'height'	=> intval($output['app_thumbnail']['height']),
			'crop'		=> intval($output['app_thumbnail']['crop'])
		);
		$output['app_image'] = array(
			'width'		=> intval($output['app_image']['width']),
			'height'	=> intval($output['app_image']['height']),
			'crop'		=> intval($output['app_image']['crop'])
		);
		
		unset ($output['_wpnonce']);
		unset ($output['_wp_http_referer']);
		unset ($output['_ajax_nonce']);
		unset ($output['wp2android_save']);
		unset ($output['action']);
		update_option('wp2android_settings', $output);
		//$outp = get_option ('web_theme_options');
		//print_r($outp);
		die();
	}
	
	function wp2android_nonajax_callback() {
		if ( isset( $_POST['_ajax_nonce'] ) && isset( $_POST['wp2android_save'] ) && ( $_POST['wp2android_save'] == 'save' ) ) {

			$nonce_key = 'wp2android-theme-options-update';

			switch ( $_REQUEST['page'] ) {
				case 'wp2android':
					$type = 'options';
					$nonce_key = 'wp2android-theme-options-update';
					break;

				default:
					$type = '';
			}

			// check security with nonce.
			if ( function_exists( 'check_admin_referer' ) ) { check_admin_referer( $nonce_key, '_ajax_nonce' ); } // End IF Statement

			// Remove non-options fields from the $_POST.
			$fields_to_remove = array( '_wpnonce', '_wp_http_referer', '_ajax_nonce', 'wp2android_save' );

			$data = array();

			foreach ( $_POST as $k => $v ) {
				if ( in_array( $k, $fields_to_remove ) ) {} else {
					$data[$k] = $v;
				}
			}
			update_option('web_theme_options', $data);
			
			if ( $status ) {
				add_action( 'admin_notices', array( &$this, 'wp2android_admin_message_success'), 0 );
			} else {
				add_action( 'admin_notices', array( &$this, 'wp2android_admin_message_error'), 0 );
			}
		}
	} 
	
	public function settings_page() {

		$options = $this->theme_options();
		global $pagenow;
?>
<div class="wrap wrap_security_key">
	<label>Api Key (Security Key for Android)</label>
    <textarea readonly="readonly"><?php echo get_option('wp2android_auth_key');?></textarea>
</div>
<div class="wrap" id="wp2android_container">
<?php
	// Custom action at the top of the admin interface.
	$page = '';
	if ( isset( $_GET['page'] ) ) {
		$page = sanitize_user( esc_attr( strip_tags( $_GET['page'] ) ) );
	} 
	do_action( 'wp2android_container_inside' );
	if ( $page != '' ) {
		do_action( 'wp2android_container_inside-' . $page );
	}
?>
<div id="wp2android-popup-save" class="wp2android-save-popup"><div class="wp2android-save-save"><?php _e( 'Options Updated', 'wp2android' ); ?></div></div>
<div id="wp2android-popup-reset" class="wp2android-save-popup"><div class="wp2android-save-reset"><?php _e( 'Options Reset', 'wp2android' ); ?></div></div>
    <form action="" enctype="multipart/form-data" id="wp2androidform" method="post">
    <?php
		// Add nonce for added security.
		if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wp2android-theme-options-update' ); }

		$wp2android_nonce = '';

		if ( function_exists( 'wp_create_nonce' ) ) { $wp2android_nonce = wp_create_nonce( 'wp2android-theme-options-update' ); }
		if ( $wp2android_nonce == '' ) {} else {
?>
    	<input type="hidden" name="_ajax_nonce" value="<?php echo $wp2android_nonce; ?>" />
    <?php
		}

		// Rev up the Options Machine
		$return = $this->machine( $options ) ;
?>
        <div id="main">
	        <div id="wp2android-nav">
	        	<div id="wp2android-nav-shadow"></div><!--/#wp2android-nav-shadow-->
				<ul>
					<?php echo $return[1]; ?>
				</ul>
                			</div>
			<div id="content">
	        	<?php echo $return[0]; /* Settings */ ?>
	        </div>
	        <div class="clear"></div>
        </div>
        <div class="save_bar_top">
        <img style="display:none" src="<?php echo plugins_url( 'img/loading-bottom.gif', WP2ANDROID_PLUGIN_FILE );?>" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
        <input type="hidden" name="wp2android_save" value="save" />
        <input type="submit" value="Save All Changes" class="button submit-button" />
        <input type="hidden" name="action" value="wp2android_ajax_post_action" />
        </form>

        <form action="" method="post" style="display: inline;" id="wp2androidform-reset">        
        <?php
		// Add nonce for added security.
		if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'wp2android-theme-options-reset' ); } // End IF Statement

		$wp2android_nonce = '';

		if ( function_exists( 'wp_create_nonce' ) ) { $wp2android_nonce = wp_create_nonce( 'wp2android-theme-options-reset' ); } // End IF Statement

		if ( $wp2android_nonce == '' ) {} else {
?>
	    	<input type="hidden" name="_ajax_nonce" value="<?php echo $wp2android_nonce; ?>" />
	    <?php

		} // End IF Statement
?>
            <span class="submit-footer-reset">
            <input name="reset" type="submit" value="Reset All Theme Options" class="button submit-button reset-button" onclick="return confirm( 'Click OK to reset all theme options. All settings will be lost!' );" />
            <input type="hidden" name="wp2android_save" value="reset" />
            </span>
        </form>
        </div>

<div style="clear:both;"></div>
</div><!--wrap-->

 <?php 
	}	
	
	private function theme_options (){
		$options   = array ();
		$options[] = array("name" => "General Options","type" => "heading");
		$options[] = array("name" => "Recent Posts","desc" => "Select the number of Posts you want to show on the recent screen","id" => "recent_posts", "std" => "","type" => "slider", "min"=>20, "max"=>80, "increment"=>1);
		$options[] = array("name" => "Image Dimensions (List View)"  , "desc" => "Please select the image dimensions you want to show in the Android App for <strong>Post Thumbnail for List View</strong>.","id" => "app_thumbnail","std"  => array('width'=>200,'height'=>200),"type" => "image_dimensions");
		$options[] = array("name" => "Image Dimensions (Single Post)", "desc" => "Please select the image dimensions you want to show in the Android App for <strong>Post Thumbnail in Single Post</strong>.","id" => "app_image","std" => array('width'=>800,'height'=>600),"type" => "image_dimensions");
		
		$options[] = array("name" => "Categoires Options", "type" => "heading");
		$options[] = array("name" => "No of Posts","desc" => "Select the number of Posts you want to show on the selected category screen","id" => "category_posts", "std" => "20", "type" => "slider", "min"=>20, "max"=>80, "increment"=>1);
		$options[] = array("name" => "Category Mode","desc" => "Select the mode of the categories you want to display in the Android App","id" => "category_mode", "std" => "","type" => "select2", "options"=>array(
			'all' => 'All Categories',
			'selective' => 'Following Selected Categories',
		));
		$options[] = array("name" => "Select Categories","desc" => "Select the categories you want to display in the Android App Navigational Drawer<br /><br />
<strong>Note: </strong> It will works when you selected the <strong>Following Selected Categories</strong> under the <strong>Category Mode</strong><br />
Hold Control ( CTRL ) key to select multiple categories", "id" => "categories_to_show", "std" => "", "type" => "categories", "multiple" => true, "options"=>array(
			'all' => 'All Categories',
			'selective' => 'Following Selected Categories',
		));
		return $options;
	}
		
	private function machine( $options ) {
		$counter = 0;
		$menu = '';
		$output = '';
		
		// Create an array of menu items - multi-dimensional, to accommodate sub-headings.
		$menu_items = array();
		$headings = array();
		$theme_options = get_option ('wp2android_settings');
		
		foreach ( $options as $k => $v ) {
			if ( $v['type'] == 'heading' || $v['type'] == 'subheading' ) {
				$headings[] = $v;
			}
		}
		
		$prev_heading_key = 0;
		
		foreach ( $headings as $k => $v ) {
			$token = 'wp2android-option-' . preg_replace( '/[^a-zA-Z0-9\s]/', '', strtolower( trim( str_replace( ' ', '', $v['name'] ) ) ) );
			
			// Capture the token.
			$v['token'] = $token;
			
			if ( $v['type'] == 'heading' ) {
				$menu_items[$token] = $v;
				$prev_heading_key = $token;
			}
		}

		// Loop through the options.
		foreach ( $options as $k => $value ) {

			$counter++;
			$val = '';
			//Start Heading
			if ( $value['type'] != 'heading' ) {
				$class = ''; if( isset( $value['class'] ) ) { $class = ' ' . $value['class']; }
				$output .= '<div class="section section-'.$value['type'] . $class .'">'."\n";
				$output .= '<h3 class="heading">'. $value['name'] .'</h3>'."\n";
				$output .= '<div class="option">'."\n" . '<div class="controls">'."\n";

			}
			//End Heading
			
			$select_value = '';
			switch ( $value['type'] ) {

				case 'text':
					$val = $value['std'];
					$std = $theme_options[ $value['id'] ];
					if ( $std != "" ) { $val = $std; }
					$val = stripslashes( $val ); // Strip out unwanted slashes.
					$output .= '<input class="wp2android-input" name="'. $value['id'] .'" id="'. $value['id'] .'" type="'. $value['type'] .'" value="'. esc_attr( $val ) .'" />';
					break;
	
				case 'select':
					$output .= '<div class="wp2android_sprites select_wrapper"><select class="wp2android-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';
	
					$select_value = stripslashes( $theme_options[ $value['id'] ] );
	
					foreach ( $value['options'] as $option ) {
	
						$selected = '';
	
						if( $select_value != '' ) {
							if ( $select_value == $option ) { $selected = ' selected="selected"';}
						} else {
							if ( isset( $value['std'] ) )
								if ( $value['std'] == $option ) { $selected = ' selected="selected"'; }
						}
	
						$output .= '<option'. $selected .'>';
						$output .= $option;
						$output .= '</option>';
	
					}
					$output .= '</select></div>';
	
					break;
				
				case 'select2':
					$output .= '<div class="select_wrapper">' . "\n";
	
					if ( is_array( $value['options'] ) ) {
						$output .= '<select class="wp2android-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';
	
						$select_value = stripslashes( $theme_options[ $value['id'] ] );
	
	
						foreach ( $value['options'] as $option => $name ) {
	
							$selected = '';
	
							if( $select_value != '' ) {
								if ( $select_value == $option ) { $selected = ' selected="selected"';}
							} else {
								if ( isset( $value['std'] ) )
									if ( $value['std'] == $option ) { $selected = ' selected="selected"'; }
							}
	
							$output .= '<option'. $selected .' value="'.esc_attr( $option ).'">';
							$output .= $name;
							$output .= '</option>';
	
						}
						$output .= '</select>' . "\n";
					}
	
					$output .= '</div>';
	
					break;
				
				case 'categories':
					$output .= '<div class="select_wrapper_multiple">' . "\n";
					
					$selected_categories_query = 'echo=0&hierarchical=1&id='. $value['id'] .'&class=wp2android-select&name='. $value['id'];
					
					
					$select_multiple = (bool)  $value['multiple'];
					
					if ($select_multiple){
						$select_value 	 = (array) $theme_options[ $value['id'] ];
						$categories_dd = wp_dropdown_categories($selected_categories_query.'[]');
						$categories_dd = str_replace("class='wp2android-select'", "class='wp2android-select' size='10' multiple='multiple'", $categories_dd);
						foreach ($select_value as $val){
							$categories_dd = str_replace('value="' . $val . '"', 'value="' . $val . '" selected="selected"', $categories_dd);
						}
						$output .= $categories_dd;
					}else{
						$select_value 	 = $theme_options[ $value['id'] ];
						$categories_dd = wp_dropdown_categories($selected_categories_query.'&selected=' . $select_value);
					}
	
					$output .= '</div>';
	
					break;
					
				case 'slider':
					$val = $value['std'];
					$std = $theme_options[ $value['id'] ];
					if ( $std != "" ) { $val = $std; }
					$val = stripslashes( $val ); // Strip out unwanted slashes.
					$output .= '<div class="ui-slide" id="'. $value['id'] .'_div" min="'. esc_attr( $value['min'] ) .'" max="'. esc_attr( $value['max'] ) .'" inc="'. esc_attr( $value['increment'] ) .'"></div>';
					$output .= '<input readonly="readonly" class="wp2android-input" name="'. $value['id'] .'" id="'. $value['id'] .'" type="'. $value['type'] .'" value="'. esc_attr( $val ) .'" />';
				break;
	
				case "heading":
					if( $counter >= 2 ) {
						$output .= '</div>'."\n";
					}
					$jquery_click_hook = preg_replace( '/[^a-zA-Z0-9\s]/', '', strtolower( $value['name'] ) );
					// $jquery_click_hook = preg_replace( '/[^\p{L}\p{N}]/u', '', strtolower( $value['name'] ) ); // Regex for UTF-8 languages.
					$jquery_click_hook = str_replace( ' ', '', $jquery_click_hook );
	
					$jquery_click_hook = "wp2android-option-" . $jquery_click_hook;
					$head_icon = isset($value['icon'])?$value['icon']:'';
					$menu .= '<li class="'.$head_icon.'"><a title="'.  $value['name'] .'" href="#'.  $jquery_click_hook  .'">'.  $value['name'] .'</a></li>';
					$output .= '<div class="group" id="'. $jquery_click_hook  .'"><h1 class="subtitle">'.$value['name'].'</h1>'."\n";
					break;
				
				case "image_dimensions":
					$val = $value['std'];
					$val_db = $theme_options[ $value['id'] ];
					if ( $val_db ) { $val = $val_db; }
					$output .= '<div class="wh_field"><label>Width:</label><input class="wp2android-input" name="'. $value['id'] .'[width]" id="'. $value['id'] .'_width" type="'. $value['type'] .'" value="'. esc_attr( $val['width'] ) .'" /></div>';
					$output .= '<div class="wh_field"><label>Height:</label><input class="wp2android-input" name="'. $value['id'] .'[height]" id="'. $value['id'] .'_height" type="'. $value['type'] .'" value="'. esc_attr( $val['height'] ) .'" /></div>';
					$output .= '<input class="wp2android-input" name="'. $value['id'] .'[crop]" id="'. $value['id'] .'_crop" type="hidden" value="1" />';
					break;
			}

			// if TYPE is an array, formatted into smaller inputs... ie smaller values
			if ( is_array( $value['type'] ) ) {
				foreach( $value['type'] as $array ) {

					$id = $array['id'];
					$std = $array['std'];
					$saved_std = $theme_options[ $value['id'] ];
					if( $saved_std != $std ) {$std = $saved_std;}
					$meta = $array['meta'];

					if( $array['type'] == 'text' ) { // Only text at this point

						$output .= '<input class="input-text-small wp2android-input" name="'. $id .'" id="'. $id .'" type="text" value="'. esc_attr( $std ) .'" />';
						$output .= '<span class="meta-two">'.$meta.'</span>';
					}
				}
			}
			if ( $value['type'] != "heading" ) {
				if ( $value['type'] != "checkbox" )
				{
					$output .= '<br/>';
				}
				$explain_value = ( isset( $value['desc'] ) ) ? $value['desc'] : '';
				$output .= '</div><div class="explain">'. $explain_value .'</div>'."\n";
				$output .= '<div class="clear"> </div></div></div>'."\n";
			}
		}

		//Checks if is not the Content Builder page
		if ( isset( $_REQUEST['page'] ) ) {
			$output .= '</div>';
		}
		
		// Override the menu with a new multi-level menu.
		if ( count( $menu_items ) > 0 ) {
			$menu = '';
			foreach ( $menu_items as $k => $v ) {
				$class = '';
				if ( isset( $v['icon'] ) && ( $v['icon'] != '' ) ) {
					$class = $v['icon'];
				}
				
				if ( isset( $v['children'] ) && ( count( $v['children'] ) > 0 ) ) {
					$class .= ' has-children';
				}
				
				$menu .= '<li class="top-level ' . $class . '">' . "\n" . '<div class="arrow"><div></div></div>'; 
				if ( isset( $v['icon'] ) && ( $v['icon'] != '' ) )
					$menu .= '<span class="icon"></span>';
				$menu .= '<a title="' . $v['name'] . '" href="#' . $v['token'] . '">' . $v['name'] . '</a>' . "\n";
				
				if ( isset( $v['children'] ) && ( count( $v['children'] ) > 0 ) ) {
					$menu .= '<ul class="sub-menu">' . "\n";
						foreach ( $v['children'] as $i => $j ) {
							$menu .= '<li class="icon">' . "\n" . '<a title="' . $j['name'] . '" href="#' . $j['token'] . '">' . $j['name'] . '</a></li>' . "\n";
						}
					$menu .= '</ul>' . "\n";
				}
				$menu .= '</li>' . "\n";

			}
		}
		return array( $output, $menu, $menu_items );
	}
}
