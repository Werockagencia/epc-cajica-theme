<?php
/** /panel/facturacion/ — historial de facturación (datos de ejemplo). */
epc_html_open( 'Historial de facturación — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'facturacion' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Historial de facturación</h1>
		<p>Consulta y descarga tus facturas emitidas por acueducto, alcantarillado y aseo. Cuenta N.° 1608353 · DG 2 Sur No 12-150.</p>
	</div>

	<div class="stat-grid" style="margin-top:22px">
		<div class="stat"><div class="valor">$69.320</div><div class="lbl">Última factura</div></div>
		<div class="stat"><div class="valor">13 ago 2026</div><div class="lbl">Próximo vencimiento</div></div>
		<div class="stat"><div class="valor acento">1</div><div class="lbl">Factura pendiente</div></div>
		<div class="stat"><div class="valor">$789.150</div><div class="lbl">Facturado en 2026</div></div>
	</div>

	<div class="tabla-wrap">
		<table class="panel-tabla">
			<thead><tr><th>N.° factura</th><th>Fecha de expedición</th><th>Valor</th><th>Fecha de vencimiento</th><th>Estado</th><th>Factura</th></tr></thead>
			<tbody>
				<tr><td>403723943-5</td><td>03/08/2026</td><td>$69.320</td><td>13/08/2026</td><td><span class="estado emitida">Emitida</span></td><td><a class="enlace-tabla" href="<?php echo esc_url( home_url('/panel/factura-detalle/') ); ?>">Ver →</a> <a class="btn-mini" href="<?php echo esc_url( home_url('/panel/pagar-factura/') ); ?>">Pagar</a></td></tr>
				<tr><td>400189297-7</td><td>01/07/2026</td><td>$114.110</td><td>10/07/2026</td><td><span class="estado pagado">Pagada</span></td><td><a class="enlace-tabla" href="<?php echo esc_url( home_url('/panel/factura-detalle/') ); ?>">Ver →</a></td></tr>
				<tr><td>396668140-7</td><td>01/06/2026</td><td>$102.630</td><td>11/06/2026</td><td><span class="estado pagado">Pagada</span></td><td><a class="enlace-tabla" href="<?php echo esc_url( home_url('/panel/factura-detalle/') ); ?>">Ver →</a></td></tr>
				<tr><td>393157565-1</td><td>04/05/2026</td><td>$161.500</td><td>13/05/2026</td><td><span class="estado pagado">Pagada</span></td><td><a class="enlace-tabla" href="<?php echo esc_url( home_url('/panel/factura-detalle/') ); ?>">Ver →</a></td></tr>
				<tr><td>389608845-4</td><td>01/04/2026</td><td>$83.090</td><td>14/04/2026</td><td><span class="estado pagado">Pagada</span></td><td><a class="enlace-tabla" href="<?php echo esc_url( home_url('/panel/factura-detalle/') ); ?>">Ver →</a></td></tr>
			</tbody>
		</table>
	</div>
</div>
<?php
epc_panel_close();
epc_html_close();
