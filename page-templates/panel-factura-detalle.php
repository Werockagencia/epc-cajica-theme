<?php
/** /panel/factura-detalle/ — detalle de una factura puntual (mock). */
$user      = wp_get_current_user();
$cuenta_id = '1608353';
epc_html_open( 'Detalle de factura — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'facturacion' );
?>
<div class="migas"><a href="<?php echo esc_url( home_url( '/panel/facturacion/' ) ); ?>">← Volver al historial de facturación</a></div>

<?php if ( ! epc_usuario_tiene_acceso_cuenta( $user, $cuenta_id ) ) : ?>
<div class="panel-card">
	<div class="alerta aviso">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
		<span>Todavía no tienes acceso a esta cuenta. <a href="<?php echo esc_url( home_url( '/panel/mis-cuentas/' ) ); ?>">Ve el estado de tu solicitud →</a></span>
	</div>
</div>
<?php else : ?>
<div class="panel-card">
	<div class="factura-cabecera">
		<div>
			<div class="num">Factura N.° 403723943-5</div>
			<div class="fechas">
				<div><span>Fecha de expedición</span><strong>03/08/2026</strong></div>
				<div><span>Fecha de vencimiento</span><strong>13/08/2026</strong></div>
				<div><span>Estado</span><strong><span class="estado emitida">Emitida</span></strong></div>
			</div>
		</div>
		<div style="display:flex;gap:10px;flex-wrap:wrap">
			<a href="#" class="btn btn-outline">Descargar PDF</a>
			<a href="<?php echo esc_url( home_url( '/panel/pagar-factura/' ) ); ?>" class="btn btn-primario">Pagar esta factura</a>
		</div>
	</div>

	<div class="tabla-wrap" style="margin-top:24px">
		<table class="panel-tabla">
			<thead><tr><th>Concepto</th><th>Valor</th></tr></thead>
			<tbody>
				<tr><td>Cargo fijo acueducto</td><td>$12.400</td></tr>
				<tr><td>Consumo acueducto (9 m³)</td><td>$21.870</td></tr>
				<tr><td>Cargo fijo alcantarillado</td><td>$9.800</td></tr>
				<tr><td>Vertimiento alcantarillado</td><td>$14.250</td></tr>
				<tr><td>Aseo</td><td>$11.000</td></tr>
				<tr style="font-weight:700"><td>Total</td><td>$69.320</td></tr>
			</tbody>
		</table>
	</div>

	<p style="font-size:13px;color:var(--gris-texto);margin-top:18px">¿No entiendes algún cargo de tu factura? Consulta la <a href="<?php echo esc_url( home_url( '/atencion/como-leer-tu-factura/' ) ); ?>">guía de cómo leer tu factura</a>.</p>
</div>
<?php endif; ?>
<?php
epc_panel_close();
epc_html_close();
