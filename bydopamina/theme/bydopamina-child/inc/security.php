<?php
/**
 * Utwardzenie bezpieczeństwa WordPressa + WooCommerce.
 *
 * To warstwa aplikacyjna. Nie zastępuje WAF (Cloudflare / serwer), kopii zapasowych,
 * 2FA ani aktualizacji – patrz docs/BEZPIECZENSTWO.md.
 *
 * @package bydopamina
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Mniej informacji dla skanerów
 * ---------------------------------------------------------------------- */

// Wersja WP w <head>, RSS i parametrach ?ver= zasobów rdzenia.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_filter(
	'style_loader_src',
	'bydopamina_strip_core_version',
	9999
);
add_filter(
	'script_loader_src',
	'bydopamina_strip_core_version',
	9999
);

/**
 * Usuwa ?ver=<wersja WP> (zostawia wersje wtyczek i motywu – potrzebne do cache-bustingu).
 *
 * @param string $src URL zasobu.
 * @return string
 */
function bydopamina_strip_core_version( $src ) {
	if ( $src && str_contains( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}

// Nagłówek generatora WooCommerce.
add_action(
	'get_header',
	function () {
		if ( function_exists( 'WC' ) ) {
			remove_action( 'wp_head', 'wc_generator_tag' );
		}
	}
);

/* -------------------------------------------------------------------------
 * 2. XML-RPC (brute-force przez system.multicall, pingback DDoS)
 *    Jeśli używasz Jetpacka lub aplikacji mobilnej WP – zablokuj na poziomie
 *    serwera z wyjątkiem IP Automattic zamiast wyłączać tutaj.
 * ---------------------------------------------------------------------- */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'pings_open', '__return_false', 9999 );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);
add_filter(
	'xmlrpc_methods',
	function () {
		return array();
	}
);

/* -------------------------------------------------------------------------
 * 3. Enumeracja użytkowników (?author=1, /wp-json/wp/v2/users, sitemapa)
 * ---------------------------------------------------------------------- */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['author'] ) || is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( current_user_can( 'list_users' ) ) {
			return $endpoints;
		}
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( str_starts_with( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

// oEmbed nie zdradza loginu autora.
add_filter(
	'oembed_response_data',
	function ( $data ) {
		unset( $data['author_name'], $data['author_url'] );
		return $data;
	}
);

/* -------------------------------------------------------------------------
 * 4. Logowanie: ogólne komunikaty błędów + limit prób
 *    (Uzupełnij o 2FA – np. Wordfence Login Security / Two Factor.)
 * ---------------------------------------------------------------------- */
add_filter(
	'login_errors',
	function () {
		return esc_html__( 'Nieprawidłowe dane logowania.', 'bydopamina' );
	}
);

const BYDOPAMINA_LOGIN_MAX_ATTEMPTS = 5;
const BYDOPAMINA_LOGIN_LOCKOUT      = 15 * MINUTE_IN_SECONDS;

/**
 * Klucz transientu dla IP klienta. Nie ufamy X-Forwarded-For (łatwy do podrobienia);
 * za Cloudflare ustaw na serwerze mod_remoteip / real_ip, wtedy REMOTE_ADDR jest prawdziwy.
 *
 * @return string
 */
function bydopamina_login_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return 'bd_login_' . md5( $ip );
}

add_filter(
	'authenticate',
	function ( $user ) {
		$attempts = (int) get_transient( bydopamina_login_key() );
		if ( $attempts >= BYDOPAMINA_LOGIN_MAX_ATTEMPTS ) {
			return new WP_Error(
				'bd_locked',
				esc_html__( 'Zbyt wiele prób logowania. Spróbuj ponownie za 15 minut.', 'bydopamina' )
			);
		}
		return $user;
	},
	30
);

add_action(
	'wp_login_failed',
	function () {
		$key      = bydopamina_login_key();
		$attempts = (int) get_transient( $key );
		set_transient( $key, $attempts + 1, BYDOPAMINA_LOGIN_LOCKOUT );
	}
);

add_action(
	'wp_login',
	function () {
		delete_transient( bydopamina_login_key() );
	}
);

/* -------------------------------------------------------------------------
 * 5. Nagłówki bezpieczeństwa HTTP
 *    Jeśli ustawiasz je już na serwerze (server/.htaccess, nginx), zostaw
 *    tylko jedno źródło – zduplikowany CSP działa jak przecięcie obu polityk.
 * ---------------------------------------------------------------------- */
add_action(
	'send_headers',
	function () {
		if ( headers_sent() ) {
			return;
		}
		// Podstawowe nagłówki – pomiń, jeśli ustawia je serwer (BYDOPAMINA_SEND_HEADERS = false), żeby się nie dublowały.
		if ( BYDOPAMINA_SEND_HEADERS ) {
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'Referrer-Policy: strict-origin-when-cross-origin' );
			if ( is_ssl() ) {
				// Zacznij od max-age=300; po tygodniu bez problemów podnieś do 63072000 i dodaj preload.
				header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
			}
		}
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), usb=(), payment=(self "https://*.przelewy24.pl" "https://*.payu.com" "https://js.stripe.com")' );
		header( 'Cross-Origin-Opener-Policy: same-origin-allow-popups' );

		// CSP – tylko front. Panel/Elementor editor potrzebuje unsafe-eval, więc go pomijamy.
		if ( is_admin() || isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$csp = implode(
			'; ',
			array(
				"default-src 'self'",
				// Elementor/WooCommerce używają skryptów inline – 'unsafe-inline' jest tu kompromisem. Po audycie raportów przejdź na nonce.
				"script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com https://js.stripe.com https://*.przelewy24.pl https://*.payu.com https://geowidget.inpost.pl https://challenges.cloudflare.com",
				"style-src 'self' 'unsafe-inline' https://geowidget.inpost.pl",
				"img-src 'self' data: blob: https://*.google-analytics.com https://*.googletagmanager.com https://secure.gravatar.com https://*.wp.com https://*.inpost.pl https://*.openstreetmap.org",
				"font-src 'self' data:",
				"connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com https://api.stripe.com https://*.inpost.pl",
				"frame-src 'self' https://www.google.com https://js.stripe.com https://hooks.stripe.com https://*.przelewy24.pl https://*.payu.com https://geowidget.inpost.pl https://challenges.cloudflare.com https://www.youtube-nocookie.com",
				"form-action 'self' https://*.przelewy24.pl https://*.payu.com",
				"frame-ancestors 'self'",
				"base-uri 'self'",
				"object-src 'none'",
				'upgrade-insecure-requests',
			)
		);
		$name = BYDOPAMINA_CSP_ENFORCE ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';
		header( $name . ': ' . $csp );
	}
);

/* -------------------------------------------------------------------------
 * 6. Panel: brak edytora plików, brak ujawniania błędów, krótsze sesje
 * ---------------------------------------------------------------------- */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	// Najlepiej ustawić w wp-config.php (patrz server/wp-config-hardening.php).
	define( 'DISALLOW_FILE_EDIT', true );
}

// Sesja „Zapamiętaj mnie” maks. 7 dni (domyślnie 14).
add_filter(
	'auth_cookie_expiration',
	function ( $length, $user_id, $remember ) {
		return $remember ? 7 * DAY_IN_SECONDS : $length;
	},
	10,
	3
);

// Klienci sklepu nie mają czego szukać w /wp-admin.
add_action(
	'admin_init',
	function () {
		global $pagenow;
		if ( wp_doing_ajax() || wp_doing_cron() || 'admin-post.php' === $pagenow || ! is_user_logged_in() ) {
			return;
		}
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			wp_safe_redirect( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) );
			exit;
		}
	}
);
add_filter(
	'show_admin_bar',
	function ( $show ) {
		return current_user_can( 'edit_posts' ) ? $show : false;
	}
);

/* -------------------------------------------------------------------------
 * 7. Uploady: blokada SVG i plików wykonywalnych (XSS / RCE przez media)
 * ---------------------------------------------------------------------- */
add_filter(
	'upload_mimes',
	function ( $mimes ) {
		unset( $mimes['svg'], $mimes['svgz'], $mimes['exe'], $mimes['htm|html'], $mimes['js'], $mimes['swf'] );
		return $mimes;
	},
	99
);

/* -------------------------------------------------------------------------
 * 8. WooCommerce: ochrona przed „card testing” i spamem kont
 * ---------------------------------------------------------------------- */

// Rate limiting Store API (koszyk/checkout blokowy) – wbudowane w WooCommerce 8.9+.
add_filter(
	'woocommerce_store_api_rate_limit_options',
	function () {
		return array(
			'enabled'       => true,
			'proxy_support' => false, // true tylko gdy serwer za zaufanym proxy ustawia REMOTE_ADDR poprawnie.
			'limit'         => 25,    // żądań…
			'seconds'       => 10,    // …na 10 s.
		);
	}
);

// Honeypot w formularzach rejestracji i checkoutu (klasyczny checkout / widget Elementora).
add_action( 'woocommerce_register_form', 'bydopamina_honeypot_field' );
add_action( 'woocommerce_after_order_notes', 'bydopamina_honeypot_field' );

/**
 * Pole-pułapka niewidoczne dla ludzi (także dla czytników ekranu).
 */
function bydopamina_honeypot_field() {
	echo '<div class="bd-hp" aria-hidden="true"><label for="bd_website">Website</label><input type="text" name="bd_website" id="bd_website" value="" tabindex="-1" autocomplete="off"></div>';
}

add_filter(
	'woocommerce_registration_errors',
	function ( $errors ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce weryfikuje nonce formularza.
		if ( ! empty( $_POST['bd_website'] ) ) {
			$errors->add( 'bd_spam', esc_html__( 'Nie udało się utworzyć konta.', 'bydopamina' ) );
		}
		return $errors;
	}
);

add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['bd_website'] ) ) {
			$errors->add( 'bd_spam', esc_html__( 'Nie udało się złożyć zamówienia. Odśwież stronę i spróbuj ponownie.', 'bydopamina' ) );
		}
	},
	10,
	2
);

// Limit nieudanych płatności z jednego IP (klasyczny checkout) – spowalnia testowanie kradzionych kart.
add_action(
	'woocommerce_order_status_failed',
	function () {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'bd_fail_' . md5( $ip );
		set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
	}
);
add_action(
	'woocommerce_checkout_process',
	function () {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( (int) get_transient( 'bd_fail_' . md5( $ip ) ) >= 5 ) {
			wc_add_notice( esc_html__( 'Zbyt wiele nieudanych płatności. Spróbuj ponownie za godzinę lub skontaktuj się z nami.', 'bydopamina' ), 'error' );
		}
	}
);

// Nie pozwalaj na tworzenie konta przy zamówieniu z „jednorazowych” domen – przykładowa lista, rozbuduj wg potrzeb.
add_filter(
	'woocommerce_registration_errors',
	function ( $errors, $username, $email ) {
		$blocked = array( 'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'yopmail.com', 'tempmail.com' );
		$domain  = strtolower( substr( strrchr( (string) $email, '@' ), 1 ) );
		if ( in_array( $domain, $blocked, true ) ) {
			$errors->add( 'bd_disposable', esc_html__( 'Użyj stałego adresu e-mail.', 'bydopamina' ) );
		}
		return $errors;
	},
	10,
	3
);
