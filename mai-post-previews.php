<?php

/**
 * Plugin Name:     Mai Post Previews
 * Plugin URI:      https://bizbudding.com
 * Description:     Show a preview embed of external posts via meta data.
 * Version:         0.2.2
 *
 * Author:          BizBudding, Mike Hemberger
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Must be at the top of the file.
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Main Mai_Post_Previews_Plugin Class.
 *
 * @since 0.1.0
 */
final class Mai_Post_Previews_Plugin {

	/**
	 * @var   Mai_Post_Previews_Plugin The one true Mai_Post_Previews_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Post_Previews_Plugin Instance.
	 *
	 * Insures that only one instance of Mai_Post_Previews_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Post_Previews_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Mai_Post_Previews_Plugin::includes() Include the required files.
	 * @uses    Mai_Post_Previews_Plugin::hooks() Activate, deactivate, etc.
	 * @see     Mai_Post_Previews_Plugin()
	 * @return  object | Mai_Post_Previews_Plugin The one true Mai_Post_Previews_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Post_Previews_Plugin;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'MAI_POST_PREVIEWS_VERSION' ) ) {
			define( 'MAI_POST_PREVIEWS_VERSION', '0.2.2' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_POST_PREVIEWS_DIR' ) ) {
			define( 'MAI_POST_PREVIEWS_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_POST_PREVIEWS_URL' ) ) {
			define( 'MAI_POST_PREVIEWS_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		foreach ( glob( MAI_POST_PREVIEWS_DIR . 'includes/*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'updater' ], 12 );
		add_action( 'plugins_loaded', [ $this, 'run' ] );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {
		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = PucFactory::buildUpdateChecker( 'https://github.com/maithemewp/mai-post-previews/', __FILE__, 'mai-post-previews' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}

	/**
	 * Instantiates plugin classes.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function run() {
		new Mai_Post_Preview_Endpoint;
		new Mai_Post_Preview_Block;
	}
}

/**
 * The main function for that returns Mai_Post_Previews_Plugin
 *
 * The main function responsible for returning the one true Mai_Post_Previews_Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = mai_post_previews_plugin(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Post_Previews_Plugin The one true Mai_Post_Previews_Plugin Instance.
 */
function mai_post_previews_plugin() {
	return Mai_Post_Previews_Plugin::instance();
}

// Get Mai_Post_Previews_Plugin Running.
mai_post_previews_plugin();
