<?php
/** /panel/pagos/ — historial de pagos (mock). */
epc_html_open( 'Historial de pagos — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'pagos' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Historial de pagos</h1>
		<p>Pagos registrados a través de las entidades recaudadoras autorizadas. Cuenta N.° 1608353.</p>
	</div>
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
</div>
<?php
epc_panel_close();
epc_html_close();
