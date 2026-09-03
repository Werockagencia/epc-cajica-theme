<?php
/** /panel/consumo/ — historial de consumo (m³) con alerta de anomalía. */
epc_html_open( 'Historial de consumo — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'consumo' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Historial de consumo</h1>
		<p>Metros cúbicos (m³) de agua registrados por tu medidor mes a mes. Cuenta N.° 1608353 · Medidor MED-04471.</p>
	</div>

	<div class="alerta-consumo" style="background:#fff7ed;border:1px solid #fbd9a8;border-radius:var(--radio-s);padding:16px 20px;margin:18px 0;display:flex;gap:12px;align-items:flex-start">
		<svg viewBox="0 0 24 24" fill="none" stroke="#e07f22" stroke-width="1.8" width="22" height="22" style="flex-shrink:0;margin-top:2px"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
		<div>
			<strong style="font-size:13.5px;color:var(--azul-oscuro)">Junio 2026 tuvo un consumo 63% más alto que tu promedio.</strong>
			<p style="font-size:13px;color:var(--gris-texto);margin-top:4px">Si no reconoces el motivo (fuga, visita de familiares, etc.), puedes radicar una PQRS para solicitar revisión del medidor.</p>
			<a href="<?php echo esc_url( home_url( '/panel/panel-pqrs/' ) ); ?>" class="btn btn-outline" style="margin-top:10px;padding:8px 16px;font-size:12.5px">Radicar PQRS de revisión →</a>
		</div>
	</div>

	<div class="stat-grid">
		<div class="stat"><div class="valor">9 m³</div><div class="lbl">Consumo mes actual</div></div>
		<div class="stat"><div class="valor">13,5 m³</div><div class="lbl">Promedio 12 meses</div></div>
		<div class="stat"><div class="valor acento">22 m³</div><div class="lbl">Mayor consumo (jun 2026)</div></div>
		<div class="stat"><div class="valor" style="color:var(--verde-oscuro)">-33%</div><div class="lbl">Frente al mes anterior</div></div>
	</div>

	<div class="grafico-consumo">
		<div class="grafico-barra"><span class="valor-barra">14</span><div class="barra" style="--h:64%"></div><span class="mes-barra">09-25</span></div>
		<div class="grafico-barra"><span class="valor-barra">15</span><div class="barra" style="--h:68%"></div><span class="mes-barra">10-25</span></div>
		<div class="grafico-barra"><span class="valor-barra">9</span><div class="barra" style="--h:41%"></div><span class="mes-barra">11-25</span></div>
		<div class="grafico-barra"><span class="valor-barra">11</span><div class="barra" style="--h:50%"></div><span class="mes-barra">12-25</span></div>
		<div class="grafico-barra"><span class="valor-barra">8</span><div class="barra" style="--h:36%"></div><span class="mes-barra">01-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">12</span><div class="barra" style="--h:55%"></div><span class="mes-barra">02-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">15</span><div class="barra" style="--h:68%"></div><span class="mes-barra">03-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">12</span><div class="barra" style="--h:55%"></div><span class="mes-barra">04-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">18</span><div class="barra" style="--h:82%"></div><span class="mes-barra">05-26</span></div>
		<div class="grafico-barra pico"><span class="valor-barra">22</span><div class="barra" style="--h:100%"></div><span class="mes-barra">06-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">16</span><div class="barra" style="--h:73%"></div><span class="mes-barra">07-26</span></div>
		<div class="grafico-barra"><span class="valor-barra">9</span><div class="barra" style="--h:41%"></div><span class="mes-barra">08-26</span></div>
	</div>
</div>
<?php
epc_panel_close();
epc_html_close();
