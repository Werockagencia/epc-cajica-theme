<?php
/**
 * Validación de propietario: un ciudadano (Usuario sin validar o
 * Arrendatario) sube su certificado de tradición y libertad para que
 * Comercial confirme que es el titular del predio y lo ascienda a
 * Propietario. Se modela como un trámite más (tipo "Validación de
 * propietario"), reutilizando la misma bandeja de radicados -- tal como
 * ya preveía el comentario original de inc/pqrs-tramites.php.
 */

define( 'EPC_TIPO_VALIDACION_PROPIETARIO', 'Validación de propietario' );

/**
 * Radica la solicitud: crea el trámite y marca al usuario como "pendiente"
 * para que Mi perfil no vuelva a mostrarle el formulario mientras se revisa.
 */
function epc_solicitar_validacion_propietario( $user_id, $doc_attachment_id, $cuenta_id ) {
	$resultado = epc_crear_tramite( [
		'tipo_tramite' => EPC_TIPO_VALIDACION_PROPIETARIO,
		'detalle'      => 'Solicitud de validación como propietario de la cuenta ' . $cuenta_id . '.',
		'documento'    => $doc_attachment_id,
		'cuenta_id'    => $cuenta_id,
	], $user_id );
	update_user_meta( $user_id, 'epc_validacion_propietario', 'pendiente' );
	update_user_meta( $user_id, 'epc_validacion_propietario_tramite', $resultado['id'] );
	return $resultado;
}

function epc_validacion_propietario_estado( $user_id ) {
	return get_user_meta( $user_id, 'epc_validacion_propietario', true ) ?: null;
}

/**
 * Todos los trámites de tipo "Validación de propietario" pendientes de
 * revisión, para la bandeja de Comercial.
 */
function epc_validaciones_propietario_pendientes() {
	return get_posts( [
		'post_type'   => 'epc_tramite',
		'post_status' => 'publish',
		'meta_query'  => [
			[ 'key' => '_epc_tipo_tramite', 'value' => EPC_TIPO_VALIDACION_PROPIETARIO ],
			[ 'key' => '_epc_estado', 'value' => [ 'nuevo', 'en_revision' ], 'compare' => 'IN' ],
		],
		'posts_per_page' => -1,
		'orderby' => 'date', 'order' => 'ASC',
	] );
}

/**
 * Cuando Comercial marca un trámite de validación de propietario como
 * "aprobado" o "rechazado" (mismo guardado que cualquier otro trámite,
 * ver inc/pqrs-tramites.php), esto reacciona: en aprobado, asciende al
 * usuario a Propietario de verdad -- no solo actualiza el radicado.
 */
add_action( 'save_post_epc_tramite', function ( $post_id ) {
	if ( ! isset( $_POST['epc_revision_nonce'] ) || ! wp_verify_nonce( $_POST['epc_revision_nonce'], 'epc_revision_save' ) ) return;
	if ( empty( $_POST['epc_estado'] ) ) return;

	$post = get_post( $post_id );
	if ( ! $post || EPC_TIPO_VALIDACION_PROPIETARIO !== get_post_meta( $post_id, '_epc_tipo_tramite', true ) ) return;
	if ( ! $post->post_author ) return;

	$estado = sanitize_key( $_POST['epc_estado'] );
	$user   = get_user_by( 'id', $post->post_author );
	if ( ! $user ) return;

	if ( 'aprobado' === $estado ) {
		$user->set_role( 'epc_propietario' ); // reemplaza epc_usuario/epc_arrendatario por Propietario.
		update_user_meta( $user->ID, 'epc_validacion_propietario', 'aprobado' );
	} elseif ( 'rechazado' === $estado ) {
		update_user_meta( $user->ID, 'epc_validacion_propietario', 'rechazado' );
	}
}, 20 );
