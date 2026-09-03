<?php
/**
 * EPC Cajicá — funciones del tema.
 */

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

	if ( epc_is_panel_page() ) {
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
