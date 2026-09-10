<?php
/**
 * /panel/comercial-pqrs/ — bandeja de PQRS para Comercial, dentro del
 * mismo panel del ciudadano (no wp-admin). Reutiliza el guardado que ya
 * existe en inc/pqrs-tramites.php (save_post con 'epc_revision_nonce'):
 * al llamar wp_update_post() con esos campos en $_POST, el mismo hook
 * de siempre guarda estado/respuesta/PDF -- no se duplica esa lógica.
 */
if ( ! empty( $_POST['epc_revision_nonce'] ) && wp_verify_nonce( $_POST['epc_revision_nonce'], 'epc_revision_save' ) && ! empty( $_POST['post_id'] ) ) {
	$post_id = (int) $_POST['post_id'];
	if ( 'epc_pqrs' === get_post_type( $post_id ) ) {
		wp_update_post( [ 'ID' => $post_id ] ); // dispara save_post -> guarda estado/respuesta/PDF.
		wp_safe_redirect( home_url( '/panel/comercial-pqrs/?guardado=1' ) );
		exit;
	}
}

$filtro = sanitize_key( $_GET['estado'] ?? 'pendientes' );
$args   = [ 'post_type' => 'epc_pqrs', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC' ];
if ( 'pendientes' === $filtro ) {
	$args['meta_query'] = [ [ 'key' => '_epc_estado', 'value' => [ 'nuevo', 'en_revision' ], 'compare' => 'IN' ] ];
}
$pqrs = get_posts( $args );

epc_html_open( 'Gestión de PQRS — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'comercial-pqrs' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Gestión de PQRS</h1>
		<p>Revisa, responde y cambia el estado de las peticiones, quejas, reclamos y sugerencias radicadas por los ciudadanos.</p>
	</div>

	<?php if ( ! empty( $_GET['guardado'] ) ) : ?>
	<div class="alerta exito">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
		<span>Cambios guardados.</span>
	</div>
	<?php endif; ?>

	<div class="noticias-filtros" style="margin-bottom:20px">
		<a href="<?php echo esc_url( home_url( '/panel/comercial-pqrs/?estado=pendientes' ) ); ?>" style="<?php echo 'pendientes' === $filtro ? 'background:var(--azul);color:#fff' : ''; ?>">Pendientes</a>
		<a href="<?php echo esc_url( home_url( '/panel/comercial-pqrs/?estado=todas' ) ); ?>" style="<?php echo 'todas' === $filtro ? 'background:var(--azul);color:#fff' : ''; ?>">Todas</a>
	</div>

	<?php if ( ! $pqrs ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">No hay PQRS en esta vista.</p>
	<?php endif; ?>

	<?php foreach ( $pqrs as $p ) :
		$get = fn( $k ) => get_post_meta( $p->ID, '_epc_' . $k, true );
		$pdf_id = (int) $get( 'respuesta_pdf' );
		$anonimo = $get( 'anonimo' );
	?>
	<div class="cuenta-card">
		<div class="cuenta-card-info">
			<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5A8.5 8.5 0 1 1 21 11.5z"/></svg></div>
			<div>
				<div class="cuenta-card-nombre"><?php echo esc_html( $p->post_title ); ?></div>
				<div class="cuenta-card-detalle"><?php echo esc_html( $get( 'radicado' ) ); ?> · <?php echo $anonimo ? 'Anónima' : esc_html( $get( 'nombre' ) ?: '(sin nombre)' ); ?> · <?php echo esc_html( get_the_date( 'd/m/Y', $p ) ); ?></div>
			</div>
		</div>
		<div class="cuenta-card-acciones">
			<span class="estado <?php echo esc_attr( epc_estado_css_class( $get( 'estado' ) ) ); ?>"><?php echo esc_html( epc_estado_label( $get( 'estado' ) ) ); ?></span>
			<button class="btn btn-outline" type="button" data-toggle-cuenta="pqrs-<?php echo esc_attr( $p->ID ); ?>" aria-expanded="false">Revisar
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		</div>
	</div>
	<div class="cuenta-detalle" id="detalle-pqrs-<?php echo esc_attr( $p->ID ); ?>" hidden>
		<p><strong>Tipo:</strong> <?php echo esc_html( $get( 'tipo' ) ); ?> <?php if ( $anonimo ) : ?>· <strong>Anónima</strong><?php endif; ?></p>
		<?php if ( ! $anonimo ) : ?>
		<p><strong>Solicitante:</strong> <?php echo esc_html( $get( 'nombre' ) ); ?> · Doc: <?php echo esc_html( $get( 'documento' ) ); ?><br><strong>Email:</strong> <?php echo esc_html( $get( 'email' ) ); ?> · <strong>Tel:</strong> <?php echo esc_html( $get( 'telefono' ) ); ?></p>
		<?php endif; ?>
		<p><strong>Cuenta:</strong> <?php echo esc_html( $get( 'cuenta_id' ) ?: '—' ); ?></p>
		<p><strong>Descripción:</strong> <?php echo esc_html( $p->post_content ); ?></p>

		<form method="post" enctype="multipart/form-data">
			<div class="campo">
				<label>Estado</label>
				<select name="epc_estado">
					<?php foreach ( [ 'nuevo', 'en_revision', 'respondida', 'escalada_integra', 'cerrada' ] as $e ) : ?>
						<option value="<?php echo esc_attr( $e ); ?>" <?php selected( $get( 'estado' ), $e ); ?>><?php echo esc_html( epc_estado_label( $e ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="campo">
				<label>Respuesta al ciudadano</label>
				<textarea name="epc_respuesta" rows="3"><?php echo esc_textarea( $get( 'respuesta' ) ); ?></textarea>
			</div>
			<div class="campo">
				<label>Adjuntar respuesta escaneada (PDF)</label>
				<?php if ( $pdf_id && get_post( $pdf_id ) ) : ?>
					<p style="font-size:12.5px"><a href="<?php echo esc_url( wp_get_attachment_url( $pdf_id ) ); ?>" target="_blank">📎 Ver adjunto actual</a> · <label><input type="checkbox" name="epc_respuesta_pdf_quitar" value="1"> quitar</label></p>
				<?php endif; ?>
				<input type="file" name="epc_respuesta_pdf" accept="application/pdf">
			</div>
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $p->ID ); ?>">
			<?php wp_nonce_field( 'epc_revision_save', 'epc_revision_nonce' ); ?>
			<button class="btn btn-azul" type="submit">Guardar cambios</button>
		</form>
	</div>
	<?php endforeach; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
