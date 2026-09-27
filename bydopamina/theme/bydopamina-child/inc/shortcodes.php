<?php
/**
 * Krótkie kody używane w szablonach Elementora (widget „Shortcode”).
 *
 *  [bd_free_shipping_bar]  pasek postępu do darmowej dostawy (koszyk, mini-koszyk, karta produktu)
 *  [bd_delivery_eta]       „Zamów w ciągu X h – wyślemy dziś”
 *  [bd_trust_badges]       4 argumenty zaufania (dostawa, zwroty, płatności, bezpieczeństwo)
 *  [bd_category_chips]     przewijane „chipsy” kategorii (strona sklepu, mobile)
 *  [bd_mobile_nav]         dolny pasek nawigacji na telefonach (strefa kciuka)
 *  [bd_product_reviews]   opinie produktu (akordeon na karcie produktu)
 *  [bd_year]               bieżący rok (stopka)
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ikony SVG inline (bez zewnętrznych bibliotek – szybciej i zgodnie z CSP).
 *
 * @param string $name Nazwa ikony.
 * @return string
 */
function bydopamina_icon( $name ) {
	$paths = array(
		'truck'  => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/>',
		'return' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
		'card'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
		'shield' => '<path d="M12 3 5 6v6c0 4.2 3 7.7 7 9 4-1.3 7-4.8 7-9V6z"/><path d="m9 12 2 2 4-4"/>',
		'home'   => '<path d="m3 11 9-7 9 7"/><path d="M5 10v10h14V10"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'grid'   => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
		'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'bag'    => '<path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="bd-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

add_shortcode(
	'bd_free_shipping_bar',
	function () {
		return function_exists( 'bydopamina_free_shipping_bar_html' ) ? bydopamina_free_shipping_bar_html() : '';
	}
);

add_shortcode(
	'bd_delivery_eta',
	function () {
		return function_exists( 'bydopamina_delivery_eta_html' ) ? bydopamina_delivery_eta_html() : '';
	}
);

add_shortcode(
	'bd_trust_badges',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'variant' => 'row' ), $atts, 'bd_trust_badges' );
		$items = array(
			/* translators: %d: próg darmowej dostawy w zł */
			array( 'truck', sprintf( __( 'Darmowa dostawa od %d zł', 'bydopamina' ), BYDOPAMINA_FREE_SHIPPING_FROM ), __( 'InPost Paczkomat, kurier', 'bydopamina' ) ),
			/* translators: %d: liczba dni na zwrot */
			array( 'return', sprintf( __( '%d dni na zwrot', 'bydopamina' ), BYDOPAMINA_RETURN_DAYS ), __( 'Bez podawania przyczyny', 'bydopamina' ) ),
			array( 'card', __( 'BLIK, karta, Apple Pay', 'bydopamina' ), __( 'Też PayPo – płać za 30 dni', 'bydopamina' ) ),
			array( 'shield', __( 'Bezpieczne zakupy', 'bydopamina' ), __( 'Szyfrowane połączenie SSL', 'bydopamina' ) ),
		);
		$html = '<ul class="bd-trust bd-trust--' . esc_attr( $atts['variant'] ) . '">';
		foreach ( $items as $item ) {
			$html .= '<li class="bd-trust__item">' . bydopamina_icon( $item[0] ) . '<span><strong>' . esc_html( $item[1] ) . '</strong><small>' . esc_html( $item[2] ) . '</small></span></li>';
		}
		return $html . '</ul>';
	}
);

add_shortcode(
	'bd_category_chips',
	function () {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		$current = is_product_category() ? get_queried_object_id() : 0;
		$shop    = wc_get_page_permalink( 'shop' );
		$html    = '<nav class="bd-chips" aria-label="' . esc_attr__( 'Kategorie', 'bydopamina' ) . '"><ul>';
		$html   .= '<li><a href="' . esc_url( $shop ) . '"' . ( is_shop() ? ' aria-current="page"' : '' ) . '>' . esc_html__( 'Wszystko', 'bydopamina' ) . '</a></li>';
		foreach ( $terms as $term ) {
			$html .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '"' . ( $current === $term->term_id ? ' aria-current="page"' : '' ) . '>' . esc_html( $term->name ) . '</a></li>';
		}
		return $html . '</ul></nav>';
	}
);

add_shortcode(
	'bd_mobile_nav',
	function () {
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return '';
		}
		$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$links = array(
			array( home_url( '/' ), 'home', __( 'Start', 'bydopamina' ), is_front_page() ),
			array( wc_get_page_permalink( 'shop' ), 'grid', __( 'Sklep', 'bydopamina' ), is_shop() || is_product_taxonomy() ),
			array( home_url( '/?s=&post_type=product' ), 'search', __( 'Szukaj', 'bydopamina' ), is_search() ),
			array( wc_get_page_permalink( 'myaccount' ), 'user', __( 'Konto', 'bydopamina' ), is_account_page() ),
		);
		$html = '<nav class="bd-mnav" aria-label="' . esc_attr__( 'Nawigacja mobilna', 'bydopamina' ) . '"><ul>';
		foreach ( $links as $l ) {
			$attr  = 'search' === $l[1] ? ' data-bd-open-search' : '';
			$html .= '<li><a href="' . esc_url( $l[0] ) . '"' . $attr . ( $l[3] ? ' aria-current="page"' : '' ) . '>' . bydopamina_icon( $l[1] ) . '<span>' . esc_html( $l[2] ) . '</span></a></li>';
		}
		$html .= '<li><a href="' . esc_url( wc_get_cart_url() ) . '"' . ( is_cart() ? ' aria-current="page"' : '' ) . '>' . bydopamina_icon( 'bag' ) . '<span>' . esc_html__( 'Koszyk', 'bydopamina' ) . '</span><span class="bd-mnav__count" data-count="' . (int) $count . '">' . (int) $count . '</span></a></li>';
		return $html . '</ul></nav>';
	}
);

// Licznik w dolnym pasku odświeżany razem z mini-koszykiem.
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.bd-mnav__count'] = '<span class="bd-mnav__count" data-count="' . (int) WC()->cart->get_cart_contents_count() . '">' . (int) WC()->cart->get_cart_contents_count() . '</span>';
		return $fragments;
	}
);

add_shortcode(
	'bd_year',
	function () {
		return esc_html( wp_date( 'Y' ) );
	}
);

// Opinie produktu (lista + formularz) – do akordeonu na karcie produktu.
add_shortcode(
	'bd_product_reviews',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ! comments_open() && ! get_comments_number() ) {
			return '';
		}
		ob_start();
		comments_template();
		return ob_get_clean();
	}
);
