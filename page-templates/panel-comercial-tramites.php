<?php
/**
 * /panel/comercial-tramites/ — bandeja de trámites para Comercial. Incluye
 * los de tipo "Validación de propietario" (también se pueden gestionar
 * aquí); /panel/comercial-validaciones/ es solo un atajo filtrado a esos.
 */
if ( ! empty( $_POST['epc_revision_nonce'] ) && wp_verify_nonce( $_POST['epc_revision_nonce'], 'epc_revision_save' ) && ! empty( $_POST['post_id'] ) ) {
	$post_id = (int) $_POST['post_id'];
	if ( 'epc_tramite' === get_post_type( $post_id ) ) {
		wp_update_post( [ 'ID' => $post_id ] ); // dispara save_post_epc_tramite -> guarda estado/respuesta/PDF y, si es validación de propietario, asciende el rol.
		wp_safe_redirect( home_url( '/panel/comercial-tramites/?guardado=1' ) );
		exit;
	}
}

$filtro = sanitize_key( $_GET['estado'] ?? 'pendientes' );
$args   = [ 'post_type' => 'epc_tramite', 'post_status' => 'publish', 'posts_per_page' => 50, 'orderby' => 'date', 'order' => 'DESC' ];
if ( 'pendientes' === $filtro ) {
	$args['meta_query'] = [ [ 'key' => '_epc_estado', 'value' => [ 'nuevo', 'en_revision' ], 'compare' => 'IN' ] ];
}
$tramites = get_posts( $args );

epc_html_open( 'Gestión de trámites — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'comercial-tramites' );
?>
<div class="panel-card">
	<div class="panel-card-header">
		<h1>Gestión de trámites</h1>
		<p>Revisa y responde los trámites radicados por los ciudadanos (reconexión, cambio de suscriptor, validación de propietario, etc.).</p>
	</div>

	<?php if ( ! empty( $_GET['guardado'] ) ) : ?>
	<div class="alerta exito">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
		<span>Cambios guardados.</span>
	</div>
	<?php endif; ?>

	<div class="noticias-filtros" style="margin-bottom:20px">
		<a href="<?php echo esc_url( home_url( '/panel/comercial-tramites/?estado=pendientes' ) ); ?>" style="<?php echo 'pendientes' === $filtro ? 'background:var(--azul);color:#fff' : ''; ?>">Pendientes</a>
		<a href="<?php echo esc_url( home_url( '/panel/comercial-tramites/?estado=todas' ) ); ?>" style="<?php echo 'todas' === $filtro ? 'background:var(--azul);color:#fff' : ''; ?>">Todas</a>
	</div>

	<?php if ( ! $tramites ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">No hay trámites en esta vista.</p>
	<?php endif; ?>

	<?php foreach ( $tramites as $t ) :
		$get = fn( $k ) => get_post_meta( $t->ID, '_epc_' . $k, true );
		$pdf_id = (int) $get( 'respuesta_pdf' );
		$es_validacion = EPC_TIPO_VALIDACION_PROPIETARIO === $get( 'tipo_tramite' );
		$doc_id = (int) $get( 'documento' );
		$autor  = $t->post_author ? get_userdata( $t->post_author ) : null;
	?>
	<div class="cuenta-card">
		<div class="cuenta-card-info">
			<div class="cuenta-card-icono"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3h6v3H9z"/><path d="M9 13l2 2 4-4"/></svg></div>
			<div>
				<div class="cuenta-card-nombre"><?php echo esc_html( $t->post_title ); ?><?php if ( $es_validacion ) : ?> <span class="badge-cuenta" style="padding:2px 10px;font-size:11px"><?php echo '👤'; ?> Ascenso de rol</span><?php endif; ?></div>
				<div class="cuenta-card-detalle"><?php echo esc_html( $get( 'radicado' ) ); ?> · <?php echo esc_html( $autor ? $autor->display_name : 'Anónimo' ); ?> · <?php echo esc_html( get_the_date( 'd/m/Y', $t ) ); ?></div>
			</div>
		</div>
		<div class="cuenta-card-acciones">
			<span class="estado <?php echo esc_attr( epc_estado_css_class( $get( 'estado' ) ) ); ?>"><?php echo esc_html( epc_estado_label( $get( 'estado' ) ) ); ?></span>
			<button class="btn btn-outline" type="button" data-toggle-cuenta="tramite-<?php echo esc_attr( $t->ID ); ?>" aria-expanded="false">Revisar
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		</div>
	</div>
	<div class="cuenta-detalle" id="detalle-tramite-<?php echo esc_attr( $t->ID ); ?>" hidden>
		<p><strong>Cuenta:</strong> <?php echo esc_html( $get( 'cuenta_id' ) ?: '—' ); ?></p>
		<p><strong>Detalle:</strong> <?php echo esc_html( $t->post_content ); ?></p>

		<?php if ( $es_validacion ) : ?>
			<div class="alerta info">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
				<span>Al marcar este trámite como <strong>Aprobado</strong>, <?php echo esc_html( $autor ? $autor->display_name : 'el usuario' ); ?> pasa automáticamente a rol <strong>Propietario</strong>. Verifica el documento antes de aprobar.</span>
			</div>
			<?php if ( $doc_id && get_post( $doc_id ) ) : ?>
				<p><a href="<?php echo esc_url( wp_get_attachment_url( $doc_id ) ); ?>" target="_blank" class="enlace-tabla">📎 Ver certificado de tradición y libertad →</a></p>
			<?php else : ?>
				<p style="font-size:12.5px;color:var(--gris-texto)">No se adjuntó documento.</p>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data">
			<div class="campo">
				<label>Estado</label>
				<select name="epc_estado">
					<?php foreach ( [ 'nuevo', 'en_revision', 'aprobado', 'rechazado', 'escalada_integra', 'cerrada' ] as $e ) : ?>
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
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $t->ID ); ?>">
			<?php wp_nonce_field( 'epc_revision_save', 'epc_revision_nonce' ); ?>
			<button class="btn btn-azul" type="submit">Guardar cambios</button>
		</form>
	</div>
	<?php endforeach; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
