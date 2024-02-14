<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

use Embed\Embed;

class Mai_Post_Preview_Block {
	/**
	 * Construct the class.
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'acf/init',                       [ $this, 'register_block' ] );
		add_action( 'acf/init',                       [ $this, 'register_field_group' ] );
		add_action( 'init',                           [ $this, 'register_styles' ] );
		add_action( 'acf/render_field/key=maipp_rel', [ $this, 'add_css' ] );
	}

	/**
	 * Registers block.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register_block() {
		register_block_type( __DIR__ . '/block.json',
			[
				'render_callback' => [ $this, 'render_block' ],
			]
		);
	}

	/**
	 * Callback function to render the block.
	 *
	 * @since 0.1.0
	 *
	 * @param array    $attributes The block attributes.
	 * @param string   $content The block content.
	 * @param bool     $is_preview Whether or not the block is being rendered for editing preview.
	 * @param int      $post_id The current post being edited or viewed.
	 * @param WP_Block $wp_block The block instance (since WP 5.5).
	 * @param array    $context The block context array.
	 *
	 * @return void
	 */
	function render_block( $attributes, $content, $is_preview, $post_id, $wp_block, $context ) {
		// Setup args.
		$args = [
			'url'     => (string) get_field( 'url' ),
			'preview' => $is_preview,
		];

		// Get rel.
		$rel = get_field( 'rel' );

		// This is a new field, but we want the default to be nofollow, so check for null before adding.
		// If this is left out, then `maipp_get_preview()` will use the default value of `nofollow` for the `rel` arg.
		if ( ! is_null( $rel ) ) {
			$args['rel'] = (array) $rel;
		}

		echo maipp_get_preview( $args );
	}

	/**
	 * Add field group.
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
					[
						'key'           => 'maipp_rel',
						'label'         => __( 'Attributes (rel)', 'mai-post-previews' ),
						'name'          => 'rel',
						'type'          => 'checkbox',
						'default_value' => [ 'nofollow' ],
						'choices'       => [
							'nofollow'   => 'nofollow <span class="maipp-checkbox-desc">' . __( 'Tells Google not to crawl the linked page to pass link equity to it.', 'mai-post-previews' ) . '</span>',
							'noreferrer' => 'noreferrer <span class="maipp-checkbox-desc">' . __( 'Tells the browser not to send any referrer information to the target resource when the user clicks the link.', 'mai-post-previews' ) . '</span>',
							'sponsored' => 'sponsored <span class="maipp-checkbox-desc">' . __( 'Indicates a link from advertisements or paid placements.', 'mai-post-previews' ) . '</span>',
						],
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
	 * Adds CSS before the field.
	 *
	 * @since 0.3.0
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	function add_css( $field ) {
		?>
		<style>
			.maipp-checkbox-desc {
				display: block;
				margin: 6px 0 12px;
				font-size: .8em;
				line-height: 1.5;
			}
		</style>
		<?php
	}
}