<?php
function wp2android_settings($setting_id){
	$auth_key = get_option('wp2android_auth_key');
	if (empty($auth_key)){
		update_option('wp2android_auth_key', base64_encode(hash('sha256', home_url('/'))));
	}
	$settings = get_option('wp2android_settings');
	if (!$settings){
		$settings = array (
			//'auth_key' => hash('sha256', home_url('/')),
			'category_mode' => 'all',
			'categories_to_show' => array(1,2,3,4,10),
			'category_posts' => 30,
			'recent_posts'   => 30,
			'app_thumbnail'	 => array ('width'=>200,'height'=>200,'crop'=>1),
			'app_image'	     => array ('width'=>800,'height'=>600,'crop'=>1),
			'image_show_placeholder' => "yes",
		);
		update_option('wp2android_settings', $settings);
	}
	return $settings[$setting_id];
}

function wp2android_get_image_size( $image_size ) {
	if ( in_array( $image_size, array( 'app_thumbnail', 'app_image' ) ) ) {
		$size           = wp2android_settings($image_size);
		$size['width']  = isset( $size['width'] ) ? $size['width'] : '300';
		$size['height'] = isset( $size['height'] ) ? $size['height'] : '300';
		$size['crop']   = isset( $size['crop'] ) ? $size['crop'] : 1;
	} else {
		$size = array(
			'width'  => '200',
			'height' => '200',
			'crop'   => 1
		);
	}
	return $size;
}

function wp2android_post_single_category($post_id){
	$post_categories = wp_get_post_categories( $post_id, array('fields' => 'names') );
	return $post_categories[0];
}

function wp2android_avatar_url($get_avatar){
    preg_match("/src='(.*?)'/i", $get_avatar, $matches);
    return $matches[1];
}

function wp2android_prettyPrint( $json ){
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

function wp2android_post_image ($post_id, $image_code = 'app_thumbnail'){
	
	if ($image_code==="wp2android_thumbnail"){
		$image_code_x = "app_thumbnail";
	}elseif ($image_code==="wp2android_image"){
		$image_code_x = "app_image";
	}
	
	$r = wp2android_get_image_size($image_code_x);
	
	$image = "";
	//if (wp2android_settings('image_show_placeholder')==="yes")
	
	$p = has_post_thumbnail($post_id);
	if ($p){
		$attachment_id = get_post_thumbnail_id( $post_id );
		$post_src = wp_get_attachment_image_src( $attachment_id, $image_code );
		$image = $post_src[0];
	}
	if (empty($image))
		$image = 'http://placehold.it/'.$r['width'].'x'.$r['height'];
		
    return $image;
}

if( !function_exists('apache_request_headers') ) {
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';

        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
           // do some nasty string manipulations to restore the original letter case
           // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }
}
?>