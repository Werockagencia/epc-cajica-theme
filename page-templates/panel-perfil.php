<?php
/**
 * /panel/perfil/ — datos de la cuenta del usuario autenticado.
 */
$user      = wp_get_current_user();
$cuenta_id = '1608353';
$es_staff  = in_array( 'epc_comercial', (array) $user->roles, true ) || in_array( 'administrator', (array) $user->roles, true );

if ( ! empty( $_POST['epc_validacion_submit'] ) && wp_verify_nonce( $_POST['epc_validacion_nonce'] ?? '', 'epc_validacion_propietario' ) ) {
	$doc_id = 0;
	if ( ! empty( $_FILES['epc_validacion_doc']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_id = media_handle_upload( 'epc_validacion_doc', 0 );
		if ( ! is_wp_error( $attachment_id ) ) {
			$doc_id = $attachment_id;
		}
	}
	epc_solicitar_validacion_propietario( $user->ID, $doc_id, $cuenta_id );
	wp_safe_redirect( home_url( '/panel/perfil/?validacion=enviada' ) );
	exit;
}

epc_html_open( 'Mi perfil — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'perfil' );
?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Mi perfil</h1>
		<p>Estos son los datos registrados en tu cuenta. Puedes actualizar tu usuario modificando tu correo o celular, y crear una nueva contraseña.</p>
	</div>

	<div class="campo-fila">
		<div class="campo">
			<label>Tipo de documento</label>
			<input type="text" value="Cédula de ciudadanía" disabled>
		</div>
		<div class="campo">
			<label>Número de documento</label>
			<input type="text" value="<?php echo esc_attr( $user->user_login ); ?>" disabled>
		</div>
	</div>
	<div class="campo-fila">
		<div class="campo">
			<label for="nombre">Nombre <span class="req">*</span></label>
			<input type="text" id="nombre" value="<?php echo esc_attr( $user->first_name ); ?>">
		</div>
		<div class="campo">
			<label for="apellido">Apellido <span class="req">*</span></label>
			<input type="text" id="apellido" value="<?php echo esc_attr( $user->last_name ); ?>">
		</div>
	</div>
	<div class="campo">
		<label for="canal">Canal preferente de contacto</label>
		<select id="canal">
			<option>WhatsApp</option>
			<option>Correo electrónico</option>
			<option>SMS</option>
			<option>Llamada</option>
		</select>
	</div>
	<button class="btn btn-primario" type="button">Actualizar canal preferente</button>
</div>

<?php if ( ! $es_staff && ! epc_user_is_propietario( $user ) ) :
	$estado_validacion = epc_validacion_propietario_estado( $user->ID );
?>
<div class="panel-card" style="background:linear-gradient(135deg,#f3f7f6,#eaf4e5);border:1px dashed var(--verde-oscuro)">

	<?php if ( 'pendiente' === $estado_validacion ) : ?>
		<div class="panel-card-header">
			<h2>Validación de propietario en revisión</h2>
			<p>Tu solicitud ya fue radicada y está siendo revisada por el equipo Comercial de la EPC. Te avisaremos cuando la aprueben.</p>
		</div>
		<span class="estado emitida">En revisión</span>

	<?php else : ?>
		<div class="panel-card-header">
			<h2>Valida tu cuenta como propietario</h2>
			<p>Si eres el titular del predio, valida tu cuenta como propietario para poder gestionar acuerdos de pago y cambio de suscriptor. Necesitarás tu certificado de tradición y libertad o tu último recibo pagado.</p>
		</div>

		<?php if ( 'rechazado' === $estado_validacion ) : ?>
			<div class="alerta aviso" style="margin-bottom:16px">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
				<span>Tu solicitud anterior no fue aprobada. Verifica tu documento y vuelve a intentarlo.</span>
			</div>
		<?php endif; ?>

		<button class="btn btn-outline" type="button" data-toggle-form="form-validacion-propietario">Iniciar validación de propietario</button>

		<div class="form-inline" id="form-form-validacion-propietario" hidden>
			<form method="post" enctype="multipart/form-data">
				<div class="campo">
					<label>Documento soporte <span class="req">*</span></label>
					<div class="adjuntar">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M7 8l5-5 5 5"/><path d="M5 21h14"/></svg>
						<div class="txt"><strong>Sube tu certificado</strong> de tradición y libertad o tu último recibo pagado — PDF, PNG o JPG</div>
					</div>
					<input type="file" name="epc_validacion_doc" accept="application/pdf,image/*" required>
				</div>
				<?php wp_nonce_field( 'epc_validacion_propietario', 'epc_validacion_nonce' ); ?>
				<input type="hidden" name="epc_validacion_submit" value="1">
				<button class="btn btn-azul" type="submit">Enviar para revisión</button>
			</form>
		</div>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="panel-card">
	<div class="panel-card-header">
		<h2>Usuario</h2>
		<p>Correo y celular con los que inicias sesión y recibes notificaciones del servicio.</p>
	</div>
	<div class="campo-fila">
		<div class="campo">
			<label for="correo">Correo electrónico <span class="req">*</span></label>
			<input type="email" id="correo" value="<?php echo esc_attr( $user->user_email ); ?>">
		</div>
		<div class="campo">
			<label for="celular">Celular <span class="req">*</span></label>
			<input type="tel" id="celular" value="<?php echo esc_attr( get_user_meta( $user->ID, 'epc_celular', true ) ); ?>">
		</div>
	</div>
	<button class="btn btn-outline" type="button">Guardar cambios</button>
</div>

<div class="panel-card">
	<div class="acordeon-item">
		<button class="acordeon-btn" type="button">
			Cambiar contraseña
			<span class="icono-mas">+</span>
		</button>
		<div class="acordeon-body">
			<div class="campo">
				<label for="clave-actual">Contraseña actual</label>
				<input type="password" id="clave-actual" placeholder="••••••••">
			</div>
			<div class="campo-fila">
				<div class="campo">
					<label for="clave-nueva">Nueva contraseña</label>
					<input type="password" id="clave-nueva" placeholder="Mínimo 8 caracteres">
				</div>
				<div class="campo">
					<label for="clave-confirmar">Confirmar contraseña</label>
					<input type="password" id="clave-confirmar" placeholder="Repite la contraseña">
				</div>
			</div>
			<button class="btn btn-azul" type="button">Actualizar contraseña</button>
		</div>
	</div>
</div>

<?php
epc_panel_close();
epc_html_close();
