<?php
/**
 * bydopamina Child – bootstrap motywu.
 *
 * Moduły:
 *  - inc/security.php     utwardzenie WordPressa i WooCommerce
 *  - inc/performance.php  odchudzenie frontu (Core Web Vitals)
 *  - inc/woocommerce.php  UX sklepu: pasek darmowej dostawy, ETA wysyłki, sticky add-to-cart
 *  - inc/shortcodes.php   krótkie kody używane w szablonach Elementora
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

define( 'BYDOPAMINA_VERSION', '1.0.0' );
define( 'BYDOPAMINA_DIR', get_stylesheet_directory() );
define( 'BYDOPAMINA_URI', get_stylesheet_directory_uri() );

/*
 * Ustawienia sklepu – zmień tutaj, a nie w szablonach.
 * Każdą wartość można nadpisać w wp-config.php (define przed załadowaniem motywu).
 */
if ( ! defined( 'BYDOPAMINA_FREE_SHIPPING_FROM' ) ) {
	define( 'BYDOPAMINA_FREE_SHIPPING_FROM', 199 );   // PLN – próg darmowej dostawy.
}
if ( ! defined( 'BYDOPAMINA_SHIPPING_CUTOFF' ) ) {
	define( 'BYDOPAMINA_SHIPPING_CUTOFF', '14:00' );  // Zamówienia do tej godziny wysyłamy tego samego dnia roboczego.
}
if ( ! defined( 'BYDOPAMINA_RETURN_DAYS' ) ) {
	define( 'BYDOPAMINA_RETURN_DAYS', 30 );
}
if ( ! defined( 'BYDOPAMINA_CSP_ENFORCE' ) ) {
	define( 'BYDOPAMINA_CSP_ENFORCE', false );        // false = Content-Security-Policy-Report-Only (bezpieczny start).
}

if ( ! defined( 'BYDOPAMINA_SEND_HEADERS' ) ) {
	define( 'BYDOPAMINA_SEND_HEADERS', true );        // false, gdy nagłówki bezpieczeństwa ustawia serwer (server/.htaccess, nginx).
}

require_once BYDOPAMINA_DIR . '/inc/security.php';
require_once BYDOPAMINA_DIR . '/inc/performance.php';
require_once BYDOPAMINA_DIR . '/inc/shortcodes.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once BYDOPAMINA_DIR . '/inc/woocommerce.php';
}

/**
 * Style i skrypty motywu.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'bydopamina-tokens',
			BYDOPAMINA_URI . '/assets/css/tokens.css',
			array(),
			BYDOPAMINA_VERSION
		);
		wp_enqueue_style(
			'bydopamina-main',
			BYDOPAMINA_URI . '/assets/css/main.css',
			array( 'bydopamina-tokens' ),
			BYDOPAMINA_VERSION
		);
		wp_enqueue_script(
			'bydopamina-main',
			BYDOPAMINA_URI . '/assets/js/main.js',
			array(),
			BYDOPAMINA_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	},
	20
);

/**
 * Link „Przejdź do treści” dla klawiatury i czytników ekranu (WCAG 2.4.1).
 * Hello Elementor ma własny skip-link, ale wskazuje #content – dodajemy kotwicę do <main>.
 */
add_action(
	'wp_body_open',
	function () {
		echo '<a class="bd-skip-link" href="#bd-main">' . esc_html__( 'Przejdź do treści', 'bydopamina' ) . '</a>';
	},
	1
);

/**
 * Obsługa motywu.
 */
add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'bydopamina', BYDOPAMINA_DIR . '/languages' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'script', 'style' ) );
		add_theme_support( 'responsive-embeds' );

		// Rozmiary obrazów pod siatkę 4:5 (trend fashion/lifestyle, lepsze wykorzystanie ekranu mobile).
		add_image_size( 'bd-card', 600, 750, true );
		add_image_size( 'bd-card-2x', 1200, 1500, true );
	}
);

/**
 * Kolor paska przeglądarki na mobile.
 */
add_action(
	'wp_head',
	function () {
		echo '<meta name="theme-color" content="#FAF8F5" media="(prefers-color-scheme: light)">' . "\n";
		echo '<meta name="theme-color" content="#121212" media="(prefers-color-scheme: dark)">' . "\n";
	},
	1
);
