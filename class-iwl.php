<?php

/**
 * Class definition for the core IWL class
 * @todo List of all IWL authors w/links to the respective posts (shortcode?)
 * @todo Disable IWL on specific pages/posts (checkbox)
 * @todo More & better i18n  
 */
class IWL {

	const PREFIX = '_pj_iwl_';
	const IWL_URL = 'https://iwl.me/api';

	/**
	 * Constructor for the IWL class
	 */
	function __construct() {
		add_action( 'save_post', array( $this, 'get_iwl_author' ), 10, 3);
		add_action( 'wp_enqueue_scripts', array( $this, 'iwl_styles' ) );

		add_filter( 'the_content', array( $this, 'display_iwl_author' ) );

	}
	/**
	 * Get the IWL author information
	 * @param int $post_id 
	 * @param WP_Post $post 
	 * @param bool $update 
	 * @return null
	 */
	function get_iwl_author( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}
		$single = true;
		$iwl_data = get_post_meta( $post_id, self::PREFIX . 'data', $single );
		if ( '' === $iwl_data ) {
			// get the IWL info from the IWL API
			$iwl_data = self::refresh_iwl_author( $post_id, $post );
			if( '' === $iwl_data ) {
				// if there's still nothin' there, bail out
				return;
			}
			update_post_meta( $post_id, self::PREFIX . 'data', $iwl_data );
		}

	}

	function iwl_styles() {
		$handle = 'iwl';
		$src = plugins_url( 'css/iwl.css', __FILE__ );
		wp_register_style( $handle, $src );
		wp_enqueue_style( $handle );
	}

	/**
	 * Filter the post content to add the IWL author section to the top
	 * @param string $content 
	 * @return string The filtered content
	 */
	function display_iwl_author( $content ) {
		/**
		 * @global $post The current post object.
		 */
		global $post;
		$single = true;
		$iwl_settings = get_option( self::PREFIX . 'settings', false );
		if( false === $iwl_settings ||
			! isset( $iwl_settings['iwl_on_' . $post->post_type ] ) ||
			1 != $iwl_settings['iwl_on_' . $post->post_type ] ) {
			// if we're not using IWL on this type of post, return the $content
			return $content;
		}
		$iwl= get_post_meta( $post->ID, self::PREFIX . 'data', $single );
		if( is_object( $iwl ) ) {
			$content = '<div class="iwl-author">
			<a href="https://iwl.me/" title="I Write Like...">IWL.me</a> thinks this sounds like the
			writing of <a href="' . $iwl->share_link . '">' . $iwl->writer . '</a>. [<a href="' . $iwl->writer_link . '">Amazon</a>]</div>' . PHP_EOL 
			. $content;
		}
		return $content;
	}

	/**
	 * Fetch IWL data for the current post's content from the IWL API
	 * @param int $post_id 
	 * @param WP_Post $post 
	 * @return object (IWL)
	 */
	function refresh_iwl_author( $post_id, $post ) {
		$iwl_settings = get_option( self::PREFIX . 'settings', false );
		if( false === $iwl_settings ) {
			// Don't proceed if there are no settings
			return false;
		}
		// Check to see if IWL is turned on for the current post type
		if( ! isset( $iwl_settings['iwl_on_' . $post->post_type ] )
			|| 1 != (bool) $iwl_settings['iwl_on_' . $post->post_type ] ) {
			return false;
		}
		$iwl_client_id = false;
		if( is_array( $iwl_settings ) && isset( $iwl_settings['iwl_client_id'] ) ) {
			$iwl_client_id = $iwl_settings['iwl_client_id'];
		}
		if( false === $iwl_client_id ) {
			// Don't proceed if the Client ID isn't set
			return false;
		}
		$post_data = array(
			'text' => strip_tags( $post->post_content ),
			'client_id' => $iwl_client_id,
			'permalink' => get_the_permalink( $post_id ),
		);
		$args = array(
		    'body' => $post_data,
		);
		$response = wp_remote_post( self::IWL_URL, $args );
		$iwl_data = json_decode( $response['body'] );
		if( is_object( $iwl_data ) ) {
			$iwl_data->updated = time();
		}
		return $iwl_data;
	}
}

new IWL();