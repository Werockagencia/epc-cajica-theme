<?php
/**
 * PQRS y Trámites como contenido real (no solo un correo que se pierde):
 * cada radicado queda guardado, ligado al usuario que lo creó (o anónimo),
 * con estado consultable y un panel de revisión para el equipo Comercial de
 * la EPC. La validación de "¿este usuario es realmente el propietario del
 * predio?" se maneja como un trámite más (tipo "validacion-propietario"),
 * reutilizando la misma bandeja de revisión.
 */

add_action( 'init', function () {
	register_post_type( 'epc_pqrs', [
		'labels' => [
			'name' => 'PQRS', 'singular_name' => 'PQRS', 'menu_name' => 'PQRS',
			'add_new_item' => 'Nueva PQRS', 'edit_item' => 'Revisar PQRS', 'search_items' => 'Buscar PQRS',
		],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-format-chat',
		'capability_type' => 'post',
		'supports'     => [ 'title', 'editor', 'author' ],
	] );

	register_post_type( 'epc_tramite', [
		'labels' => [
			'name' => 'Trámites', 'singular_name' => 'Trámite', 'menu_name' => 'Trámites',
			'add_new_item' => 'Nuevo trámite', 'edit_item' => 'Revisar trámite', 'search_items' => 'Buscar trámites',
		],
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-media-document',
		'capability_type' => 'post',
		'supports'     => [ 'title', 'editor', 'author' ],
	] );

	$comercial_caps = [
		'read' => true, 'edit_posts' => true, 'edit_others_posts' => true,
		'edit_published_posts' => true, 'publish_posts' => true, 'delete_posts' => true,
		'upload_files' => true, 'epc_aprobar_propietario' => true,
	];
	if ( ! get_role( 'epc_comercial' ) ) {
		add_role( 'epc_comercial', 'Personal EPC (Comercial)', $comercial_caps );
	}
} );

function epc_generar_radicado( $prefijo ) {
	return $prefijo . '-' . gmdate( 'Y' ) . '-' . wp_rand( 10000, 99999 );
}

/**
 * Crea una PQRS. $datos: tipo, asunto, descripcion, nombre, documento, email,
 * telefono, cuenta_id, anonimo (bool). user_id = 0 si es anónima.
 */
function epc_crear_pqrs( array $datos, int $user_id = 0 ) {
	$radicado = epc_generar_radicado( 'PQR' );
	$id = wp_insert_post( [
		'post_type'    => 'epc_pqrs',
		'post_title'   => $datos['asunto'],
		'post_content' => $datos['descripcion'],
		'post_status'  => 'publish',
		'post_author'  => $user_id,
	] );
	$meta = [
		'radicado' => $radicado, 'tipo' => $datos['tipo'], 'estado' => 'nuevo',
		'anonimo'  => ! empty( $datos['anonimo'] ) ? '1' : '',
		'nombre'   => $datos['nombre'] ?? '', 'documento' => $datos['documento'] ?? '',
		'email'    => $datos['email'] ?? '', 'telefono' => $datos['telefono'] ?? '',
		'cuenta_id'=> $datos['cuenta_id'] ?? '', 'respuesta' => '',
	];
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, '_epc_' . $k, $v );
	}
	return [ 'id' => $id, 'radicado' => $radicado ];
}

/**
 * Crea un trámite. $datos: tipo_tramite (slug legible), detalle, documento,
 * cuenta_id. user_id = 0 si no aplica (no debería pasar, trámites requieren
 * login salvo validación de propietario que también exige cuenta creada).
 */
function epc_crear_tramite( array $datos, int $user_id = 0 ) {
	$radicado = epc_generar_radicado( 'TRM' );
	$id = wp_insert_post( [
		'post_type'    => 'epc_tramite',
		'post_title'   => $datos['tipo_tramite'],
		'post_content' => $datos['detalle'] ?? '',
		'post_status'  => 'publish',
		'post_author'  => $user_id,
	] );
	$meta = [
		'radicado' => $radicado, 'tipo_tramite' => $datos['tipo_tramite'], 'estado' => 'nuevo',
		'documento'=> $datos['documento'] ?? '', 'cuenta_id' => $datos['cuenta_id'] ?? '', 'respuesta' => '',
	];
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, '_epc_' . $k, $v );
	}
	return [ 'id' => $id, 'radicado' => $radicado ];
}

function epc_estado_label( $estado ) {
	$labels = [
		'nuevo'            => 'Recibida',
		'en_revision'      => 'En revisión',
		'respondida'       => 'Respondida',
		'escalada_integra' => 'Escalada a Integra',
		'cerrada'          => 'Cerrada',
		'aprobado'         => 'Aprobado',
		'rechazado'        => 'Rechazado',
	];
	return $labels[ $estado ] ?? ucfirst( $estado );
}

/* ---------------- Bandeja de revisión (admin) ---------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'epc_revision', 'Revisión Comercial', 'epc_render_revision_metabox', [ 'epc_pqrs', 'epc_tramite' ], 'normal', 'high' );
} );

// El formulario de edición de WP no trae multipart/form-data por defecto -- sin
// esto, el <input type="file"> del adjunto PDF nunca llega a $_FILES.
add_action( 'post_edit_form_tag', function () {
	if ( in_array( get_post_type(), [ 'epc_pqrs', 'epc_tramite' ], true ) ) {
		echo ' enctype="multipart/form-data"';
	}
} );

function epc_render_revision_metabox( $post ) {
	wp_nonce_field( 'epc_revision_save', 'epc_revision_nonce' );
	$get = fn( $k ) => get_post_meta( $post->ID, '_epc_' . $k, true );
	$estados = 'epc_pqrs' === $post->post_type
		? [ 'nuevo', 'en_revision', 'respondida', 'escalada_integra', 'cerrada' ]
		: [ 'nuevo', 'en_revision', 'aprobado', 'rechazado', 'escalada_integra', 'cerrada' ];
	?>
	<p><strong>Radicado:</strong> <?php echo esc_html( $get( 'radicado' ) ); ?></p>
	<?php if ( 'epc_pqrs' === $post->post_type ) : ?>
		<p><strong>Tipo:</strong> <?php echo esc_html( $get( 'tipo' ) ); ?> — <strong>Anónima:</strong> <?php echo $get( 'anonimo' ) ? 'Sí' : 'No'; ?></p>
		<p><strong>Solicitante:</strong> <?php echo esc_html( $get( 'nombre' ) ?: '(anónimo)' ); ?> · Doc: <?php echo esc_html( $get( 'documento' ) ); ?><br>
		<strong>Email:</strong> <?php echo esc_html( $get( 'email' ) ); ?> · <strong>Tel:</strong> <?php echo esc_html( $get( 'telefono' ) ); ?></p>
	<?php else : ?>
		<p><strong>Trámite:</strong> <?php echo esc_html( $get( 'tipo_tramite' ) ); ?></p>
	<?php endif; ?>
	<p><strong>Cuenta:</strong> <?php echo esc_html( $get( 'cuenta_id' ) ?: '—' ); ?></p>
	<p><label for="epc_estado"><strong>Estado</strong></label><br>
		<select name="epc_estado" id="epc_estado">
			<?php foreach ( $estados as $e ) : ?>
				<option value="<?php echo esc_attr( $e ); ?>" <?php selected( $get( 'estado' ), $e ); ?>><?php echo esc_html( epc_estado_label( $e ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p><label for="epc_respuesta"><strong>Respuesta al ciudadano</strong> (si se responde directo, sin pasar a Integra)</label><br>
		<textarea name="epc_respuesta" id="epc_respuesta" rows="4" style="width:100%"><?php echo esc_textarea( $get( 'respuesta' ) ); ?></textarea>
	</p>
	<p>
		<label for="epc_respuesta_pdf"><strong>Adjuntar respuesta escaneada (PDF)</strong></label><br>
		<span class="description">Para cuando Integra entrega la respuesta como un escaneo/PDF y el asesor solo necesita subirlo.</span><br>
		<?php $pdf_id = (int) get_post_meta( $post->ID, '_epc_respuesta_pdf', true ); ?>
		<?php if ( $pdf_id && get_post( $pdf_id ) ) : ?>
			<p><a href="<?php echo esc_url( wp_get_attachment_url( $pdf_id ) ); ?>" target="_blank">📎 <?php echo esc_html( basename( get_attached_file( $pdf_id ) ) ); ?></a>
			— <label><input type="checkbox" name="epc_respuesta_pdf_quitar" value="1"> quitar</label></p>
		<?php endif; ?>
		<input type="file" name="epc_respuesta_pdf" accept="application/pdf">
	</p>
	<p class="description">Si el caso requiere gestión en Integra (pago, cambio de datos, etc.), márcalo como "Escalada a Integra" — el estado quedará visible para el ciudadano igual.</p>
	<?php
}

// Una PQRS anónima no debe dejar de serlo solo porque un asesor la abrió y
// guardó cambios: el cuadro "Autor" de WP no tiene opción "(anónimo)", así
// que al guardar el formulario reasigna el post a quien esté editando. Se
// reafirma post_author=0 después de cada guardado si la PQRS nació anónima.
add_action( 'save_post', function ( $post_id ) {
	static $en_progreso = [];
	if ( 'epc_pqrs' !== get_post_type( $post_id ) || isset( $en_progreso[ $post_id ] ) ) return;
	if ( ! get_post_meta( $post_id, '_epc_anonimo', true ) ) return;
	if ( 0 === (int) get_post( $post_id )->post_author ) return;
	$en_progreso[ $post_id ] = true;
	wp_update_post( [ 'ID' => $post_id, 'post_author' => 0 ] );
}, 20 );

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['epc_revision_nonce'] ) || ! wp_verify_nonce( $_POST['epc_revision_nonce'], 'epc_revision_save' ) ) return;
	if ( ! in_array( get_post_type( $post_id ), [ 'epc_pqrs', 'epc_tramite' ], true ) ) return;
	if ( isset( $_POST['epc_estado'] ) ) {
		update_post_meta( $post_id, '_epc_estado', sanitize_key( $_POST['epc_estado'] ) );
	}
	if ( isset( $_POST['epc_respuesta'] ) ) {
		update_post_meta( $post_id, '_epc_respuesta', sanitize_textarea_field( wp_unslash( $_POST['epc_respuesta'] ) ) );
	}
	if ( ! empty( $_POST['epc_respuesta_pdf_quitar'] ) ) {
		delete_post_meta( $post_id, '_epc_respuesta_pdf' );
	}
	if ( ! empty( $_FILES['epc_respuesta_pdf']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_id = media_handle_upload( 'epc_respuesta_pdf', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			update_post_meta( $post_id, '_epc_respuesta_pdf', $attachment_id );
		}
	}
} );

foreach ( [ 'epc_pqrs', 'epc_tramite' ] as $cpt ) {
	add_filter( "manage_{$cpt}_posts_columns", function ( $cols ) use ( $cpt ) {
		$new = [ 'cb' => $cols['cb'], 'title' => 'Asunto', 'radicado' => 'Radicado', 'estado' => 'Estado', 'author' => 'Usuario', 'date' => $cols['date'] ];
		return $new;
	} );
	add_action( "manage_{$cpt}_posts_custom_column", function ( $col, $post_id ) {
		if ( 'radicado' === $col ) echo esc_html( get_post_meta( $post_id, '_epc_radicado', true ) );
		if ( 'estado' === $col ) echo esc_html( epc_estado_label( get_post_meta( $post_id, '_epc_estado', true ) ) );
	}, 10, 2 );
}
