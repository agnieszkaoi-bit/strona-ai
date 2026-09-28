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

// Produkty na stronę sklepu/kategorii: 48 (12 rzędów × 4). Przy większym asortymencie wróć do 24.
add_filter(
	'loop_shop_per_page',
	function () {
		return 48; // ~50 produktów na start = prawie cały sklep na jednej stronie (mniej klikania).
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

/* -------------------------------------------------------------------------
 * Mini-koszyk: „Pasuje do tego” – upsell w momencie dodania do koszyka
 * Źródło: Sprzedaż krzyżowa (cross-sells) produktów z koszyka, bez tych już dodanych. Maks. 2.
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_after_mini_cart',
	function () {
		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}
		$in_cart = array();
		$ids     = array();
		foreach ( WC()->cart->get_cart() as $item ) {
			$in_cart[] = (int) $item['product_id'];
			$ids       = array_merge( $ids, $item['data']->get_cross_sell_ids() );
		}
		$ids = array_diff( array_unique( array_map( 'intval', $ids ) ), $in_cart );
		$out = '';
		$n   = 0;
		foreach ( $ids as $id ) {
			$p = wc_get_product( $id );
			if ( ! $p || ! $p->is_visible() || ! $p->is_in_stock() ) {
				continue;
			}
			$button = $p->is_type( 'simple' ) && $p->is_purchasable()
				? sprintf( '<a href="%1$s" data-quantity="1" data-product_id="%2$d" class="button add_to_cart_button ajax_add_to_cart" aria-label="%3$s" rel="nofollow"></a>', esc_url( $p->add_to_cart_url() ), $p->get_id(), esc_attr( sprintf( /* translators: %s: produkt */ __( 'Dodaj do koszyka: %s', 'bydopamina' ), $p->get_name() ) ) )
				: sprintf( '<a href="%1$s" class="button" aria-label="%2$s"></a>', esc_url( $p->get_permalink() ), esc_attr( sprintf( /* translators: %s: produkt */ __( 'Wybierz wariant: %s', 'bydopamina' ), $p->get_name() ) ) );
			$out   .= sprintf(
				'<li>%1$s<a class="bd-mc-upsell__name" href="%2$s">%3$s %4$s</a>%5$s</li>',
				$p->get_image( 'woocommerce_gallery_thumbnail', array( 'alt' => '' ) ),
				esc_url( $p->get_permalink() ),
				esc_html( $p->get_name() ),
				wp_kses_post( $p->get_price_html() ),
				$button // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapowane wyżej.
			);
			if ( ++$n >= 2 ) {
				break;
			}
		}
		if ( $out ) {
			echo '<div class="bd-mc-upsell"><p class="bd-label">' . esc_html__( 'Pasuje do tego', 'bydopamina' ) . '</p><ul>' . $out . '</ul></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
);

/* -------------------------------------------------------------------------
 * Karty produktów: dostępne kolory jako kropki pod ceną (produkty z wariantami)
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_after_shop_loop_item_title',
	function () {
		global $product;
		$taxonomy = 'pa_' . apply_filters( 'bydopamina_color_attribute', 'kolor' );
		if ( ! $product || ! $product->is_type( 'variable' ) || ! function_exists( 'bydopamina_swatch_color' ) ) {
			return;
		}
		$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
		if ( count( $terms ) < 2 ) {
			return;
		}
		$names = wp_list_pluck( $terms, 'name' );
		echo '<span class="bd-card-swatches" role="img" aria-label="' . esc_attr( sprintf( /* translators: %s: lista kolorów */ __( 'Kolory: %s', 'bydopamina' ), implode( ', ', $names ) ) ) . '">';
		foreach ( array_slice( $terms, 0, 5 ) as $term ) {
			echo '<i style="--sw:' . esc_attr( bydopamina_swatch_color( $term ) ) . '"></i>';
		}
		if ( count( $terms ) > 5 ) {
			echo '<span class="bd-mono">+' . (int) ( count( $terms ) - 5 ) . '</span>';
		}
		echo '</span>';
	},
	15
);

/* -------------------------------------------------------------------------
 * Wyszukiwarka rozumie kamienie, kolory i materiały
 * „naszyjnik z ametystem” → produkty z atrybutem Kamień = Ametyst, w tytule „naszyjnik”.
 * Dopasowanie po początku słowa (odmiana: ametyst/ametystem/ametystowy), bez polskich znaków.
 * ---------------------------------------------------------------------- */
/**
 * Czy słowo z wyszukiwarki to odmiana wartości atrybutu?
 * Wspólny początek ≥ długość sluga − 2 (min. 4): ametystem→ametyst, perłowy→perla, ceramiczne→ceramika, złote→zloty.
 * Albo niedokończone słowo, które jest początkiem sluga (kwarc → kwarc-rozowy).
 *
 * @param string $word Znormalizowane słowo (bez polskich znaków).
 * @param string $slug Slug wartości atrybutu.
 * @return bool
 */
function bydopamina_word_matches_slug( $word, $slug ) {
	if ( str_starts_with( $slug, $word ) ) {
		return true;
	}
	$base   = strtok( $slug, '-' ); // pierwszy człon: „kwarc-rozowy” → „kwarc”.
	$need   = max( 4, strlen( $base ) - 2 );
	$common = 0;
	$max    = min( strlen( $word ), strlen( $base ) );
	while ( $common < $max && $word[ $common ] === $base[ $common ] ) {
		++$common;
	}
	return $common >= $need;
}

add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}
		$search = (string) $query->get( 's' );
		if ( '' === trim( $search ) ) {
			return;
		}
		$attributes = apply_filters( 'bydopamina_search_attributes', array( 'kamien', 'kolor', 'material' ) );
		$words      = preg_split( '/\s+/u', trim( $search ) );
		$rest       = array();
		$tax_query  = array();
		$stopwords  = array( 'z', 'ze', 'w', 'i', 'na', 'do', 'dla', 'oraz' );

		foreach ( $words as $word ) {
			$norm = sanitize_title( remove_accents( $word ) );
			if ( in_array( $norm, $stopwords, true ) ) {
				continue;
			}
			$matched = false;
			if ( mb_strlen( $norm ) >= 4 ) {
				foreach ( $attributes as $attribute ) {
					$taxonomy = 'pa_' . $attribute;
					if ( ! taxonomy_exists( $taxonomy ) ) {
						continue;
					}
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => true,
							'fields'     => 'id=>slug',
						)
					);
					foreach ( (array) $terms as $term_id => $slug ) {
						if ( bydopamina_word_matches_slug( $norm, $slug ) ) {
							$tax_query[ $taxonomy ][] = (int) $term_id;
							$matched                  = true;
						}
					}
				}
			}
			if ( ! $matched ) {
				$rest[] = $word;
			}
		}
		if ( ! $tax_query ) {
			return;
		}
		$query->set( 'post_type', 'product' );
		$existing = (array) $query->get( 'tax_query' );
		foreach ( $tax_query as $taxonomy => $ids ) {
			$existing[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => array_unique( $ids ),
			);
		}
		$query->set( 'tax_query', $existing );
		$query->set( 's', implode( ' ', $rest ) );
		$query->set( 'bd_original_search', $search );
	},
	20
);

// Nagłówek wyników pokazuje to, co wpisał klient (a nie okrojoną frazę).
add_filter(
	'get_search_query',
	function ( $q ) {
		global $wp_query;
		$original = $wp_query ? $wp_query->get( 'bd_original_search' ) : '';
		return $original ? esc_attr( $original ) : $q;
	}
);

/* -------------------------------------------------------------------------
 * Karty produktów w stylu klasycznych sklepów jubilerskich:
 * NAZWA → podtytuł (materiał) → cena → pole promocji / oszczędności zestawu.
 * ---------------------------------------------------------------------- */

/**
 * Podtytuł produktu: pole własne `bd_subtitle` (np. „z perłą, stal złocona”), a gdy puste – wartości atrybutu Materiał.
 *
 * @param WC_Product $product Produkt.
 * @return string
 */
function bydopamina_product_subtitle( $product ) {
	$subtitle = (string) $product->get_meta( 'bd_subtitle' );
	if ( '' === $subtitle ) {
		$names    = wc_get_product_terms( $product->get_id(), 'pa_material', array( 'fields' => 'names' ) );
		$subtitle = $names ? mb_strtolower( implode( ', ', $names ) ) : '';
	}
	return $subtitle;
}

add_action(
	'woocommerce_shop_loop_item_title',
	function () {
		global $product;
		$subtitle = $product ? bydopamina_product_subtitle( $product ) : '';
		if ( $subtitle ) {
			echo '<p class="bd-card-sub">' . esc_html( $subtitle ) . '</p>';
		}
	},
	11
);

// Etykieta „Bestseller” – produkty z tagiem „bestseller” (ręczny wybór = pełna kontrola).
add_action(
	'woocommerce_before_shop_loop_item_title',
	function () {
		global $product;
		if ( $product && has_term( 'bestseller', 'product_tag', $product->get_id() ) && ! $product->is_on_sale() ) {
			echo '<span class="bd-badge bd-badge--light">' . esc_html__( 'Bestseller', 'bydopamina' ) . '</span>';
		}
	},
	8
);

/**
 * Po cenie:
 *  – zestaw (kategoria „zestawy”) w promocji: „Cena poza zestawem” + „Oszczędzasz X zł (Y%)”
 *    (cena regularna = suma produktów osobno, cena promocyjna = cena w zestawie – bez wtyczek);
 *  – produkt z tagiem „promocja”: pole z hasłem akcji (BYDOPAMINA_PROMO_LABEL).
 */
add_action(
	'woocommerce_after_shop_loop_item_title',
	function () {
		global $product;
		if ( ! $product ) {
			return;
		}
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
		if ( has_term( 'zestawy', 'product_cat', $product->get_id() ) && $product->is_on_sale() && $regular > 0 && $sale > 0 ) {
			$saved = $regular - $sale;
			printf(
				'<p class="bd-set-regular">%1$s <span>%2$s</span></p><span class="bd-save">%3$s</span>',
				esc_html__( 'Cena produktów poza zestawem:', 'bydopamina' ),
				wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $regular ) ) ) ),
				/* translators: 1: kwota, 2: procent */
				esc_html( sprintf( __( 'Oszczędzasz %1$s (%2$d%%)', 'bydopamina' ), wp_strip_all_tags( wc_price( $saved ) ), round( $saved / $regular * 100 ) ) )
			);
			return;
		}
		if ( has_term( 'promocja', 'product_tag', $product->get_id() ) && BYDOPAMINA_PROMO_LABEL ) {
			echo '<span class="bd-promo-tag">' . esc_html( BYDOPAMINA_PROMO_LABEL ) . '</span>';
		}
	},
	20
);

// W zestawach cena z dopiskiem „Cena w zestawie:”.
add_filter(
	'woocommerce_get_price_html',
	function ( $html, $product ) {
		if ( is_admin() || ! $product->is_on_sale() || ! has_term( 'zestawy', 'product_cat', $product->get_id() ) ) {
			return $html;
		}
		return '<span class="bd-set-label">' . esc_html__( 'Cena w zestawie:', 'bydopamina' ) . '</span> ' . wc_price( wc_get_price_to_display( $product ) );
	},
	20,
	2
);
