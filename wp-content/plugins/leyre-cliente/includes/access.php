<?php
defined( 'ABSPATH' ) || exit;

// ─── B-01: Control de acceso custom ──────────────────────────────────────────

/**
 * Comprueba si un usuario tiene acceso activo al programa.
 */
function leyre_tiene_acceso( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( ! $user_id ) return false;

    $activo    = get_user_meta( $user_id, 'leyre_acceso_activo', true );
    $fecha_fin = get_user_meta( $user_id, 'leyre_fecha_fin', true );

    if ( ! $activo || ! $fecha_fin ) return false;
    return strtotime( $fecha_fin ) >= strtotime( 'today' );
}

/**
 * Activa el acceso de un usuario fijando fecha_inicio y fecha_fin.
 */
function leyre_activar_acceso( $user_id ) {
    $duracion  = (int) get_option( 'leyre_duracion_programa', 90 );
    $fecha_ini = date( 'Y-m-d' );
    $fecha_fin = date( 'Y-m-d', strtotime( "+{$duracion} days" ) );

    update_user_meta( $user_id, 'leyre_fecha_inicio',  $fecha_ini );
    update_user_meta( $user_id, 'leyre_fecha_fin',     $fecha_fin );
    update_user_meta( $user_id, 'leyre_acceso_activo', '1' );
}

/**
 * Permite al admin extender o modificar el acceso de una alumna.
 */
function leyre_actualizar_acceso( $user_id, $nueva_fecha_fin ) {
    update_user_meta( $user_id, 'leyre_fecha_fin',     $nueva_fecha_fin );
    update_user_meta( $user_id, 'leyre_acceso_activo', '1' );
}

// ─── Hook: activar acceso al completar pedido WooCommerce ────────────────────

add_action( 'woocommerce_order_status_completed', function( $order_id ) {
    $order       = wc_get_order( $order_id );
    $producto_id = (int) get_option( 'leyre_producto_id', 0 );
    $user_id     = $order->get_user_id();

    if ( ! $user_id || ! $producto_id ) return;

    foreach ( $order->get_items() as $item ) {
        if ( (int) $item->get_product_id() !== $producto_id ) continue;

        // No sobreescribir un acceso activo ya existente
        if ( get_user_meta( $user_id, 'leyre_fecha_inicio', true ) ) continue;

        leyre_activar_acceso( $user_id );
        leyre_enviar_email_bienvenida( $user_id );
    }
});

// ─── Protección de URLs del área privada ────────────────────────────────────

add_action( 'template_redirect', function() {
    $slugs_protegidos = [ 'area-privada', 'mis-cursos', 'mis-sesiones', 'recursos', 'mi-perfil' ];
    $slug_actual      = get_query_var( 'pagename' );

    if ( ! in_array( $slug_actual, $slugs_protegidos, true ) ) return;

    if ( ! is_user_logged_in() ) {
        wp_redirect( wp_login_url( get_permalink() ) );
        exit;
    }

    if ( ! leyre_tiene_acceso() ) {
        wp_redirect( home_url( '/acceso' ) );
        exit;
    }
});

// ─── Campos de acceso en perfil de usuario (WP Admin) ───────────────────────

add_action( 'show_user_profile',        'leyre_campos_perfil_usuario' );
add_action( 'edit_user_profile',        'leyre_campos_perfil_usuario' );
add_action( 'personal_options_update',  'leyre_guardar_campos_perfil' );
add_action( 'edit_user_profile_update', 'leyre_guardar_campos_perfil' );

function leyre_campos_perfil_usuario( $user ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    $fecha_ini = get_user_meta( $user->ID, 'leyre_fecha_inicio',  true );
    $fecha_fin = get_user_meta( $user->ID, 'leyre_fecha_fin',     true );
    $activo    = get_user_meta( $user->ID, 'leyre_acceso_activo', true );
    ?>
    <h2>Leonas en Tacones — Acceso al programa</h2>
    <table class="form-table">
        <tr>
            <th><label for="leyre_fecha_inicio">Fecha inicio</label></th>
            <td><input type="date" name="leyre_fecha_inicio" id="leyre_fecha_inicio" value="<?php echo esc_attr( $fecha_ini ); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="leyre_fecha_fin">Fecha fin</label></th>
            <td><input type="date" name="leyre_fecha_fin" id="leyre_fecha_fin" value="<?php echo esc_attr( $fecha_fin ); ?>" class="regular-text"></td>
        </tr>
        <tr>
            <th><label for="leyre_acceso_activo">Acceso activo</label></th>
            <td><input type="checkbox" name="leyre_acceso_activo" id="leyre_acceso_activo" value="1" <?php checked( $activo, '1' ); ?>></td>
        </tr>
    </table>
    <?php
}

function leyre_guardar_campos_perfil( $user_id ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    update_user_meta( $user_id, 'leyre_fecha_inicio',  sanitize_text_field( $_POST['leyre_fecha_inicio'] ?? '' ) );
    update_user_meta( $user_id, 'leyre_fecha_fin',     sanitize_text_field( $_POST['leyre_fecha_fin']    ?? '' ) );
    update_user_meta( $user_id, 'leyre_acceso_activo', isset( $_POST['leyre_acceso_activo'] ) ? '1' : '' );
}
