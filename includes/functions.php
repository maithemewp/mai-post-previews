<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets a preview.
 *
 * @since 0.1.0
 *
 * @param string $url        The post url.
 * @param bool   $is_preview If in the admin editor.
 *
 * @return string
 */
function maipp_get_preview( $url, $is_preview = false ) {
	$preview = new Mai_Post_Preview( $url, $is_preview );
	return $preview->get();
}

/**
 * Gets url data.
 *
 * @since 0.1.0
 *
 * @param array|string $urls The url or urls to check asynchronously.
 *
 * @return array
 */
function maipp_get_data( $urls ) {
	$data = new Mai_Post_Preview_Data( $urls );
	return $data->get_data();
}

/**
 * Gets a transient.
 *
 * @since 0.1.0
 *
 * @param string $url The sanitized url.
 *
 * @return array|false
 */
function maipp_get_transient( $url ) {
	$transient = maipp_get_key( $url );
	return get_transient( $transient );
}

/**
 * Sets a transient.
 *
 * @since 0.1.0
 *
 * @param string $url  The sanitized url.
 * @param array  $data The url values.
 *
 * @return void
 */
function maipp_set_transient( $url, $data ) {
	$transient = maipp_get_key( $url );
	set_transient( $transient, $data, 24 * HOUR_IN_SECONDS );
}

/**
 * Deletes a transient.
 *
 * @since 0.1.0
 *
 * @param string $url The sanitized url.
 *
 * @return void
 */
function maipp_delete_transient( $url ) {
	$transient = maipp_get_key( $url );
	delete_transient( $transient );
}

/**
 * Gets the script/style `.min` suffix for minified files.
 *
 * @since 0.1.0
 *
 * @return string
 */
function maipp_get_suffix() {
	static $suffix = null;

	if ( ! is_null( $suffix ) ) {
		return $suffix;
	}

	$debug  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	$suffix = $debug ? '' : '.min';

	return $suffix;
}

/**
 * Gets a hashed key from a url.
 * This is used in the transient name.
 *
 * @since 0.1.0
 *
 * @param string $url The URL.
 *
 * @return string
 */
function maipp_get_key( $url ) {
	return 'mai_post_preview_' . md5( maipp_sanitize_url( $url ) );
}

/**
 * Sanitizes an array of URLs.
 *
 * @since 0.1.0
 *
 * @param array $urls Array of URLs previewed on a single page.
 *
 * @return array
 */
function maipp_sanitize_urls( array $urls ) {
	$urls = array_filter( $urls );
	$urls = array_map( 'maipp_sanitize_url', $urls );
	$urls = array_unique( $urls );
	$urls = array_values( $urls );
	sort( $urls );

	return $urls;
}

/**
 * Sanitizes a URL.
 *
 * @since 0.1.0
 *
 * @param string $url The URL.
 *
 * @return array
 */
function maipp_sanitize_url( $url ) {
	return trailingslashit( esc_url( $url ) );
}
