<?php
defined( 'ABSPATH' ) || exit;

// ─── Rewrite rules para rutas del área privada ────────────────────────────────

add_action( 'init', 'leyre_registrar_rewrites' );

function leyre_registrar_rewrites() {
    // /mis-cursos/modulo-123
    add_rewrite_rule(
        '^mis-cursos/modulo-([0-9]+)/?$',
        'index.php?leyre_modulo_id=$matches[1]',
        'top'
    );
    // /audios/ — biblioteca de audios sin necesitar página WP
    add_rewrite_rule(
        '^audios/?$',
        'index.php?leyre_page=audios',
        'top'
    );
    // /login/ — login corporativo sin necesitar página WP
    add_rewrite_rule(
        '^login/?$',
        'index.php?leyre_page=login',
        'top'
    );
}

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'leyre_modulo_id';
    $vars[] = 'leyre_page';
    return $vars;
});

// Auto-flush rewrite rules cuando se actualiza el routing
add_action( 'init', function() {
    if ( get_option( 'leyre_routing_ver' ) !== '1.4' ) {
        flush_rewrite_rules();
        update_option( 'leyre_routing_ver', '1.4' );
    }
}, 999 );

// Filtrar wp_login_url() para que apunte al login corporativo
add_filter( 'login_url', function( $url, $redirect, $force_reauth ) {
    $custom = home_url( '/login/' );
    // add_query_arg ya urlencodea el valor, así que NO llamamos urlencode() antes
    if ( $redirect ) $custom = add_query_arg( 'redirect_to', $redirect, $custom );
    return $custom;
}, 10, 3 );

// Redirigir wp-login.php (GET) al login corporativo
add_action( 'login_init', function() {
    $action = $_REQUEST['action'] ?? 'login';
    if ( ! in_array( $action, [ 'login', '' ], true ) ) return;
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) return;
    $redirect = $_GET['redirect_to'] ?? home_url( '/area-privada/' );
    wp_redirect( home_url( '/login/?redirect_to=' . urlencode( $redirect ) ) );
    exit;
} );

// ── /comprar/ — añade el programa al carrito y redirige a checkout ────────────
add_action( 'template_redirect', function() {
    if ( ! isset( $_GET['leyre_comprar'] ) ) return;
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;

    $ids_permitidos = array_filter( array_map( 'intval', explode( ',', get_option( 'leyre_producto_id', '' ) ) ) );
    $pedido_id      = absint( $_GET['leyre_comprar'] );

    // Si el parámetro es un ID concreto y está en la lista, usarlo; si no, usar el primero
    if ( $pedido_id && in_array( $pedido_id, $ids_permitidos, true ) ) {
        $producto_id = $pedido_id;
    } else {
        $producto_id = $ids_permitidos ? reset( $ids_permitidos ) : 0;
    }
    if ( ! $producto_id ) return;

    WC()->cart->empty_cart();
    WC()->cart->add_to_cart( $producto_id );
    wp_redirect( wc_get_checkout_url() );
    exit;
} );

// Template para /login/
add_filter( 'template_include', function( $template ) {
    if ( get_query_var( 'leyre_page' ) !== 'login' ) return $template;
    $custom = get_stylesheet_directory() . '/page-login.php';
    return file_exists( $custom ) ? $custom : $template;
} );

// Template para /audios/
add_filter( 'template_include', function( $template ) {
    if ( get_query_var( 'leyre_page' ) !== 'audios' ) return $template;

    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( home_url( '/audios/' ) ) );
        exit;
    }
    if ( ! leyre_tiene_acceso() ) {
        wp_redirect( home_url( '/acceso' ) );
        exit;
    }

    $custom = get_stylesheet_directory() . '/page-audios.php';
    return file_exists( $custom ) ? $custom : $template;
});

// Template para /mis-cursos/modulo-{id}/
add_filter( 'template_include', function( $template ) {
    $modulo_id = get_query_var( 'leyre_modulo_id' );
    if ( ! $modulo_id ) return $template;

    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( home_url( '/mis-cursos/modulo-' . $modulo_id ) ) );
        exit;
    }
    if ( ! leyre_tiene_acceso() ) {
        wp_redirect( home_url( '/acceso' ) );
        exit;
    }

    $custom = get_stylesheet_directory() . '/leyre-modulo-interior.php';
    return file_exists( $custom ) ? $custom : $template;
});
