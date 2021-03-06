<?php
/**
 * Settings class file.
 *
 * @package i-write-like
 * @since 1.0.0
 */

/**
 * Settings class.
 *
 * @since 1.0.0
 */
class IWL_Settings {

	/**
	 * Class constructor.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'iwl_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'iwl_settings_init' ) );
	}

	/**
	 * Adds an admin menu.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function iwl_add_admin_menu() {

		add_options_page( 'I Write Like', 'I Write Like', 'manage_options', 'i_write_like', array( $this, 'iwl_options_page' ) );

	}

	/**
	 * Initializes the settings page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function iwl_settings_init() {

		register_setting( 'iwl_plugin_page', IWL::PREFIX . 'settings' );

		add_settings_section(
			'iwl_plugin_page_section',
			__( 'Settings for the <abbr title="I Write Like...">IWL</abbr> plugin', 'iwl' ),
			array( $this, 'iwl_settings_section_callback' ),
			'iwl_plugin_page'
		);

		add_settings_field(
			'iwl_client_id',
			__( 'Client ID from <a href="https://iwl.me/">IWL.me</a>', 'iwl' ),
			array( $this, 'iwl_client_id_render' ),
			'iwl_plugin_page',
			'iwl_plugin_page_section'
		);

		add_settings_field(
			'iwl_post_types',
			__( 'Post Type(s) to use IWL data', 'iwl' ),
			array( $this, 'iwl_post_types_render' ),
			'iwl_plugin_page',
			'iwl_plugin_page_section'
		);
	}

	/**
	 * Displays the IWL Client ID box.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function iwl_client_id_render() {

		$options = get_option( IWL::PREFIX . 'settings' );
		echo( "<input
					type='text'
					name='" . IWL::PREFIX . "settings[iwl_client_id]'
					value='{$options['iwl_client_id']}'>"
			);

	}

	/**
	 * Displays the IWL Post Types selector.
	 *
	 * @return void
	 */
	function iwl_post_types_render() {

		$options = get_option( IWL::PREFIX . 'settings' );
		$post_types = get_post_types( '', 'objects' );
		$output = '';
		$no_echo = false;
		// Remove some post types.
		unset( $post_types['attachment'] );
		unset( $post_types['revision'] );
		unset( $post_types['nav_menu_item'] );

		foreach ( $post_types as $post_type => $post_type_obj ) {
			$output .= "<input
						type='checkbox'
						name='" . IWL::PREFIX . "settings[iwl_on_{$post_type}]'
						" . checked( $options[ 'iwl_on_' . $post_type ], 1, $no_echo ) . "
						value='1'>
						{$post_type_obj->labels->name}<br />" . PHP_EOL;

		}

		echo $output;

	}
	/**
	 * Displays the settings section.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function iwl_settings_section_callback() {

		_e( 'Who do you write like? Let <abbr title="I Write Like...">IWL</abbr> find out for you.', 'iwl' );

	}

	/**
	 * Displays the IWL Options page.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function iwl_options_page() {

		?>
		<form action='options.php' method='post'>

			<h2>I Write Like... settings</h2>

			<?php
			settings_fields( 'iwl_plugin_page' );
			do_settings_sections( 'iwl_plugin_page' );
			submit_button();
			?>

		</form>
		<?php

	}

} // End of the class.

new IWL_Settings;
