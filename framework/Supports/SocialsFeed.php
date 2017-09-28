<?php

namespace JustCoded\ThemeFramework\Supports;

use JustCoded\ThemeFramework\Admin\ThemeSettings;
use Facebook\Facebook as FacebookSDK;
use MetzWeb\Instagram\Instagram as InstagramSDK;
use Abraham\TwitterOAuth\TwitterOAuth;


/**
 * Class SocialsFeed
 *
 * @package JustCoded\ThemeFramework\Supports
 */
class SocialsFeed {

	/**
	 * Posts array
	 *
	 * @var $fb_posts
	 */
	public $posts = array();

	/**
	 * SocialsFeed constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_socials_posttype' ) );
		add_action( 'init', array( $this, 'register_socials_taxonomy' ) );
		add_action( 'init', array( $this, 'create_settings_panel' ) );

		wp_schedule_event( time(), 'hourly', 'get_social_posts' );


		add_action( 'init', array( $this, 'insert_posts' ) );

	}

	/**
	 * Register Socials post type
	 */
	public function register_socials_posttype() {
		register_post_type( 'socials_feed', array(
			'labels'       => array(
				'name' => 'Socials Feed',
			),
			'public'       => true,
			'hierarchical' => true,
			'taxonomies'   => array(),
		) );
	}

	/**
	 * Register Socials taxonomy
	 */
	public function register_socials_taxonomy() {
		register_taxonomy( 'socials_category', array( 'socials_feed' ), array(
			'label'        => '',
			'labels'       => array(
				'name' => 'Socials Category',
			),
			'public'       => true,
			'hierarchical' => false,
		) );

	}

	/**
	 * Create Titan Framework tab for socials API settings
	 */
	public function create_settings_panel() {

		$panel = ThemeSettings::$panel;

		$tab = $panel->createTab( array(
			'name' => 'Socials Feed',
		) );

		if ( class_exists( 'Facebook\Facebook' ) ) {

			$tab->createOption( array(
				'name' => 'Facebook',
				'type' => 'heading',
			) );

			$tab->createOption( array(
				'name' => 'Facebook Application ID',
				'id'   => 'facebook_app_id',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Facebook Application Secret',
				'id'   => 'facebook_app_secret',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Facebook Page Name',
				'id'   => 'facebook_page_name',
				'type' => 'text',
			) );

		}

		if ( class_exists( 'Abraham\TwitterOAuth\TwitterOAuth' ) ) {

			$tab->createOption( array(
				'name' => 'Twitter',
				'type' => 'heading',
			) );

			$tab->createOption( array(
				'name' => 'Twitter Customer Key',
				'id'   => 'twitter_customer_key',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Twitter Customer Secret',
				'id'   => 'twitter_customer_secret',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Twitter Access Token',
				'id'   => 'twitter_access_token',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Twitter Access Token Secret',
				'id'   => 'twitter_access_token_secret',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Twitter User Name',
				'id'   => 'twitter_user_name',
				'type' => 'text',
			) );
		}

		if ( class_exists( 'MetzWeb\Instagram\Instagram' ) ) {

			$tab->createOption( array(
				'name' => 'Instagram',
				'type' => 'heading',
			) );

			$tab->createOption( array(
				'name' => 'Instagram User Name',
				'id'   => 'instagram_user_name',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Instagram API Key',
				'id'   => 'instagram_api_key',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Instagram API Secret',
				'id'   => 'instagram_api_secret',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Instagram Access Token',
				'id'   => 'instagram_access_token',
				'type' => 'text',
			) );

		}

		$tab->createOption( array(
			'type' => 'save',
		) );
	}

	/**
	 * Get posts from Facebook
	 *
	 * @return mixed
	 */
	public function get_fb_posts() {

		$facebook_app_id     = ThemeSettings::get( 'facebook_app_id' );
		$facebook_app_secret = ThemeSettings::get( 'facebook_app_secret' );
		$facebook_page_name  = ThemeSettings::get( 'facebook_page_name' );

		$fb = new FacebookSDK( array(
			'app_id'                => $facebook_app_id,
			'app_secret'            => $facebook_app_secret,
			'default_graph_version' => 'v2.10',
		) );

		$response = $fb->get( '/' . $facebook_page_name . '/posts?fields=full_picture,message,created_time,message_tags,link', ThemeSettings::get( 'facebook_app_id' ) . '|' . ThemeSettings::get( 'facebook_app_secret' ) );


		$response_body = $response->getDecodedBody();

		$fb_posts = $response_body['data'];

		foreach ( $fb_posts as $fb_post ) {
			$timestamp     = strtotime( $fb_post['created_time'] );
			$this->posts[] = array(
				'post_title'   => 'Facebook post #' . $fb_post['id'],
				'post_content' => ( ! empty( $fb_post['message'] ) ) ? $fb_post['message'] : '&nbsp;',
				'post_date'    => date( 'Y-m-d H:i:s', $timestamp ),
				'post_name'    => 'facebook_post_' . $fb_post['id'],
				'post_type'    => 'socials_feed',
				'tax_input'    => array( 'socials_category' => array( 'facebook' ) ),
				'meta_fields'  => array(
					'postmeta_image' => ( ! empty( $fb_post['full_picture'] ) ) ? $fb_post['full_picture'] : '',
				),
			);
		}

		return true;

	}

	/**
	 * Get posts from Instagram
	 *
	 * @return mixed
	 */
	public function get_insta_posts() {
		$instagram_api_key      = ThemeSettings::get( 'instagram_api_key' );
		$instagram_api_secret   = ThemeSettings::get( 'instagram_api_secret' );
		$instagram_access_token = ThemeSettings::get( 'instagram_access_token' );

		$instagram = new InstagramSDK( array(
			'apiKey'      => $instagram_api_key,
			'apiSecret'   => $instagram_api_secret,
			'apiCallback' => get_bloginfo( 'url' ),
		) );

		if ( $instagram_access_token != false ) {
			$instagram->setAccessToken( $instagram_access_token );
			$media = $instagram->getUserMedia( 'self', 50 );
			if ( ! isset( $media->data ) || ( isset( $media->code ) && in_array( $media->code, [
						'400',
						'401',
						'403',
						'404',
						'500',
					] ) )
			) {
				delete_post_meta( get_the_ID(), 'instagram_access_token' );
				$sign_in = $instagram->getLoginUrl();
			} else {
				$insta_posts = $media->data;
			}
		}

		foreach ( $insta_posts as $insta_post ) {

			$timestamp     = $insta_post->created_time;
			$this->posts[] = array(
				'post_title'   => 'Instagram post #' . $insta_post->id,
				'post_content' => ( ! empty( $insta_post->caption->text ) ) ? $insta_post->caption->text : '&nbsp;',
				'post_date'    => date( 'Y-m-d H:i:s', $timestamp ),
				'post_name'    => 'instagram_post_' . $insta_post->id,
				'post_type'    => 'socials_feed',
				'tax_input'    => array( 'socials_category' => array( 'instagram' ) ),
				'meta_fields'  => array(
					'postmeta_image' => ( ! empty( $insta_post->images->standard_resolution->url ) ) ? $insta_post->images->standard_resolution->url : '',
				),
			);
		}

		return true;

	}

	/**
	 * Get posts from Twitter
	 */
	public function get_twitter_posts() {
		$twitter_customer_key        = ThemeSettings::get( 'twitter_customer_key' );
		$twitter_customer_secret     = ThemeSettings::get( 'twitter_customer_secret' );
		$twitter_access_token        = ThemeSettings::get( 'twitter_access_token' );
		$twitter_access_token_secret = ThemeSettings::get( 'twitter_access_token_secret' );
		$twitter_user_name           = ThemeSettings::get( 'twitter_user_name' );

		$connection = new TwitterOAuth( $twitter_customer_key, $twitter_customer_secret, $twitter_access_token, $twitter_access_token_secret );


		$twitter_posts = $connection->get( 'statuses/user_timeline', array(
			'screen_name' => $twitter_user_name,
			'count'       => 10,
		) );

		foreach ( $twitter_posts as $twitter_post ) {
			$timestamp     = strtotime( $twitter_post->created_at );
			$this->posts[] = array(
				'post_title'   => 'Twitter post #' . $twitter_post->id,
				'post_content' => ( ! empty( $twitter_post->text ) ) ? $twitter_post->text : '&nbsp;',
				'post_date'    => date( 'Y-m-d H:i:s', $timestamp ),
				'post_name'    => 'twitter_post_' . $twitter_post->id,
				'post_type'    => 'socials_feed',
				'post_status'  => 'publish',
				'tax_input'    => array( 'socials_category' => array( 'twitter' ) ),
				'meta_fields'  => array(
					'postmeta_image' => ( ! empty( $twitter_post->entities->media[0]->media_url ) ) ? $twitter_post->entities->media[0]->media_url : '',
				),
			);
		}

		return true;
	}

	/**
	 * Insert WP posts
	 *
	 * @return bool
	 */
	public function insert_posts() {

		$this->get_fb_posts();

		if ( class_exists( 'MetzWeb\Instagram\Instagram' ) ) {
			$this->get_insta_posts();
		}


		$this->get_twitter_posts();


		foreach ( $this->posts as $post ) {
			$meta_fields = array();
			if ( $this->is_exists( $post['post_title'] ) ) {
				continue;
			}
			if ( isset( $post['meta_fields'] ) ) {
				$meta_fields = $post['meta_fields'];
				unset( $post['meta_fields'] );
			}

			$post_id = wp_insert_post( $post );

			foreach ( $meta_fields as $meta_key => $meta_value ) {
				update_post_meta( $post_id, $meta_key, $meta_value );
			}
		}

		return true;

	}

	protected function is_exists( $post_title ) {
		return get_page_by_title( wp_strip_all_tags( $post_title ), OBJECT, 'socials_feed' );
	}


}