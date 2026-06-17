<?php
/**
 * Template Name: Sin acceso — Leonas en Tacones
 * Página /acceso — se muestra cuando el acceso ha caducado o no existe.
 */
defined( 'ABSPATH' ) || exit;

// Si tiene acceso activo, redirigir al área privada directamente
if ( is_user_logged_in() && function_exists( 'leyre_tiene_acceso' ) && leyre_tiene_acceso() ) {
    wp_redirect( home_url( '/area-privada' ) );
    exit;
}

$caducada = is_user_logged_in();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al programa — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada leyre-sin-acceso-page' ); ?>>
<?php wp_body_open(); ?>

<div class="leyre-acceso-wrap">
    <div class="leyre-acceso-card">

        <div class="leyre-acceso-logo">Leonas en Tacones</div>

        <?php if ( $caducada ) : ?>
            <div class="leyre-acceso-icon">⏳</div>
            <h1 class="leyre-acceso-title">Tu acceso ha caducado</h1>
            <p class="leyre-acceso-desc">
                El período de acceso a tu programa ha finalizado.<br>
                Si crees que es un error, escríbenos.
            </p>
            <a href="<?php echo home_url( '/' ); ?>" class="leyre-btn leyre-btn--beige">Ver el programa de nuevo</a>
            <p class="leyre-acceso-footer">
                <a href="<?php echo wp_logout_url( home_url() ); ?>">Cerrar sesión</a>
            </p>
        <?php else : ?>
            <div class="leyre-acceso-icon">🔒</div>
            <h1 class="leyre-acceso-title">Área exclusiva para alumnas</h1>
            <p class="leyre-acceso-desc">
                Esta sección es privada y está disponible solo para alumnas de <strong>Leonas en Tacones</strong>.
            </p>
            <a href="<?php echo home_url( '/' ); ?>" class="leyre-btn leyre-btn--beige">Conocer el programa</a>
            <p class="leyre-acceso-footer">
                ¿Ya eres alumna? <a href="<?php echo wp_login_url( home_url( '/area-privada' ) ); ?>">Iniciar sesión</a>
            </p>
        <?php endif; ?>

    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
