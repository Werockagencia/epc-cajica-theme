<?php
/**
 * Red de seguridad: cualquier página bajo /panel/ que no tenga su propio
 * page-templates/panel-{slug}.php (para que nunca truene por archivo
 * faltante) cae aquí con el contenido normal de WordPress.
 */
epc_html_open( get_the_title() . ' — Portal del Ciudadano · EPC Cajicá' );
epc_panel_open( get_post_field( 'post_name', get_the_ID() ) );
?>
<div class="panel-card">
	<div class="panel-card-header"><h1><?php the_title(); ?></h1></div>
	<?php the_content(); ?>
</div>
<?php
epc_panel_close();
epc_html_close();
