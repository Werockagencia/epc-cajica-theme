<?php
/**
 * Vínculo arrendatario↔cuenta con aprobación del propietario. Mientras no
 * hay una tabla real de "cuentas" (eso lo dará Integra), se modela con user
 * meta: cada usuario guarda `epc_vinculo_{cuenta_id}` = pendiente|aprobado.
 * El propietario de esa cuenta ve y decide las solicitudes pendientes desde
 * su propio Mis cuentas — nunca al revés, un arrendatario no ve a otros.
 */

function epc_vinculo_meta_key( $cuenta_id ) {
	return 'epc_vinculo_' . sanitize_key( $cuenta_id );
}

function epc_vinculo_estado( $user_id, $cuenta_id ) {
	return get_user_meta( $user_id, epc_vinculo_meta_key( $cuenta_id ), true ) ?: null;
}

function epc_vinculo_solicitar( $user_id, $cuenta_id ) {
	update_user_meta( $user_id, epc_vinculo_meta_key( $cuenta_id ), 'pendiente' );
}

function epc_vinculo_set_estado( $user_id, $cuenta_id, $estado ) {
	if ( 'quitado' === $estado ) {
		delete_user_meta( $user_id, epc_vinculo_meta_key( $cuenta_id ) );
		return;
	}
	update_user_meta( $user_id, epc_vinculo_meta_key( $cuenta_id ), $estado );
}

/**
 * Un propietario siempre tiene acceso a su propia cuenta; un arrendatario
 * solo cuando el propietario ya aprobó su vínculo con esa cuenta puntual.
 * Centraliza la regla que antes solo se aplicaba en Mis cuentas -- las demás
 * páginas del panel (facturación, consumo, pagos, pagar factura) mostraban
 * los datos igual aunque el arrendatario siguiera pendiente de aprobación.
 */
function epc_usuario_tiene_acceso_cuenta( $user, $cuenta_id ) {
	if ( epc_user_is_propietario( $user ) ) return true;
	if ( epc_user_is_arrendatario( $user ) ) return 'aprobado' === epc_vinculo_estado( $user->ID, $cuenta_id );
	return false;
}

/**
 * Todos los usuarios arrendatarios vinculados (o solicitando vincularse) a
 * una cuenta, con su estado. Solo el propietario de esa cuenta debe llamar
 * a esto — no hay filtro de permisos aquí, se controla en la vista.
 */
function epc_vinculos_de_cuenta( $cuenta_id ) {
	$users = get_users( [
		'meta_key' => epc_vinculo_meta_key( $cuenta_id ),
		'role'     => 'epc_arrendatario',
	] );
	return array_map( function ( $u ) use ( $cuenta_id ) {
		return [ 'user' => $u, 'estado' => epc_vinculo_estado( $u->ID, $cuenta_id ) ];
	}, $users );
}
