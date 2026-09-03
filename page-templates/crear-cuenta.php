<?php
/**
 * /crear-cuenta/ — registro público. Crea la cuenta con rol base
 * "epc_usuario" (sin validar como propietario todavía); la validación de
 * propietario se pide después, desde /panel/perfil/.
 */
$error = null;
if ( ! empty( $_POST['epc_registro_submit'] ) && wp_verify_nonce( $_POST['epc_registro_nonce'] ?? '', 'epc_registro' ) ) {
	$documento = sanitize_text_field( wp_unslash( $_POST['documento'] ?? '' ) );
	$nombre    = sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) );
	$apellido  = sanitize_text_field( wp_unslash( $_POST['apellido'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$clave     = $_POST['clave'] ?? '';

	if ( empty( $documento ) || empty( $email ) || strlen( $clave ) < 8 ) {
		$error = 'Revisa los campos: la contraseña debe tener mínimo 8 caracteres.';
	} elseif ( username_exists( $documento ) ) {
		$error = 'Ya existe una cuenta con ese número de documento.';
	} else {
		$id = wp_insert_user( [
			'user_login' => $documento,
			'user_pass'  => $clave,
			'user_email' => $email,
			'first_name' => $nombre,
			'last_name'  => $apellido,
			'role'       => 'epc_usuario',
		] );
		if ( is_wp_error( $id ) ) {
			$error = $id->get_error_message();
		} else {
			wp_set_current_user( $id );
			wp_set_auth_cookie( $id );
			wp_safe_redirect( home_url( '/panel/perfil/' ) );
			exit;
		}
	}
}

epc_html_open( 'Crear cuenta — Portal del Ciudadano · EPC Cajicá' );
?>
<div class="gov-bar">
	<div class="gov-bar-inner">
		<a href="https://www.gov.co" class="gov-logo" target="_blank" rel="noopener"><span class="gov-tricolor"><i></i><i></i><i></i></span><span class="gov-wordmark">GOV.CO</span></a>
	</div>
</div>
<div class="login-wrap">
	<div class="login-visual">
		<h2>Crea tu cuenta en un par de minutos.</h2>
		<p>Con tu cuenta puedes radicar PQRS con seguimiento, pagar tus facturas y vincular los predios de los que eres propietario o arrendatario.</p>
	</div>
	<div class="login-form-wrap">
		<form class="login-form" method="post">
			<img class="logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="EPC">
			<h1>Regístrate</h1>
			<p class="sub">No necesitas tener una cuenta/predio a tu nombre para crear tu usuario.</p>

			<?php if ( $error ) : ?>
				<p style="background:#fdecea;color:#b3261e;padding:12px 16px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:16px;"><?php echo esc_html( $error ); ?></p>
			<?php endif; ?>

			<div class="campo"><label>Número de documento <span class="req">*</span></label><input type="text" name="documento" required></div>
			<div class="campo-fila">
				<div class="campo"><label>Nombre <span class="req">*</span></label><input type="text" name="nombre" required></div>
				<div class="campo"><label>Apellido <span class="req">*</span></label><input type="text" name="apellido" required></div>
			</div>
			<div class="campo"><label>Correo electrónico <span class="req">*</span></label><input type="email" name="email" required></div>
			<div class="campo"><label>Contraseña <span class="req">*</span></label><input type="password" name="clave" placeholder="Mínimo 8 caracteres" required></div>

			<label class="campo-check" style="margin-bottom:22px">
				<input type="checkbox" required>
				Autorizo el tratamiento de mis datos personales conforme a la <a href="<?php echo esc_url( home_url( '/politica-tratamiento-datos/' ) ); ?>">política de tratamiento de datos</a> <span class="req">*</span>
			</label>

			<?php wp_nonce_field( 'epc_registro', 'epc_registro_nonce' ); ?>
			<input type="hidden" name="epc_registro_submit" value="1">
			<button type="submit" class="btn btn-azul" style="width:100%;justify-content:center;margin-top:6px;padding:14px">Crear mi cuenta</button>

			<div class="login-divisor">o</div>
			<p class="login-registro">¿Ya tienes cuenta? <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">Inicia sesión</a></p>
		</form>
	</div>
</div>
<?php
epc_html_close();
