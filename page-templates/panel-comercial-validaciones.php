<?php
/**
 * /panel/comercial-validaciones/ — atajo a solo los trámites de tipo
 * "Validación de propietario": aquí Comercial confirma que un ciudadano
 * es el titular real del predio (certificado de tradición y libertad) y
 * lo asciende a Propietario. Mismo guardado reutilizado de siempre.
 */
if ( ! empty( $_POST['epc_revision_nonce'] ) && wp_verify_nonce( $_POST['epc_revision_nonce'], 'epc_revision_save' ) && ! empty( $_POST['post_id'] ) ) {
	$post_id = (int) $_POST['post_id'];
	if ( 'epc_tramite' === get_post_type( $post_id ) ) {
		wp_update_post( [ 'ID' => $post_id ] ); // dispara save_post_epc_tramite -> guarda estado y, si aprueba, asciende el rol a Propietario.
		wp_safe_redirect( home_url( '/panel/comercial-validaciones/?guardado=1' ) );
		exit;
	}
}

$ver_todas = ! empty( $_GET['todas'] );
if ( $ver_todas ) {
	$validaciones = get_posts( [ 'post_type' => 'epc_tramite', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC',
		'meta_query' => [ [ 'key' => '_epc_tipo_tramite', 'value' => EPC_TIPO_VALIDACION_PROPIETARIO ] ] ] );
} else {
	$validaciones = epc_validaciones_propietario_pendientes();
}

epc_html_open( 'Validaciones de propietario — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'comercial-validaciones' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Validaciones de propietario</h1>
		<p>Cuando un ciudadano solicita ser reconocido como propietario de un predio, sube su certificado de tradición y libertad (o su último recibo pagado) aquí. Verifícalo antes de aprobar: al aprobar, su cuenta pasa automáticamente al rol Propietario.</p>
	</div>

	<?php if ( ! empty( $_GET['guardado'] ) ) : ?>
	<div class="alerta exito">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
		<span>Cambios guardados.</span>
	</div>
	<?php endif; ?>

	<div class="noticias-filtros" style="margin-bottom:20px">
		<a href="<?php echo esc_url( home_url( '/panel/comercial-validaciones/' ) ); ?>" style="<?php echo ! $ver_todas ? 'background:var(--azul);color:#fff' : ''; ?>">Pendientes</a>
		<a href="<?php echo esc_url( home_url( '/panel/comercial-validaciones/?todas=1' ) ); ?>" style="<?php echo $ver_todas ? 'background:var(--azul);color:#fff' : ''; ?>">Todas</a>
	</div>

	<?php if ( ! $validaciones ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">No hay solicitudes de validación de propietario <?php echo $ver_todas ? '' : 'pendientes'; ?>.</p>
	<?php endif; ?>

	<?php foreach ( $validaciones as $t ) :
		$get    = fn( $k ) => get_post_meta( $t->ID, '_epc_' . $k, true );
		$doc_id = (int) $get( 'documento' );
		$autor  = $t->post_author ? get_userdata( $t->post_author ) : null;
		$estado = $get( 'estado' );
	?>
	<div class="cuenta-card">
		<div class="cuenta-card-info">
			<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg></div>
			<div>
				<div class="cuenta-card-nombre"><?php echo esc_html( $autor ? $autor->display_name : '(usuario eliminado)' ); ?></div>
				<div class="cuenta-card-detalle"><?php echo esc_html( $get( 'radicado' ) ); ?> · Cuenta <?php echo esc_html( $get( 'cuenta_id' ) ?: '—' ); ?> · <?php echo esc_html( get_the_date( 'd/m/Y', $t ) ); ?></div>
			</div>
		</div>
		<div class="cuenta-card-acciones">
			<span class="estado <?php echo esc_attr( epc_estado_css_class( $estado ) ); ?>"><?php echo esc_html( epc_estado_label( $estado ) ); ?></span>
			<button class="btn btn-outline" type="button" data-toggle-cuenta="val-<?php echo esc_attr( $t->ID ); ?>" aria-expanded="false">Revisar
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		</div>
	</div>
	<div class="cuenta-detalle" id="detalle-val-<?php echo esc_attr( $t->ID ); ?>" hidden>
		<?php if ( $doc_id && get_post( $doc_id ) ) : ?>
			<p><a href="<?php echo esc_url( wp_get_attachment_url( $doc_id ) ); ?>" target="_blank" class="enlace-tabla">📎 Ver certificado de tradición y libertad →</a></p>
		<?php else : ?>
			<p style="font-size:12.5px;color:var(--gris-texto)">No se adjuntó documento.</p>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
			<input type="hidden" name="epc_estado" value="aprobado">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $t->ID ); ?>">
			<?php wp_nonce_field( 'epc_revision_save', 'epc_revision_nonce' ); ?>
			<button class="btn btn-verde" type="submit">✓ Aprobar y ascender a Propietario</button>
		</form>
		<form method="post" enctype="multipart/form-data" style="margin-top:8px">
			<input type="hidden" name="epc_estado" value="rechazado">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $t->ID ); ?>">
			<?php wp_nonce_field( 'epc_revision_save', 'epc_revision_nonce' ); ?>
			<button class="btn btn-outline" type="submit">Rechazar</button>
		</form>
	</div>
	<?php endforeach; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
