<?php
/**
 * Strona w panelu: Narzędzia → bydopamina Setup.
 *
 * @package bydopamina-setup
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_management_page( 'bydopamina Setup', 'bydopamina Setup', 'manage_options', 'bydopamina-setup', 'bydopamina_setup_render_page' );
	}
);

// Przypomnienie w panelu, dopóki konfiguracja nie została uruchomiona.
add_action(
	'admin_notices',
	function () {
		if ( get_option( 'bydopamina_setup_done' ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'tools_page_bydopamina-setup' === $screen->id ) {
			return;
		}
		printf(
			'<div class="notice notice-info"><p><strong>bydopamina:</strong> %1$s <a class="button button-primary" href="%2$s">%3$s</a></p></div>',
			esc_html__( 'Sklep czeka na automatyczną konfigurację.', 'bydopamina-setup' ),
			esc_url( admin_url( 'tools.php?page=bydopamina-setup' ) ),
			esc_html__( 'Przejdź do konfiguracji', 'bydopamina-setup' )
		);
	}
);

/**
 * Widok strony konfiguracji.
 */
function bydopamina_setup_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Brak uprawnień.', 'bydopamina-setup' ) );
	}

	$log = array();
	if ( isset( $_POST['bydopamina_run'] ) ) {
		check_admin_referer( 'bydopamina_setup' );
		$log = ( new Bydopamina_Installer() )->run();
	}

	$reqs   = Bydopamina_Installer::requirements();
	$all_ok = ! in_array( false, wp_list_pluck( $reqs, 'ok' ), true );
	?>
	<div class="wrap">
		<h1>bydopamina Setup</h1>
		<p><?php esc_html_e( 'Ten kreator jednym kliknięciem przygotuje sklep: strony, szablony, ustawienia WooCommerce dla Polski, kategorie, atrybuty, menu i zabezpieczenia. Możesz go uruchomić ponownie – to, co już istnieje, zostanie pominięte.', 'bydopamina-setup' ); ?></p>

		<h2><?php esc_html_e( '1. Wymagania', 'bydopamina-setup' ); ?></h2>
		<table class="widefat striped" style="max-width:760px">
			<tbody>
			<?php foreach ( $reqs as $req ) : ?>
				<tr>
					<td style="width:28px"><?php echo $req['ok'] ? '✅' : '❌'; ?></td>
					<td><strong><?php echo esc_html( $req['label'] ); ?></strong><?php echo $req['ok'] ? '' : '<br><span style="color:#b32d2e">' . esc_html( $req['fix'] ) . '</span>'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( '2. Konfiguracja', 'bydopamina-setup' ); ?></h2>
		<form method="post">
			<?php wp_nonce_field( 'bydopamina_setup' ); ?>
			<p>
				<button type="submit" name="bydopamina_run" value="1" class="button button-primary button-hero" <?php disabled( ! $all_ok ); ?>>
					<?php esc_html_e( 'Uruchom konfigurację sklepu', 'bydopamina-setup' ); ?>
				</button>
			</p>
			<?php if ( ! $all_ok ) : ?>
				<p><em><?php esc_html_e( 'Przycisk włączy się, gdy wszystkie wymagania będą spełnione.', 'bydopamina-setup' ); ?></em></p>
			<?php endif; ?>
		</form>

		<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Wynik', 'bydopamina-setup' ); ?></h2>
			<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-width:760px;white-space:pre-wrap"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
		<?php endif; ?>

		<?php if ( get_option( 'bydopamina_setup_done' ) ) : ?>
			<h2><?php esc_html_e( '3. Co zostało do zrobienia ręcznie', 'bydopamina-setup' ); ?></h2>
			<ol style="max-width:760px">
				<li><?php esc_html_e( 'Dodaj produkty (zdjęcia 1:1, drugie zdjęcie na modelce), przypisz kategorie, atrybuty Kolor / Materiał / Kamień i tagi „bestseller”, „promocja”.', 'bydopamina-setup' ); ?></li>
				<li><?php esc_html_e( 'Wstaw zdjęcia: slider na stronie głównej, miniatury kategorii, banery, newsletter (Elementor → Edytuj stronę główną).', 'bydopamina-setup' ); ?></li>
				<li><?php esc_html_e( 'Uzupełnij i opublikuj strony w wersji roboczej: Regulamin, Polityka prywatności i cookies, Dostawa i płatności, Zwroty, Odstąpienie od umowy, Deklaracja dostępności.', 'bydopamina-setup' ); ?></li>
				<li><?php esc_html_e( 'Zainstaluj i podłącz: płatności (Przelewy24 / PayU), InPost, Omnibus, baner cookies, wtyczkę bezpieczeństwa z 2FA, kopie zapasowe.', 'bydopamina-setup' ); ?></li>
				<li><?php esc_html_e( 'WooCommerce → Wysyłka: ustaw strefę Polska i darmową dostawę od 199 zł.', 'bydopamina-setup' ); ?></li>
			</ol>
		<?php endif; ?>
	</div>
	<?php
}
