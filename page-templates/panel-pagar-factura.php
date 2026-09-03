<?php
/** /panel/pagar-factura/ — pago de factura (mock, listo para la URL de pago de Integra). */
epc_html_open( 'Pagar factura — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'pagar-factura' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Pagar factura</h1>
		<p>Paga en línea, de forma rápida y segura, a través de la pasarela de pagos autorizada por la EPC. Cuenta N.° 1608353.</p>
	</div>

	<div class="tabla-wrap">
		<table class="panel-tabla">
			<thead><tr><th></th><th>N.° factura</th><th>Tipo</th><th>Valor</th><th>Fecha de vencimiento</th></tr></thead>
			<tbody>
				<tr>
					<td><input type="checkbox" checked style="width:17px;height:17px;accent-color:var(--azul)"></td>
					<td>403723943-5</td>
					<td>Factura de servicios</td>
					<td>$69.320</td>
					<td>13/08/2026</td>
				</tr>
			</tbody>
		</table>
	</div>

	<h2 style="font-size:16px;margin-top:30px;margin-bottom:4px">Selecciona el medio de pago</h2>
	<p style="font-size:13px;color:var(--gris-texto)">Todos los medios son procesados de forma segura por la pasarela de pagos de la EPC (Integra).</p>

	<div class="medio-pago-grid">
		<div class="medio-pago seleccionado" data-medio="pse">
			<div class="icono-pago"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div>
			<div class="nombre-pago">PSE — Débito a tu banco</div>
		</div>
		<div class="medio-pago" data-medio="tarjeta">
			<div class="icono-pago"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg></div>
			<div class="nombre-pago">Tarjeta débito o crédito</div>
		</div>
		<div class="medio-pago" data-medio="corresponsal">
			<div class="icono-pago"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V9l8-5 8 5v12"/><path d="M9 21v-6h6v6"/></svg></div>
			<div class="nombre-pago">Corresponsal bancario</div>
		</div>
	</div>

	<div class="resumen-pago">
		<div class="resumen-linea"><span>Subtotal</span><span>$69.320</span></div>
		<div class="resumen-linea"><span>Costo de la transacción</span><span>$0</span></div>
		<div class="resumen-linea total"><span>Total a pagar</span><span>$69.320</span></div>
	</div>

	<button class="btn btn-primario" style="margin-top:22px;padding:15px 32px;font-size:14.5px" type="button">
		Iniciar pago seguro
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3 7h7l-5.5 4.5L18.5 21 12 16.5 5.5 21l2-7.5L2 9h7z"/></svg>
	</button>
	<p style="font-size:12px;color:var(--gris-texto);margin-top:10px">Al confirmar serás redirigido a la pasarela de pagos de Integra. El flujo completo (banco, confirmación, recibo) lo gestiona Integra.</p>
</div>
<?php
epc_panel_close();
epc_html_close();
