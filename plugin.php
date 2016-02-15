<?php

/**
 * Plugin Name: I Write Like...
 * Plugin URI: http://patj.ca/wp/plugins/i-write-like
 * Description: What famous author is your doppelgänger? Find out!
 * Author Name: Patrick Johanneson
 * Author URI: http://patj.ca/
 * License: GPL v3
 */
 
require_once( 'class-iwl.php' ); 
require_once( 'class-iwl-settings.php' ); 

add_action( 'shutdown', function() {
	_dump( get_option( IWL::PREFIX . 'settings' ) );
});