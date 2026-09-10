<?php
/**
 * /panel/comercial-usuarios/ — todas las cuentas de ciudadanos (Usuario sin
 * validar, Propietario, Arrendatario), para que Comercial pueda corregir un
 * rol a mano cuando haga falta -- ej. quitarle Propietario a una cuenta por
 * error o mala validación, sin depender solo del flujo de aprobación.
 */
if ( ! empty( $_POST['epc_usuario_rol_submit'] ) && wp_verify_nonce( $_POST['epc_usuario_rol_nonce'] ?? '', 'epc_usuario_rol' ) ) {
	$target_id = (int) ( $_POST['user_id'] ?? 0 );
	$nuevo_rol = sanitize_key( $_POST['nuevo_rol'] ?? '' );
	$roles_validos = [ 'epc_usuario', 'epc_propietario', 'epc_arrendatario' ];
	$target = get_user_by( 'id', $target_id );
	if ( $target && in_array( $nuevo_rol, $roles_validos, true ) && array_intersect( $roles_validos, (array) $target->roles ) ) {
		$target->set_role( $nuevo_rol );
	}
	wp_safe_redirect( home_url( '/panel/comercial-usuarios/?guardado=1' ) );
	exit;
}

$roles_ciudadano = [ 'epc_usuario', 'epc_propietario', 'epc_arrendatario' ];
$usuarios = get_users( [ 'role__in' => $roles_ciudadano, 'orderby' => 'registered', 'order' => 'DESC' ] );

epc_html_open( 'Gestión de usuarios — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'comercial-usuarios' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Gestión de usuarios</h1>
		<p>Todas las cuentas de ciudadanos del portal. Puedes corregir el rol de una cuenta a mano — por ejemplo, quitarle "Propietario" a una cuenta validada por error.</p>
	</div>

	<?php if ( ! empty( $_GET['guardado'] ) ) : ?>
	<div class="alerta exito">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
		<span>Rol actualizado.</span>
	</div>
	<?php endif; ?>

	<div class="tabla-wrap">
		<table class="panel-tabla">
			<thead><tr><th>Usuario</th><th>Documento</th><th>Rol actual</th><th>Registrado</th><th>Cambiar rol</th></tr></thead>
			<tbody>
			<?php foreach ( $usuarios as $u ) : ?>
				<tr>
					<td><?php echo esc_html( $u->display_name ); ?></td>
					<td><?php echo esc_html( $u->user_login ); ?></td>
					<td><span class="estado emitida"><?php echo esc_html( epc_user_role_label( $u ) ); ?></span></td>
					<td><?php echo esc_html( mysql2date( 'd/m/Y', $u->user_registered ) ); ?></td>
					<td>
						<form method="post" style="display:flex;gap:8px;align-items:center">
							<select name="nuevo_rol" style="padding:8px 10px;border-radius:8px;border:1.5px solid var(--gris-borde);font-size:12.5px">
								<option value="epc_usuario" <?php selected( in_array( 'epc_usuario', (array) $u->roles, true ) ); ?>>Usuario (sin validar)</option>
								<option value="epc_propietario" <?php selected( in_array( 'epc_propietario', (array) $u->roles, true ) ); ?>>Propietario</option>
								<option value="epc_arrendatario" <?php selected( in_array( 'epc_arrendatario', (array) $u->roles, true ) ); ?>>Arrendatario</option>
							</select>
							<input type="hidden" name="user_id" value="<?php echo esc_attr( $u->ID ); ?>">
							<?php wp_nonce_field( 'epc_usuario_rol', 'epc_usuario_rol_nonce' ); ?>
							<input type="hidden" name="epc_usuario_rol_submit" value="1">
							<button class="btn-mini" type="submit">Guardar</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php
epc_panel_close();
epc_html_close();
