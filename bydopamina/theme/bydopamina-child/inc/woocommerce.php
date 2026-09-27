<?php
/**
 * UX sklepu WooCommerce.
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Pasek darmowej dostawy – liczony z koszyka, odświeżany przez fragmenty AJAX
 * ---------------------------------------------------------------------- */

/**
 * HTML paska postępu do darmowej dostawy.
 *
 * @return string
 */
function bydopamina_free_shipping_bar_html() {
	$threshold = (float) BYDOPAMINA_FREE_SHIPPING_FROM;
	$total     = 0.0;
	if ( WC()->cart ) {
		$total = (float) WC()->cart->get_displayed_subtotal();
		if ( WC()->cart->display_prices_including_tax() ) {
			$total -= (float) WC()->cart->get_discount_tax();
		}
		$total -= (float) WC()->cart->get_discount_total();
	}
	$missing = max( 0, $threshold - $total );
	$percent = $threshold > 0 ? min( 100, round( $total / $threshold * 100 ) ) : 100;

	if ( $missing > 0 ) {
		/* translators: %s: kwota brakująca do darmowej dostawy */
		$label = sprintf( __( 'Brakuje Ci %s do darmowej dostawy', 'bydopamina' ), wp_strip_all_tags( wc_price( $missing ) ) );
	} else {
		$label = __( 'Masz darmową dostawę', 'bydopamina' );
	}

	return sprintf(
		'<div class="bd-freeship" role="status" aria-live="polite"><p class="bd-freeship__label">%1$s</p><div class="bd-freeship__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%2$d" aria-label="%3$s"><span class="bd-freeship__fill" style="width:%2$d%%"></span></div></div>',
		esc_html( $label ),
		(int) $percent,
		esc_attr__( 'Postęp do darmowej dostawy', 'bydopamina' )
	);
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['div.bd-freeship'] = bydopamina_free_shipping_bar_html();
		return $fragments;
	}
);

// Pasek także w mini-koszyku (off-canvas Elementora) – nad listą produktów.
add_action(
	'woocommerce_before_mini_cart',
	function () {
		echo bydopamina_free_shipping_bar_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapowane w funkcji.
	}
);

/* -------------------------------------------------------------------------
 * Karta produktu
 * ---------------------------------------------------------------------- */

/**
 * Szacowany czas wysyłki: „Zamów w ciągu 2 h 15 min – wyślemy dziś”.
 * Uwzględnia weekend; święta dodaj w filtrze bydopamina_holidays (Y-m-d).
 *
 * @return string
 */
function bydopamina_delivery_eta_html() {
	$tz       = wp_timezone();
	$now      = new DateTimeImmutable( 'now', $tz );
	$holidays = apply_filters( 'bydopamina_holidays', array() );
	list( $h, $m ) = array_map( 'intval', explode( ':', BYDOPAMINA_SHIPPING_CUTOFF ) );

	$is_workday = static function ( DateTimeImmutable $d ) use ( $holidays ) {
		return (int) $d->format( 'N' ) < 6 && ! in_array( $d->format( 'Y-m-d' ), $holidays, true );
	};

	$cutoff = $now->setTime( $h, $m );
	if ( $is_workday( $now ) && $now < $cutoff ) {
		$diff = $now->diff( $cutoff );
		/* translators: 1: godziny, 2: minuty */
		$text = sprintf( __( 'Zamów w ciągu %1$d h %2$d min – wyślemy dziś', 'bydopamina' ), $diff->h, $diff->i );
	} else {
		$ship = $now->modify( '+1 day' );
		while ( ! $is_workday( $ship ) ) {
			$ship = $ship->modify( '+1 day' );
		}
		/* translators: %s: data wysyłki */
		$text = sprintf( __( 'Wysyłka %s', 'bydopamina' ), wp_date( 'l, j F', $ship->getTimestamp() ) );
	}

	return '<p class="bd-eta"><span class="bd-eta__dot" aria-hidden="true"></span>' . esc_html( $text ) . '</p>';
}

/**
 * Mobilny sticky pasek „Dodaj do koszyka” – pokazywany przez JS, gdy główny przycisk zniknie z ekranu.
 */
add_action(
	'wp_footer',
	function () {
		if ( ! is_product() ) {
			return;
		}
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
			return;
		}
		printf(
			'<div class="bd-sticky-atc" hidden><div class="bd-sticky-atc__info"><span class="bd-sticky-atc__title">%1$s</span><span class="bd-sticky-atc__price">%2$s</span></div><button type="button" class="bd-btn bd-btn--primary bd-sticky-atc__btn" data-bd-scroll-atc>%3$s</button></div>',
			esc_html( $product->get_name() ),
			wp_kses_post( $product->get_price_html() ),
			$product->is_type( 'simple' ) ? esc_html__( 'Dodaj do koszyka', 'bydopamina' ) : esc_html__( 'Wybierz wariant', 'bydopamina' )
		);
	}
);

/* -------------------------------------------------------------------------
 * Checkout: mniej pól = wyższa konwersja
 * ---------------------------------------------------------------------- */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		// Firma i drugi wiersz adresu rzadko potrzebne – opcjonalne i na końcu.
		if ( isset( $fields['billing']['billing_company'] ) ) {
			$fields['billing']['billing_company']['required'] = false;
			$fields['billing']['billing_company']['priority'] = 120;
		}
		unset( $fields['billing']['billing_address_2'], $fields['shipping']['shipping_address_2'] );

		// Poprawne podpowiedzi klawiatury mobilnej i autouzupełnianie.
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['custom_attributes']['inputmode'] = 'tel';
		}
		if ( isset( $fields['billing']['billing_postcode'] ) ) {
			$fields['billing']['billing_postcode']['custom_attributes']['inputmode'] = 'numeric';
			$fields['billing']['billing_postcode']['placeholder']                    = '00-000';
		}
		if ( isset( $fields['billing']['billing_email'] ) ) {
			$fields['billing']['billing_email']['priority'] = 5; // E-mail na początku – odzyskiwanie porzuconych koszyków.
		}
		return $fields;
	}
);

// Nazwa przycisku zamówienia zgodna z art. 17 ustawy o prawach konsumenta.
add_filter(
	'woocommerce_order_button_text',
	function () {
		return __( 'Zamawiam i płacę', 'bydopamina' );
	}
);

// Karty produktów: przycisk „Dodaj” bez przeładowania strony (spójny z mini-koszykiem off-canvas).
add_filter( 'woocommerce_product_add_to_cart_text', 'bydopamina_loop_atc_text', 10, 2 );

/**
 * Krótszy tekst przycisku na kartach produktów.
 *
 * @param string     $text    Domyślny tekst.
 * @param WC_Product $product Produkt.
 * @return string
 */
function bydopamina_loop_atc_text( $text, $product ) {
	if ( is_product() ) {
		return $text;
	}
	return $product->is_type( 'simple' ) && $product->is_in_stock() ? __( 'Dodaj', 'bydopamina' ) : __( 'Zobacz', 'bydopamina' );
}

// Znaczek „-20%” zamiast „Promocja!”.
add_filter(
	'woocommerce_sale_flash',
	function ( $html, $post, $product ) {
		if ( ! $product->is_type( 'simple' ) || ! $product->get_regular_price() ) {
			return '<span class="onsale">' . esc_html__( 'Promocja', 'bydopamina' ) . '</span>';
		}
		$pct = round( 100 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() * 100 ) );
		return '<span class="onsale">-' . (int) $pct . '%</span>';
	},
	10,
	3
);

// Produkty na stronę kategorii: 24 (6 rzędów × 4 / 12 × 2 na mobile).
add_filter(
	'loop_shop_per_page',
	function () {
		return 24;
	}
);

// Wyszukiwarka w sklepie zwraca produkty (a nie wpisy/strony).
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() && ! $query->get( 'post_type' ) ) {
			$query->set( 'post_type', 'product' );
		}
	}
);

/* -------------------------------------------------------------------------
 * Karty produktów: drugie zdjęcie (na modelce) po najechaniu + etykieta „Nowość”
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_before_shop_loop_item_title',
	function () {
		global $product;
		$created = $product ? $product->get_date_created() : null;
		if ( $created && ( time() - $created->getTimestamp() ) < 30 * DAY_IN_SECONDS && ! $product->is_on_sale() ) {
			echo '<span class="bd-badge">' . esc_html__( 'Nowość', 'bydopamina' ) . '</span>';
		}
		if ( $product && ! $product->is_in_stock() ) {
			echo '<span class="bd-badge bd-badge--muted">' . esc_html__( 'Wyprzedane', 'bydopamina' ) . '</span>';
		}
	},
	9
);

add_action(
	'woocommerce_before_shop_loop_item_title',
	function () {
		global $product;
		$ids = $product ? $product->get_gallery_image_ids() : array();
		if ( $ids ) {
			echo wp_get_attachment_image( $ids[0], 'woocommerce_thumbnail', false, array( 'class' => 'bd-card__alt', 'loading' => 'lazy', 'alt' => '', 'aria-hidden' => 'true' ) );
		}
	},
	11
);

/* -------------------------------------------------------------------------
 * Pakowanie na prezent (klasyczny checkout / widget Elementora)
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_review_order_before_payment',
	function () {
		$checked = WC()->session && WC()->session->get( 'bd_giftwrap' );
		printf(
			'<div class="bd-giftwrap"><label><input type="checkbox" name="bd_giftwrap" value="1"%1$s> <span><strong>%2$s</strong> <span class="bd-mono">+%3$s</span><small>%4$s</small></span></label></div>',
			checked( $checked, true, false ),
			esc_html__( 'Zapakuj na prezent', 'bydopamina' ),
			wp_kses_post( wc_price( BYDOPAMINA_GIFTWRAP_PRICE ) ),
			esc_html__( 'Papier, wstążka i liścik z Twoimi słowami. Bez paragonu w paczce.', 'bydopamina' )
		);
	}
);

// Checkout odświeża podsumowanie przez AJAX – zapisujemy wybór w sesji.
add_action(
	'woocommerce_checkout_update_order_review',
	function ( $post_data ) {
		parse_str( (string) $post_data, $data );
		WC()->session->set( 'bd_giftwrap', ! empty( $data['bd_giftwrap'] ) );
	}
);
add_action(
	'woocommerce_checkout_process',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce weryfikuje nonce checkoutu.
		WC()->session->set( 'bd_giftwrap', ! empty( $_POST['bd_giftwrap'] ) );
	},
	1
);
add_action(
	'woocommerce_cart_calculate_fees',
	function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		if ( WC()->session && WC()->session->get( 'bd_giftwrap' ) ) {
			$cart->add_fee( __( 'Pakowanie na prezent', 'bydopamina' ), (float) BYDOPAMINA_GIFTWRAP_PRICE, true );
		}
	}
);
add_action(
	'woocommerce_checkout_create_order',
	function ( $order ) {
		if ( WC()->session && WC()->session->get( 'bd_giftwrap' ) ) {
			$order->update_meta_data( '_bd_giftwrap', 'yes' );
			WC()->session->set( 'bd_giftwrap', false );
		}
	}
);
