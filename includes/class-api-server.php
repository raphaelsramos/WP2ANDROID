<?php
/**
 *
 * Handles REST API requests
 *
 * @author      ThunderBear Design
 * @category    API
 * @package     WPAndroid/API
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once ABSPATH . 'wp-admin/includes/admin.php';

class WP2ANDROID_API_Server extends WP2ANDROID_API_Server_Instance {
    
	public function __construct($request, $auth_key) {
        parent::__construct($request);
		
		$auth_data = apache_request_headers();
		$API_KEY = "";
		
		extract($auth_data);
		if (empty ($API_KEY))
			$API_KEY = $APIKEY;
		
		if (empty ($API_KEY))
			$API_KEY = $ApiKey;

		if (empty ($API_KEY))
			$API_KEY = $Apikey;

		if (empty ($API_KEY))
			$API_KEY = $apikey;
		
		if (empty ($API_KEY)){
			if (array_key_exists('ApiKey', $auth_data)){
				$API_KEY = $auth_data['ApiKey'];
			}else{
				$API_KEY = $auth_data['APIKEY'];
			}
		}
		
        if (empty($API_KEY)) {
            throw new Exception('No API Authentication Key provided for the Application');
        } elseif (get_option('wp2android_auth_key')!==$API_KEY) {
            throw new Exception('Invalid API Authentication Key for the Application');
        }
    }

    /**
     * Categories Endpoint
     */
     protected function categories() {
        if ($this->method == 'GET') {
			$category_settings = array('hide_empty' => 0);
			$categories_mode = wp2android_settings('category_mode');
			if ($categories_mode=="selective"){
				$category_settings['include'] = wp2android_settings('categories_to_show');
			}
			$categories = get_categories($category_settings);
			$output['categories'] = $categories;
            return $output;
        }
		return $this->error('Only Accepted the GET Method');
     }
	 
	 
	 function custom_excerpt_length( $length ) {
		 return 30;
	 }
	 
	 private function custom_excerpt($text){
		 add_filter( 'excerpt_length', array($this, 'custom_excerpt_length'), 999 );
		 $texts = wp_trim_words(apply_filters('the_excerpt', $text), 30);
		 remove_filter( 'excerpt_length', array($this, 'custom_excerpt_length') );
		 return $texts;
	 }
	 
	 protected function latest(){
		if ($this->method == 'GET') {
			$feeds = array();
			query_posts('showposts=' . wp2android_settings('recent_posts'));
			while(have_posts()): the_post();
				$feeds[] = array(
					"id"	=>get_the_ID(),
					"category" => wp2android_post_single_category(get_the_ID()),
					"name"	=>get_the_title(),
					"description" => $this->custom_excerpt(get_the_excerpt()),
					"image" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_thumbnail')),
					"image_big" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_image')),
					"status"=> strip_tags(get_the_excerpt()),
					"profilePic"=> wp2android_avatar_url(get_avatar( get_the_author_meta('ID'), 180 )),
					"timeStamp"=> get_post_time('U', true)*1000,
					"url"=> get_permalink(get_the_ID())
				);
			endwhile;
			wp_reset_query();
			$output['feed'] = !empty($feeds)?$feeds:null;
            return $output;
        }
		return $this->error('Only Accepted the GET Method');
	 	
	 }
	 
	 protected function category($path){
		if ($this->method == 'GET') {
			$feeds = array();
			query_posts('showposts=' . wp2android_settings('category_posts') . '&cat=' . intval($path[0]));
			while(have_posts()): the_post();
				$feeds[] = array(
					"id"	=>get_the_ID(),
					"category" => wp2android_post_single_category(get_the_ID()),
					"name"	=>get_the_title(),
					"description" => $this->custom_excerpt(get_the_excerpt()),
					"image" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_thumbnail')),
					"image_big" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_image')),
					"profilePic"=> wp2android_avatar_url(get_avatar( get_the_author_meta('ID'), 180 )),
					"timeStamp"=> get_post_time('U', true)*1000,
					"url"=> get_permalink(get_the_ID())
				);
			endwhile;
			wp_reset_query();
			$output['feed'] = !empty($feeds)?$feeds:null;
            return $output;
        }
		return $this->error('Only Accepted the GET Method'); 	
	 }
	 
	 protected function post($path){
		if ($this->method == 'GET') {
			
			$feeds = array();
			query_posts('post_type=any&p='.intval($path[0]));
			$total_found = $wp_query->found_posts;
			the_post();
			$content = get_the_content( null, false );
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			
			$feeds = array(
				"id"	=>get_the_ID(),
				"name"	=> html_entity_decode(get_the_title()),
				"image" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_image')),
				"description" => $this->custom_excerpt(get_the_excerpt()),
				"profilePic"=> wp2android_avatar_url(get_avatar( get_the_author_meta('ID'), 180 )),
				"timeStamp"=> get_post_time('U', true)*1000,
				"url"=> get_permalink(get_the_ID()),
				"author" => get_the_author(),
				"can_comment" => (comments_open()?"yes":"no"),
				"comments" => get_comments_number(),//comments_number( 0, 1, '%' ),
				"story_content"=>$content,
			);
			wp_reset_query();
			$output=$feeds;
            return $output;
        }
		return $this->error('Only Accepted the GET Method');
	 }
	 
	 protected function search ($path){
		if ($this->method == 'GET') {
			$feeds = array();
			$rec_per_page = wp2android_settings('category_posts');
			query_posts('showposts=' . $rec_per_page . '&s='.$this->verb.'&paged=' . intval($path[0]));

			global $wp_query;
			$total_found = $wp_query->found_posts;
			$total_pages = ceil($wp_query->found_posts/$rec_per_page);
			while(have_posts()): the_post();
				$feeds[] = array(
					"id"	=>get_the_ID(),
					"category" => wp2android_post_single_category(get_the_ID()),
					"name"	=>get_the_title(),
					"description" => $this->custom_excerpt(get_the_excerpt()),
					"image" => html_entity_decode(wp2android_post_image(get_the_ID(), 'wp2android_thumbnail')),
					"status"=> strip_tags(get_the_excerpt()),
					"profilePic"=> wp2android_avatar_url(get_avatar( get_the_author_meta('ID'), 180 )),
					"timeStamp"=> get_post_time('U', true)*1000,
					"url"=> get_permalink(get_the_ID())
				);
			endwhile;
			wp_reset_query();
			$output['total_items'] = $total_found;
			$output['total_pages'] = $total_pages;
			$output['feed'] = !empty($feeds)?$feeds:null;
            return $output;
        }
		return $this->error('Only Accepted the GET Method');
	 }
	 	 
/**
 * @function    Post Comment
 * @since       1.0
 */
	 protected function post_comment($path){
		if ($this->method == 'POST') {
			$post_id = intval($path[0]);
			$output = array();
			query_posts('post_type=any&p=' . $post_id);
			$total_found = $wp_query->found_posts;
			if (have_posts()){
				$comment_name	= $_POST['comment_name'];
                $comment_email	= $_POST['comment_email'];
                $post_comment	= $_POST['post_comment'];
				
				$time = current_time('mysql');

				$data = array(
					'comment_post_ID' => $post_id,
					'comment_author' => $comment_name,
					'comment_author_email' => $comment_email,
					'comment_author_url' => 'http://',
					'comment_content' => $post_comment,
					'comment_type' => '',
					'comment_parent' => 0,
					'user_id' => 0,
					'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
					'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
					'comment_date' => $time,
					'comment_approved' => 0,
				);
				
				$wp_insert = wp_insert_comment($data);
				if ( is_wp_error( $wp_insert ) ) {
				   $output['error'] = $result->get_error_message();
				}else{
					$output['success'] = "yes";
				}
				//wp_insert_comment($data);
			}
			wp_reset_query();
            return $output;
        }
		return $this->error('Only Accepted the POST Method');
	 }
	 
/**
 * @function    Get Comment
 * @since       1.0
 */
	 protected function comments($path){
		
		if ($this->method == 'GET') {
			$feeds = array();
			query_posts('showposts=' . $rec_per_page . '&s='.$this->verb.'&paged=' . intval($path[0]));

			$comment_array = get_approved_comments($path[0]);
			
			foreach ($comment_array as $comment){
				$nfeed = array(
					"ID"			=> $comment->comment_ID,
					"author"		=> $comment->comment_author,
					"author_email"	=> $comment->comment_author_email,
					"comment_date"	=> strtotime($comment->comment_date)*1000,
					"comment_content"=> $comment->comment_content,
					"avatar"=> null
				);
				$feeds[] = $nfeed;
			}
			
			$output['total_items'] = count($feeds);
			$output['total_pages'] = 1;
			$output['feed'] = !empty($feeds)?$feeds:null;
            return $output;
        }
		return $this->error('Only Accepted the GET Method');
		
	 }
}