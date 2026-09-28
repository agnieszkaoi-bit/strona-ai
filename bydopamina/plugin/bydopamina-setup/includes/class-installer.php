<?php
/**
 * Instalator sklepu bydopamina. Każdy krok jest idempotentny: ponowne uruchomienie
 * niczego nie duplikuje (istniejące strony, szablony, terminy i menu są pomijane).
 *
 * @package bydopamina-setup
 */

defined( 'ABSPATH' ) || exit;

/**
 * Konfiguracja sklepu krok po kroku.
 */
class Bydopamina_Installer {

	/**
	 * Dziennik działań (pokazywany w panelu / WP-CLI).
	 *
	 * @var string[]
	 */
	private $log = array();

	/**
	 * Wymagania przed uruchomieniem.
	 *
	 * @return array<int, array{label: string, ok: bool, fix: string}>
	 */
	public static function requirements() {
		$theme = wp_get_theme();
		return array(
			array(
				'label' => 'Motyw „bydopamina Child” aktywny (motyw nadrzędny: Hello Elementor)',
				'ok'    => 'bydopamina-child' === get_stylesheet() && 'hello-elementor' === $theme->get_template(),
				'fix'   => 'Wygląd → Motywy: zainstaluj Hello Elementor, wgraj bydopamina-child.zip i aktywuj bydopamina Child.',
			),
			array(
				'label' => 'WooCommerce',
				'ok'    => class_exists( 'WooCommerce' ),
				'fix'   => 'Wtyczki → Dodaj nową → „WooCommerce” → Zainstaluj i włącz.',
			),
			array(
				'label' => 'Elementor',
				'ok'    => defined( 'ELEMENTOR_VERSION' ),
				'fix'   => 'Wtyczki → Dodaj nową → „Elementor” → Zainstaluj i włącz.',
			),
			array(
				'label' => 'Elementor Pro (licencja)',
				'ok'    => defined( 'ELEMENTOR_PRO_VERSION' ),
				'fix'   => 'Wgraj plik elementor-pro.zip z konta elementor.com i aktywuj licencję.',
			),
			array(
				'label' => 'Szablony w pakiecie',
				'ok'    => is_readable( BYDOPAMINA_SETUP_DIR . '/templates/03-strona-glowna.json' ),
				'fix'   => 'Wgraj pełną paczkę bydopamina-setup.zip (z folderem templates).',
			),
		);
	}

	/**
	 * Uruchamia wszystkie kroki.
	 *
	 * @return string[] Dziennik.
	 */
	public function run() {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}
		$steps = array(
			'general'     => 'Ustawienia WordPressa',
			'woocommerce' => 'Ustawienia WooCommerce (Polska, PLN, zakupy bez konta, opinie tylko od kupujących)',
			'taxonomies'  => 'Kategorie, tagi i atrybuty',
			'shipping'    => 'Strefa wysyłki Polska',
			'elementor'   => 'Ustawienia Elementora',
			'pages'       => 'Strony',
			'templates'   => 'Szablony Elementora (header, stopka, produkt, sklep, 404)',
			'menu'        => 'Menu główne',
		);
		foreach ( $steps as $method => $label ) {
			$this->log[] = '— ' . $label;
			try {
				$this->{'step_' . $method}();
			} catch ( Throwable $e ) {
				$this->log[] = '   ✗ Błąd: ' . $e->getMessage();
			}
		}
		flush_rewrite_rules();
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		update_option( 'bydopamina_setup_done', time() );
		$this->log[] = '✓ Konfiguracja zakończona. Strona główna: ' . home_url( '/' );
		return $this->log;
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Język, strefa czasowa, bezpośrednie odnośniki, rejestracja, komentarze.
	 */
	private function step_general() {
		update_option( 'timezone_string', 'Europe/Warsaw' );
		update_option( 'date_format', 'j F Y' );
		update_option( 'time_format', 'H:i' );
		update_option( 'start_of_week', 1 );
		update_option( 'users_can_register', 0 );           // Konta klientów tworzy WooCommerce.
		update_option( 'default_comment_status', 'closed' ); // Opinie produktów działają niezależnie.
		update_option( 'default_ping_status', 'closed' );
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' ); // Od razu działa też w tym samym żądaniu.

		if ( 'pl_PL' !== get_option( 'WPLANG' ) ) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$ok = wp_download_language_pack( 'pl_PL' );
			if ( $ok ) {
				update_option( 'WPLANG', 'pl_PL' );
				$this->log[] = '   ✓ Język polski';
			} else {
				$this->log[] = '   ! Nie udało się pobrać języka polskiego – ustaw w Ustawienia → Ogólne → Język.';
			}
		}
		// Przykładowe treści WordPressa (tylko jeśli nie były edytowane).
		foreach ( array( get_page_by_path( 'sample-page' ), get_page_by_path( 'hello-world', OBJECT, 'post' ) ) as $sample ) {
			if ( $sample && $sample->post_date === $sample->post_modified ) {
				wp_trash_post( $sample->ID );
			}
		}
		$this->log[] = '   ✓ Strefa czasowa, format daty, odnośniki /nazwa-strony/, rejestracja WP i komentarze wyłączone';
	}

	/**
	 * Ustawienia sklepu pod polskiego klienta.
	 */
	private function step_woocommerce() {
		$options = array(
			'woocommerce_default_country'                     => 'PL',
			'woocommerce_allowed_countries'                   => 'specific',
			'woocommerce_specific_allowed_countries'          => array( 'PL' ),
			'woocommerce_ship_to_countries'                   => '',
			'woocommerce_currency'                            => 'PLN',
			'woocommerce_currency_pos'                        => 'right_space',
			'woocommerce_price_thousand_sep'                  => ' ',
			'woocommerce_price_decimal_sep'                   => ',',
			'woocommerce_price_num_decimals'                  => 2,
			'woocommerce_weight_unit'                         => 'g',
			'woocommerce_dimension_unit'                      => 'cm',
			'woocommerce_enable_guest_checkout'               => 'yes',
			'woocommerce_enable_checkout_login_reminder'      => 'yes',
			'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
			'woocommerce_enable_myaccount_registration'       => 'yes',
			'woocommerce_registration_generate_password'      => 'yes',
			'woocommerce_enable_ajax_add_to_cart'             => 'yes',
			'woocommerce_cart_redirect_after_add'             => 'no',
			'woocommerce_enable_reviews'                      => 'yes',
			'woocommerce_enable_review_rating'                => 'yes',
			'woocommerce_review_rating_required'              => 'yes',
			'woocommerce_review_rating_verification_label'    => 'yes',
			'woocommerce_review_rating_verification_required' => 'yes', // Opinie tylko od kupujących.
			'woocommerce_manage_stock'                        => 'yes',
			'woocommerce_notify_low_stock_amount'             => 2,
			'woocommerce_hide_out_of_stock_items'             => 'no',
			'woocommerce_catalog_columns'                     => 4,
			'woocommerce_permalinks'                          => array(
				'product_base'           => 'produkt',
				'category_base'          => 'kategoria-produktu',
				'tag_base'               => 'tag-produktu',
				'attribute_base'         => '',
				'use_verbose_page_rules' => false,
			),
		);
		foreach ( $options as $key => $value ) {
			update_option( $key, $value );
		}
		$this->log[] = '   ✓ Polska, PLN („129,00 zł”), zakupy bez konta, dodawanie do koszyka bez przeładowania, opinie tylko od kupujących';
		$this->log[] = '   ! Podatki: sprawdź WooCommerce → Ustawienia → Ogólne → „Włącz podatki” (zależy od tego, czy jesteś płatnikiem VAT)';
	}

	/**
	 * Kategorie, tagi i atrybuty zgodne z szablonem.
	 */
	private function step_taxonomies() {
		$categories = array(
			'kolczyki'    => 'Kolczyki',
			'naszyjniki'  => 'Naszyjniki',
			'bransoletki' => 'Bransoletki',
			'pierscionki' => 'Pierścionki',
			'zestawy'     => 'Zestawy',
			'prezenty'    => 'Prezenty',
		);
		$order = 0;
		foreach ( $categories as $slug => $name ) {
			$id = $this->ensure_term( $name, 'product_cat', $slug );
			if ( $id ) {
				update_term_meta( $id, 'order', $order++ );
			}
		}
		$this->log[] = '   ✓ Kategorie: ' . implode( ', ', $categories );

		$tags = array(
			'bestseller'        => 'bestseller',
			'promocja'          => 'promocja',
			'nastroj-radosc'    => 'nastrój: radość',
			'nastroj-energia'   => 'nastrój: energia',
			'nastroj-czulosc'   => 'nastrój: czułość',
			'nastroj-spokoj'    => 'nastrój: spokój',
			'nastroj-marzenia'  => 'nastrój: marzenia',
			'nastroj-swiezosc'  => 'nastrój: świeżość',
		);
		foreach ( $tags as $slug => $name ) {
			$this->ensure_term( $name, 'product_tag', $slug );
		}
		$this->log[] = '   ✓ Tagi: bestseller, promocja, nastroj-*';

		$attributes = array(
			'kolor'    => array(
				'Kolor',
				array( 'Złoty' => 'zloty', 'Srebrny' => 'srebrny', 'Różowe złoto' => 'rozowe-zloto', 'Różowy' => 'rozowy', 'Koralowy' => 'koralowy', 'Czerwony' => 'czerwony', 'Pomarańczowy' => 'pomaranczowy', 'Żółty' => 'zolty', 'Zielony' => 'zielony', 'Miętowy' => 'mietowy', 'Turkusowy' => 'turkusowy', 'Niebieski' => 'niebieski', 'Liliowy' => 'liliowy', 'Fioletowy' => 'fioletowy', 'Biały' => 'bialy', 'Czarny' => 'czarny', 'Perłowy' => 'perlowy', 'Multikolor' => 'multikolor' ),
			),
			'material' => array(
				'Materiał',
				array( 'Stal chirurgiczna' => 'stal-chirurgiczna', 'Stal złocona 18K' => 'stal-zlocona', 'Srebro 925' => 'srebro-925', 'Ceramika' => 'ceramika', 'Perły' => 'perly', 'Muszle' => 'muszle', 'Emalia' => 'emalia', 'Kamienie naturalne' => 'kamienie-naturalne' ),
			),
			'kamien'   => array(
				'Kamień',
				array( 'Ametyst' => 'ametyst', 'Kwarc różowy' => 'kwarc-rozowy', 'Turkus' => 'turkus', 'Perła' => 'perla', 'Labradoryt' => 'labradoryt', 'Onyks' => 'onyks', 'Awenturyn' => 'awenturyn', 'Lapis lazuli' => 'lapis-lazuli', 'Tygrysie oko' => 'tygrysie-oko', 'Cyrkonia' => 'cyrkonia', 'Kamień księżycowy' => 'kamien-ksiezycowy', 'Karneol' => 'karneol', 'Agat' => 'agat', 'Jadeit' => 'jadeit', 'Granat' => 'granat', 'Akwamaryn' => 'akwamaryn', 'Cytryn' => 'cytryn' ),
			),
			'dlugosc'  => array(
				'Długość',
				array( '40 cm' => '40-cm', '45 cm' => '45-cm', '50 cm' => '50-cm', '60 cm' => '60-cm' ),
			),
			'rozmiar'  => array(
				'Rozmiar',
				array( '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', 'Regulowany' => 'regulowany' ),
			),
		);
		foreach ( $attributes as $slug => $def ) {
			list( $label, $terms ) = $def;
			if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
				$res = wc_create_attribute(
					array(
						'name'         => $label,
						'slug'         => $slug,
						'type'         => 'select',
						'order_by'     => 'menu_order',
						'has_archives' => true,
					)
				);
				if ( is_wp_error( $res ) ) {
					$this->log[] = '   ✗ Atrybut ' . $label . ': ' . $res->get_error_message();
					continue;
				}
			}
			$taxonomy = wc_attribute_taxonomy_name( $slug );
			if ( ! taxonomy_exists( $taxonomy ) ) {
				// Atrybut utworzony w tym żądaniu – rejestrujemy taksonomię tymczasowo, żeby dodać wartości.
				register_taxonomy( $taxonomy, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
			}
			$i = 0;
			foreach ( $terms as $name => $term_slug ) {
				$id = $this->ensure_term( (string) $name, $taxonomy, $term_slug );
				if ( $id ) {
					update_term_meta( $id, 'order', $i++ );
				}
			}
			$this->log[] = '   ✓ Atrybut ' . $label . ' (' . count( $terms ) . ' wartości)';
		}
		delete_transient( 'wc_attribute_taxonomies' );
	}

	/**
	 * Strefa „Polska”: kurier + darmowa dostawa od 199 zł (koszty do zmiany).
	 */
	private function step_shipping() {
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			if ( 'Polska' === $zone['zone_name'] ) {
				$this->log[] = '   – Strefa „Polska” już istnieje – pomijam';
				return;
			}
		}
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'Polska' );
		$zone->add_location( 'PL', 'country' );
		$zone->save();

		$flat = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $flat . '_settings',
			array(
				'title'      => 'Kurier',
				'tax_status' => 'taxable',
				'cost'       => '14.99',
			)
		);
		$free = $zone->add_shipping_method( 'free_shipping' );
		update_option(
			'woocommerce_free_shipping_' . $free . '_settings',
			array(
				'title'            => 'Darmowa dostawa',
				'requires'         => 'min_amount',
				'min_amount'       => defined( 'BYDOPAMINA_FREE_SHIPPING_FROM' ) ? (string) BYDOPAMINA_FREE_SHIPPING_FROM : '199',
				'ignore_discounts' => 'no',
			)
		);
		$this->log[] = '   ✓ Strefa Polska: Kurier 14,99 zł (zmień), darmowa dostawa od 199 zł. Paczkomaty doda wtyczka InPost.';
	}

	/**
	 * Elementor: kolory globalne, szerokość, fonty z motywu, kontenery.
	 */
	private function step_elementor() {
		update_option( 'elementor_google_font', '0' );           // Fonty ładuje motyw lokalnie (RODO).
		update_option( 'elementor_font_display', 'swap' );
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		update_option( 'elementor_load_fa4_shim', '' );
		update_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		foreach ( array( 'container', 'nested-elements', 'e_optimized_markup', 'e_font_icon_svg', 'e_lazyload' ) as $exp ) {
			update_option( 'elementor_experiment-' . $exp, 'active' );
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( $kit_id ) {
			$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
			$settings = is_array( $settings ) ? $settings : array();
			$settings['system_colors'] = array(
				array( '_id' => 'primary', 'title' => 'Tekst – ciepła czerń', 'color' => '#2B2421' ),
				array( '_id' => 'secondary', 'title' => 'Tekst drugorzędny', 'color' => '#6B5F58' ),
				array( '_id' => 'text', 'title' => 'Tekst', 'color' => '#2B2421' ),
				array( '_id' => 'accent', 'title' => 'Malina (akcent)', 'color' => '#A3385A' ),
			);
			$settings['custom_colors'] = array(
				array( '_id' => 'bdbg', 'title' => 'Tło – porcelana', 'color' => '#FBF8F5' ),
				array( '_id' => 'bdpowder', 'title' => 'Pudrowy róż', 'color' => '#F5ECE7' ),
				array( '_id' => 'bdpack', 'title' => 'Tło zdjęć produktów', 'color' => '#F4F2F0' ),
				array( '_id' => 'bdline', 'title' => 'Linia', 'color' => '#E9DED7' ),
				array( '_id' => 'bdbutter', 'title' => 'Masło (Nowość)', 'color' => '#F6EBC8' ),
			);
			$settings['container_width'] = array( 'unit' => 'px', 'size' => 1440, 'sizes' => array() );
			$settings['body_background_background'] = 'classic';
			$settings['body_background_color']      = '#FBF8F5';
			update_post_meta( $kit_id, '_elementor_page_settings', $settings );
		}
		$this->log[] = '   ✓ Kolory globalne, szerokość 1440 px, Google Fonts wyłączone (fonty lokalne z motywu), kontenery Flexbox';
	}

	/**
	 * Strony: gotowe (z szablonami) i robocze (do uzupełnienia treścią prawną).
	 */
	private function step_pages() {
		$built = array(
			// slug => [tytuł, plik szablonu lub null, opcja WooCommerce].
			'start'       => array( 'Start', '03-strona-glowna.json', null ),
			'sklep'       => array( 'Sklep', null, 'woocommerce_shop_page_id' ),
			'koszyk'      => array( 'Koszyk', '06-koszyk.json', 'woocommerce_cart_page_id' ),
			'zamowienie'  => array( 'Zamówienie', '07-zamowienie.json', 'woocommerce_checkout_page_id' ),
			'moje-konto'  => array( 'Moje konto', '08-moje-konto.json', 'woocommerce_myaccount_page_id' ),
			'kontakt'     => array( 'Kontakt', '09-kontakt.json', null ),
		);
		foreach ( $built as $slug => $def ) {
			list( $title, $file, $wc_option ) = $def;
			$page_id = $wc_option ? (int) get_option( $wc_option ) : 0;
			if ( $page_id && get_post( $page_id ) ) {
				// Strona WooCommerce już istnieje (np. „Cart”) – ustawiamy polski tytuł i adres.
				wp_update_post( array( 'ID' => $page_id, 'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish' ) );
			} else {
				$page_id = $this->ensure_page( $slug, $title, '', 'publish' );
			}
			if ( $wc_option ) {
				update_option( $wc_option, $page_id );
			}
			if ( $file ) {
				$data = $this->read_template( $file );
				if ( $data ) {
					$this->apply_elementor( $page_id, $data['content'], 'wp-page', $data['page_settings'] ?? array() );
					update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
				}
			}
			$this->log[] = '   ✓ ' . $title . ' → ' . get_permalink( $page_id );
		}

		$front = get_page_by_path( 'start' );
		if ( $front ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $front->ID );
		}

		// Strony z gotową treścią (shortcody motywu / wtyczek).
		$this->ensure_page( 'promocje', 'Promocje', '<!-- wp:shortcode -->[products on_sale="true" limit="48" columns="4" paginate="true"]<!-- /wp:shortcode -->', 'publish' );
		$this->ensure_page( 'lista-zyczen', 'Ulubione', '<!-- wp:shortcode -->[ti_wishlistsview]<!-- /wp:shortcode -->', 'publish' );
		$this->ensure_page( 'rozmiarowka', 'Rozmiarówka', '<!-- wp:shortcode -->[bd_size_guide]<!-- /wp:shortcode -->', 'publish' );
		$this->log[] = '   ✓ Promocje, Ulubione, Rozmiarówka';

		// Strony do uzupełnienia – szkice, żeby nie opublikować pustych regulaminów.
		$drafts = array(
			'o-nas'                  => 'O nas',
			'faq'                    => 'FAQ',
			'dostawa-i-platnosci'    => 'Dostawa i płatności',
			'zwroty'                 => 'Zwroty i reklamacje',
			'pielegnacja'            => 'Pielęgnacja biżuterii',
			'regulamin'              => 'Regulamin',
			'polityka-cookies'       => 'Polityka cookies',
			'odstapienie-od-umowy'   => 'Odstąpienie od umowy',
			'deklaracja-dostepnosci' => 'Deklaracja dostępności',
			'karta-podarunkowa'      => 'Karta podarunkowa',
			'stylizacje'             => 'Stylizacje',
			'opinie'                 => 'Opinie',
		);
		foreach ( $drafts as $slug => $title ) {
			$this->ensure_page( $slug, $title, '<!-- wp:paragraph --><p>[Uzupełnij treść i opublikuj stronę.]</p><!-- /wp:paragraph -->', 'draft' );
		}
		$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
		if ( $privacy && get_post( $privacy ) ) {
			wp_update_post( array( 'ID' => $privacy, 'post_name' => 'polityka-prywatnosci', 'post_title' => 'Polityka prywatności' ) );
		}
		$terms = get_page_by_path( 'regulamin' );
		if ( $terms ) {
			update_option( 'woocommerce_terms_page_id', $terms->ID );
		}
		$this->log[] = '   ! Szkice do uzupełnienia: ' . implode( ', ', $drafts ) . ', Polityka prywatności';
	}

	/**
	 * Szablony Kreatora motywu z warunkami wyświetlania.
	 */
	private function step_templates() {
		$map = array(
			'01-header.json'         => array( 'header', array( 'include/general' ) ),
			'02-footer.json'         => array( 'footer', array( 'include/general' ) ),
			'04-karta-produktu.json' => array( 'product', array( 'include/product' ) ),
			'05-sklep-archiwum.json' => array( 'product-archive', array( 'include/product_archive' ) ),
			'10-404.json'            => array( 'error-404', array( 'include/singular/not_found404' ) ),
		);
		foreach ( $map as $file => $def ) {
			list( $type, $conditions ) = $def;
			$data = $this->read_template( $file );
			if ( ! $data ) {
				$this->log[] = '   ✗ Brak pliku ' . $file;
				continue;
			}
			$existing = get_posts(
				array(
					'post_type'   => 'elementor_library',
					'post_status' => 'any',
					'meta_key'    => '_bd_setup_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'fields'      => 'ids',
				)
			);
			if ( $existing ) {
				$this->log[] = '   – ' . $data['title'] . ' już istnieje – pomijam';
				continue;
			}
			$content = $data['content'];
			if ( 'header' === $type ) {
				$content = $this->set_widget_setting( $content, 'nav-menu', 'menu', 'glowne' );
			}
			$id = wp_insert_post(
				array(
					'post_type'   => 'elementor_library',
					'post_status' => 'publish',
					'post_title'  => $data['title'],
				)
			);
			if ( is_wp_error( $id ) || ! $id ) {
				$this->log[] = '   ✗ ' . $data['title'];
				continue;
			}
			wp_set_object_terms( $id, $type, 'elementor_library_type' );
			update_post_meta( $id, '_bd_setup_key', $file );
			$this->apply_elementor( $id, $content, $type, $data['page_settings'] ?? array() );
			update_post_meta( $id, '_elementor_conditions', $conditions );
			$this->log[] = '   ✓ ' . $data['title'] . ' (' . implode( ', ', $conditions ) . ')';
		}

		// Odśwież pamięć warunków Kreatora motywu, żeby szablony zadziałały od razu.
		if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();
		}
	}

	/**
	 * Menu „Główne” zgodne z headerem.
	 */
	private function step_menu() {
		$menu = wp_get_nav_menu_object( 'glowne' );
		if ( $menu ) {
			$this->log[] = '   – Menu „Główne” już istnieje – pomijam';
			return;
		}
		$menu_id = wp_create_nav_menu( 'Główne' );
		if ( is_wp_error( $menu_id ) ) {
			$this->log[] = '   ✗ ' . $menu_id->get_error_message();
			return;
		}
		wp_update_term( $menu_id, 'nav_menu', array( 'slug' => 'glowne' ) );
		$shop = wc_get_page_permalink( 'shop' );

		$this->menu_link( $menu_id, 'Nowości', add_query_arg( 'orderby', 'date', $shop ) );
		$jewellery = (int) wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Biżuteria',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => wc_get_page_id( 'shop' ),
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
		foreach ( array( 'kolczyki', 'naszyjniki', 'bransoletki', 'pierscionki', 'zestawy' ) as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $term->name,
						'menu-item-object'    => 'product_cat',
						'menu-item-object-id' => $term->term_id,
						'menu-item-type'      => 'taxonomy',
						'menu-item-parent-id' => $jewellery,
						'menu-item-status'    => 'publish',
					)
				);
			}
		}
		$this->menu_link( $menu_id, 'Bestsellery', add_query_arg( 'orderby', 'popularity', $shop ) );
		$gifts = get_term_by( 'slug', 'prezenty', 'product_cat' );
		if ( $gifts ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => 'Na prezent',
					'menu-item-object'    => 'product_cat',
					'menu-item-object-id' => $gifts->term_id,
					'menu-item-type'      => 'taxonomy',
					'menu-item-status'    => 'publish',
				)
			);
		}
		$this->menu_link( $menu_id, 'Promocje', home_url( '/promocje/' ), 'bd-menu-promo' );

		$locations           = get_theme_mod( 'nav_menu_locations', array() );
		$locations['menu-1'] = $menu_id; // Lokalizacja Hello Elementor (zapasowo).
		set_theme_mod( 'nav_menu_locations', $locations );
		$this->log[] = '   ✓ Menu: Nowości · Biżuteria (podkategorie) · Bestsellery · Na prezent · Promocje';
	}

	/* ------------------------------------------------------------------ */

	/**
	 * Czyta szablon JSON z pakietu.
	 *
	 * @param string $file Nazwa pliku.
	 * @return array|null
	 */
	private function read_template( $file ) {
		$path = BYDOPAMINA_SETUP_DIR . '/templates/' . $file;
		if ( ! is_readable( $path ) ) {
			return null;
		}
		$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return is_array( $data ) && isset( $data['content'] ) ? $data : null;
	}

	/**
	 * Zapisuje układ Elementora w poście.
	 *
	 * @param int    $post_id       Post.
	 * @param array  $content       Drzewo elementów.
	 * @param string $type          Typ szablonu Elementora.
	 * @param array  $page_settings Ustawienia strony.
	 */
	private function apply_elementor( $post_id, $content, $type, $page_settings ) {
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.25.0' );
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );
		if ( $page_settings ) {
			update_post_meta( $post_id, '_elementor_page_settings', $page_settings );
		}
		delete_post_meta( $post_id, '_elementor_css' );
	}

	/**
	 * Ustawia wartość w każdym widżecie danego typu (rekurencyjnie).
	 *
	 * @param array  $elements Drzewo.
	 * @param string $widget   Typ widżetu.
	 * @param string $key      Klucz ustawienia.
	 * @param mixed  $value    Wartość.
	 * @return array
	 */
	private function set_widget_setting( $elements, $widget, $key, $value ) {
		foreach ( $elements as &$el ) {
			if ( ( $el['widgetType'] ?? '' ) === $widget ) {
				$el['settings'][ $key ] = $value;
			}
			if ( ! empty( $el['elements'] ) ) {
				$el['elements'] = $this->set_widget_setting( $el['elements'], $widget, $key, $value );
			}
		}
		return $elements;
	}

	/**
	 * Strona o danym adresie – tworzy, jeśli nie istnieje.
	 *
	 * @param string $slug    Adres.
	 * @param string $title   Tytuł.
	 * @param string $content Treść.
	 * @param string $status  publish|draft.
	 * @return int ID strony.
	 */
	private function ensure_page( $slug, $title, $content, $status ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return $page->ID;
		}
		return (int) wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => $status,
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
			)
		);
	}

	/**
	 * Termin – tworzy, jeśli nie istnieje.
	 *
	 * @param string $name     Nazwa.
	 * @param string $taxonomy Taksonomia.
	 * @param string $slug     Slug.
	 * @return int ID terminu (0 przy błędzie).
	 */
	private function ensure_term( $name, $taxonomy, $slug ) {
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term ) {
			return (int) $term->term_id;
		}
		$res = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
		return is_wp_error( $res ) ? 0 : (int) $res['term_id'];
	}

	/**
	 * Pozycja menu z własnym linkiem.
	 *
	 * @param int    $menu_id Menu.
	 * @param string $title   Tytuł.
	 * @param string $url     Adres.
	 * @param string $classes Klasy CSS.
	 * @return int ID pozycji.
	 */
	private function menu_link( $menu_id, $title, $url, $classes = '' ) {
		return (int) wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'   => $title,
				'menu-item-url'     => is_string( $url ) ? $url : home_url( '/' ),
				'menu-item-type'    => 'custom',
				'menu-item-classes' => $classes,
				'menu-item-status'  => 'publish',
			)
		);
	}
}
