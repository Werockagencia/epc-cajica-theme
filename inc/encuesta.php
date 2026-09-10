<?php
/**
 * Encuesta de satisfacción propia (reemplaza el Google Form actual):
 * página pública sin ruido visual en los menús (no se agrega a ningún
 * nav, se enlaza puntualmente desde Home/Participa), respuestas guardadas
 * como CPT, y un reporte con estadísticas para el rol Administrador
 * (branding EPC en vez de las gráficas genéricas de Google Forms).
 */

add_action( 'init', function () {
	register_post_type( 'epc_encuesta', [
		'labels'          => [ 'name' => 'Encuesta de satisfacción', 'singular_name' => 'Respuesta' ],
		'public'          => false,
		'show_ui'         => false,
		'capability_type' => 'post',
		'supports'        => [ 'title' ],
	] );
} );

add_filter( 'template_include', function ( $template ) {
	if ( is_page( 'encuesta-satisfaccion' ) ) {
		return get_template_directory() . '/page-templates/encuesta-satisfaccion.php';
	}
	return $template;
}, 20 );

function epc_guardar_encuesta( array $datos ) {
	$id = wp_insert_post( [
		'post_type'   => 'epc_encuesta',
		'post_title'  => 'Respuesta ' . gmdate( 'Y-m-d H:i:s' ),
		'post_status' => 'publish',
	] );
	update_post_meta( $id, '_epc_calificacion', (int) $datos['calificacion'] );
	update_post_meta( $id, '_epc_servicio', sanitize_text_field( $datos['servicio'] ?? '' ) );
	update_post_meta( $id, '_epc_comentario', sanitize_textarea_field( $datos['comentario'] ?? '' ) );
	return $id;
}

add_action( 'admin_menu', function () {
	add_menu_page(
		'Encuesta de satisfacción', 'Encuesta satisfacción', 'edit_posts', 'epc-encuesta-reporte',
		'epc_render_encuesta_reporte', 'dashicons-chart-bar', 4
	);
} );

function epc_render_encuesta_reporte() {
	$respuestas = get_posts( [ 'post_type' => 'epc_encuesta', 'posts_per_page' => -1, 'post_status' => 'publish' ] );
	$total = count( $respuestas );
	$suma  = 0;
	$por_calificacion = [ 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 ];
	$por_servicio = [];
	foreach ( $respuestas as $r ) {
		$cal = (int) get_post_meta( $r->ID, '_epc_calificacion', true );
		if ( $cal >= 1 && $cal <= 5 ) { $por_calificacion[ $cal ]++; $suma += $cal; }
		$serv = get_post_meta( $r->ID, '_epc_servicio', true ) ?: 'General';
		$por_servicio[ $serv ] = ( $por_servicio[ $serv ] ?? 0 ) + 1;
	}
	$promedio = $total ? round( $suma / $total, 2 ) : 0;
	?>
	<div class="wrap">
		<h1>📊 Encuesta de satisfacción — EPC Cajicá</h1>
		<p>Reemplaza el formulario de Google Forms — las respuestas se radican desde <code><?php echo esc_html( home_url( '/encuesta-satisfaccion/' ) ); ?></code>.</p>

		<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:24px 0;max-width:800px">
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #2271b1;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) $total; ?></div>
				<div>Personas que calificaron</div>
			</div>
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #00a32a;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo esc_html( $promedio ); ?> / 5</div>
				<div>Calificación promedio</div>
			</div>
			<div style="background:#fff;border:1px solid #ccd0d4;border-left:4px solid #dba617;border-radius:4px;padding:16px">
				<div style="font-size:28px;font-weight:700"><?php echo (int) ( $por_calificacion[1] + $por_calificacion[2] ); ?></div>
				<div>Calificaciones bajas (1-2 ⭐)</div>
			</div>
		</div>

		<h2>Distribución de calificaciones</h2>
		<div style="max-width:500px">
			<?php for ( $i = 5; $i >= 1; $i-- ) :
				$pct = $total ? round( $por_calificacion[ $i ] / $total * 100 ) : 0;
			?>
				<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
					<div style="width:80px;white-space:nowrap;flex-shrink:0"><?php echo str_repeat( '⭐', $i ); ?></div>
					<div style="flex:1;background:#f0f0f1;border-radius:4px;overflow:hidden;height:18px">
						<div style="width:<?php echo esc_attr( $pct ); ?>%;background:#2271b1;height:100%"></div>
					</div>
					<div style="width:90px;text-align:right"><?php echo (int) $por_calificacion[ $i ]; ?> (<?php echo esc_html( $pct ); ?>%)</div>
				</div>
			<?php endfor; ?>
		</div>

		<h2 style="margin-top:30px">Por servicio evaluado</h2>
		<table class="widefat striped" style="max-width:500px">
			<thead><tr><th>Servicio</th><th>Respuestas</th></tr></thead>
			<tbody>
			<?php foreach ( $por_servicio as $serv => $n ) : ?>
				<tr><td><?php echo esc_html( $serv ); ?></td><td><?php echo (int) $n; ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<p style="margin-top:24px"><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=epc_exportar_encuesta' ), 'epc_exportar_encuesta' ) ); ?>" class="button button-primary">⬇ Descargar todas las respuestas (CSV)</a></p>

		<h2 style="margin-top:30px">Últimos comentarios</h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th>Fecha</th><th>Servicio</th><th>Calificación</th><th>Comentario</th></tr></thead>
			<tbody>
			<?php foreach ( array_slice( $respuestas, 0, 20 ) as $r ) : ?>
				<tr>
					<td><?php echo esc_html( get_the_date( 'd/m/Y H:i', $r ) ); ?></td>
					<td><?php echo esc_html( get_post_meta( $r->ID, '_epc_servicio', true ) ?: 'General' ); ?></td>
					<td><?php echo esc_html( str_repeat( '⭐', (int) get_post_meta( $r->ID, '_epc_calificacion', true ) ) ); ?></td>
					<td><?php echo esc_html( get_post_meta( $r->ID, '_epc_comentario', true ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			<?php if ( ! $respuestas ) : ?><tr><td colspan="4">Todavía no hay respuestas.</td></tr><?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

add_action( 'admin_post_epc_exportar_encuesta', function () {
	if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'epc_exportar_encuesta' ) ) {
		wp_die( 'No autorizado.' );
	}
	$respuestas = get_posts( [ 'post_type' => 'epc_encuesta', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=epc-encuesta-satisfaccion-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' );
	fputs( $out, "\xEF\xBB\xBF" );
	fputcsv( $out, [ 'Fecha', 'Servicio', 'Calificación', 'Comentario' ] );
	foreach ( $respuestas as $r ) {
		fputcsv( $out, [
			get_the_date( 'Y-m-d H:i', $r ),
			get_post_meta( $r->ID, '_epc_servicio', true ),
			get_post_meta( $r->ID, '_epc_calificacion', true ),
			get_post_meta( $r->ID, '_epc_comentario', true ),
		] );
	}
	fclose( $out );
	exit;
} );
