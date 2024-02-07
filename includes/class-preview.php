<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Preview class wrapper.
 *
 * @uses https://github.com/oscarotero/Embed
 */
class Mai_Post_Preview {
	protected $url;
	protected $is_preview;

	/**
	 * Constructs the class.
	 */
	function __construct( $url, $is_preview = false ) {
		$this->url        = maipp_sanitize_url( $url ?: 'https://bizbudding.com/mai-theme/' );
		$this->is_preview = $is_preview; // If in editor.
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
		if ( $this->is_preview ) {
			$class    .= ' mai-post-preview-editor';
			$data      = maipp_get_data( $this->url );
		}
		// Front end only checks transient, if no data we'll prepare for ajax.
		else {
			$transient = maipp_get_key( $this->url );
			$data      = get_transient( $transient );
		}

		// Check if this is for ajax.
		$for_ajax = ! $data;

		// Enqueue styles.
		wp_enqueue_style( 'mai-post-previews' );

		if ( $for_ajax ) {
			$class .= $for_ajax ? ' mai-post-preview-ajax' : '';

			// Enqueue styles/scripts for ajax.
			wp_enqueue_style( 'mai-post-previews-loading' );
			wp_enqueue_script( 'mai-post-previews' );
		}

		// Build HTML.
		$html  = '';

		// If for ajax.
		if ( $for_ajax ) {
			$data          = [];
			$data['url']   = $this->url;
			$data['image'] = '&nbsp;';
			$data['host']  = '&nbsp;';
			$data['title'] = '&nbsp;';
			$data['desc']  = '&nbsp;';

			$html .= sprintf( '<div class="%s" data-url="%s">', $class, $data['url'] );
			$html .= sprintf( '<div class="mai-post-preview-figure"></div>', esc_url( (string) $data['image'] ) );
		}
		// No ajax.
		else {
			$html .= sprintf( '<div class="%s">', $class );
			$html .= sprintf( '<figure class="mai-post-preview-figure"><img class="mai-post-preview-image" src="%s" alt="%s" width="266" height="354" /></figure>', esc_url( (string) $data['image'] ), esc_attr( $data['title'] ) );
		}

			// Build inner HTML.
			$html .= '<div class="mai-post-preview-inner">';
				$html .= sprintf( '<p class="mai-post-preview-domain">%s</p>', esc_html( ltrim( (string) $data['host'], 'www.' ) ) );
				$html .= sprintf( '<h3 class="mai-post-preview-title">%s</h3>', esc_html( $data['title'] ) );
				$html .= sprintf( '<p class="mai-post-preview-desc">%s</p>', esc_html( $data['desc'] ) );
			$html .= '</div>';

			// If not for ajax.
			if ( ! $for_ajax ) {
				// Open in new tab if not the same host.
				$target = $data['host'] !== parse_url( home_url(), PHP_URL_HOST ) ? ' target="_blank"' : '';

				// Add link.
				$html .= sprintf( '<a rel="noopener noreferrer" class="mai-post-preview-link" href="%s"%s><span class="screen-reader-text">%s</span></a>', esc_url( (string) $data['url'] ), esc_attr( $target ), esc_html( $data['title'] ) );
			}
		$html .= '</div>';

		return $html;
	}
}
