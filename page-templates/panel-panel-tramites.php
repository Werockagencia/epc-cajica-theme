<?php
/**
 * /panel/panel-tramites/ — Trámites en línea desde el panel. Igual que en
 * el sitio actual: el usuario llena el formulario, llega al equipo
 * comercial, que verifica documentos/solicitud y envía el link de pago de
 * Integra. "Cambio de suscriptor" y "Suspensión temporal" son exclusivos
 * del Propietario (solo el titular del predio puede autorizarlos).
 */
$user            = wp_get_current_user();
$es_propietario  = epc_user_is_propietario( $user );
$radicado        = null;

if ( ! empty( $_POST['epc_tramite_submit'] ) && wp_verify_nonce( $_POST['epc_tramite_nonce'] ?? '', 'epc_tramite' ) ) {
	$tramite   = sanitize_text_field( wp_unslash( $_POST['tramite'] ?? '' ) );
	$resultado = epc_crear_tramite( [
		'tipo_tramite' => $tramite,
		'detalle'      => sanitize_textarea_field( wp_unslash( $_POST['detalle'] ?? '' ) ),
		'documento'    => $user->user_login,
		'cuenta_id'    => '1608353',
	], $user->ID );
	$radicado = $resultado['radicado'];

	wp_mail( 'atencion.usuario@epccajica.gov.co', "Nueva solicitud de trámite — {$radicado}",
		"Radicado: {$radicado}\nUsuario: {$user->display_name} ({$user->user_email})\nTrámite: {$tramite}\nRevisar en el panel de administración de WordPress." );
}

$tramites = [
	'reconexion'   => [ 'Solicitud de reconexión', 'Reconexión del servicio tras suspensión, previo pago de la tarifa vigente.', false ],
	'suscriptor'   => [ 'Cambio de suscriptor', 'Transferencia de la titularidad de la cuenta a un nuevo propietario.', true ],
	'suspension'   => [ 'Suspensión temporal', 'Interrupción voluntaria del servicio por un período definido.', true ],
	'estrato'      => [ 'Actualización de estrato', 'Cambio de estrato por reclasificación oficial de Planeación.', false ],
	'nomenclatura' => [ 'Actualización de nomenclatura', 'Actualización de la dirección del predio por cambio oficial.', false ],
];

$mis_tramites = get_posts( [ 'post_type' => 'epc_tramite', 'author' => $user->ID, 'posts_per_page' => 10, 'post_status' => 'publish' ] );

epc_html_open( 'Trámites en línea — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( 'panel-tramites' );
?>

<?php if ( $radicado ) : ?>
	<div class="panel-card" style="background:#eefaf0;border:1px solid #b8e6c2">
		<div class="panel-card-header"><h1>¡Trámite radicado!</h1><p>El equipo comercial verificará tus documentos y tu solicitud, y te enviará el link de pago si aplica.</p></div>
		<div class="stat" style="max-width:260px"><div class="valor"><?php echo esc_html( $radicado ); ?></div><div class="lbl">Número de radicado</div></div>
	</div>
<?php endif; ?>

<div class="panel-card">
	<div class="panel-card-header">
		<h1>Trámites en línea</h1>
		<p>Gestiona tus solicitudes sobre el servicio sin tener que ir a un punto de atención.</p>
	</div>

	<a class="chat-primero" href="https://wa.me/573163476418?text=Hola%2C%20quiero%20hacer%20un%20tr%C3%A1mite" target="_blank" rel="noopener">
		<div class="icono-chat">
			<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.66 15L2 22l5.14-1.31A10 10 0 1 0 12 2zm5.62 14.24c-.24.68-1.4 1.3-1.94 1.36-.5.06-1.12.09-1.8-.11a14.4 14.4 0 0 1-1.65-.6 11.5 11.5 0 0 1-4.5-3.98 5.3 5.3 0 0 1-1.1-2.8c0-.9.47-1.34.65-1.53.17-.18.37-.23.5-.23h.35c.13 0 .3-.02.46.35.17.4.57 1.4.62 1.5.05.1.08.22.02.36-.06.14-.09.22-.18.34-.1.12-.2.27-.28.36-.1.1-.2.21-.08.4.11.2.5.83 1.08 1.34.75.66 1.37.86 1.57.96.2.1.32.08.44-.05.13-.13.53-.62.67-.83.14-.21.28-.18.47-.1.2.07 1.25.6 1.46.7.22.11.36.16.42.25.05.1.05.55-.19 1.23z"/></svg>
		</div>
		<div class="txt">
			<div class="etq">La forma más rápida</div>
			<h2>¿Prefieres hacerlo por chat?</h2>
			<p>Cuéntale a nuestro asistente qué necesitas y te guía paso a paso — disponible las 24 horas.</p>
		</div>
		<span class="btn">Chatear ahora
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</span>
	</a>

	<h2 style="font-size:16px;margin-bottom:4px">O radica el formulario directamente</h2>
	<p style="font-size:13px;color:var(--gris-texto);margin-bottom:18px">Cada trámite se radica con los soportes que la normatividad exige, y queda con número de seguimiento.</p>

	<?php if ( ! $es_propietario ) : ?>
	<p style="font-size:12.5px;color:var(--naranja-h);background:#fdf1e6;border-radius:var(--radio-s);padding:10px 14px;margin-bottom:16px">Como arrendatario ves solo los trámites que puedes gestionar. "Cambio de suscriptor" y "Suspensión temporal" solo puede radicarlos el propietario validado del predio.</p>
	<?php endif; ?>

	<div class="tramite-grid">
		<?php foreach ( $tramites as $slug => $t ) :
			if ( $t[2] && ! $es_propietario ) continue; // exclusivo de propietario
		?>
			<div class="tramite-card">
				<h3><?php echo esc_html( $t[0] ); ?></h3>
				<p><?php echo esc_html( $t[1] ); ?></p>
				<button class="enlace" type="button" data-toggle-form="tramite-<?php echo esc_attr( $slug ); ?>" style="background:none;border:none;padding:0;cursor:pointer;font:inherit">Iniciar solicitud →</button>
			</div>
			<div class="form-inline tramite-form" id="form-tramite-<?php echo esc_attr( $slug ); ?>" hidden>
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="tramite" value="<?php echo esc_attr( $t[0] ); ?>">
					<div class="campo"><label>Detalle de tu solicitud <span class="req">*</span></label><textarea name="detalle" rows="3" required></textarea></div>
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

<div class="panel-card">
	<div class="panel-card-header"><h2>Mis trámites radicados</h2><p>Solo ves los que tú radicaste.</p></div>
	<?php if ( ! $mis_tramites ) : ?>
		<p style="font-size:13.5px;color:var(--gris-texto)">Todavía no has radicado ningún trámite.</p>
	<?php else : ?>
		<div class="tabla-wrap">
			<table class="panel-tabla">
				<thead><tr><th>N.° radicado</th><th>Fecha</th><th>Trámite</th><th>Estado</th></tr></thead>
				<tbody>
				<?php foreach ( $mis_tramites as $t ) : ?>
					<tr>
						<td><?php echo esc_html( get_post_meta( $t->ID, '_epc_radicado', true ) ); ?></td>
						<td><?php echo esc_html( get_the_date( 'd/m/Y', $t ) ); ?></td>
						<td><?php echo esc_html( $t->post_title ); ?></td>
						<td><span class="estado emitida"><?php echo esc_html( epc_estado_label( get_post_meta( $t->ID, '_epc_estado', true ) ) ); ?></span></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
<?php
epc_panel_close();
epc_html_close();
