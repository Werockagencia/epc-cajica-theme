<?php
/**
 * Panel propio para el rol "Personal EPC (Comercial)": al iniciar sesión
 * caen directo aquí (no al escritorio genérico de WordPress), con un
 * resumen de PQRS/trámites por estado, accesos directos, y descarga de
 * reporte CSV — la base para cuando se conecte a Integra y este mismo
 * panel muestre también lo descargado de allá.
 */

add_filter( 'login_redirect', function ( $redirect_to, $requested_redirect_to, $user ) {
	if ( $user instanceof WP_User && in_array( 'epc_comercial', (array) $user->roles, true ) ) {
		return admin_url( 'admin.php?page=epc-comercial' );
	}
	return $redirect_to;
}, 10, 3 );

// Si un usuario Comercial cae en el escritorio genérico (ej. bookmark viejo),
// lo mandamos a su panel en vez de dejarlo ver el dashboard de WordPress.
add_action( 'load-index.php', function () {
	$user = wp_get_current_user();
	if ( in_array( 'epc_comercial', (array) $user->roles, true ) && ! in_array( 'administrator', (array) $user->roles, true ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=epc-comercial' ) );
		exit;
	}
} );

add_action( 'admin_menu', function () {
	add_menu_page(
		'Panel Comercial', 'Panel Comercial', 'edit_posts', 'epc-comercial',
		'epc_render_comercial_panel', 'dashicons-analytics', 3
	);
} );

function epc_contar_por_estado( $post_type ) {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT pm.meta_value AS estado, COUNT(*) AS total
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_epc_estado' AND p.post_type = %s AND p.post_status = 'publish'
		 GROUP BY pm.meta_value", $post_type
	) );
	$out = [];
	foreach ( $rows as $r ) {
		$out[ $r->estado ] = (int) $r->total;
	}
	return $out;
}

function epc_render_comercial_panel() {
	$pqrs_estados    = epc_contar_por_estado( 'epc_pqrs' );
	$tramite_estados = epc_contar_por_estado( 'epc_tramite' );
	$pendientes_pqrs = ( $pqrs_estados['nuevo'] ?? 0 ) + ( $pqrs_estados['en_revision'] ?? 0 );
	$pendientes_tram = ( $tramite_estados['nuevo'] ?? 0 ) + ( $tramite_estados['en_revision'] ?? 0 );
	$recientes = get_posts( [ 'post_type' => [ 'epc_pqrs', 'epc_tramite' ], 'posts_per_page' => 10, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
	?>
	<div class="wrap">
		<h1>Panel Comercial — EPC</h1>
		<p>Bienvenido/a, <?php echo esc_html( wp_get_current_user()->display_name ); ?>. Aquí gestionas las PQRS y trámites radicados desde el portal del ciudadano.</p>

		<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin:24px 0;max-width:1000px">
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #d63638;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) $pendientes_pqrs; ?></div>
				<div>PQRS pendientes</div>
			</div>
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #dba617;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) $pendientes_tram; ?></div>
				<div>Trámites pendientes</div>
			</div>
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #00a32a;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) ( $pqrs_estados['respondida'] ?? 0 ); ?></div>
				<div>PQRS respondidas</div>
			</div>
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #2271b1;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) ( ( $pqrs_estados['escalada_integra'] ?? 0 ) + ( $tramite_estados['escalada_integra'] ?? 0 ) ); ?></div>
				<div>Escaladas a Integra</div>
			</div>
		</div>

		<p>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=epc_pqrs' ) ); ?>" class="button button-primary">Ver todas las PQRS</a>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=epc_tramite' ) ); ?>" class="button">Ver todos los trámites</a>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epc_exportar_reporte' ), 'epc_exportar_reporte' ) ); ?>" class="button">⬇ Descargar reporte CSV</a>
		</p>

		<h2>Actividad reciente</h2>
		<table class="widefat striped" style="max-width:1000px">
			<thead><tr><th>Radicado</th><th>Tipo</th><th>Asunto</th><th>Estado</th><th>Usuario</th><th>Fecha</th></tr></thead>
			<tbody>
			<?php foreach ( $recientes as $p ) : $author = get_userdata( $p->post_author ); ?>
				<tr>
					<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_post_meta( $p->ID, '_epc_radicado', true ) ); ?></a></td>
					<td><?php echo 'epc_pqrs' === $p->post_type ? 'PQRS' : 'Trámite'; ?></td>
					<td><?php echo esc_html( $p->post_title ); ?></td>
					<td><?php echo esc_html( epc_estado_label( get_post_meta( $p->ID, '_epc_estado', true ) ) ); ?></td>
					<td><?php echo $author ? esc_html( $author->display_name ) : 'Anónimo'; ?></td>
					<td><?php echo esc_html( get_the_date( 'd/m/Y H:i', $p ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<p class="description" style="margin-top:20px;max-width:700px">Cuando Integra confirme su API, este panel podrá traer directamente el historial de gestión desde allá (en vez de depender solo de lo que el asesor registre aquí) — por ahora, todo lo que ves sale de lo radicado en este portal.</p>
	</div>
	<?php
}

add_action( 'admin_post_epc_exportar_reporte', function () {
	if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'epc_exportar_reporte' ) ) {
		wp_die( 'No autorizado.' );
	}
	$posts = get_posts( [ 'post_type' => [ 'epc_pqrs', 'epc_tramite' ], 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=epc-pqrs-tramites-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fputs( $out, "\xEF\xBB\xBF" ); // BOM para que Excel abra bien las tildes
	fputcsv( $out, [ 'Radicado', 'Tipo', 'Asunto', 'Estado', 'Anónima', 'Documento', 'Cuenta', 'Usuario', 'Fecha', 'Respuesta' ] );
	foreach ( $posts as $p ) {
		$author = get_userdata( $p->post_author );
		fputcsv( $out, [
			get_post_meta( $p->ID, '_epc_radicado', true ),
			'epc_pqrs' === $p->post_type ? 'PQRS' : 'Trámite',
			$p->post_title,
			epc_estado_label( get_post_meta( $p->ID, '_epc_estado', true ) ),
			get_post_meta( $p->ID, '_epc_anonimo', true ) ? 'Sí' : 'No',
			get_post_meta( $p->ID, '_epc_documento', true ),
			get_post_meta( $p->ID, '_epc_cuenta_id', true ),
			$author ? $author->display_name : 'Anónimo',
			get_the_date( 'Y-m-d H:i', $p ),
			get_post_meta( $p->ID, '_epc_respuesta', true ),
		] );
	}
	fclose( $out );
	exit;
} );
