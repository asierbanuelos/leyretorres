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
}

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'leyre_modulo_id';
    return $vars;
});

// Cargar el template del child theme para la ruta del módulo
add_filter( 'template_include', function( $template ) {
    $modulo_id = get_query_var( 'leyre_modulo_id' );
    if ( ! $modulo_id ) return $template;

    // Verificar acceso
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
