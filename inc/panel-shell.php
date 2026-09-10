<?php
/**
 * Fuerza plantillas PHP propias para /login/ y /panel/* (más confiable que
 * depender de la jerarquía page-{slug}.php en un tema de bloques híbrido).
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_page( 'login' ) ) {
		return get_template_directory() . '/page-templates/login.php';
	}
	if ( is_page( 'crear-cuenta' ) ) {
		return get_template_directory() . '/page-templates/crear-cuenta.php';
	}
	if ( is_page( 'pqrs' ) ) {
		return get_template_directory() . '/page-templates/pqrs-publico.php';
	}
	if ( epc_is_panel_page() ) {
		$slug     = get_post_field( 'post_name', get_the_ID() );
		$specific = get_template_directory() . "/page-templates/panel-{$slug}.php";
		if ( file_exists( $specific ) ) {
			return $specific;
		}
		return get_template_directory() . '/page-templates/panel-generico.php';
	}
	return $template;
} );

/**
 * Ítems del menú lateral del panel. El menú cambia según el rol: personal
 * EPC (Comercial/Administrador) ve su propia bandeja de gestión en vez de
 * las páginas de un ciudadano (facturación, consumo, pagos no le aplican).
 */
function epc_panel_nav_items() {
	$user = wp_get_current_user();
	if ( in_array( 'epc_comercial', (array) $user->roles, true ) ) {
		return [
			[ 'slug' => 'comercial', 'label' => 'Panel Comercial', 'icon' => 'home' ],
			[ 'slug' => 'comercial-pqrs', 'label' => 'PQRS', 'icon' => 'pqrs' ],
			[ 'slug' => 'comercial-tramites', 'label' => 'Trámites', 'icon' => 'tramites' ],
			[ 'slug' => 'comercial-validaciones', 'label' => 'Validaciones de propietario', 'icon' => 'user' ],
			[ 'slug' => 'comercial-usuarios', 'label' => 'Usuarios', 'icon' => 'bars' ],
			[ 'slug' => 'perfil', 'label' => 'Perfil', 'icon' => 'user' ],
		];
	}
	return [
		[ 'slug' => '', 'label' => 'Mi panel', 'icon' => 'home' ],
		[ 'slug' => 'perfil', 'label' => 'Perfil', 'icon' => 'user' ],
		[ 'slug' => 'mis-cuentas', 'label' => 'Mis cuentas', 'icon' => 'home' ],
		[ 'slug' => 'facturacion', 'label' => 'Historial de facturación', 'icon' => 'doc' ],
		[ 'slug' => 'consumo', 'label' => 'Historial de consumo', 'icon' => 'bars' ],
		[ 'slug' => 'pagos', 'label' => 'Historial de pagos', 'icon' => 'card' ],
		[ 'slug' => 'pagar-factura', 'label' => 'Pagar factura', 'icon' => 'pay', 'destacado' => true ],
		[ 'slug' => 'panel-pqrs', 'label' => 'Radicar PQRS', 'icon' => 'pqrs' ],
		[ 'slug' => 'panel-tramites', 'label' => 'Trámites en línea', 'icon' => 'tramites' ],
	];
}

function epc_panel_icon_svg( $name ) {
	$icons = [
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/>',
		'home'     => '<path d="M4 11l8-7 8 7"/><path d="M6 9v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9"/>',
		'doc'      => '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M9 12h6M9 16h6"/>',
		'bars'     => '<path d="M4 20V12M10 20V6M16 20v-6M4 20h16"/>',
		'card'     => '<path d="M4 4h16v16l-3-2-3 2-3-2-3 2-4-2V4z"/><path d="M8 9h8M8 13h5"/>',
		'pay'      => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M16 12h3M3 10h18"/>',
		'pqrs'     => '<path d="M4 5h16v11H8l-4 4V5z"/><path d="M8 10h8M8 13h5"/>',
		'tramites' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3h6v3H9z"/><path d="M9 13l2 2 4-4"/>',
	];
	return $icons[ $name ] ?? '';
}

/**
 * Abre el layout del panel (header + nav lateral). $active_slug = post_name
 * de la página actual, para resaltar el ítem del menú correspondiente.
 */
function epc_panel_open( $active_slug ) {
	$user    = wp_get_current_user();
	$iniciales = strtoupper( mb_substr( $user->first_name ?: $user->display_name, 0, 1 ) . mb_substr( $user->last_name, 0, 1 ) );
	?>
	<div class="gov-bar">
		<div class="gov-bar-inner">
			<a href="https://www.gov.co" class="gov-logo" target="_blank" rel="noopener"><span class="gov-tricolor"><i></i><i></i><i></i></span><span class="gov-wordmark">GOV.CO</span></a>
		</div>
	</div>
	<header class="panel-header">
		<div class="panel-header-inner">
			<div style="display:flex;align-items:center;gap:14px">
				<button class="panel-menu-btn" aria-label="Abrir menú"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="panel-logo"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="EPC — Empresa de Servicios Públicos de Cajicá"></a>
			</div>
			<div class="panel-header-acciones">
				<div class="panel-usuario">
					<div class="panel-usuario-avatar"><?php echo esc_html( $iniciales ?: 'EPC' ); ?></div>
					<div class="panel-usuario-info">
						<div class="panel-usuario-nombre"><?php echo esc_html( $user->display_name ); ?></div>
						<div class="panel-usuario-cuenta"><?php echo esc_html( epc_user_role_label( $user ) ); ?></div>
					</div>
				</div>
				<a href="<?php echo esc_url( home_url( '/?epc_logout=1' ) ); ?>" class="panel-salir">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
					Salir
				</a>
			</div>
		</div>
	</header>
	<div class="panel-nav-overlay"></div>
	<div class="panel-layout">
		<nav class="panel-nav">
			<div class="panel-bienvenida">
				<div class="hola">Bienvenido</div>
				<div class="nombre"><?php echo esc_html( $user->first_name ?: $user->display_name ); ?></div>
				<div class="rol-badge"><?php echo esc_html( epc_user_role_label( $user ) ); ?></div>
			</div>
			<?php foreach ( epc_panel_nav_items() as $item ) : ?>
				<a href="<?php echo esc_url( trailingslashit( home_url( '/panel/' . $item['slug'] ) ) ); ?>"
				   class="<?php echo $active_slug === $item['slug'] ? 'activo' : ''; ?> <?php echo ! empty( $item['destacado'] ) ? 'destacado' : ''; ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?php echo epc_panel_icon_svg( $item['icon'] ); ?></svg>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			<?php endforeach; ?>
			<div class="separador"></div>
			<a href="<?php echo esc_url( home_url( '/?epc_logout=1' ) ); ?>" class="panel-nav-salir">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
				Cerrar sesión
			</a>
		</nav>
		<main class="panel-contenido">
<?php }

function epc_panel_close() {
	?>
		</main>
	</div>
	<a class="chat-flotante" href="https://wa.me/573163476418" target="_blank" rel="noopener" aria-label="Chatear por WhatsApp">
		<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.66 15L2 22l5.14-1.31A10 10 0 1 0 12 2zm5.62 14.24c-.24.68-1.4 1.3-1.94 1.36-.5.06-1.12.09-1.8-.11a14.4 14.4 0 0 1-1.65-.6 11.5 11.5 0 0 1-4.5-3.98 5.3 5.3 0 0 1-1.1-2.8c0-.9.47-1.34.65-1.53.17-.18.37-.23.5-.23h.35c.13 0 .3-.02.46.35.17.4.57 1.4.62 1.5.05.1.08.22.02.36-.06.14-.09.22-.18.34-.1.12-.2.27-.28.36-.1.1-.2.21-.08.4.11.2.5.83 1.08 1.34.75.66 1.37.86 1.57.96.2.1.32.08.44-.05.13-.13.53-.62.67-.83.14-.21.28-.18.47-.1.2.07 1.25.6 1.46.7.22.11.36.16.42.25.05.1.05.55-.19 1.23z"/></svg>
	</a>
	<?php
}
