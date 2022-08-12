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
		add_action( 'init',                        [ $this, 'register_styles' ] );
		// add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_style' ] );
		add_filter( 'script_loader_tag',           [ $this, 'add_script_atts' ], 10, 3 );
		add_action( 'acf/init',                    [ $this, 'register_block' ] );
		add_action( 'acf/init',                    [ $this, 'register_field_group' ] );
	}

	/**
	 * Registers styles so they can easily be enqueued in blocks, classes, and helper functions.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_styles() {
		$suffix = maipp_get_suffix();
		wp_register_style( 'mai-post-previews', MAI_POST_PREVIEWS_PLUGIN_URL . sprintf( 'assets/css/mai-post-previews%s.css', $suffix ), [], MAI_POST_PREVIEWS_VERSION . '.' . date( 'njYHi', filemtime( MAI_POST_PREVIEWS_PLUGIN_DIR . sprintf( 'assets/css/mai-post-previews%s.css', $suffix ) ) ) );
		wp_register_style( 'mai-post-previews-loading', MAI_POST_PREVIEWS_PLUGIN_URL . sprintf( 'assets/css/mai-post-previews-loading%s.css', $suffix ), [], MAI_POST_PREVIEWS_VERSION . '.' . date( 'njYHi', filemtime( MAI_POST_PREVIEWS_PLUGIN_DIR . sprintf( 'assets/css/mai-post-previews-loading%s.css', $suffix ) ) ) );
		wp_register_script( 'mai-post-previews', MAI_POST_PREVIEWS_PLUGIN_URL . sprintf( 'assets/js/mai-post-previews%s.js', $suffix ), [], MAI_POST_PREVIEWS_VERSION . '.' . date( 'njYHi', filemtime( MAI_POST_PREVIEWS_PLUGIN_DIR . sprintf( 'assets/js/mai-post-previews%s.js', $suffix ) ) ), true );
		wp_localize_script( 'mai-post-previews', 'maippScriptVars',
			[
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'maipp_nonce' ),
			]
		);
	}

	/**
	 * Enqueues editor styles.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function enqueue_editor_style() {
		wp_enqueue_style( 'mai-post-previews' );
	}

	/**
	 * Adds attributes to scripts.
	 *
	 * @since 0.1.0
	 *
	 * @param string $tag    The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 *
	 * @return string
	 */
	function add_script_atts( $tag, $handle, $src ) {
		if ( 'mai-post-previews' !== $handle ) {
			return $tag;
		}

		$tag = str_replace( ' src', ' async src', $tag );

		return $tag;
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
				'script'            => 'mai-post-previews',
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
