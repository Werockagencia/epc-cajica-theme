<?php
/**
 * EPC Cajicá — funciones del tema.
 */

require get_template_directory() . '/inc/roles.php';

// El Portal del Ciudadano no es zona de administración: sin barra de admin
// para los roles del panel, aunque sí para editores/administradores.
add_filter( 'show_admin_bar', function ( $show ) {
	$user = wp_get_current_user();
	$epc_roles = [ 'epc_usuario', 'epc_propietario', 'epc_arrendatario', 'epc_comercial' ];
	if ( array_intersect( $epc_roles, (array) $user->roles ) ) {
		return false;
	}
	return $show;
} );
require get_template_directory() . '/inc/auth.php';
require get_template_directory() . '/inc/panel-shell.php';
require get_template_directory() . '/inc/pqrs-tramites.php';
require get_template_directory() . '/inc/comercial-panel.php';
require get_template_directory() . '/inc/encuesta.php';
require get_template_directory() . '/inc/vinculos.php';
require get_template_directory() . '/inc/validacion-propietario.php';

/**
 * Abre el <html><head>...<body> de una plantilla PHP clásica (login/panel),
 * ya que estas no pasan por header.html del tema de bloques.
 */
function epc_html_open( $title ) {
	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $title ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<?php
}

function epc_html_close() {
	wp_footer();
	?>
</body>
</html>
	<?php
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus( [
		'principal' => __( 'Menú principal', 'epc-cajica' ),
		'transparencia' => __( 'Mega menú — Transparencia', 'epc-cajica' ),
		'pie' => __( 'Enlaces del pie de página', 'epc-cajica' ),
	] );
} );

add_action( 'wp_enqueue_scripts', function () {
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	wp_enqueue_style(
		'epc-cajica-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'epc-cajica-style',
		$theme_uri . '/assets/css/style.css',
		[ 'epc-cajica-fonts' ],
		filemtime( $theme_dir . '/assets/css/style.css' )
	);

	wp_enqueue_script(
		'epc-cajica-site',
		$theme_uri . '/assets/js/site.js',
		[],
		filemtime( $theme_dir . '/assets/js/site.js' ),
		true
	);

	wp_enqueue_script(
		'epc-cajica-header',
		$theme_uri . '/assets/js/header.js',
		[],
		filemtime( $theme_dir . '/assets/js/header.js' ),
		true
	);

	if ( epc_is_panel_page() || is_page( 'login' ) || is_page( 'crear-cuenta' ) || is_page( 'pqrs' ) || is_page( 'encuesta-satisfaccion' ) ) {
		wp_enqueue_style(
			'epc-cajica-panel',
			$theme_uri . '/assets/css/panel.css',
			[ 'epc-cajica-style' ],
			filemtime( $theme_dir . '/assets/css/panel.css' )
		);
		wp_enqueue_script(
			'epc-cajica-panel',
			$theme_uri . '/assets/js/panel.js',
			[],
			filemtime( $theme_dir . '/assets/js/panel.js' ),
			true
		);
	}
} );

/**
 * true si la página actual es /panel/ o una de sus páginas hijas.
 */
function epc_is_panel_page() {
	if ( ! is_page() ) return false;
	$post = get_post();
	if ( ! $post ) return false;
	if ( 'panel' === $post->post_name ) return true;
	$ancestors = get_post_ancestors( $post );
	foreach ( $ancestors as $ancestor_id ) {
		if ( 'panel' === get_post_field( 'post_name', $ancestor_id ) ) return true;
	}
	return false;
}
