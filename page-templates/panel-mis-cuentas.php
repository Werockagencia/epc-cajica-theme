<?php
/**
 * /panel/mis-cuentas/ — cuentas asociadas al usuario. El detalle y las
 * acciones disponibles cambian según el rol (Propietario vs Arrendatario),
 * con datos de ejemplo listos para reemplazar por la API de Integra.
 */
$user         = wp_get_current_user();
$es_propietario = epc_user_is_propietario( $user );
$es_arrendatario = epc_user_is_arrendatario( $user );

epc_html_open( 'Mis cuentas — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'mis-cuentas' );
?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Mis cuentas</h1>
		<p>Selecciona una cuenta para ver su detalle. Si tienes más de un predio o más de un medidor de agua, puedes asociarlos todos aquí.</p>
	</div>

	<div class="cuenta-card">
		<div class="cuenta-card-info">
			<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 11l8-7 8 7"/><path d="M6 9v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9"/></svg></div>
			<div>
				<div class="cuenta-card-nombre"><?php echo esc_html( $user->display_name ); ?></div>
				<div class="cuenta-card-detalle">Cuenta N.° 1608353 · DG 2 Sur No 12-150</div>
				<div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
					<span class="badge-cuenta"><span class="punto"></span>Medidor MED-04471</span>
					<span class="rol-badge"><?php echo esc_html( epc_user_role_label( $user ) ); ?> de esta cuenta</span>
				</div>
			</div>
		</div>
		<div class="cuenta-card-acciones">
			<button class="btn btn-outline" data-toggle-cuenta="1608353" aria-expanded="false">Ver detalle
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		</div>
	</div>

	<div class="cuenta-detalle" id="detalle-1608353" hidden>
		<h3>Datos de la cuenta</h3>
		<div class="datos-cuenta-grid">
			<div><span>Titular</span><strong><?php echo $es_propietario ? esc_html( $user->display_name ) : 'Mercedes de Higuera'; ?></strong></div>
			<div><span>Dirección del predio</span><strong>DG 2 Sur No 12-150</strong></div>
			<div><span>Estrato</span><strong>4</strong></div>
			<div><span>Tipo de uso</span><strong>Residencial</strong></div>
		</div>

		<h3>Medidores</h3>
		<div class="medidor-fila">
			<div>
				<strong style="font-size:13.5px;color:var(--azul-oscuro)">MED-04471</strong>
				<div style="font-size:12px;color:var(--gris-texto);margin-top:2px">Principal</div>
			</div>
			<?php if ( $es_arrendatario ) : ?>
				<span class="badge-cuenta" style="background:var(--gris-claro)">Vinculado a tu cuenta</span>
			<?php endif; ?>
		</div>

		<?php if ( $es_arrendatario ) : ?>
			<button class="btn-agregar-medidor" data-toggle-form="medidor-1608353">+ Asociar otro medidor a mi cuenta</button>
			<div class="form-inline" id="form-medidor-1608353" hidden>
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

	<button class="btn-agregar-medidor" data-toggle-form="nueva-cuenta" style="margin-top:22px">+ Vincular otra cuenta</button>
	<div class="form-inline" id="form-nueva-cuenta" hidden>
		<div class="campo-fila">
			<div class="campo"><label>Número de cuenta</label><input type="text" placeholder="Ej. 1608353"></div>
			<div class="campo"><label>Dígito de verificación</label><input type="text" placeholder="DV"></div>
		</div>
		<p style="font-size:12.5px;color:var(--gris-texto);margin-bottom:14px">¿Eres el propietario de este predio? Después de vincular la cuenta podrás validar tu titularidad desde <a href="<?php echo esc_url( home_url( '/panel/perfil/' ) ); ?>">tu perfil</a> para desbloquear acuerdos de pago y cambio de suscriptor.</p>
		<button class="btn btn-azul" type="button">Buscar y vincular cuenta</button>
	</div>
</div>

<?php
epc_panel_close();
epc_html_close();
