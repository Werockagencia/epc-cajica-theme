<?php
/**
 * /encuesta-satisfaccion/ — reemplaza el Google Form. Página deliberadamente
 * "oculta" (no aparece en ningún menú de navegación, solo enlazada desde
 * Home y Participa), pública y sin necesidad de iniciar sesión.
 */
$enviado = false;
if ( ! empty( $_POST['epc_encuesta_submit'] ) && wp_verify_nonce( $_POST['epc_encuesta_nonce'] ?? '', 'epc_encuesta' ) ) {
	epc_guardar_encuesta( [
		'calificacion' => absint( $_POST['calificacion'] ?? 0 ),
		'servicio'     => wp_unslash( $_POST['servicio'] ?? '' ),
		'comentario'   => wp_unslash( $_POST['comentario'] ?? '' ),
	] );
	$enviado = true;
}

epc_html_open( 'Encuesta de satisfacción — EPC Cajicá' );
block_template_part( 'header' );
?>
<main id="contenido">
<section class="pagina-hero">
	<div class="contenedor pagina-hero-wrap">
		<div class="pagina-hero-texto">
			<div class="migas-pub"><a href="/">Inicio</a> / Encuesta de satisfacción</div>
			<div class="badge-red">Participa</div>
			<h1>Tu opinión nos impulsa.</h1>
			<p class="lead">Ayúdanos a mejorar los servicios de acueducto, alcantarillado y aseo — tu encuesta es anónima y toma menos de un minuto.</p>
		</div>
	</div>
</section>

<section class="seccion">
	<div class="contenedor" style="max-width:640px">
		<?php if ( $enviado ) : ?>
			<div class="panel-card" style="background:#eefaf0;border:1px solid #b8e6c2;text-align:center;padding:40px 30px">
				<h2 style="margin-bottom:10px">¡Gracias por tu opinión! 🙌</h2>
				<p style="color:var(--gris-texto)">Tu respuesta nos ayuda a mejorar el servicio para todos los cajiqueños.</p>
				<a href="/" class="btn btn-primario" style="margin-top:18px;display:inline-flex">Volver al inicio</a>
			</div>
		<?php else : ?>
			<div class="panel-card">
				<form method="post">
					<div class="campo">
						<label>¿Qué tan satisfecho estás con nuestro servicio? <span class="req">*</span></label>
						<div style="display:flex;gap:10px;margin-top:10px" role="radiogroup" aria-label="Calificación de 1 a 5 estrellas">
							<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
								<label style="display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer;font-size:26px">
									<input type="radio" name="calificacion" value="<?php echo esc_attr( $i ); ?>" required style="width:18px;height:18px">
									<span><?php echo str_repeat( '⭐', $i ); ?></span>
								</label>
							<?php endfor; ?>
						</div>
					</div>
					<div class="campo">
						<label>¿Sobre qué servicio es tu comentario?</label>
						<select name="servicio">
							<option>General</option>
							<option>Acueducto</option>
							<option>Alcantarillado</option>
							<option>Aseo</option>
							<option>Atención al usuario</option>
							<option>Portal del Ciudadano (panel/app)</option>
						</select>
					</div>
					<div class="campo">
						<label for="comentario">¿Algo que quieras contarnos? (opcional)</label>
						<textarea id="comentario" name="comentario" rows="4"></textarea>
					</div>
					<?php wp_nonce_field( 'epc_encuesta', 'epc_encuesta_nonce' ); ?>
					<input type="hidden" name="epc_encuesta_submit" value="1">
					<button class="btn btn-primario" style="padding:14px 30px" type="submit">Enviar mi opinión
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
					</button>
				</form>
			</div>
		<?php endif; ?>
	</div>
</section>
</main>
<?php
block_template_part( 'footer' );
epc_html_close();
