<?php
/**
 * /panel/panel-tramites/ — Trámites en línea desde el panel. Igual que en
 * el sitio actual: el usuario llena el formulario, llega por correo al
 * equipo comercial, que verifica documentos/solicitud y envía el link de
 * pago de Integra. No hay integración directa con Integra todavía.
 */
$radicado = null;
if ( ! empty( $_POST['epc_tramite_submit'] ) && wp_verify_nonce( $_POST['epc_tramite_nonce'] ?? '', 'epc_tramite' ) ) {
	$user     = wp_get_current_user();
	$tramite  = sanitize_text_field( wp_unslash( $_POST['tramite'] ?? '' ) );
	$detalle  = sanitize_textarea_field( wp_unslash( $_POST['detalle'] ?? '' ) );
	$radicado = 'TRM-' . gmdate( 'Y' ) . '-' . wp_rand( 10000, 99999 );

	wp_mail(
		'atencion.usuario@epccajica.gov.co',
		"Nueva solicitud de trámite — {$radicado}",
		"Radicado: {$radicado}\nUsuario: {$user->display_name} ({$user->user_email})\nTrámite: {$tramite}\n\nDetalle:\n{$detalle}"
	);
}

$tramites = [
	'reconexion'    => [ 'Solicitud de reconexión', 'Reconexión del servicio tras suspensión, previo pago de la tarifa vigente.' ],
	'suscriptor'    => [ 'Cambio de suscriptor', 'Transferencia de la titularidad de la cuenta a un nuevo propietario.' ],
	'suspension'    => [ 'Suspensión temporal', 'Interrupción voluntaria del servicio por un período definido.' ],
	'estrato'       => [ 'Actualización de estrato', 'Cambio de estrato por reclasificación oficial de Planeación.' ],
	'nomenclatura'  => [ 'Actualización de nomenclatura', 'Actualización de la dirección del predio por cambio oficial.' ],
];

epc_html_open( 'Trámites en línea — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'panel-tramites' );
?>

<?php if ( $radicado ) : ?>
	<div class="panel-card" style="background:#eefaf0;border:1px solid #b8e6c2">
		<div class="panel-card-header">
			<h1>¡Trámite radicado!</h1>
			<p>El equipo comercial verificará tus documentos y tu solicitud, y te enviará el link de pago si aplica.</p>
		</div>
		<div class="stat" style="max-width:260px"><div class="valor"><?php echo esc_html( $radicado ); ?></div><div class="lbl">Número de radicado</div></div>
	</div>
<?php endif; ?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Trámites en línea</h1>
		<p>Gestiona tus solicitudes sobre el servicio sin tener que ir a un punto de atención.</p>
	</div>

	<div class="tramite-grid">
		<?php foreach ( $tramites as $slug => $t ) : ?>
			<div class="tramite-card">
				<h3><?php echo esc_html( $t[0] ); ?></h3>
				<p><?php echo esc_html( $t[1] ); ?></p>
				<button class="enlace" type="button" data-toggle-form="tramite-<?php echo esc_attr( $slug ); ?>" style="background:none;border:none;padding:0;cursor:pointer;font:inherit">Iniciar solicitud →</button>
			</div>
			<div class="form-inline tramite-form" id="form-tramite-<?php echo esc_attr( $slug ); ?>" hidden>
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="tramite" value="<?php echo esc_attr( $t[0] ); ?>">
					<div class="campo">
						<label>Detalle de tu solicitud <span class="req">*</span></label>
						<textarea name="detalle" rows="3" required></textarea>
					</div>
					<div class="campo">
						<label>Documentos soporte</label>
						<div class="adjuntar">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M7 8l5-5 5 5"/><path d="M5 21h14"/></svg>
							<div class="txt"><strong>Sube tus archivos</strong> — cédula, certificado de tradición y libertad, último recibo, etc. según el trámite</div>
						</div>
					</div>
					<?php wp_nonce_field( 'epc_tramite', 'epc_tramite_nonce' ); ?>
					<input type="hidden" name="epc_tramite_submit" value="1">
					<button class="btn btn-azul" type="submit">Radicar trámite</button>
				</form>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<?php
epc_panel_close();
epc_html_close();
