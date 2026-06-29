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

// Filtrar wp_login_url() para que apunte al login corporativo.
// Si el destino es wp-admin, dejamos wp-login.php intacto para que los admins
// puedan acceder al panel sin pasar por el login de alumnas.
add_filter( 'login_url', function( $url, $redirect, $force_reauth ) {
    if ( $redirect && strpos( $redirect, 'wp-admin' ) !== false ) {
        return $url;
    }
    $custom = home_url( '/login/' );
    if ( $redirect ) $custom = add_query_arg( 'redirect_to', $redirect, $custom );
    return $custom;
}, 10, 3 );

// Redirigir wp-login.php (GET) al login corporativo, salvo si el destino es wp-admin.
add_action( 'login_init', function() {
    $action = $_REQUEST['action'] ?? 'login';
    if ( ! in_array( $action, [ 'login', '' ], true ) ) return;
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) return;
    $redirect = $_GET['redirect_to'] ?? '';
    if ( $redirect && strpos( $redirect, 'wp-admin' ) !== false ) return;
    $url = home_url( '/login/' );
    if ( $redirect ) $url = add_query_arg( 'redirect_to', $redirect, $url );
    wp_redirect( $url );
    exit;
} );

// ── /comprar/ — redirige directo a checkout tras añadir al carrito ────────────
// El link es: ?add-to-cart=ID&leyre_checkout=1
// Vaciamos el carrito antes (prioridad 15, antes del add_to_cart_action de WC en 20)
// para que no acumule si se pincha varias veces.
add_action( 'wp_loaded', function() {
    if ( ! isset( $_REQUEST['add-to-cart'], $_REQUEST['leyre_checkout'] ) ) return;
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) return;
    WC()->cart->empty_cart();
}, 15 );

// Tras añadir, redirigir a checkout en vez de al carrito.
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
