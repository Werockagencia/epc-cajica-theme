<?php
/**
 * /pqrs/ — Radicar PQRS público, SIN necesidad de iniciar sesión ni crear
 * cuenta (obligatorio por ley: el derecho de petición es de cualquier
 * persona, Ley 1755 de 2015, y las denuncias deben poder radicarse de forma
 * anónima). Si el visitante ya tiene cuenta, se le invita a iniciar sesión
 * para que su PQRS quede ligada a su historial, pero no es un requisito.
 *
 * También resuelve la consulta pública de estado por número de radicado,
 * sin login (verificando radicado + documento/correo para no exponer datos
 * de otra persona a quien solo adivine un número de radicado).
 */
$radicado_nuevo = null;
$consulta       = null;
$consulta_error = null;

if ( ! empty( $_POST['epc_pqrs_pub_submit'] ) && wp_verify_nonce( $_POST['epc_pqrs_pub_nonce'] ?? '', 'epc_pqrs_pub' ) ) {
	$anonimo = ! empty( $_POST['anonimo'] );
	$resultado = epc_crear_pqrs( [
		'tipo'        => sanitize_text_field( wp_unslash( $_POST['tipo'] ?? '' ) ),
		'asunto'      => sanitize_text_field( wp_unslash( $_POST['asunto'] ?? '' ) ),
		'descripcion' => sanitize_textarea_field( wp_unslash( $_POST['descripcion'] ?? '' ) ),
		'nombre'      => $anonimo ? '' : sanitize_text_field( wp_unslash( $_POST['nombre'] ?? '' ) ),
		'documento'   => $anonimo ? '' : sanitize_text_field( wp_unslash( $_POST['documento'] ?? '' ) ),
		'email'       => $anonimo ? '' : sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
		'telefono'    => $anonimo ? '' : sanitize_text_field( wp_unslash( $_POST['telefono'] ?? '' ) ),
		'cuenta_id'   => sanitize_text_field( wp_unslash( $_POST['cuenta'] ?? '' ) ),
		'anonimo'     => $anonimo,
	], is_user_logged_in() ? get_current_user_id() : 0 );
	$radicado_nuevo = $resultado['radicado'];

	wp_mail( 'atencion.usuario@epccajica.gov.co', "Nueva PQRS " . ( $anonimo ? '(anónima) ' : '' ) . "— {$radicado_nuevo}",
		"Radicado: {$radicado_nuevo}\nAnónima: " . ( $anonimo ? 'Sí' : 'No' ) . "\nRevisar en el panel de administración de WordPress." );
}

if ( ! empty( $_POST['epc_pqrs_consulta_submit'] ) && wp_verify_nonce( $_POST['epc_pqrs_consulta_nonce'] ?? '', 'epc_pqrs_consulta' ) ) {
	$radicado_buscado = sanitize_text_field( wp_unslash( $_POST['radicado'] ?? '' ) );
	$identificador     = sanitize_text_field( wp_unslash( $_POST['identificador'] ?? '' ) );

	$posts = get_posts( [
		'post_type'   => 'epc_pqrs',
		'post_status' => 'publish',
		'meta_query'  => [ [ 'key' => '_epc_radicado', 'value' => $radicado_buscado ] ],
		'numberposts' => 1,
	] );
	$match = $posts[0] ?? null;
	if ( $match && $identificador && (
		get_post_meta( $match->ID, '_epc_documento', true ) === $identificador ||
		get_post_meta( $match->ID, '_epc_email', true ) === $identificador
	) ) {
		$consulta = $match;
	} else {
		$consulta_error = 'No encontramos una PQRS con ese radicado y ese documento/correo. Verifica los datos.';
	}
}

epc_html_open( 'Radicar PQRS — EPC Cajicá' );
block_template_part( 'header' );
?>
<main id="contenido">
<section class="pagina-hero">
	<div class="contenedor pagina-hero-wrap">
		<div class="pagina-hero-texto">
			<div class="migas-pub"><a href="/">Inicio</a> / Radicar PQRS</div>
			<div class="badge-red">Atención al ciudadano</div>
			<h1>Radica tu PQRS.</h1>
			<p class="lead">Peticiones, quejas, reclamos, sugerencias y denuncias — puedes radicarla <strong>sin necesidad de iniciar sesión</strong>, incluso de forma anónima. Si tienes cuenta, <a href="/login/">inicia sesión</a> para que quede ligada a tu historial.</p>
		</div>
	</div>
</section>

<section class="seccion">
	<div class="contenedor" style="max-width:760px">

		<?php if ( $radicado_nuevo ) : ?>
			<div class="panel-card" style="background:#eefaf0;border:1px solid #b8e6c2;margin-bottom:30px">
				<h2 style="margin-bottom:6px">¡Tu solicitud fue radicada!</h2>
				<p style="font-size:13.5px;color:var(--gris-texto);margin-bottom:14px">Guarda este número — lo necesitarás para consultar el estado de tu solicitud, sobre todo si la radicaste de forma anónima (en ese caso es la <strong>única</strong> forma de hacerle seguimiento).</p>
				<div class="stat" style="max-width:260px"><div class="valor"><?php echo esc_html( $radicado_nuevo ); ?></div><div class="lbl">Número de radicado</div></div>
			</div>
		<?php endif; ?>

		<div class="panel-card">
			<h2 style="margin-bottom:6px">Radicar una PQRS</h2>
			<p style="font-size:13.5px;color:var(--gris-texto);margin-bottom:20px">Petición, queja, reclamo, sugerencia o denuncia.</p>

			<form method="post">
				<label class="campo-check" style="margin-bottom:18px;background:var(--gris-claro);padding:12px 16px;border-radius:var(--radio-s)">
					<input type="checkbox" name="anonimo" id="epc-anonimo" value="1">
					Prefiero radicar de forma <strong>anónima</strong> (no daré mis datos de contacto)
				</label>

				<div class="campo"><label>Tipo de solicitud <span class="req">*</span></label>
					<select name="tipo" required>
						<option>Petición</option><option>Queja</option><option>Reclamo</option><option>Sugerencia</option><option>Denuncia</option>
					</select>
				</div>

				<div id="epc-datos-identificado">
					<div class="campo-fila">
						<div class="campo"><label>Nombre completo</label><input type="text" name="nombre"></div>
						<div class="campo"><label>Documento de identidad</label><input type="text" name="documento"></div>
					</div>
					<div class="campo-fila">
						<div class="campo"><label>Correo electrónico</label><input type="email" name="email"></div>
						<div class="campo"><label>Teléfono / celular</label><input type="tel" name="telefono"></div>
					</div>
				</div>

				<div class="campo"><label>Cuenta asociada (si aplica)</label><input type="text" name="cuenta" placeholder="N.° de cuenta, si tu PQRS es sobre un predio específico"></div>
				<div class="campo"><label for="asunto">Asunto <span class="req">*</span></label><input type="text" id="asunto" name="asunto" required></div>
				<div class="campo"><label for="descripcion">Descripción <span class="req">*</span></label><textarea id="descripcion" name="descripcion" rows="5" required></textarea></div>
				<div class="campo">
					<label>Adjuntar soporte (opcional)</label>
					<div class="adjuntar">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M7 8l5-5 5 5"/><path d="M5 21h14"/></svg>
						<div class="txt"><strong>Sube un archivo</strong> — PDF, PNG, JPG o Word</div>
					</div>
				</div>
				<label class="campo-check" style="margin-bottom:22px"><input type="checkbox" required> Autorizo el tratamiento de mis datos personales conforme a la <a href="/politica-tratamiento-datos/">política de tratamiento de datos</a> de la EPC (si estoy radicando de forma anónima, esta autorización no aplica) <span class="req">*</span></label>

				<?php wp_nonce_field( 'epc_pqrs_pub', 'epc_pqrs_pub_nonce' ); ?>
				<input type="hidden" name="epc_pqrs_pub_submit" value="1">
				<button class="btn btn-primario" style="padding:14px 30px" type="submit">Radicar solicitud
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</button>
			</form>
		</div>

		<div class="panel-card" style="margin-top:30px">
			<h2 style="margin-bottom:6px">Consultar el estado de una PQRS</h2>
			<p style="font-size:13.5px;color:var(--gris-texto);margin-bottom:20px">Ingresa tu número de radicado y el documento o correo con el que la radicaste (si fue anónima, no podrás consultarla por este medio salvo que hayas dejado un correo).</p>

			<?php if ( $consulta_error ) : ?>
				<p style="background:#fdecea;color:#b3261e;padding:12px 16px;border-radius:10px;font-size:13.5px;font-weight:600;margin-bottom:16px;"><?php echo esc_html( $consulta_error ); ?></p>
			<?php endif; ?>

			<?php if ( $consulta ) : ?>
				<div class="stat-grid" style="margin-bottom:18px">
					<div class="stat"><div class="valor" style="font-size:15px"><?php echo esc_html( get_post_meta( $consulta->ID, '_epc_radicado', true ) ); ?></div><div class="lbl">Radicado</div></div>
					<div class="stat"><div class="valor" style="font-size:15px"><?php echo esc_html( epc_estado_label( get_post_meta( $consulta->ID, '_epc_estado', true ) ) ); ?></div><div class="lbl">Estado</div></div>
					<div class="stat"><div class="valor" style="font-size:15px"><?php echo esc_html( get_the_date( 'd/m/Y', $consulta ) ); ?></div><div class="lbl">Fecha de radicación</div></div>
				</div>
				<?php $resp = get_post_meta( $consulta->ID, '_epc_respuesta', true ); if ( $resp ) : ?>
					<div style="background:var(--gris-claro);border-radius:var(--radio-s);padding:16px 18px;font-size:13.5px"><strong>Respuesta de la EPC:</strong><br><?php echo nl2br( esc_html( $resp ) ); ?></div>
				<?php endif; ?>
			<?php endif; ?>

			<form method="post">
				<div class="campo-fila">
					<div class="campo"><label>Número de radicado <span class="req">*</span></label><input type="text" name="radicado" placeholder="Ej. PQR-2026-00421" required></div>
					<div class="campo"><label>Documento o correo con el que radicaste <span class="req">*</span></label><input type="text" name="identificador" required></div>
				</div>
				<?php wp_nonce_field( 'epc_pqrs_consulta', 'epc_pqrs_consulta_nonce' ); ?>
				<input type="hidden" name="epc_pqrs_consulta_submit" value="1">
				<button class="btn btn-outline" type="submit">Consultar estado</button>
			</form>
		</div>

	</div>
</section>
</main>
<script>
document.getElementById('epc-anonimo').addEventListener('change', function () {
	document.getElementById('epc-datos-identificado').style.display = this.checked ? 'none' : '';
});
</script>
<?php
block_template_part( 'footer' );
epc_html_close();
