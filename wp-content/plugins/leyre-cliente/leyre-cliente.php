<?php
/**
 * Plugin Name: Leyre Cliente — Área Privada
 * Description: Área de cliente para el programa Leonas en Tacones. Acceso custom, CPTs, progreso, Calendly y descargas seguras.
 * Version:     1.0.0
 * Author:      Asier Bañuelos
 * Text Domain: leyre-cliente
 */

defined( 'ABSPATH' ) || exit;

define( 'LEYRE_VERSION',     '1.0.0' );
define( 'LEYRE_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'LEYRE_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

require_once LEYRE_PLUGIN_DIR . 'includes/access.php';
require_once LEYRE_PLUGIN_DIR . 'includes/cpts.php';
require_once LEYRE_PLUGIN_DIR . 'includes/progress.php';
require_once LEYRE_PLUGIN_DIR . 'includes/calendly.php';
require_once LEYRE_PLUGIN_DIR . 'includes/downloads.php';
require_once LEYRE_PLUGIN_DIR . 'includes/rest-api.php';
require_once LEYRE_PLUGIN_DIR . 'includes/admin.php';
require_once LEYRE_PLUGIN_DIR . 'includes/emails.php';

add_action( 'wp_enqueue_scripts', function() {
    if ( ! leyre_es_pagina_privada() ) return;
    wp_enqueue_style(
        'leyre-area-cliente',
        LEYRE_PLUGIN_URL . 'assets/css/area-cliente.css',
        [],
        LEYRE_VERSION
    );
    wp_enqueue_script(
        'leyre-area-cliente',
        LEYRE_PLUGIN_URL . 'assets/js/area-cliente.js',
        [],
        LEYRE_VERSION,
        true
    );
    wp_localize_script( 'leyre-area-cliente', 'leyreConfig', [
        'apiUrl'   => rest_url( 'leyre/v1/' ),
        'nonce'    => wp_create_nonce( 'wp_rest' ),
        'userId'   => get_current_user_id(),
        'userName' => wp_get_current_user()->display_name,
    ]);
});

function leyre_es_pagina_privada() {
    $slugs = [ 'area-privada', 'mis-cursos', 'mis-sesiones', 'recursos', 'mi-perfil' ];
    return is_page( $slugs );
}
