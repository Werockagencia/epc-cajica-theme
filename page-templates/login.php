<?php
/**
 * /login/ — login propio del Portal del Ciudadano (wp_authenticate, no
 * wp-login.php). El POST se procesa en inc/auth.php (template_redirect,
 * prioridad 5) antes de que este archivo se renderice.
 */

$error       = $GLOBALS['epc_login_error'] ?? null;
$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/panel/perfil/' );

epc_html_open( 'Iniciar sesión — Portal del Ciudadano · EPC Cajicá' );
?>
<div class="gov-bar">
	<div class="gov-bar-inner">
		<a href="https://www.gov.co" class="gov-logo" target="_blank" rel="noopener"><span class="gov-tricolor"><i></i><i></i><i></i></span><span class="gov-wordmark">GOV.CO</span></a>
	</div>
</div>

<div class="login-wrap">
	<div class="login-visual">
		<h2>Tu cuenta, tu servicio, tu ciudad.</h2>
		<p>Consulta tus facturas, tu consumo y tus pagos, radica PQRS y gestiona tus trámites desde un solo lugar — el Portal del Ciudadano de la Empresa de Servicios Públicos de Cajicá.</p>
	</div>

	<div class="login-form-wrap">
		<form class="login-form" method="post">
			<img class="logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="EPC — Empresa de Servicios Públicos de Cajicá">
			<h1>Inicia sesión</h1>
			<p class="sub">Ingresa con tu usuario y contraseña del portal.</p>

			<?php if ( $error ) : ?>
				<p style="background:#fdecea;color:#b3261e;padding:12px 16px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:16px;"><?php echo esc_html( $error ); ?></p>
			<?php endif; ?>

			<div class="campo">
				<label for="usuario">Número de cuenta o documento <span class="req">*</span></label>
				<input type="text" id="usuario" name="usuario" placeholder="Ej. 1070010122" required>
			</div>
			<div class="campo">
				<label for="clave">Contraseña <span class="req">*</span></label>
				<input type="password" id="clave" name="clave" placeholder="••••••••" required>
			</div>

			<div class="login-links">
				<label class="campo-check"><input type="checkbox" name="recordarme" checked> Recuérdame</label>
				<a href="#">¿Olvidaste tu contraseña?</a>
			</div>

			<?php wp_nonce_field( 'epc_login', 'epc_login_nonce' ); ?>
			<input type="hidden" name="epc_login_submit" value="1">
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

			<button type="submit" class="btn btn-azul" style="width:100%;justify-content:center;margin-top:26px;padding:14px">
				Iniciar sesión
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</button>

			<div class="login-divisor">o</div>

			<p class="login-registro">¿Todavía no tienes cuenta en el portal? <a href="<?php echo esc_url( home_url( '/crear-cuenta/' ) ); ?>">Regístrate con tu número de cuenta</a></p>
		</form>
	</div>
</div>
<?php
epc_html_close();
