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
	wp_enqueue_style(
		'epc-cajica-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap',
		[],
		null
	);
} );
