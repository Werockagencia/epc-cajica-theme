<?php
/** /panel/pagos/ — historial de pagos (mock). */
$user      = wp_get_current_user();
$cuenta_id = '1608353';
epc_html_open( 'Historial de pagos — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'pagos' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Historial de pagos</h1>
		<p>Pagos registrados a través de las entidades recaudadoras autorizadas. Cuenta N.° 1608353.</p>
	</div>
	<?php if ( ! epc_usuario_tiene_acceso_cuenta( $user, $cuenta_id ) ) : ?>
	<div class="alerta aviso" style="margin-top:22px">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
		<span>Todavía no tienes acceso a esta cuenta. <a href="<?php echo esc_url( home_url( '/panel/mis-cuentas/' ) ); ?>">Ve el estado de tu solicitud →</a></span>
	</div>
	<?php else : ?>
	<div class="tabla-wrap">
		<table class="panel-tabla">
			<thead><tr><th>N.° documento</th><th>Fecha de pago</th><th>Entidad recaudadora</th><th>Descripción</th><th>Valor pagado</th></tr></thead>
			<tbody>
				<tr><td>400189297</td><td>15/07/2026</td><td>PSE</td><td>Factura</td><td>$114.110</td></tr>
				<tr><td>396668140</td><td>16/06/2026</td><td>PSE</td><td>Factura</td><td>$102.630</td></tr>
				<tr><td>393157565</td><td>11/05/2026</td><td>PSE</td><td>Factura</td><td>$161.500</td></tr>
				<tr><td>389608845</td><td>10/04/2026</td><td>Bancolombia</td><td>Factura</td><td>$83.090</td></tr>
				<tr><td>386110457</td><td>10/03/2026</td><td>PSE</td><td>Factura</td><td>$128.870</td></tr>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
