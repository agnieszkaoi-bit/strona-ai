<?php
/**
 * Krótkie kody używane w szablonach Elementora (widget „Shortcode”).
 *
 *  [bd_usp variant="row|list"]            4 cechy materiału: 18K złoto na stali 316L, hipoalergiczna, wodoodporna, nie ciemnieje
 *  [bd_trust_badges]                      dostawa, zwroty, pudełko prezentowe, płatności
 *  [bd_category_arches]                   kategorie w łukach (przewijane na mobile)
 *  [bd_product_tabs limit="8"]            zakładki: Bestsellery / Nowości / Promocje
 *  [bd_shop_the_look image="ID" products="ID:x:y,ID:x:y"]   zdjęcie z punktami produktów (x, y w %)
 *  [bd_reviews limit="10"]                prawdziwe opinie z WooCommerce (4–5★, zweryfikowane zakupy)
 *  [bd_gift_finder]                       prezent wg budżetu
 *  [bd_size_guide]                        przycisk + okno rozmiarówki (pierścionki, długości łańcuszków)
 *  [bd_product_claims]                    etykiety mono na karcie produktu
 *  [bd_rating_summary]                    średnia ocena i liczba opinii (z WooCommerce)
 *  [bd_shop_by_color attribute="kolor"]   próbki kolorów (atrybut pa_kolor) → sklep przefiltrowany po kolorze
 *  [bd_shop_by_material attribute="material"]  chipsy materiałów (pa_material): stal, ceramika, perły, muszle…
 *  [bd_shop_by_stone attribute="kamien"]  próbki kamieni (pa_kamien) + wybór kamienia urodzinowego
 *  [bd_shop_by]                           przełącznik Kolor / Kamień / Materiał w jednej sekcji
 *  [bd_free_shipping_bar] [bd_delivery_eta] [bd_category_chips] [bd_mobile_nav] [bd_product_reviews] [bd_year]
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ikony SVG inline (bez bibliotek – szybciej i zgodnie z CSP). Linia 1.25 – delikatnie, jak grawer.
 *
 * @param string $name Nazwa ikony.
 * @return string
 */
function bydopamina_icon( $name ) {
	$paths = array(
		'truck'  => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="1.6"/><circle cx="17.5" cy="18" r="1.6"/>',
		'return' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
		'card'   => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="M3 10h18M7 15h3"/>',
		'gift'   => '<rect x="3.5" y="9" width="17" height="11.5" rx="1"/><path d="M2.5 9h19M12 9v11.5M12 9c-1.5-3-5-4-5.5-2S9 9 12 9zm0 0c1.5-3 5-4 5.5-2S15 9 12 9z"/>',
		'drop'   => '<path d="M12 3s6 6.6 6 11a6 6 0 0 1-12 0c0-4.4 6-11 6-11z"/>',
		'leaf'   => '<path d="M5 19c0-8 5-13 14-14-1 9-6 14-14 14z"/><path d="M5 19 13 11"/>',
		'spark'  => '<path d="M12 3v5M12 16v5M3 12h5M16 12h5M6 6l3 3M15 15l3 3M18 6l-3 3M9 15l-3 3"/>',
		'ring'   => '<circle cx="12" cy="14" r="6.5"/><path d="m9.5 5 2.5-2.5L14.5 5 12 7.5z"/>',
		'home'   => '<path d="m3 11 9-7 9 7"/><path d="M5 10v10h14V10"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'grid'   => '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/>',
		'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'bag'    => '<path d="M5 8h14l-1 13H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
		'heart'  => '<path d="M12 20s-7.5-4.6-7.5-10A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 7.5 3c0 5.4-7.5 10-7.5 10z"/>',
		'plus'   => '<path d="M12 5v14M5 12h14"/>',
		'close'  => '<path d="M6 6l12 12M18 6 6 18"/>',
		'ruler'  => '<rect x="2.5" y="8" width="19" height="8" rx="1"/><path d="M6 8v3M9.5 8v4M13 8v3M16.5 8v4"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="bd-icon bd-icon--' . esc_attr( $name ) . '" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Lista „ikona + tytuł + opis”.
 *
 * @param array  $items   [ ikona, tytuł, opis ].
 * @param string $variant Klasa wariantu.
 * @param string $label   Etykieta listy dla czytników.
 * @return string
 */
function bydopamina_feature_list( $items, $variant, $label ) {
	$html = '<ul class="bd-features bd-features--' . esc_attr( $variant ) . '" aria-label="' . esc_attr( $label ) . '">';
	foreach ( $items as $item ) {
		$html .= '<li>' . bydopamina_icon( $item[0] ) . '<span><strong>' . esc_html( $item[1] ) . '</strong>' . ( $item[2] ? '<small>' . esc_html( $item[2] ) . '</small>' : '' ) . '</span></li>';
	}
	return $html . '</ul>';
}

add_shortcode(
	'bd_usp',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'variant' => 'row' ), $atts, 'bd_usp' );
		$items = apply_filters(
			'bydopamina_usp',
			array(
				array( 'ring', __( 'Stal 316L i złoto 18K', 'bydopamina' ), __( 'Nie ciemnieje, nie rdzewieje', 'bydopamina' ) ),
				array( 'leaf', __( 'Hipoalergiczna', 'bydopamina' ), __( 'Bez niklu i ołowiu', 'bydopamina' ) ),
				array( 'drop', __( 'Wodoodporna', 'bydopamina' ), __( 'Prysznic, basen, siłownia', 'bydopamina' ) ),
				array( 'spark', __( 'Kolory na każdy sezon', 'bydopamina' ), __( 'Ceramika, perły, muszle, emalia', 'bydopamina' ) ),
			)
		);
		return bydopamina_feature_list( $items, $atts['variant'], __( 'Cechy biżuterii', 'bydopamina' ) );
	}
);

add_shortcode(
	'bd_trust_badges',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'variant' => 'row' ), $atts, 'bd_trust_badges' );
		$items = array(
			/* translators: %d: próg darmowej dostawy w zł */
			array( 'truck', sprintf( __( 'Darmowa dostawa od %d zł', 'bydopamina' ), BYDOPAMINA_FREE_SHIPPING_FROM ), __( 'InPost i kurier, wysyłka w 24 h', 'bydopamina' ) ),
			/* translators: %d: liczba dni na zwrot */
			array( 'return', sprintf( __( '%d dni na zwrot', 'bydopamina' ), BYDOPAMINA_RETURN_DAYS ), __( 'Bez podawania przyczyny', 'bydopamina' ) ),
			array( 'gift', __( 'Pudełko w cenie', 'bydopamina' ), __( 'Gotowe do wręczenia', 'bydopamina' ) ),
			array( 'card', __( 'BLIK, Apple Pay, PayPo', 'bydopamina' ), __( 'Bezpieczne płatności', 'bydopamina' ) ),
		);
		return bydopamina_feature_list( $items, $atts['variant'], __( 'Dlaczego u nas', 'bydopamina' ) );
	}
);

add_shortcode(
	'bd_category_arches',
	function ( $atts ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		$atts  = shortcode_atts( array( 'limit' => 6 ), $atts, 'bd_category_arches' );
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => (int) $atts['limit'],
				'orderby'    => 'menu_order', // Kolejność z Produkty → Kategorie (przeciągnij).
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		$html = '<nav class="bd-arches" aria-label="' . esc_attr__( 'Kategorie biżuterii', 'bydopamina' ) . '"><ul>';
		foreach ( array_values( $terms ) as $i => $term ) {
			$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
			$img   = $thumb ? wp_get_attachment_image( $thumb, 'bd-card', false, array( 'loading' => 'lazy', 'alt' => '' ) ) : '<span class="bd-arches__ph"></span>';
			$html .= sprintf(
				'<li><a href="%1$s"><span class="bd-arches__img">%2$s</span><span class="bd-arches__name"><span class="bd-mono">%3$02d</span>%4$s</span></a></li>',
				esc_url( get_term_link( $term ) ),
				$img,
				$i + 1,
				esc_html( $term->name )
			);
		}
		return $html . '</ul></nav>';
	}
);

add_shortcode(
	'bd_product_tabs',
	function ( $atts ) {
		static $instance = 0;
		++$instance;
		$atts   = shortcode_atts( array( 'limit' => 8 ), $atts, 'bd_product_tabs' );
		$tabs   = apply_filters(
			'bydopamina_product_tabs',
			array(
				'bestsellery' => array( __( 'Bestsellery', 'bydopamina' ), 'best_selling="true"' ),
				'nowosci'     => array( __( 'Nowości', 'bydopamina' ), 'orderby="date" order="DESC"' ),
				'promocje'    => array( __( 'Promocje', 'bydopamina' ), 'on_sale="true"' ),
			)
		);
		$nav    = '';
		$panels = '';
		$first  = true;
		foreach ( $tabs as $key => $tab ) {
			$id      = 'bd-tab-' . $instance . '-' . sanitize_key( $key );
			$nav    .= sprintf(
				'<button type="button" role="tab" id="%1$s-t" aria-controls="%1$s" aria-selected="%2$s" tabindex="%3$s">%4$s</button>',
				esc_attr( $id ),
				$first ? 'true' : 'false',
				$first ? '0' : '-1',
				esc_html( $tab[0] )
			);
			$panels .= sprintf(
				'<div role="tabpanel" id="%1$s" aria-labelledby="%1$s-t" class="bd-rail" tabindex="0"%2$s>%3$s</div>',
				esc_attr( $id ),
				$first ? '' : ' hidden',
				do_shortcode( '[products limit="' . (int) $atts['limit'] . '" columns="4" ' . $tab[1] . ']' )
			);
			$first   = false;
		}
		return '<div class="bd-tabs" data-bd-tabs><div role="tablist" class="bd-tabs__list" aria-label="' . esc_attr__( 'Wybór produktów', 'bydopamina' ) . '">' . $nav . '</div>' . $panels . '</div>';
	}
);

add_shortcode(
	'bd_shop_the_look',
	function ( $atts ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}
		$atts   = shortcode_atts(
			array(
				'image'    => 0,
				'products' => '',
				'title'    => __( 'Na zdjęciu', 'bydopamina' ),
			),
			$atts,
			'bd_shop_the_look'
		);
		$points = array();
		foreach ( array_filter( array_map( 'trim', explode( ',', $atts['products'] ) ) ) as $chunk ) {
			$parts   = array_map( 'floatval', explode( ':', $chunk ) );
			$product = wc_get_product( (int) $parts[0] );
			if ( $product && $product->is_visible() ) {
				$points[] = array( $product, min( 95, max( 5, $parts[1] ?? 50 ) ), min( 95, max( 5, $parts[2] ?? 50 ) ) );
			}
		}
		$image = $atts['image']
			? wp_get_attachment_image( (int) $atts['image'], 'full', false, array( 'class' => 'bd-look__img', 'loading' => 'lazy', 'sizes' => '(min-width: 1025px) 60vw, 100vw' ) )
			: '<span class="bd-look__img bd-look__ph"></span>';

		$dots = '';
		$list = '';
		foreach ( $points as $i => $p ) {
			list( $product, $x, $y ) = $p;
			$pid   = 'bd-look-' . $product->get_id() . '-' . $i;
			$dots .= sprintf(
				'<button type="button" class="bd-look__dot" style="left:%1$s%%;top:%2$s%%" aria-describedby="%3$s" data-bd-look="%3$s"><span class="screen-reader-text">%4$s</span></button>',
				esc_attr( $x ),
				esc_attr( $y ),
				esc_attr( $pid ),
				esc_html( $product->get_name() )
			);
			$list .= sprintf(
				'<li id="%1$s" class="bd-look__item"><a href="%2$s">%3$s<span><span class="bd-mono">%4$02d</span><strong>%5$s</strong><span class="bd-look__price">%6$s</span></span></a></li>',
				esc_attr( $pid ),
				esc_url( $product->get_permalink() ),
				$product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ),
				$i + 1,
				esc_html( $product->get_name() ),
				wp_kses_post( $product->get_price_html() )
			);
		}
		return '<div class="bd-look"><figure class="bd-look__media">' . $image . $dots . '</figure><div class="bd-look__panel"><p class="bd-label">' . esc_html( $atts['title'] ) . '</p><ol class="bd-look__list">' . $list . '</ol></div></div>';
	}
);

add_shortcode(
	'bd_reviews',
	function ( $atts ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}
		$atts  = shortcode_atts( array( 'limit' => 10 ), $atts, 'bd_reviews' );
		$cache = 'bd_reviews_' . (int) $atts['limit'];
		$html  = get_transient( $cache );
		if ( false !== $html ) {
			return $html;
		}
		$comments = get_comments(
			array(
				'post_type'  => 'product',
				'status'     => 'approve',
				'type'       => 'review',
				'number'     => (int) $atts['limit'],
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'     => 'rating',
						'value'   => 4,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
					array(
						'key'   => 'verified',
						'value' => 1,
					),
				),
			)
		);
		if ( ! $comments ) {
			return '';
		}
		$html = '<div class="bd-reviews"><ul class="bd-reviews__list">';
		foreach ( $comments as $c ) {
			$rating  = max( 1, min( 5, (int) get_comment_meta( $c->comment_ID, 'rating', true ) ) );
			$name    = explode( ' ', trim( $c->comment_author ) );
			$display = $name[0] . ( isset( $name[1] ) ? ' ' . mb_substr( $name[1], 0, 1 ) . '.' : '' );
			$product = wc_get_product( $c->comment_post_ID );
			$html   .= '<li class="bd-review">'
				/* translators: %d: ocena */
				. '<p class="bd-stars" role="img" aria-label="' . esc_attr( sprintf( __( 'Ocena %d na 5', 'bydopamina' ), $rating ) ) . '">' . str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) . '</p>'
				. '<blockquote><p>' . esc_html( wp_trim_words( $c->comment_content, 38 ) ) . '</p></blockquote>'
				. '<footer><span>' . esc_html( $display ) . ' · <span class="bd-mono">' . esc_html__( 'Zweryfikowany zakup', 'bydopamina' ) . '</span></span>'
				. ( $product ? '<a href="' . esc_url( $product->get_permalink() ) . '">' . $product->get_image( array( 56, 56 ) ) . '<span>' . esc_html( $product->get_name() ) . '</span></a>' : '' )
				. '</footer></li>';
		}
		$html .= '</ul><p class="bd-reviews__note">' . esc_html__( 'Pokazujemy wyłącznie opinie osób, które kupiły produkt w naszym sklepie (weryfikacja po numerze zamówienia).', 'bydopamina' ) . '</p></div>';
		set_transient( $cache, $html, 6 * HOUR_IN_SECONDS );
		return $html;
	}
);

/**
 * Nowa lub zatwierdzona opinia = odśwież cache sekcji opinii.
 */
function bydopamina_flush_reviews_cache() {
	foreach ( array( 6, 8, 10, 12 ) as $n ) {
		delete_transient( 'bd_reviews_' . $n );
	}
}
add_action( 'comment_post', 'bydopamina_flush_reviews_cache' );
add_action( 'wp_set_comment_status', 'bydopamina_flush_reviews_cache' );

add_shortcode(
	'bd_gift_finder',
	function () {
		if ( ! function_exists( 'wc_get_page_permalink' ) ) {
			return '';
		}
		$shop   = wc_get_page_permalink( 'shop' );
		$ranges = apply_filters( 'bydopamina_gift_ranges', array( 79, 129, 199 ) );
		$html   = '<ul class="bd-giftfinder">';
		foreach ( $ranges as $max ) {
			$html .= '<li><a href="' . esc_url( add_query_arg( 'max_price', (int) $max, $shop ) ) . '"><span class="bd-mono">' . esc_html__( 'do', 'bydopamina' ) . '</span>' . (int) $max . ' zł</a></li>';
		}
		$html .= '<li><a href="' . esc_url( add_query_arg( 'orderby', 'popularity', $shop ) ) . '"><span class="bd-mono">' . esc_html__( 'pewniak', 'bydopamina' ) . '</span>' . esc_html__( 'Bestsellery', 'bydopamina' ) . '</a></li>';
		return $html . '</ul>';
	}
);

add_shortcode(
	'bd_size_guide',
	function () {
		static $printed = false;
		$button = '<button type="button" class="bd-textlink" data-bd-dialog="bd-size-guide">' . bydopamina_icon( 'ruler' ) . esc_html__( 'Rozmiarówka', 'bydopamina' ) . '</button>';
		if ( $printed ) {
			return $button;
		}
		$printed = true;
		$rows    = '';
		// Polski rozmiar pierścionka = obwód w mm − 40.
		for ( $size = 9; $size <= 22; $size++ ) {
			$circ  = $size + 40;
			$rows .= sprintf( '<tr><th scope="row">%d</th><td>%d mm</td><td>%s mm</td></tr>', $size, $circ, esc_html( number_format_i18n( $circ / M_PI, 1 ) ) );
		}
		ob_start();
		?>
		<dialog id="bd-size-guide" class="bd-dialog" aria-labelledby="bd-size-guide-title">
			<div class="bd-dialog__head">
				<h2 id="bd-size-guide-title"><?php esc_html_e( 'Rozmiarówka', 'bydopamina' ); ?></h2>
				<button type="button" class="bd-dialog__close" data-bd-dialog-close aria-label="<?php esc_attr_e( 'Zamknij', 'bydopamina' ); ?>"><?php echo bydopamina_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
			<div class="bd-dialog__body">
				<h3><?php esc_html_e( 'Pierścionki', 'bydopamina' ); ?></h3>
				<ol class="bd-howto">
					<li><?php esc_html_e( 'Owiń pasek papieru wokół palca – najlepiej wieczorem, gdy palce są lekko większe.', 'bydopamina' ); ?></li>
					<li><?php esc_html_e( 'Zaznacz miejsce styku i zmierz długość linijką – to obwód.', 'bydopamina' ); ?></li>
					<li><?php esc_html_e( 'Odczytaj rozmiar z tabeli. Jesteś pomiędzy? Wybierz większy.', 'bydopamina' ); ?></li>
				</ol>
				<table class="bd-table">
					<caption class="screen-reader-text"><?php esc_html_e( 'Rozmiary pierścionków', 'bydopamina' ); ?></caption>
					<thead><tr><th scope="col"><?php esc_html_e( 'Rozmiar', 'bydopamina' ); ?></th><th scope="col"><?php esc_html_e( 'Obwód', 'bydopamina' ); ?></th><th scope="col"><?php esc_html_e( 'Średnica', 'bydopamina' ); ?></th></tr></thead>
					<tbody><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- same liczby. ?></tbody>
				</table>
				<h3><?php esc_html_e( 'Długość łańcuszka', 'bydopamina' ); ?></h3>
				<dl class="bd-lengths">
					<div><dt>38–40 cm</dt><dd><?php esc_html_e( 'Choker – u podstawy szyi', 'bydopamina' ); ?></dd></div>
					<div><dt>42–45 cm</dt><dd><?php esc_html_e( 'Na obojczykach – wybierana najczęściej', 'bydopamina' ); ?></dd></div>
					<div><dt>50 cm</dt><dd><?php esc_html_e( 'Poniżej obojczyków – do warstw', 'bydopamina' ); ?></dd></div>
					<div><dt>60 cm</dt><dd><?php esc_html_e( 'Na mostku – do golfów i dekoltów V', 'bydopamina' ); ?></dd></div>
				</dl>
			</div>
		</dialog>
		<?php
		return $button . ob_get_clean();
	}
);

add_shortcode(
	'bd_product_claims',
	function () {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return '';
		}
		$claims  = array();
		$created = $product->get_date_created();
		if ( $created && ( time() - $created->getTimestamp() ) < 30 * DAY_IN_SECONDS ) {
			$claims[] = array( __( 'Nowość', 'bydopamina' ), 'new' );
		}
		$defaults = array( __( '18K złoto', 'bydopamina' ), __( 'Stal 316L', 'bydopamina' ), __( 'Wodoodporna', 'bydopamina' ) );
		foreach ( apply_filters( 'bydopamina_product_claims', $defaults, $product ) as $claim ) {
			$claims[] = array( $claim, '' );
		}
		$html = '<ul class="bd-claims">';
		foreach ( $claims as $c ) {
			$html .= '<li' . ( $c[1] ? ' class="is-' . esc_attr( $c[1] ) . '"' : '' ) . '>' . esc_html( $c[0] ) . '</li>';
		}
		return $html . '</ul>';
	}
);

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
	'bd_category_chips',
	function () {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		// Na stronie kategorii: jej podkategorie; w sklepie: kategorie główne.
		$parent = is_product_category() ? get_queried_object_id() : 0;
		$terms  = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $parent,
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}
		$html = '<nav class="bd-chips" aria-label="' . esc_attr__( 'Kategorie', 'bydopamina' ) . '"><ul>';
		foreach ( $terms as $term ) {
			$html .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . ' <span class="bd-mono">' . (int) $term->count . '</span></a></li>';
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
			array( home_url( '/lista-zyczen/' ), 'heart', __( 'Ulubione', 'bydopamina' ), false ),
		);
		$html  = '<nav class="bd-mnav" aria-label="' . esc_attr__( 'Nawigacja mobilna', 'bydopamina' ) . '"><ul>';
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
		$n                                = (int) WC()->cart->get_cart_contents_count();
		$fragments['span.bd-mnav__count'] = '<span class="bd-mnav__count" data-count="' . $n . '">' . $n . '</span>';
		return $fragments;
	}
);

// Opinie produktu (lista + formularz) – do akordeonu na karcie produktu.
add_shortcode(
	'bd_product_reviews',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() || ( ! comments_open() && ! get_comments_number() ) ) {
			return '';
		}
		ob_start();
		comments_template();
		return ob_get_clean();
	}
);

add_shortcode(
	'bd_year',
	function () {
		return esc_html( wp_date( 'Y' ) );
	}
);

// Podsumowanie ocen sklepu liczone z prawdziwych opinii (bez wpisywania liczb ręcznie).
add_shortcode(
	'bd_rating_summary',
	function () {
		$data = get_transient( 'bd_rating_summary' );
		if ( false === $data ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- jedno zapytanie agregujące, wynik w cache.
			$data = $wpdb->get_row(
				"SELECT COUNT(*) AS cnt, AVG(CAST(m.meta_value AS DECIMAL(3,2))) AS avg
				 FROM {$wpdb->comments} c
				 INNER JOIN {$wpdb->commentmeta} m ON m.comment_id = c.comment_ID AND m.meta_key = 'rating'
				 INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID AND p.post_type = 'product'
				 WHERE c.comment_approved = '1' AND c.comment_type = 'review'",
				ARRAY_A
			);
			set_transient( 'bd_rating_summary', $data, 6 * HOUR_IN_SECONDS );
		}
		if ( empty( $data['cnt'] ) ) {
			return '';
		}
		$avg = round( (float) $data['avg'], 1 );
		return sprintf(
			'<div class="bd-rating"><b>%1$s</b><span><span class="bd-stars" role="img" aria-label="%2$s">★★★★★</span><br><span class="bd-label">%3$s</span></span></div>',
			esc_html( number_format_i18n( $avg, 1 ) ),
			/* translators: %s: średnia ocena */
			esc_attr( sprintf( __( 'Średnia ocena %s na 5', 'bydopamina' ), number_format_i18n( $avg, 1 ) ) ),
			/* translators: %s: liczba opinii */
			esc_html( sprintf( _n( '%s opinia', '%s opinii', (int) $data['cnt'], 'bydopamina' ), number_format_i18n( (int) $data['cnt'] ) ) )
		);
	}
);
add_action(
	'comment_post',
	function () {
		delete_transient( 'bd_rating_summary' );
	}
);
add_action(
	'wp_set_comment_status',
	function () {
		delete_transient( 'bd_rating_summary' );
	}
);

/**
 * Kolor próbki dla wartości atrybutu: meta wtyczki Variation Swatches → własna meta `bd_color` → mapa nazw.
 *
 * @param WP_Term $term Wartość atrybutu (np. pa_kolor).
 * @return string Wartość CSS background.
 */
function bydopamina_swatch_color( $term ) {
	foreach ( array( 'product_attribute_color', 'bd_color' ) as $key ) {
		$hex = sanitize_hex_color( (string) get_term_meta( $term->term_id, $key, true ) );
		if ( $hex ) {
			return $hex;
		}
	}
	$map = apply_filters(
		'bydopamina_swatch_map',
		array(
			'zloty'        => 'linear-gradient(135deg,#F3DE9E,#C79A4B)',
			'srebrny'      => 'linear-gradient(135deg,#F4F4F4,#A9A9A9)',
			'rozowe-zloto' => 'linear-gradient(135deg,#F6CDBB,#C98A73)',
			'rozowy'       => '#F2A7B8',
			'czerwony'     => '#C8323A',
			'koralowy'     => '#F07A5E',
			'pomaranczowy' => '#F2803A',
			'zolty'        => '#F4C542',
			'zielony'      => '#6DAE7C',
			'mietowy'      => '#9FDCC8',
			'turkusowy'    => '#2FB7B0',
			'niebieski'    => '#4D7FD6',
			'granatowy'    => '#243A73',
			'fioletowy'    => '#8E6FCB',
			'liliowy'      => '#CDBDEB',
			'bezowy'       => '#D9C3A5',
			'brazowy'      => '#7A5236',
			'bialy'        => '#FFFFFF',
			'czarny'       => '#1C1917',
			'perlowy'      => 'radial-gradient(circle at 35% 30%,#FFFFFF,#EDE3D6 60%,#D8CBB9)',
			'multikolor'   => 'conic-gradient(#F07A5E,#F4C542,#6DAE7C,#2FB7B0,#8E6FCB,#F2A7B8,#F07A5E)',
		)
	);
	return $map[ sanitize_title( remove_accents( $term->slug ) ) ] ?? ( $map[ $term->slug ] ?? 'var(--bd-surface-2)' );
}

/**
 * Wartości atrybutu produktu (niepuste) + link do sklepu przefiltrowanego po tej wartości.
 *
 * @param string $attribute Nazwa atrybutu bez prefiksu pa_ (np. kolor).
 * @return array<int, array{0: WP_Term, 1: string}>
 */
function bydopamina_attribute_links( $attribute ) {
	$attribute = sanitize_title( $attribute );
	$taxonomy  = 'pa_' . $attribute;
	if ( ! function_exists( 'wc_get_page_permalink' ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	// Na stronie kategorii filtrujemy w jej obrębie, w innych miejscach – cały sklep.
	$shop = wc_get_page_permalink( 'shop' );
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$link = get_term_link( get_queried_object() );
		$shop = is_wp_error( $link ) ? $shop : $link;
	}
	$out  = array();
	foreach ( $terms as $term ) {
		// Format filtrów warstwowych WooCommerce: ?filter_kolor=zloty.
		$out[] = array( $term, add_query_arg( 'filter_' . $attribute, $term->slug, $shop ) );
	}
	return $out;
}

add_shortcode(
	'bd_shop_by_color',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'attribute' => 'kolor' ), $atts, 'bd_shop_by_color' );
		$items = bydopamina_attribute_links( $atts['attribute'] );
		if ( ! $items ) {
			return '';
		}
		return bydopamina_swatch_nav( $items, 'bydopamina_swatch_color', __( 'Zakupy według koloru', 'bydopamina' ) );
	}
);

add_shortcode(
	'bd_shop_by_material',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'attribute' => 'material' ), $atts, 'bd_shop_by_material' );
		$items = bydopamina_attribute_links( $atts['attribute'] );
		if ( ! $items ) {
			return '';
		}
		$html = '<ul class="bd-materials" aria-label="' . esc_attr__( 'Zakupy według materiału', 'bydopamina' ) . '">';
		foreach ( $items as list( $term, $url ) ) {
			$html .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . ' <span class="bd-mono">' . (int) $term->count . '</span></a></li>';
		}
		return $html . '</ul>';
	}
);

/**
 * Wygląd próbki kamienia: meta `product_attribute_color` / `bd_color` → mapa nazw kamieni.
 *
 * @param WP_Term $term Wartość atrybutu pa_kamien.
 * @return string Wartość CSS background.
 */
function bydopamina_stone_color( $term ) {
	foreach ( array( 'product_attribute_color', 'bd_color' ) as $key ) {
		$hex = sanitize_hex_color( (string) get_term_meta( $term->term_id, $key, true ) );
		if ( $hex ) {
			return $hex;
		}
	}
	$g   = static function ( $light, $mid, $dark ) {
		return "radial-gradient(circle at 32% 28%,{$light},{$mid} 45%,{$dark})";
	};
	$map = apply_filters(
		'bydopamina_stone_map',
		array(
			'ametyst'            => $g( '#D9C2F0', '#9B6FD0', '#5B3A8C' ),
			'kwarc-rozowy'       => $g( '#FBE3E8', '#F2BFCB', '#D9909F' ),
			'agat'               => $g( '#EFE3D6', '#B9967A', '#6E5140' ),
			'turkus'             => $g( '#BFF0EA', '#43BFB3', '#1E7F77' ),
			'jadeit'             => $g( '#CFEBD2', '#6FB483', '#3A7A4E' ),
			'awenturyn'          => $g( '#CDEDD0', '#5FAE73', '#2D6B40' ),
			'malachit'           => $g( '#B9E8CF', '#2F9C66', '#135B38' ),
			'onyks'              => $g( '#6B6663', '#2A2725', '#0E0D0C' ),
			'perla'              => $g( '#FFFFFF', '#F1E9DE', '#D6C8B6' ),
			'macica-perlowa'     => 'conic-gradient(from 40deg,#F6F1EA,#DDE9F0,#F3E0EA,#E9F2E4,#F6F1EA)',
			'lapis-lazuli'       => $g( '#8CA6E8', '#2F4FA8', '#16275E' ),
			'cyrkonia'           => $g( '#FFFFFF', '#E6EEF5', '#AFC0CF' ),
			'krysztal-gorski'    => $g( '#FFFFFF', '#EEF3F6', '#C3D0D8' ),
			'labradoryt'         => 'conic-gradient(from 200deg,#3E4A5C,#4F8FB8,#6FC2B8,#3E4A5C,#8FA3C9,#3E4A5C)',
			'tygrysie-oko'       => $g( '#F2C97A', '#B7832F', '#5E3A12' ),
			'cytryn'             => $g( '#FFF1B8', '#F2C94C', '#C08A12' ),
			'akwamaryn'          => $g( '#E3F7FB', '#9FDCEB', '#5AAFC6' ),
			'granat'             => $g( '#E8A0A8', '#9C1C33', '#4E0A17' ),
			'howlit'             => $g( '#FFFFFF', '#ECECEA', '#B8B6B1' ),
			'karneol'            => $g( '#F9C39A', '#E0703A', '#9E3E14' ),
			'amazonit'           => $g( '#D6F3EE', '#8FD1C5', '#4E9C90' ),
			'kamien-ksiezycowy'  => 'radial-gradient(circle at 35% 30%,#FFFFFF,#E7EEF6 45%,#BFD0E3)',
			'hematyt'            => $g( '#A9A9AD', '#4E4D52', '#1F1E22' ),
			'rubin'              => $g( '#F7A6B4', '#C8173A', '#6E0A1E' ),
			'szmaragd'           => $g( '#A8EAC4', '#1F9E5C', '#0B5A32' ),
			'szafir'             => $g( '#A9C0F5', '#2A4BC4', '#122470' ),
			'topaz'              => $g( '#DDF1FB', '#8DC9EA', '#3F8FBF' ),
			'opal'               => 'conic-gradient(from 90deg,#F7F2FF,#CDEFF2,#F9DDE8,#FFF4C9,#D9F0DA,#F7F2FF)',
			'perydot'            => $g( '#E9F7B0', '#A8CF3A', '#627D12' ),
		)
	);
	$slug = sanitize_title( remove_accents( $term->slug ) );
	return $map[ $slug ] ?? $map[ sanitize_title( remove_accents( $term->name ) ) ] ?? 'radial-gradient(circle at 32% 28%,#fff,#E3D9CB 50%,#B9A993)';
}

/**
 * Kamienie urodzinowe (tradycja polska/europejska) – miesiąc → slug kamienia w pa_kamien.
 * Pokazujemy tylko te, które są w sklepie. Zmień filtrem bydopamina_birthstones.
 *
 * @return array<string, string[]>
 */
function bydopamina_birthstones() {
	return apply_filters(
		'bydopamina_birthstones',
		array(
			'Styczeń'     => array( 'granat' ),
			'Luty'        => array( 'ametyst' ),
			'Marzec'      => array( 'akwamaryn' ),
			'Kwiecień'    => array( 'cyrkonia', 'krysztal-gorski' ),
			'Maj'         => array( 'szmaragd', 'jadeit', 'awenturyn' ),
			'Czerwiec'    => array( 'perla', 'kamien-ksiezycowy' ),
			'Lipiec'      => array( 'rubin', 'karneol' ),
			'Sierpień'    => array( 'perydot' ),
			'Wrzesień'    => array( 'szafir', 'lapis-lazuli' ),
			'Październik' => array( 'opal', 'kwarc-rozowy' ),
			'Listopad'    => array( 'topaz', 'cytryn' ),
			'Grudzień'    => array( 'turkus' ),
		)
	);
}

/**
 * Lista próbek (kolor albo kamień) jako nawigacja.
 *
 * @param array    $items  Wynik bydopamina_attribute_links().
 * @param callable $color  Funkcja zwracająca tło próbki.
 * @param string   $label  Etykieta dla czytników ekranu.
 * @param string   $extra  Dodatkowa klasa (np. bd-swatches--gems).
 * @return string
 */
function bydopamina_swatch_nav( $items, $color, $label, $extra = '' ) {
	$html = '<nav class="bd-swatches ' . esc_attr( $extra ) . '" aria-label="' . esc_attr( $label ) . '"><ul>';
	foreach ( $items as list( $term, $url ) ) {
		$html .= sprintf(
			'<li><a href="%1$s"><span class="bd-swatches__dot" style="--sw:%2$s" aria-hidden="true"></span><span class="bd-swatches__name">%3$s<span class="bd-mono">%4$d</span></span></a></li>',
			esc_url( $url ),
			esc_attr( $color( $term ) ),
			esc_html( $term->name ),
			(int) $term->count
		);
	}
	return $html . '</ul></nav>';
}

/**
 * Wybór kamienia urodzinowego: miesiąc → sklep przefiltrowany po kamieniach tego miesiąca.
 *
 * @param string $attribute Atrybut kamieni (bez pa_).
 * @return string
 */
function bydopamina_birthstone_picker( $attribute ) {
	$taxonomy = 'pa_' . sanitize_title( $attribute );
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return '';
	}
	$shop  = wc_get_page_permalink( 'shop' );
	$items = '';
	foreach ( bydopamina_birthstones() as $month => $slugs ) {
		$found = array();
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );
			if ( $term && $term->count > 0 ) {
				$found[] = $term;
			}
		}
		if ( ! $found ) {
			continue;
		}
		// Kilka kamieni w miesiącu = filtr LUB (query_type_…=or).
		$url    = add_query_arg(
			array(
				'filter_' . $attribute     => implode( ',', wp_list_pluck( $found, 'slug' ) ),
				'query_type_' . $attribute => 'or',
			),
			$shop
		);
		$items .= '<li><a href="' . esc_url( $url ) . '"><span class="bd-mono">' . esc_html( $month ) . '</span>' . esc_html( implode( ' · ', wp_list_pluck( $found, 'name' ) ) ) . '</a></li>';
	}
	return $items ? '<div class="bd-birthstones"><p class="bd-label">' . esc_html__( 'Kamień urodzinowy', 'bydopamina' ) . '</p><ul>' . $items . '</ul></div>' : '';
}

add_shortcode(
	'bd_shop_by_stone',
	function ( $atts ) {
		$atts  = shortcode_atts(
			array(
				'attribute'   => 'kamien',
				'birthstones' => 'yes',
			),
			$atts,
			'bd_shop_by_stone'
		);
		$items = bydopamina_attribute_links( $atts['attribute'] );
		if ( ! $items ) {
			return '';
		}
		$html = bydopamina_swatch_nav( $items, 'bydopamina_stone_color', __( 'Zakupy według kamienia', 'bydopamina' ), 'bd-swatches--gems' );
		if ( 'yes' === $atts['birthstones'] ) {
			$html .= bydopamina_birthstone_picker( $atts['attribute'] );
		}
		return $html;
	}
);

/**
 * [bd_shop_by] – przełącznik Kolor / Kamień / Materiał w jednej sekcji (zakładki ARIA, JS z main.js).
 * Zakładka bez danych (brak atrybutu lub produktów) jest pomijana.
 */
add_shortcode(
	'bd_shop_by',
	function ( $atts ) {
		static $instance = 0;
		++$instance;
		$atts   = shortcode_atts(
			array(
				'color'    => 'kolor',
				'stone'    => 'kamien',
				'material' => 'material',
			),
			$atts,
			'bd_shop_by'
		);
		$panels = array_filter(
			array(
				'kolor'    => array( __( 'Kolor', 'bydopamina' ), do_shortcode( '[bd_shop_by_color attribute="' . esc_attr( $atts['color'] ) . '"]' ) ),
				'kamien'   => array( __( 'Kamień', 'bydopamina' ), do_shortcode( '[bd_shop_by_stone attribute="' . esc_attr( $atts['stone'] ) . '"]' ) ),
				'material' => array( __( 'Materiał', 'bydopamina' ), do_shortcode( '[bd_shop_by_material attribute="' . esc_attr( $atts['material'] ) . '"]' ) ),
			),
			static function ( $p ) {
				return '' !== $p[1];
			}
		);
		if ( ! $panels ) {
			return '';
		}
		$nav   = '';
		$body  = '';
		$first = true;
		foreach ( $panels as $key => $p ) {
			$id    = 'bd-shopby-' . $instance . '-' . $key;
			$nav  .= sprintf( '<button type="button" role="tab" id="%1$s-t" aria-controls="%1$s" aria-selected="%2$s" tabindex="%3$s">%4$s</button>', esc_attr( $id ), $first ? 'true' : 'false', $first ? '0' : '-1', esc_html( $p[0] ) );
			$body .= sprintf( '<div role="tabpanel" id="%1$s" aria-labelledby="%1$s-t" tabindex="0"%2$s>%3$s</div>', esc_attr( $id ), $first ? '' : ' hidden', $p[1] );
			$first = false;
		}
		return '<div class="bd-tabs bd-shopby" data-bd-tabs><div role="tablist" class="bd-tabs__list bd-tabs__list--small" aria-label="' . esc_attr__( 'Szukaj według', 'bydopamina' ) . '">' . $nav . '</div>' . $body . '</div>';
	}
);
