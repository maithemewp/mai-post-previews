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

		if ( $for_ajax ) {
			$data['url']   = $this->url;
			$data['image'] = '&nbsp;';
			$data['host']  = '&nbsp;';
			$data['title'] = '&nbsp;';
			$data['desc']  = '&nbsp;';

			$html .= sprintf( '<div class="%s" data-url="%s">', $class, $data['url'] );
			$html .= sprintf( '<div class="mai-post-preview-figure"></div>', esc_url( $data['image'] ) );
		} else {
			$html .= sprintf( '<div class="%s">', $class );
			$html .= sprintf( '<figure class="mai-post-preview-figure"><img class="mai-post-preview-image" src="%s" alt="%s" width="266" height="354" /></figure>', esc_url( $data['image'] ), esc_attr( $data['title'] ) );
		}

			$html .= '<div class="mai-post-preview-inner">';
				$html .= sprintf( '<p class="mai-post-preview-domain">%s</p>', esc_html( ltrim( $data['host'], 'www.' ) ) );
				$html .= sprintf( '<h2 class="mai-post-preview-title">%s</h2>', esc_html( $data['title'] ) );
				$html .= sprintf( '<p class="mai-post-preview-desc">%s</p>', esc_html( $data['desc'] ) );
			$html .= '</div>';

			if ( ! $for_ajax ) {
				$html .= sprintf( '<a target="_blank" rel="noopener noreferrer" class="mai-post-preview-link" href="%s"><span class="screen-reader-text">%s</span></a>', esc_url( $data['url'] ), esc_html( $data['title'] ) );
			}
		$html .= '</div>';

		return $html;
	}
}
