<?php
/**
 * /panel/mis-cuentas/ — cuentas asociadas al usuario. El detalle y las
 * acciones disponibles cambian según el rol (Propietario vs Arrendatario):
 * - El arrendatario SOLICITA vincularse a una cuenta; queda "Pendiente"
 *   hasta que el propietario de esa cuenta lo apruebe desde aquí mismo.
 * - El propietario ve esas solicitudes, aprueba/rechaza, y puede quitarle
 *   el acceso a un arrendatario ya aprobado en cualquier momento.
 * Cuenta de ejemplo: 1608353 (mock, lista para reemplazar por Integra).
 */
$user            = wp_get_current_user();
$cuenta_id       = '1608353';
$es_propietario  = epc_user_is_propietario( $user );
$es_arrendatario = epc_user_is_arrendatario( $user );

// Acciones del propietario sobre solicitudes de arrendatarios.
if ( $es_propietario && ! empty( $_POST['epc_vinculo_accion'] ) && wp_verify_nonce( $_POST['epc_vinculo_nonce'] ?? '', 'epc_vinculo' ) ) {
	$target_id = absint( $_POST['epc_vinculo_user'] ?? 0 );
	$accion    = sanitize_key( $_POST['epc_vinculo_accion'] );
	$mapa      = [ 'aprobar' => 'aprobado', 'rechazar' => 'quitado', 'quitar' => 'quitado' ];
	if ( $target_id && isset( $mapa[ $accion ] ) ) {
		epc_vinculo_set_estado( $target_id, $cuenta_id, $mapa[ $accion ] );
	}
}

// Solicitud del arrendatario para vincularse a la cuenta.
$solicitud_enviada = false;
if ( $es_arrendatario && ! empty( $_POST['epc_solicitar_vinculo'] ) && wp_verify_nonce( $_POST['epc_solicitar_nonce'] ?? '', 'epc_solicitar' ) ) {
	epc_vinculo_solicitar( $user->ID, $cuenta_id );
	$solicitud_enviada = true;
}

$mi_estado = $es_arrendatario ? epc_vinculo_estado( $user->ID, $cuenta_id ) : null;

epc_html_open( 'Mis cuentas — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'mis-cuentas' );
?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Mis cuentas</h1>
		<p>Selecciona una cuenta para ver su detalle. Si tienes más de un predio o más de un medidor de agua, puedes asociarlos todos aquí.</p>
	</div>

	<?php if ( $es_arrendatario && 'aprobado' !== $mi_estado ) : ?>

		<?php if ( $solicitud_enviada || 'pendiente' === $mi_estado ) : ?>
			<div class="panel-card" style="background:#fff7ed;border:1px solid #fbd9a8">
				<strong style="color:var(--azul-oscuro);font-size:14.5px">Solicitud enviada — pendiente de aprobación</strong>
				<p style="font-size:13px;color:var(--gris-texto);margin-top:6px">El propietario de la cuenta <?php echo esc_html( $cuenta_id ); ?> debe aprobar tu acceso antes de que puedas ver facturas, consumo o pagos. Te avisaremos cuando responda.</p>
			</div>
		<?php else : ?>
			<div class="cuenta-card">
				<div class="cuenta-card-info">
					<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11l8-7 8 7"/><path d="M6 9v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9"/></svg></div>
					<div><div class="cuenta-card-nombre">Vincularme como arrendatario</div><div class="cuenta-card-detalle">Cuenta N.° <?php echo esc_html( $cuenta_id ); ?> · DG 2 Sur No 12-150</div></div>
				</div>
			</div>
			<form method="post" style="margin-top:16px">
				<p style="font-size:12.5px;color:var(--gris-texto);margin-bottom:12px">Al solicitar, el propietario de esta cuenta recibirá tu solicitud y debe aprobarla antes de que tengas acceso.</p>
				<?php wp_nonce_field( 'epc_solicitar', 'epc_solicitar_nonce' ); ?>
				<input type="hidden" name="epc_solicitar_vinculo" value="1">
				<button class="btn btn-azul" type="submit">Solicitar vinculación como arrendatario</button>
			</form>
		<?php endif; ?>

	<?php else : ?>

		<div class="cuenta-card">
			<div class="cuenta-card-info">
				<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11l8-7 8 7"/><path d="M6 9v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9"/></svg></div>
				<div>
					<div class="cuenta-card-nombre"><?php echo esc_html( $user->display_name ); ?></div>
					<div class="cuenta-card-detalle">Cuenta N.° <?php echo esc_html( $cuenta_id ); ?> · DG 2 Sur No 12-150</div>
					<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
						<span class="badge-cuenta"><span class="punto"></span>Medidor MED-04471</span>
						<span class="rol-badge"><?php echo esc_html( epc_user_role_label( $user ) ); ?> de esta cuenta</span>
					</div>
				</div>
			</div>
			<div class="cuenta-card-acciones">
				<button class="btn btn-outline" data-toggle-cuenta="<?php echo esc_attr( $cuenta_id ); ?>" aria-expanded="false">Ver detalle
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
				</button>
			</div>
		</div>

		<div class="cuenta-detalle" id="detalle-<?php echo esc_attr( $cuenta_id ); ?>" hidden>
			<h3>Datos de la cuenta</h3>
			<div class="datos-cuenta-grid">
				<div><span>Titular</span><strong><?php echo $es_propietario ? esc_html( $user->display_name ) : 'Mercedes de Higuera'; ?></strong></div>
				<div><span>Dirección del predio</span><strong>DG 2 Sur No 12-150</strong></div>
				<div><span>Estrato</span><strong>4</strong></div>
				<div><span>Tipo de uso</span><strong>Residencial</strong></div>
			</div>

			<h3>Medidores</h3>
			<div class="medidor-fila">
				<div><strong style="font-size:13.5px;color:var(--azul-oscuro)">MED-04471</strong><div style="font-size:12px;color:var(--gris-texto);margin-top:2px">Principal</div></div>
				<?php if ( $es_arrendatario ) : ?><span class="badge-cuenta" style="background:var(--gris-claro)">Vinculado a tu cuenta</span><?php endif; ?>
			</div>

			<?php if ( $es_arrendatario ) : ?>
				<button class="btn-agregar-medidor" data-toggle-form="medidor-<?php echo esc_attr( $cuenta_id ); ?>">+ Asociar otro medidor a mi cuenta</button>
				<div class="form-inline" id="form-medidor-<?php echo esc_attr( $cuenta_id ); ?>" hidden>
					<div class="campo-fila">
						<div class="campo"><label>Número de medidor</label><input type="text" placeholder="Ej. MED-04471"></div>
						<div class="campo"><label>Tipo</label><select><option>Principal</option><option>Secundario</option><option>Riego</option></select></div>
					</div>
					<button class="btn btn-azul" type="button">Guardar medidor</button>
				</div>

				<h3>Automatización de pagos</h3>
				<p style="font-size:13.5px;color:var(--gris-texto);margin-bottom:14px">Como arrendatario puedes prepagar tu factura o activar el pago automático para no pasarte de la fecha de vencimiento.</p>
				<div style="display:flex;gap:10px;flex-wrap:wrap">
					<button class="btn btn-azul" type="button">Activar pago automático</button>
					<a href="<?php echo esc_url( home_url( '/panel/pagar-factura/' ) ); ?>" class="btn btn-outline">Pago inmediato</a>
				</div>
			<?php endif; ?>

			<?php if ( $es_propietario ) : ?>
				<h3>Trámites exclusivos del propietario</h3>
				<p style="font-size:13.5px;color:var(--gris-texto);margin-bottom:14px">Por ser el titular registrado de este predio, eres la única persona que puede gestionar estos trámites.</p>
				<div style="display:flex;gap:10px;flex-wrap:wrap">
					<button class="btn btn-azul" type="button">Acuerdo de pago</button>
					<button class="btn btn-outline" type="button">Cambio de suscriptor</button>
				</div>
			<?php endif; ?>

			<h3>Preferencias de notificación</h3>
			<div class="campo-fila">
				<div class="campo"><label>Correo para factura electrónica</label><input type="email" value="<?php echo esc_attr( $user->user_email ); ?>"></div>
				<div class="campo"><label>Dirección de notificación</label><input type="text" value="DG 2 Sur No 12-150, Cajicá"></div>
			</div>
			<button class="btn btn-azul" type="button">Guardar cambios</button>

			<div class="cuenta-detalle-accesos">
				<a href="<?php echo esc_url( home_url( '/panel/facturacion/' ) ); ?>" class="btn btn-outline">Historial de facturación</a>
				<a href="<?php echo esc_url( home_url( '/panel/consumo/' ) ); ?>" class="btn btn-outline">Historial de consumo</a>
				<a href="<?php echo esc_url( home_url( '/panel/pagos/' ) ); ?>" class="btn btn-outline">Historial de pagos</a>
			</div>
		</div>

	<?php endif; ?>
</div>

<?php if ( $es_propietario ) :
	$vinculos    = epc_vinculos_de_cuenta( $cuenta_id );
	$pendientes  = array_filter( $vinculos, fn( $v ) => 'pendiente' === $v['estado'] );
	$aprobados   = array_filter( $vinculos, fn( $v ) => 'aprobado' === $v['estado'] );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h2>Solicitudes de arrendatarios</h2>
		<p>Solo tú, como propietario validado de la cuenta <?php echo esc_html( $cuenta_id ); ?>, puedes aprobar o quitar el acceso de arrendatarios.</p>
	</div>

	<?php if ( ! $pendientes ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">No tienes solicitudes pendientes.</p>
	<?php else : foreach ( $pendientes as $v ) : ?>
		<div class="medidor-fila">
			<div><strong style="font-size:13.5px;color:var(--azul-oscuro)"><?php echo esc_html( $v['user']->display_name ); ?></strong><div style="font-size:12px;color:var(--gris-texto)">Documento: <?php echo esc_html( $v['user']->user_login ); ?> · Pendiente de aprobación</div></div>
			<div style="display:flex;gap:8px">
				<form method="post"><?php wp_nonce_field( 'epc_vinculo', 'epc_vinculo_nonce' ); ?><input type="hidden" name="epc_vinculo_user" value="<?php echo esc_attr( $v['user']->ID ); ?>"><input type="hidden" name="epc_vinculo_accion" value="aprobar"><button class="btn-mini" type="submit">Aprobar</button></form>
				<form method="post"><?php wp_nonce_field( 'epc_vinculo', 'epc_vinculo_nonce' ); ?><input type="hidden" name="epc_vinculo_user" value="<?php echo esc_attr( $v['user']->ID ); ?>"><input type="hidden" name="epc_vinculo_accion" value="rechazar"><button class="btn-mini" style="background:transparent;border:1px solid rgba(214,69,69,.35);color:var(--rojo)" type="submit">Rechazar</button></form>
			</div>
		</div>
	<?php endforeach; endif; ?>

	<h3 style="margin-top:22px">Arrendatarios con acceso</h3>
	<?php if ( ! $aprobados ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">Todavía no le has dado acceso a ningún arrendatario.</p>
	<?php else : foreach ( $aprobados as $v ) : ?>
		<div class="medidor-fila">
			<div><strong style="font-size:13.5px;color:var(--azul-oscuro)"><?php echo esc_html( $v['user']->display_name ); ?></strong><div style="font-size:12px;color:var(--gris-texto)">Acceso activo · Puede ver facturas, consumo y pagar</div></div>
			<form method="post"><?php wp_nonce_field( 'epc_vinculo', 'epc_vinculo_nonce' ); ?><input type="hidden" name="epc_vinculo_user" value="<?php echo esc_attr( $v['user']->ID ); ?>"><input type="hidden" name="epc_vinculo_accion" value="quitar"><button class="btn-mini" style="background:transparent;border:1px solid rgba(214,69,69,.35);color:var(--rojo)" type="submit">Quitar acceso</button></form>
		</div>
	<?php endforeach; endif; ?>
</div>
<?php endif; ?>

<?php
epc_panel_close();
epc_html_close();
