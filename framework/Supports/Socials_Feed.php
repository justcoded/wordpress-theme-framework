<?php

namespace JustCoded\WP\Framework\Supports;

use JustCoded\WP\Framework\Admin\Theme_Settings;
use Facebook\Facebook as FacebookSDK;
use MetzWeb\Instagram\Instagram as InstagramSDK;


/**
 * Class SocialsFeed
 *
 * @package JustCoded\ThemeFramework\Supports
 */
class Socials_Feed {

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

		add_action( 'jtf_social_sheduler', array( $this, 'get_social_posts' ) );
		if ( ! wp_next_scheduled( 'jtf_social_sheduler' ) ) {
			wp_schedule_event( time(), 'hourly', 'jtf_social_sheduler' );
		}


		//add_action( 'init', array( $this, 'insert_posts' ) );

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

		$panel = Theme_Settings::$panel;

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
				'name' => 'Facebook Access Token',
				'id'   => 'facebook_access_token',
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
				'name' => 'Instagram Client ID',
				'id'   => 'instagram_api_key',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Instagram Client Secret',
				'id'   => 'instagram_api_secret',
				'type' => 'text',
			) );

			$tab->createOption( array(
				'name' => 'Instagram Access Token',
				'id'   => 'instagram_access_token',
				'type' => 'text',
				'desc' => 'Get access token on http://instagram.pixelunion.net (you should be logged in your Instagram account in browser)',
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

		$facebook_app_id     = Theme_Settings::get( 'facebook_app_id' );
		$facebook_app_secret = Theme_Settings::get( 'facebook_app_secret' );
		$facebook_access_token  = Theme_Settings::get( 'facebook_access_token' );

		$fb = new FacebookSDK( array(
			'app_id'                => $facebook_app_id,
			'app_secret'            => $facebook_app_secret,
			'default_graph_version' => 'v2.12',
		) );

		$response = $fb->get( '/me/feed?fields=full_picture,message,created_time,message_tags,link', $facebook_access_token );

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
					'postmeta_url'   => ( ! empty( $fb_post['link'] ) ) ? $fb_post['link'] : '',
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
		$instagram_api_key      = Theme_Settings::get( 'instagram_api_key' );
		$instagram_api_secret   = Theme_Settings::get( 'instagram_api_secret' );
		$instagram_access_token = Theme_Settings::get( 'instagram_access_token' );

		$instagram = new InstagramSDK( array(
			'apiKey'      => $instagram_api_key,
			'apiSecret'   => $instagram_api_secret,
			'apiCallback' => get_bloginfo( 'url' ),
		) );


		$insta_posts = null;

		if ( $instagram_access_token != false ) {
			$instagram->setAccessToken( $instagram_access_token );
			$media       = $instagram->getUserMedia( 'self', 50 );
			$insta_posts = $media->data;

		}


		foreach ( $insta_posts as $insta_post ) {
			//pa($insta_post);
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
					'postmeta_url'   => ( ! empty( $insta_post->link ) ) ? $insta_post->link : '',
					'postmeta_url'   => ( ! empty( $insta_post->videos ) ) ? $insta_post->videos->standard_resolution->url : '',
				),
			);
		}

		return true;

	}

	/**
	 * Get posts from Twitter
	 */
	public function get_twitter_posts() {


		$settings = array(
			'oauth_access_token'        => Theme_Settings::get( 'twitter_access_token' ),
			'oauth_access_token_secret' => Theme_Settings::get( 'twitter_access_token_secret' ),
			'consumer_key'              => Theme_Settings::get( 'twitter_customer_key' ),
			'consumer_secret'           => Theme_Settings::get( 'twitter_customer_secret' ),
		);

		$url           = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield      = '?screen_name=' . Theme_Settings::get( 'twitter_user_name' );
		$requestMethod = 'GET';

		$twitter = new \TwitterAPIExchange( $settings );

		$twitter_posts = $twitter->setGetfield( $getfield )
		                         ->buildOauth( $url, $requestMethod )
		                         ->performRequest();

		$twitter_posts = json_decode($twitter_posts);

		foreach ( $twitter_posts as $twitter_post ) {
			$timestamp     = strtotime( $twitter_post->created_at );
			$this->posts[] = array(
				'post_title'   => 'Twitter post #' . $twitter_post->id,
				'post_content' => ( ! empty( $twitter_post->text ) ) ? $twitter_post->text : '&nbsp;',
				'post_date'    => date( 'Y-m-d H:i:s', $timestamp ),
				'post_name'    => 'twitter_post_' . $twitter_post->id,
				'post_type'    => 'socials_feed',
				'post_status'  => 'draft',
				'tax_input'    => array( 'socials_category' => array( 'twitter' ) ),
				'meta_fields'  => array(
					'postmeta_image' => ( ! empty( $twitter_post->entities->media[0]->media_url ) ) ? $twitter_post->entities->media[0]->media_url : '',
					'postmeta_url'   => 'https://twitter/' . $twitter_post->user->name . '/status/' . $twitter_post->id,
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
		$this->get_insta_posts();
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