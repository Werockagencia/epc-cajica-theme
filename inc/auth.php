<?php
/**
 * Autenticación del Portal del Ciudadano: login propio en /login/ (en vez
 * de wp-login.php) y bloqueo de /panel/* a usuarios no autenticados.
 */

// 1) Procesa el POST de /login/ (si lo hay) ANTES que cualquier otra regla
// de redirección de esta misma hook, para poder redirigir según el resultado.
add_action( 'template_redirect', function () {
	if ( ! is_page( 'login' ) || empty( $_POST['epc_login_submit'] ) ) return;

	if ( empty( $_POST['epc_login_nonce'] ) || ! wp_verify_nonce( $_POST['epc_login_nonce'], 'epc_login' ) ) {
		$GLOBALS['epc_login_error'] = 'La sesión del formulario expiró, intenta de nuevo.';
		return;
	}

	$usuario = sanitize_text_field( wp_unslash( $_POST['usuario'] ?? '' ) );
	$clave   = $_POST['clave'] ?? '';
	$user    = wp_authenticate( $usuario, $clave );

	if ( is_wp_error( $user ) ) {
		$GLOBALS['epc_login_error'] = 'Usuario o contraseña incorrectos.';
		return;
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, ! empty( $_POST['recordarme'] ) );

	// El filtro nativo 'login_redirect' de WordPress no se dispara aquí (este
	// formulario es propio, no wp-login.php) -- Comercial entra directo a su
	// panel, igual que Propietario/Arrendatario entran al suyo.
	$destino_defecto = in_array( 'epc_comercial', (array) $user->roles, true ) && ! in_array( 'administrator', (array) $user->roles, true )
		? home_url( '/panel/comercial/' )
		: home_url( '/panel/perfil/' );
	$redirect = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : $destino_defecto;
	wp_safe_redirect( $redirect );
	exit;
}, 5 );

// 2) /panel/* exige sesión iniciada.
add_action( 'template_redirect', function () {
	if ( ! epc_is_panel_page() ) return;
	if ( is_user_logged_in() ) return;

	wp_safe_redirect( home_url( '/login/?redirect_to=' . urlencode( home_url( $_SERVER['REQUEST_URI'] ) ) ) );
	exit;
}, 10 );

// 3) Ya logueado no debe ver /login/ de nuevo.
add_action( 'template_redirect', function () {
	if ( ! is_page( 'login' ) || ! is_user_logged_in() || ! empty( $_POST['epc_login_submit'] ) ) return;
	$user = wp_get_current_user();
	$destino = in_array( 'epc_comercial', (array) $user->roles, true ) && ! in_array( 'administrator', (array) $user->roles, true )
		? home_url( '/panel/comercial/' )
		: home_url( '/panel/perfil/' );
	wp_safe_redirect( $destino );
	exit;
}, 10 );

/**
 * Cierre de sesión propio (en vez de wp-login.php?action=logout).
 */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['epc_logout'] ) ) return;
	wp_logout();
	wp_safe_redirect( home_url( '/' ) );
	exit;
}, 5 );
