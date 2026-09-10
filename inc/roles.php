<?php
/**
 * Roles de usuario del Portal del Ciudadano.
 *
 * - epc_usuario: cuenta básica recién creada, aún sin validar como propietario
 *   de ningún predio (puede vincular medidores como arrendatario, pero no
 *   hacer trámites reservados al titular del predio).
 * - epc_propietario: validado como titular de al menos una cuenta/predio.
 *   Único rol que puede gestionar acuerdos de pago y cambio de suscriptor.
 * - epc_arrendatario: asociado a una cuenta como arrendatario (no titular).
 *   Puede asociar medidor, automatizar pagos y pagar, pero no firma
 *   acuerdos de pago ni cambia el suscriptor.
 *
 * Todos heredan las capacidades de "subscriber" (leer, editar su perfil) más
 * las capacidades propias del panel, listadas abajo.
 */
add_action( 'init', function () {
	$panel_caps = [
		'read'                 => true,
		'epc_ver_panel'        => true,
		'epc_ver_facturacion'  => true,
		'epc_pagar_factura'    => true,
		'epc_radicar_pqrs'     => true,
		'epc_radicar_tramites' => true,
	];

	if ( ! get_role( 'epc_usuario' ) ) {
		add_role( 'epc_usuario', 'Usuario EPC (sin validar)', $panel_caps );
	}

	if ( ! get_role( 'epc_propietario' ) ) {
		add_role( 'epc_propietario', 'Propietario', array_merge( $panel_caps, [
			'epc_acuerdo_pago'       => true,
			'epc_cambio_suscriptor'  => true,
			'epc_administrar_cuenta' => true,
		] ) );
	}

	if ( ! get_role( 'epc_arrendatario' ) ) {
		add_role( 'epc_arrendatario', 'Arrendatario', array_merge( $panel_caps, [
			'epc_asociar_medidor'      => true,
			'epc_automatizar_pagos'    => true,
		] ) );
	}

	// Roles internos de WordPress (gestión del sitio, no del portal del
	// ciudadano): Administrador ya existe por defecto con acceso total.
	// Informática y Comunicaciones son equivalentes al rol nativo "Editor"
	// (páginas, entradas, medios) — ajustar capacidades puntuales si el
	// cliente pide algo más fino (ej. Comunicaciones sin borrar páginas).
	$editor = get_role( 'editor' );
	$editor_caps = $editor ? $editor->capabilities : [];
	if ( ! get_role( 'epc_informatica' ) ) {
		add_role( 'epc_informatica', 'Informática', $editor_caps );
	}
	if ( ! get_role( 'epc_comunicaciones' ) ) {
		add_role( 'epc_comunicaciones', 'Comunicaciones', $editor_caps );
	}
} );

/**
 * Etiqueta legible del rol EPC del usuario actual (o el pasado por parámetro).
 */
function epc_user_role_label( $user = null ) {
	$user = $user ?: wp_get_current_user();
	if ( in_array( 'epc_propietario', (array) $user->roles, true ) ) return 'Propietario';
	if ( in_array( 'epc_arrendatario', (array) $user->roles, true ) ) return 'Arrendatario';
	if ( in_array( 'epc_usuario', (array) $user->roles, true ) ) return 'Usuario (sin validar)';
	return 'Usuario';
}

function epc_user_is_propietario( $user = null ) {
	$user = $user ?: wp_get_current_user();
	return in_array( 'epc_propietario', (array) $user->roles, true );
}

function epc_user_is_arrendatario( $user = null ) {
	$user = $user ?: wp_get_current_user();
	return in_array( 'epc_arrendatario', (array) $user->roles, true );
}
