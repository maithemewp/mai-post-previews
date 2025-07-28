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
				'methods'             => 'PUT',
				'callback'            => [ $this, 'rest_callback' ],
				'args'                => [
					'urls' => [
						'required'          => true,
						'sanitize_callback' => 'maipp_sanitize_urls',
						'validate_callback' => function( $param, $request, $key ) {
							if ( ! is_array( $param ) ) {
								return false;
							}

							// Limit number of URLs to prevent abuse.
							if ( count( $param ) > 50 ) {
								return false;
							}

							return true;
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
		try {
			// Add timeout for external requests.
			$timeout = apply_filters( 'maipp_request_timeout', 30 );

			// Add memory limit check.
			$memory_limit = ini_get( 'memory_limit' );
			$memory_usage = memory_get_usage( true );

			if ( $memory_usage > ( 0.8 * $this->get_memory_limit_bytes( $memory_limit ) ) ) {
				return rest_ensure_response(
					[
						'success' => false,
						'message' => __( 'Server resources exceeded.', 'mai-post-previews' ),
					]
				);
			}

			$urls = $request->get_body();
			$urls = explode( ',', $urls );
			$urls = maipp_sanitize_urls( $urls );

			// If no urls, return error.
			if ( ! $urls ) {
				return rest_ensure_response(
					[
						'success' => false,
						'message' => __( 'No URLs available.', 'mai-post-previews' ),
					]
				);
			}

			// Start previews.
			$previews = [];

			// Get data with error handling.
			$data = maipp_get_data( $urls );

			// Check if data retrieval failed.
			if ( is_wp_error( $data ) ) {
				$this->log_error( 'Data retrieval failed', [ 'urls' => $urls, 'error' => $data->get_error_message() ] );
				return rest_ensure_response(
					[
						'success' => false,
						'message' => $data->get_error_message(),
					]
				);
			}

			// Loop through the data.
			foreach ( $data as $url => $values ) {
				// Get the preview with error handling.
				$preview = maipp_get_preview( [ 'url' => $url ] );

				if ( is_wp_error( $preview ) ) {
					$previews[ $url ] = [
						'error' => $preview->get_error_message(),
					];
					$this->log_error( 'Preview generation failed', [ 'url' => $url, 'error' => $preview->get_error_message() ] );
				} else {
					$previews[ $url ] = $preview;
				}
			}

			// Return the previews.
			return rest_ensure_response(
				[
					'success'  => true,
					'previews' => $previews,
				]
			);

		} catch ( Exception $e ) {
			$this->log_error( 'Unexpected error in REST callback', [ 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ] );
			return rest_ensure_response(
				[
					'success' => false,
					'message' => __( 'An unexpected error occurred.', 'mai-post-previews' ),
					'debug'   => WP_DEBUG ? $e->getMessage() : null,
				]
			);
		}
	}

	/**
	 * Get memory limit in bytes.
	 *
	 * @since 0.1.0
	 *
	 * @param string $memory_limit The memory limit string.
	 *
	 * @return int
	 */
	private function get_memory_limit_bytes( $memory_limit ) {
		$unit = strtolower( substr( $memory_limit, -1 ) );
		$value = (int) substr( $memory_limit, 0, -1 );

		switch ( $unit ) {
			case 'g': return $value * 1024 * 1024 * 1024;
			case 'm': return $value * 1024 * 1024;
			case 'k': return $value * 1024;
			default: return $value;
		}
	}

	/**
	 * Log error for debugging.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message The error message.
	 * @param array  $context Additional context.
	 *
	 * @return void
	 */
	private function log_error( $message, $context = [] ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[Mai Post Previews] %s - %s', $message, json_encode( $context ) ) );
		}
	}
}
