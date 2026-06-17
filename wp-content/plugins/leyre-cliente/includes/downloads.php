<?php
defined( 'ABSPATH' ) || exit;

// ─── B-05: Descargas seguras ──────────────────────────────────────────────────

// Proteger el directorio de archivos privados
add_action( 'init', 'leyre_crear_htaccess_privado' );
function leyre_crear_htaccess_privado() {
    $dir      = WP_CONTENT_DIR . '/uploads/leyre-privado';
    $htaccess = $dir . '/.htaccess';
    if ( ! file_exists( $dir ) ) wp_mkdir_p( $dir );
    if ( ! file_exists( $htaccess ) ) {
        file_put_contents( $htaccess, "deny from all\n" );
    }
}

// Endpoint de descarga: /descargar-recurso/?id=X
add_action( 'init', 'leyre_registrar_endpoint_descarga' );
function leyre_registrar_endpoint_descarga() {
    add_rewrite_rule( '^descargar-recurso/?$', 'index.php?leyre_descarga=1', 'top' );
    add_rewrite_tag( '%leyre_descarga%', '1' );
}

add_action( 'template_redirect', 'leyre_servir_descarga' );
function leyre_servir_descarga() {
    if ( ! get_query_var( 'leyre_descarga' ) ) return;

    if ( ! is_user_logged_in() || ! leyre_tiene_acceso() ) {
        wp_redirect( home_url( '/acceso' ) );
        exit;
    }

    $recurso_id = absint( $_GET['id'] ?? 0 );
    if ( ! $recurso_id ) wp_die( 'Recurso no válido.', 400 );

    $post = get_post( $recurso_id );
    if ( ! $post || $post->post_type !== 'leyre_recurso' ) wp_die( 'Recurso no encontrado.', 404 );

    $ruta_relativa = get_post_meta( $recurso_id, '_leyre_ruta_archivo', true );
    if ( ! $ruta_relativa ) wp_die( 'Archivo no disponible.', 404 );

    $ruta_completa = WP_CONTENT_DIR . '/uploads/leyre-privado/' . $ruta_relativa;
    if ( ! file_exists( $ruta_completa ) ) wp_die( 'Archivo no encontrado en el servidor.', 404 );

    $nombre_archivo = basename( $ruta_completa );
    $mime           = mime_content_type( $ruta_completa ) ?: 'application/octet-stream';

    header( 'Content-Type: ' . $mime );
    header( 'Content-Disposition: attachment; filename="' . $nombre_archivo . '"' );
    header( 'Content-Length: ' . filesize( $ruta_completa ) );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Cache-Control: no-store' );

    while ( ob_get_level() ) ob_end_clean();
    readfile( $ruta_completa );
    exit;
}
