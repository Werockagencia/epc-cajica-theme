<?php
/** /panel/ — dashboard de bienvenida, primer vistazo a la cuenta. */
$user = wp_get_current_user();
epc_html_open( 'Mi panel — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( '' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Hola, <?php echo esc_html( $user->first_name ?: $user->display_name ); ?> 👋</h1>
		<p>Este es el resumen de tu cuenta. Rol actual: <strong><?php echo esc_html( epc_user_role_label( $user ) ); ?></strong>.</p>
	</div>
	<div class="stat-grid">
		<div class="stat"><div class="valor">$69.320</div><div class="lbl">Última factura</div></div>
		<div class="stat"><div class="valor">13 ago 2026</div><div class="lbl">Próximo vencimiento</div></div>
		<div class="stat"><div class="valor acento">1</div><div class="lbl">Factura pendiente</div></div>
		<div class="stat"><div class="valor">9 m³</div><div class="lbl">Consumo del mes</div></div>
	</div>
	<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:22px">
		<a href="<?php echo esc_url( home_url( '/panel/pagar-factura/' ) ); ?>" class="btn btn-primario">Pagar factura</a>
		<a href="<?php echo esc_url( home_url( '/panel/mis-cuentas/' ) ); ?>" class="btn btn-outline">Ver mis cuentas</a>
		<a href="<?php echo esc_url( home_url( '/panel/panel-pqrs/' ) ); ?>" class="btn btn-outline">Radicar PQRS</a>
	</div>
</div>

<?php if ( ! epc_user_is_propietario( $user ) && ! epc_user_is_arrendatario( $user ) ) : ?>
<div class="panel-card" style="background:linear-gradient(135deg,#f3f7f6,#eaf4e5);border:1px dashed var(--verde-oscuro)">
	<div class="panel-card-header">
		<h2>Todavía no has vinculado ninguna cuenta</h2>
		<p>Vincula el predio donde vives o trabajas para ver tus facturas, tu consumo y poder pagar en línea.</p>
	</div>
	<a href="<?php echo esc_url( home_url( '/panel/mis-cuentas/' ) ); ?>" class="btn btn-primario">Vincular una cuenta</a>
</div>
<?php endif; ?>
<?php
epc_panel_close();
epc_html_close();
