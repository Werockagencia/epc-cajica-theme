<?php
/**
 * /panel/panel-pqrs/ — Radicar PQRS desde el panel (ligada a la cuenta y al
 * usuario). Se guarda como epc_pqrs (no solo un correo que se pierde), pasa
 * primero por el equipo comercial y el estado queda visible aquí mismo.
 */
$user     = wp_get_current_user();
$radicado = null;
if ( ! empty( $_POST['epc_pqrs_submit'] ) && wp_verify_nonce( $_POST['epc_pqrs_nonce'] ?? '', 'epc_pqrs' ) ) {
	$resultado = epc_crear_pqrs( [
		'tipo'        => sanitize_text_field( wp_unslash( $_POST['tipo'] ?? '' ) ),
		'asunto'      => sanitize_text_field( wp_unslash( $_POST['asunto'] ?? '' ) ),
		'descripcion' => sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ?? '' ) ),
		'nombre'      => $user->display_name,
		'documento'   => $user->user_login,
		'email'       => $user->user_email,
		'cuenta_id'   => sanitize_text_field( wp_unslash( $_POST['cuenta'] ?? '' ) ),
	], $user->ID );
	$radicado = $resultado['radicado'];

	wp_mail( 'atencion.usuario@epccajica.gov.co', "Nueva PQRS del portal — {$radicado}",
		"Radicado: {$radicado}\nUsuario: {$user->display_name} ({$user->user_email})\nRevisar en el panel de administración de WordPress." );
}

$mis_pqrs = get_posts( [ 'post_type' => 'epc_pqrs', 'author' => $user->ID, 'posts_per_page' => 10, 'post_status' => 'publish' ] );

epc_html_open( 'Radicar PQRS — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'panel-pqrs' );
?>

<?php if ( $radicado ) : ?>
	<div class="panel-card" style="background:#eefaf0;border:1px solid #b8e6c2">
		<div class="panel-card-header">
			<h1>¡Tu solicitud fue radicada!</h1>
			<p>Un asesor comercial revisará tu caso y te contactará por el canal preferente registrado en tu perfil.</p>
		</div>
		<div class="stat" style="max-width:260px"><div class="valor"><?php echo esc_html( $radicado ); ?></div><div class="lbl">Número de radicado</div></div>
	</div>
<?php endif; ?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Radicar PQRS</h1>
		<p>Peticiones, quejas, reclamos, sugerencias y denuncias — tu solicitud queda radicada con número de seguimiento y pasa primero por revisión del equipo comercial.</p>
	</div>

	<form method="post">
		<div class="campo">
			<label>Tipo de solicitud <span class="req">*</span></label>
			<select name="tipo" required>
				<option>Petición</option><option>Queja</option><option>Reclamo</option><option>Sugerencia</option><option>Denuncia</option>
			</select>
		</div>
		<div class="campo">
			<label>Cuenta asociada <span class="req">*</span></label>
			<select name="cuenta">
				<option value="1608353">1608353 · DG 2 Sur No 12-150</option>
				<option value="">No aplica a una cuenta específica</option>
			</select>
		</div>
		<div class="campo"><label for="asunto">Asunto <span class="req">*</span></label><input type="text" id="asunto" name="asunto" placeholder="Resume tu solicitud en pocas palabras" required></div>
		<div class="campo"><label for="descripcion">Descripción <span class="req">*</span></label><textarea id="descripcion" name="descripcion" rows="5" placeholder="Cuéntanos con el mayor detalle posible qué pasó, cuándo y dónde" required></textarea></div>
		<div class="campo">
			<label>Adjuntar soporte (opcional)</label>
			<div class="adjuntar">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M7 8l5-5 5 5"/><path d="M5 21h14"/></svg>
				<div class="txt"><strong>Sube un archivo</strong> o arrástralo aquí — PDF, PNG, JPG o Word</div>
			</div>
		</div>
		<label class="campo-check" style="margin-bottom:22px"><input type="checkbox" required> Autorizo el tratamiento de mis datos personales conforme a la política de protección de datos de la EPC <span class="req">*</span></label>
		<?php wp_nonce_field( 'epc_pqrs', 'epc_pqrs_nonce' ); ?>
		<input type="hidden" name="epc_pqrs_submit" value="1">
		<button class="btn btn-primario" style="padding:14px 30px" type="submit">Radicar solicitud
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</button>
	</form>
</div>

<div class="panel-card">
	<div class="panel-card-header"><h2>Mis PQRS radicadas</h2><p>Seguimiento a tus solicitudes anteriores — solo ves las tuyas.</p></div>
	<?php if ( ! $mis_pqrs ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">Todavía no has radicado ninguna PQRS.</p>
	<?php else : ?>
		<div class="tabla-wrap">
			<table class="panel-tabla">
				<thead><tr><th>N.° radicado</th><th>Fecha</th><th>Tipo</th><th>Asunto</th><th>Estado</th></tr></thead>
				<tbody>
				<?php foreach ( $mis_pqrs as $p ) : ?>
					<tr>
						<td><?php echo esc_html( get_post_meta( $p->ID, '_epc_radicado', true ) ); ?></td>
						<td><?php echo esc_html( get_the_date( 'd/m/Y', $p ) ); ?></td>
						<td><?php echo esc_html( get_post_meta( $p->ID, '_epc_tipo', true ) ); ?></td>
						<td><?php echo esc_html( $p->post_title ); ?></td>
						<td><span class="estado <?php echo 'respondida' === get_post_meta( $p->ID, '_epc_estado', true ) ? 'pagado' : 'emitida'; ?>"><?php echo esc_html( epc_estado_label( get_post_meta( $p->ID, '_epc_estado', true ) ) ); ?></span></td>
					</tr>
					<?php $resp = get_post_meta( $p->ID, '_epc_respuesta', true ); if ( $resp ) : ?>
					<tr><td colspan="5" style="font-size:12.5px;color:var(--gris-texto);background:var(--gris-claro)"><strong>Respuesta EPC:</strong> <?php echo esc_html( $resp ); ?></td></tr>
					<?php endif; ?>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
