<?php
defined( 'ABSPATH' ) || exit;

// Encolar estilos del padre + hijo
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'hello-elementor-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'leyre-child-style', get_stylesheet_uri(), [ 'hello-elementor-style' ], filemtime( get_stylesheet_directory() . '/style.css' ) );

    // Google Fonts
    wp_enqueue_style(
        'leyre-google-fonts',
        'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400;1,700&family=Inter:wght@400;600;700&display=swap',
        [],
        null
    );
});

// Añadir clase body en el área privada
add_filter( 'body_class', function( $classes ) {
    $slugs_privados = [ 'area-privada', 'mis-cursos', 'mis-sesiones', 'recursos', 'audios', 'mi-perfil' ];
    if ( is_page( $slugs_privados ) ) {
        $classes[] = 'leyre-area-privada';
    }
    return $classes;
});
