<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

use Embed\Embed;


/**
 * Mai Post Preview block registration class.
 */
class Mai_Post_Preview_Block {
	/**
	 * Constructs the class.
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'init',     [ $this, 'register_styles' ] );
		add_action( 'acf/init', [ $this, 'register_block' ] );
		add_action( 'acf/init', [ $this, 'register_field_group' ] );
	}

	/**
	 * Registers styles so they can easily be enqueued in blocks, classes, and helper functions.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_styles() {
		$suffix           = maipp_get_suffix();
		$file_previews    = "assets/css/mai-post-previews{$suffix}.css";
		$file_loading     = "assets/css/mai-post-previews-loading{$suffix}.css";
		$file_previews_js = "assets/js/mai-post-previews{$suffix}.js";

		wp_register_style( 'mai-post-previews', $this->get_file_data( $file_previews, 'url' ), [], $this->get_file_data( $file_previews, 'version' ) );
		wp_register_style( 'mai-post-previews-loading', $this->get_file_data( $file_loading, 'url' ), [], $this->get_file_data( $file_loading, 'version' ) );
		wp_register_script( 'mai-post-previews', $this->get_file_data( $file_previews_js, 'url' ), [], $this->get_file_data( $file_previews_js, 'version' ), [ 'strategy' => 'async' ] );
		wp_localize_script( 'mai-post-previews', 'maippScriptVars',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'maipp_nonce' ),
			]
		);
	}

	/**
	 * Gets file data.
	 *
	 * @since 0.2.0
	 *
	 * @param string $file The file path name.
	 * @param string $key The specific key to return
	 *
	 * @return array|string
	 */
	function get_file_data( $file, $key = '' ) {
		static $cache = null;

		if ( ! is_null( $cache ) && isset( $cache[ $file ] ) ) {
			if ( $key ) {
				return $cache[ $file ][ $key ];
			}

			return $cache[ $file ];
		}

		$file_path      = MAI_POST_PREVIEWS_DIR . $file;
		$file_url       = MAI_POST_PREVIEWS_URL . $file;
		$version        = MAI_POST_PREVIEWS_VERSION . '.' . date( 'njYHi', filemtime( $file_path ) );
		$cache[ $file ] = [
			'path'    => $file_path,
			'url'     => $file_url,
			'version' => $version,
		];

		if ( $key ) {
			return $cache[ $file ][ $key ];
		}

		return $cache[ $file ];
	}

	/**
	 * Registers blocks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_block() {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		}

		acf_register_block_type(
			[
				'name'              => 'mai-post-preview',
				'title'             => __( 'Mai Post Preview', 'mai-post-previews' ),
				'description'       => __( 'Preview a post with image, title, description, and link.', 'mai-post-previews' ),
				'icon'              => 'embed-post',
				'category'          => 'widgets',
				'keywords'          => [ 'embed', 'post', 'preview' ],
				'api_version'       => 2,
				'acf_block_version' => 2,
				'render_callback'   => [ $this, 'do_preview' ],
				'style'             => 'mai-post-previews',
				'supports'          => [
					'align'  => false,
					'anchor' => false,
				],
				'example'          => [
					'attributes' => [
						'mode' => 'preview',
						'data' => [
						  'url' => 'https://bizbudding.com/mai-theme/',
						]
					]
				]
			]
		);
	}

	/**
	 * Renders the post preview.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function do_preview( $block, $content = '', $is_preview = false ) {
		$url = get_field( 'url' );

		echo maipp_get_preview( $url, $is_preview );
	}

	/**
	 * Registers field groups.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			[
				'key'    => 'maipp_field_group',
				'title'  => __( 'Mai Post Preview', 'mai-post-previews' ),
				'fields' => [
					[
						'key'   => 'maipp_post_url',
						'label' => __( 'Post URL', 'mai-post-previews' ),
						'name'  => 'url',
						'type'  => 'url',
					],
				],
				'location' => [
					[
						[
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/mai-post-preview',
						],
					],
				],
				'active' => true,
			]
		);
	}
}
