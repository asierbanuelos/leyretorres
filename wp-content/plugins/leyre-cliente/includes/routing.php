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
}

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'leyre_modulo_id';
    $vars[] = 'leyre_page';
    return $vars;
});

// Auto-flush rewrite rules cuando se actualiza el routing
add_action( 'init', function() {
    if ( get_option( 'leyre_routing_ver' ) !== '1.2' ) {
        flush_rewrite_rules();
        update_option( 'leyre_routing_ver', '1.2' );
    }
}, 999 );

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
