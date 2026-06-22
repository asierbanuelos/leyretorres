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

// ── /comprar/ — redirige directo a checkout tras añadir al carrito ────────────
// El link es: ?add-to-cart=ID&leyre_checkout=1
// WooCommerce añade el producto con su mecanismo nativo; el filtro manda a checkout.
add_filter( 'woocommerce_add_to_cart_redirect', function( $url ) {
    if ( isset( $_REQUEST['leyre_checkout'] ) ) {
        return wc_get_checkout_url();
    }
    return $url;
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
