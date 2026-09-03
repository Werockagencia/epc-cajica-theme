<?php
/**
 * /panel/perfil/ — datos de la cuenta del usuario autenticado.
 */
$user = wp_get_current_user();

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

<?php if ( ! epc_user_is_propietario( $user ) ) : ?>
<div class="panel-card" style="background:linear-gradient(135deg,#f3f7f6,#eaf4e5);border:1px dashed var(--verde-oscuro)">
	<div class="panel-card-header">
		<h2>Valida tu cuenta como propietario</h2>
		<p>Si eres el titular del predio, valida tu cuenta como propietario para poder gestionar acuerdos de pago y cambio de suscriptor. Necesitarás tu certificado de tradición y libertad o tu último recibo pagado.</p>
	</div>
	<button class="btn btn-outline" type="button">Iniciar validación de propietario</button>
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
