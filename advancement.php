<?php
/*
Plugin Name: Advancement
Version: 1.0
Author: Daniel Heller
Description: Features specific to the USC Giving website
Text Domain: advance
*/

/**

	Table of Contents

	1.0 - Settings Page
	2.0 - Post Types
	3.0 - Custom Fields
	4.0 - Page Templates
	5.0 - Scheduled Feeds
	6.0 - AJAX Requests
*/



if ( ! defined('ABSPATH') ) {
	exit; // if loading directly
}


/**
 * Add settings link for plugin
 *
 * @param Array $links: an array of links to plugin settings
 */
function advance_plugin_action_links( $plugin_actions, $plugin_file ) {
	$new_actions = array();

	if ( false !== strpos( $plugin_file, 'advancement.php' ) ) {
		$new_actions['advance_settings'] = sprintf( __( '<a href="%s">Settings</a>', 'advance' ), esc_url( admin_url( 'options-general.php?page=advance-settings' ) ) );
	}

	return array_merge( $new_actions, $plugin_actions );
}
if ( is_admin() ) {
	add_filter( 'plugin_action_links', 'advance_plugin_action_links', 10, 2 );
}


if ( ! class_exists( 'ADV' ) ) {

	/**
	 * The main ACF class
	 */
	#[AllowDynamicProperties]
	class ADV {

		/**
		 * Defines a constant if doesnt already exist.
		 *
		 * @date    3/5/17
		 * @since   5.5.13
		 *
		 * @param   string $name  The constant name.
		 * @param   mixed  $value The constant value.
		 * @return  void
		 */
		public function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		public function initialize() {
			$this->define( 'ADVANCE_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'ADVANCE_URL', plugin_dir_url( __FILE__ ) );

			/*--------------------------------------------------------------
			1.0 - Settings Page
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/settings.php';


			/*--------------------------------------------------------------
			2.0 - Post Types
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/post-types.php';


			/*--------------------------------------------------------------
			3.0 - Custom Fields
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/acf-groups.php';


			/*--------------------------------------------------------------
			4.0 - Page Templates
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/templates.php';


			/*--------------------------------------------------------------
			5.0 - Scheduled Feeds
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/scheduled-feeds.php';


			/*--------------------------------------------------------------
			6.0 - AJAX Requests
			--------------------------------------------------------------*/

			include_once ADVANCE_PATH . 'functions/admin-ajax.php';

		}
	}
	
	function adv() {
		global $adv;

		// Instantiate only once.
		if ( ! isset( $adv ) ) {
			$adv = new ADV();
			$adv->initialize();
		}
		return $adv;
	}
	adv();
}
