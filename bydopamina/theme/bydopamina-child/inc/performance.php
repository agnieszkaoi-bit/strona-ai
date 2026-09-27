<?php
/**
 * Wydajność frontu (LCP < 2,5 s, INP < 200 ms, CLS < 0,1).
 * Cache stron, CDN i optymalizację obrazów załatwia serwer/wtyczka – patrz README.
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

// Emoji: skrypt + style ładowane na każdej stronie bez potrzeby.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

// Linki do feedów komentarzy i oEmbed discovery – zbędne w sklepie.
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// Style bloków Gutenberga nie są potrzebne na stronach budowanych w Elementorze.
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_singular( 'post' ) ) {
			return; // Blog może korzystać z bloków.
		}
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );
	},
	100
);

// Heartbeat rzadziej w panelu, wyłączony na froncie (mniej obciążenia admin-ajax).
add_filter(
	'heartbeat_settings',
	function ( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}
);
add_action(
	'init',
	function () {
		if ( ! is_admin() ) {
			wp_deregister_script( 'heartbeat' );
		}
	},
	1
);

// Preconnect do bramek płatności tylko na checkoutcie.
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation && function_exists( 'is_checkout' ) && is_checkout() ) {
			$urls[] = 'https://secure.przelewy24.pl';
			$urls[] = 'https://geowidget.inpost.pl';
		}
		return $urls;
	},
	10,
	2
);

// Obraz LCP (pierwszy obraz hero oznaczony klasą bd-lcp) ładowany priorytetowo.
add_filter(
	'wp_get_attachment_image_attributes',
	function ( $attr ) {
		if ( isset( $attr['class'] ) && str_contains( $attr['class'], 'bd-lcp' ) ) {
			$attr['fetchpriority'] = 'high';
			$attr['loading']       = 'eager';
			$attr['decoding']      = 'async';
		}
		return $attr;
	}
);
