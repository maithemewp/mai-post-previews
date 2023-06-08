<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

use Embed\Embed;

/**
 * Mai Post Preview data fetcher.
 */
class Mai_Post_Preview_Data {
	protected $urls;

	/**
	 * Constructs the class.
	 *
	 * @param array|string $urls The url or urls to check asynchronously.
	 */
	function __construct( $urls ) {
		$this->urls = $urls;
	}

	/**
	 * Gets data from urls.
	 * This is slow and should be cached and pre-fetched if possible.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_data() {
		if ( is_array( $this->urls ) ) {
			return $this->get_data_from_urls();
		}

		return $this->get_data_from_url();
	}

	/**
	 * Gets data from an array of urls asynchronously.
	 * This does not check transients because it is only called via the REST API.
	 * Updates cache.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_data_from_urls() {
		$data  = [];
		$embed = new Embed();
		$infos = $embed->getMulti( ...$this->urls );

		foreach ( $infos as $index => $info ) {
			$data[ $this->urls[ $index ] ] = $this->data_from_info( $info );
		}

		// Store in cache.
		foreach ( $data as $url => $values ) {
			maipp_set_transient( $url, $values );
		}

		return $data;
	}

	/**
	 * Gets data from a single url.
	 * Checks/updates cache.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	function get_data_from_url() {
		if ( false === ( $data = maipp_get_transient( $this->urls ) ) ) {
			$embed = new Embed();
			$info  = $embed->get( $this->urls );
			$data  = $this->data_from_info( $info );

			maipp_set_transient( $this->urls, $data );
		}

		return $data;
	}

	/**
	 * Gets preview data from Embed info.
	 *
	 * @since 0.1.0
	 *
	 * @param object Embed info data.
	 *
	 * @return array
	 */
	function data_from_info( $info ) {
		static $i = 0;

		// Initial data.
		$metas = $info->getMetas();

		// Vars.
		$url   = $metas->str( 'og:url' );
		$image = $metas->str( 'og:image' );
		$title = $metas->str( 'og:title' );
		$desc  = $metas->str( 'og:description' );

		// Fallbacks.
		$url   = $url ?: $this->get_fallback_url( $i );
		$image = $image ?: $metas->get( 'twitter:image' );
		$parts = wp_parse_url( $url );
		$host  = isset( $parts['host'] ) ? $parts['host'] : '';
		$title = $title ?: $metas->get( 'twitter:title' );
		$desc  = $desc ?: $metas->get( 'description' );
		$desc  = $desc ?: $metas->get( 'twitter:description' );
		$desc  = rtrim( $desc, '.' ) . '...';

		$i++;

		return [
			'url'   => $url,
			'image' => $image,
			'host'  => $host,
			'title' => $title,
			'desc'  => $desc,
		];
	}

	/**
	 * Gets fallback URL from field value(s).
	 *
	 * @since 0.1.1
	 *
	 * @param int $i The index.
	 *
	 * @return string
	 */
	function get_fallback_url( $i ) {
		return isset( $this->urls[ $i ] ) ? $this->urls[ $i ] : '';
	}
}
