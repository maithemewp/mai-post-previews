<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Mai Post Preview endpoint class.
 */
class Mai_Post_Preview_Endpoint {
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
		add_action( 'rest_api_init', [ $this, 'register_endpoint' ] );
	}

	/**
	 * Register custom endpoint.
	 *
	 * @since 0.1.0
	 *
	 * @return  void
	 */
	function register_endpoint() {
		register_rest_route( 'maipostpreviews/v1', '/urls/',
			[
				'methods'             => 'PUT', // The API does check for auth cookies and nonces when you make POST or PUT requests, but not GET requests.
				'callback'            => [ $this, 'rest_callback' ],
				'args'                => [
					'urls' => [
						'sanitize_callback' => 'maipp_sanitize_urls',
						'validate_callback' => function( $param, $request, $key ) {
							return is_array( $param );
						}
					],
				],
				'permission_callback' => function() {
					return true;
				},
			]
		);
	}

	/**
	 * API callback to get data.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	function rest_callback( WP_REST_Request $request ) {
		$urls = $request->get_body();
		$urls = explode( ',', $urls );
		$urls = maipp_sanitize_urls( $urls );

		if ( ! $urls ) {
			return rest_ensure_response(
				[
					'success' => false,
					'message' => __( 'No URLs available.', 'mai-post-previews' ),
				]
			);
		}


		$previews = [];
		$data     = maipp_get_data( $urls );

		foreach ( $data as $url => $data ) {
			$previews[ $url ] = maipp_get_preview( $url );
		}

		return rest_ensure_response(
			[
				'success'  => true,
				'previews' => $previews,
			]
		);
	}
}
