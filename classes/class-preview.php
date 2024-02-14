<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Preview class wrapper.
 *
 * @uses https://github.com/oscarotero/Embed
 */
class Mai_Post_Preview {
	protected $args;

	/**
	 * Constructs the class.
	 */
	function __construct( $args ) {
		// Parse.
		$this->args = wp_parse_args( $args,
			[
				'url'     => '',
				'rel'     => [ 'nofollow' ],
				'preview' => false,
			]
		);

		// Sanitize.
		$this->args['url']     = maipp_sanitize_url( (string) $this->args['url'] ?: 'https://bizbudding.com/mai-theme/' );
		$this->args['rel']     = rest_sanitize_array( (array) $this->args['rel'] );
		$this->args['preview'] = rest_sanitize_boolean( $this->args['preview'] );
	}

	/**
	 * Gets the preview.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function get() {
		$class = 'mai-post-preview';

		// If in editor, check transient then get data.
		if ( $this->args['preview'] ) {
			$class .= ' mai-post-preview-editor';
			$data   = maipp_get_data( $this->args['url'] );
		}
		// Front end only checks transient, if no data we'll prepare for ajax.
		else {
			$data = maipp_get_transient( $this->args['url'] );
		}

		// If error, we'd have an empty array.
		if ( is_array( $data ) && empty( $data ) ) {
			// If preview, show notice.
			if ( $this->args['preview'] ) {
				$data = [
					'url'   => '',
					'image' => '',
					'host'  => '',
					'title' => __( 'Error', 'mai-post-previews' ),
					'desc'  => __( 'There was an error getting the preview data. Please provide a valid and accessible URL.', 'mai-post-previews' ),
				];
			} else {
				$data = [
					'url'   => $this->args['url'],
					'image' => '',
					'host'  => $this->args['url'],
					'title' => __( 'Uh oh.', 'mai-post-previews' ),
					'desc'  => __( 'This post is not available at this time. Please try again later.', 'mai-post-previews' ),
				];
			}
		}

		// Check if this is for ajax.
		$for_ajax = false === $data;

		// Enqueue styles.
		wp_enqueue_style( 'mai-post-previews' );

		if ( $for_ajax ) {
			$class .= $for_ajax ? ' mai-post-preview-ajax' : '';

			// Enqueue styles/scripts for ajax.
			wp_enqueue_style( 'mai-post-previews-loading' );
			wp_enqueue_script( 'mai-post-previews' );
		}

		// Start HTML.
		$html = '';

		// If for ajax.
		if ( $for_ajax ) {
			$data          = [];
			$data['url']   = $this->args['url'];
			$data['image'] = '&nbsp;';
			$data['host']  = '&nbsp;';
			$data['title'] = '&nbsp;';
			$data['desc']  = '&nbsp;';

			$html .= sprintf( '<div class="%s" data-url="%s">', $class, $data['url'] );
			$html .= '<div class="mai-post-preview-figure"></div>';
		}
		// No ajax.
		else {
			$html .= sprintf( '<div class="%s">', $class );
			$html .= '<figure class="mai-post-preview-figure">';
				// We may not have an image if it's an error and preview.
				if ( $data['image'] ) {
					$html .= sprintf( '<img class="mai-post-preview-image" src="%s" alt="%s" width="266" height="354">', esc_url( (string) $data['image'] ), esc_attr( $data['title'] ) );
				}
			$html .= '</figure>';
		}

			// Build inner HTML.
			$html .= '<div class="mai-post-preview-inner">';
				$html .= sprintf( '<p class="mai-post-preview-domain">%s</p>', esc_html( ltrim( (string) $data['host'], 'www.' ) ) );
				$html .= sprintf( '<h3 class="mai-post-preview-title">%s</h3>', esc_html( $data['title'] ) );
				$html .= sprintf( '<p class="mai-post-preview-desc">%s</p>', esc_html( $data['desc'] ) );
			$html .= '</div>';

			// If not for ajax.
			if ( ! $for_ajax ) {
				// Build link attributes.
				$attr = [
					'class' => [ 'mai-post-preview-link' ],
					'href'  => esc_url( (string) $data['url'] ),
					'rel'   => $this->args['rel'],
				];

				// If external link, add target _blank and rel noopener.
				if ( $data['host'] !== parse_url( home_url(), PHP_URL_HOST ) ) {
					$attr['target'] = '_blank';
					$attr['rel'][]  = 'noopener';
				}

				// Atts string.
				$attributes = '';

				// Loop through attr.
				foreach ( $attr as $key => $value ) {
					// Convert array to string.
					$value = is_array( $value ) ? implode( ' ', $value ) : $value;

					// Skip if empty string.
					if ( '' === $value ) {
						continue;
					}

					// If null, only add key.
					if ( is_null( $value ) ) {
						$attributes .= sprintf( ' %s', esc_attr( $key ) );
					}
					// Add key value.
					else {
						$attributes .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
					}
				}

				// Add link.
				$html .= sprintf( '<a%s><span class="screen-reader-text">%s</span></a>', $attributes, esc_html( $data['title'] ) );
			}

		// Close HTML.
		$html .= '</div>';

		return $html;
	}
}
