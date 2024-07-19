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

		// Loop through the results.
		foreach ( $infos as $index => $info ) {
			$values = [];

			// If no errors, get data.
			if ( $info && 200 === $info->getResponse()->getStatusCode() ) {
				$values = $this->data_from_info( $info );
			}

			// Set data.
			$data[ $this->urls[ $index ] ] = $values;
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
		// Check transient.
		if ( false === ( $data = maipp_get_transient( $this->urls ) ) ) {
			$data  = [];
			$embed = new Embed();
			$info  = $embed->get( $this->urls );

			// If no error, get data.
			if ( $info && 200 === $info->getResponse()->getStatusCode() ) {
				$data = $this->data_from_info( $info );
			}

			// Store in cache.
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
		$url   = $this->get_url( $i );
		$image = $metas->url( 'og:image' );
		$title = $metas->str( 'og:title' );
		$desc  = $metas->str( 'og:description' );

 		// Fallbacks.
		$host  = (string) parse_url( $url, PHP_URL_HOST );
		$host  = ltrim( (string) $host, 'www.' );
		$image = $info->image;
		$image = $image ? $image->__toString() : '';
		$image = $image ?: $metas->url( 'twitter:image' );
		$title = $title ?: $metas->str( 'twitter:title' );
		$title = $title ?: (string) $info->title;
		$desc  = $desc ?: $metas->html( 'description' );
		$desc  = $desc ?: $metas->html( 'twitter:description' );
		$desc  = $desc ?: (string) $info->description;
		$desc  = rtrim( (string) $desc, '.' ) . '...';

		$i++;

		$data = [
			'url'   => $url,
			'image' => $image,
			'host'  => $host,
			'title' => $title,
			'desc'  => $desc,
		];

		return $data;
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
	function get_url( $i ) {
		return isset( $this->urls[ $i ] ) ? $this->urls[ $i ] : '';
	}
}
