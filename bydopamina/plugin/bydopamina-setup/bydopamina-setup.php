<?php
/**
 * Plugin Name:       bydopamina Setup
 * Description:       Automatyczna konfiguracja sklepu bydopamina.pl: strony, szablony Elementora (header, stopka, strona główna, produkt, sklep, 404), ustawienia WooCommerce dla Polski, kategorie, atrybuty (Kolor, Materiał, Kamień), tagi, menu i zabezpieczenia. Uruchamiasz jednym przyciskiem w Narzędzia → bydopamina Setup albo komendą „wp bydopamina setup”.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            bydopamina.pl
 * License:           GPL-2.0-or-later
 * Text Domain:       bydopamina-setup
 *
 * @package bydopamina-setup
 */

defined( 'ABSPATH' ) || exit;

define( 'BYDOPAMINA_SETUP_DIR', __DIR__ );

require_once __DIR__ . '/includes/class-installer.php';
require_once __DIR__ . '/includes/admin-page.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Konfiguracja sklepu bydopamina z wiersza poleceń.
	 *
	 * ## PRZYKŁAD
	 *
	 *     wp bydopamina setup
	 *     wp bydopamina check
	 */
	WP_CLI::add_command(
		'bydopamina',
		new class() {
			/**
			 * Sprawdza wymagania (motyw, wtyczki).
			 */
			public function check() {
				foreach ( Bydopamina_Installer::requirements() as $req ) {
					WP_CLI::log( ( $req['ok'] ? '✓ ' : '✗ ' ) . $req['label'] . ( $req['ok'] ? '' : ' — ' . $req['fix'] ) );
				}
			}

			/**
			 * Uruchamia konfigurację (można powtarzać – istniejące elementy są pomijane).
			 */
			public function setup() {
				$log = ( new Bydopamina_Installer() )->run();
				foreach ( $log as $line ) {
					WP_CLI::log( $line );
				}
				WP_CLI::success( 'Gotowe.' );
			}
		}
	);
}
