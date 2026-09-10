<?php
/**
 * /panel/comercial/ — dashboard de bienvenida para el rol Comercial, con
 * el mismo look del panel de un ciudadano (no el escritorio de WordPress).
 */
$user = wp_get_current_user();

$pendientes_pqrs = count( get_posts( [ 'post_type' => 'epc_pqrs', 'post_status' => 'publish', 'posts_per_page' => -1,
	'meta_query' => [ [ 'key' => '_epc_estado', 'value' => [ 'nuevo', 'en_revision' ], 'compare' => 'IN' ] ] ] ) );
$pendientes_tram = count( get_posts( [ 'post_type' => 'epc_tramite', 'post_status' => 'publish', 'posts_per_page' => -1,
	'meta_query' => [
		[ 'key' => '_epc_estado', 'value' => [ 'nuevo', 'en_revision' ], 'compare' => 'IN' ],
		[ 'key' => '_epc_tipo_tramite', 'value' => EPC_TIPO_VALIDACION_PROPIETARIO, 'compare' => '!=' ],
	] ] ) );
$pendientes_val = count( epc_validaciones_propietario_pendientes() );

epc_html_open( 'Panel Comercial — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'comercial' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Hola, <?php echo esc_html( $user->first_name ?: $user->display_name ); ?> 👋</h1>
		<p>Aquí gestionas las PQRS, los trámites y las validaciones de propietario radicadas desde el Portal del Ciudadano.</p>
	</div>
	<div class="stat-grid">
		<div class="stat"><div class="valor acento"><?php echo (int) $pendientes_pqrs; ?></div><div class="lbl">PQRS pendientes</div></div>
		<div class="stat"><div class="valor acento"><?php echo (int) $pendientes_tram; ?></div><div class="lbl">Trámites pendientes</div></div>
		<div class="stat"><div class="valor acento"><?php echo (int) $pendientes_val; ?></div><div class="lbl">Validaciones de propietario pendientes</div></div>
	</div>
	<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:22px">
		<a href="<?php echo esc_url( home_url( '/panel/comercial-pqrs/' ) ); ?>" class="btn btn-primario">Ver PQRS</a>
		<a href="<?php echo esc_url( home_url( '/panel/comercial-tramites/' ) ); ?>" class="btn btn-outline">Ver trámites</a>
		<a href="<?php echo esc_url( home_url( '/panel/comercial-validaciones/' ) ); ?>" class="btn btn-outline">Ver validaciones</a>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epc_exportar_reporte' ), 'epc_exportar_reporte' ) ); ?>" class="btn btn-outline">⬇ Descargar reporte CSV</a>
	</div>
</div>
<?php
epc_panel_close();
epc_html_close();
