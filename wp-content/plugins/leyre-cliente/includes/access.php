<?php
defined( 'ABSPATH' ) || exit;

// ─── B-01: Control de acceso custom ──────────────────────────────────────────

/**
 * Comprueba si un usuario tiene acceso activo al programa.
 */
function leyre_tiene_acceso( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( ! $user_id ) return false;

    // Los administradores siempre tienen acceso
    if ( user_can( $user_id, 'manage_options' ) ) return true;

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

// ─── Hook: capturar contraseña al crear cuenta WooCommerce ───────────────────
// woocommerce_created_customer recibe la contraseña en texto plano antes de que
// wp_insert_user la hashee, así podemos incluirla en el email de bienvenida.

add_action( 'woocommerce_created_customer', function( $customer_id, $new_customer_data, $password_generated ) {
    if ( ! empty( $new_customer_data['user_pass'] ) ) {
        set_transient( 'leyre_wc_pass_' . $customer_id, $new_customer_data['user_pass'], HOUR_IN_SECONDS );
    }
}, 10, 3 );

// ─── Hook: activar acceso al confirmar pago WooCommerce ──────────────────────
// Usamos woocommerce_payment_complete (pago confirmado) y también
// woocommerce_order_status_completed como fallback, la misma función con guard.

add_action( 'woocommerce_payment_complete',        'leyre_activar_por_woocommerce' );
add_action( 'woocommerce_order_status_processing', 'leyre_activar_por_woocommerce' );
add_action( 'woocommerce_order_status_completed',  'leyre_activar_por_woocommerce' );

function leyre_activar_por_woocommerce( $order_id ) {
    $order       = wc_get_order( $order_id );
    $producto_id = (int) get_option( 'leyre_producto_id', 0 );

    if ( ! $order || ! $producto_id ) return;

    // Verificar que el pedido contiene el producto del programa
    $tiene_producto = false;
    foreach ( $order->get_items() as $item ) {
        if ( (int) $item->get_product_id() === $producto_id ) {
            $tiene_producto = true;
            break;
        }
    }
    if ( ! $tiene_producto ) return;

    $user_id        = $order->get_user_id();
    $nueva_password = null;

    // Compra de invitado: crear cuenta WordPress con el email de facturación
    if ( ! $user_id ) {
        $email = $order->get_billing_email();
        if ( ! $email ) return;

        $existing = get_user_by( 'email', $email );
        if ( $existing ) {
            $user_id = $existing->ID;
        } else {
            $nueva_password = wp_generate_password( 12, false );
            $nombre         = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
            $user_id        = wp_insert_user([
                'user_login'   => $email,
                'user_email'   => $email,
                'display_name' => $nombre ?: $email,
                'first_name'   => $order->get_billing_first_name(),
                'last_name'    => $order->get_billing_last_name(),
                'user_pass'    => $nueva_password,
                'role'         => 'alumno',
            ]);
            if ( is_wp_error( $user_id ) ) return;
        }

        // Loguear al usuario para que la página de "gracias" no pida verificación
        wp_set_auth_cookie( $user_id, true );

        // Vincular pedido al nuevo usuario
        $order->set_customer_id( $user_id );
        $order->save();
    }

    // Guard: no activar dos veces
    if ( get_user_meta( $user_id, 'leyre_fecha_inicio', true ) ) return;

    leyre_activar_acceso( $user_id );

    // Asignar rol alumno si no lo tiene ya
    $user_obj = new WP_User( $user_id );
    if ( ! in_array( 'alumno', (array) $user_obj->roles, true ) ) {
        $user_obj->set_role( 'alumno' );
    }

    // Enviar email con credenciales
    $password = $nueva_password ?: get_transient( 'leyre_wc_pass_' . $user_id );
    if ( $password ) {
        leyre_enviar_email_credenciales( $user_id, $password );
        delete_transient( 'leyre_wc_pass_' . $user_id );
    } else {
        leyre_enviar_email_bienvenida( $user_id );
    }
}

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
    $dia       = leyre_get_dia_programa( $user->ID );
    $duracion  = (int) get_option( 'leyre_duracion_programa', 90 );
    $progreso  = leyre_get_progreso_global( $user->ID );
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
        <?php if ( $dia !== null ) : ?>
        <tr>
            <th>Día del programa</th>
            <td>
                <strong>Día <?php echo $dia; ?> de <?php echo $duracion; ?></strong>
                &nbsp;—&nbsp;
                Progreso: <strong><?php echo $progreso['porcentaje']; ?>%</strong>
                (<?php echo $progreso['completadas']; ?>/<?php echo $progreso['total']; ?> lecciones)
                <div style="background:#eee;border-radius:4px;height:8px;width:200px;margin-top:6px;display:inline-block;vertical-align:middle">
                    <div style="background:#C5A882;width:<?php echo $progreso['porcentaje']; ?>%;height:100%;border-radius:4px"></div>
                </div>
            </td>
        </tr>
        <?php endif; ?>
    </table>

    <?php leyre_perfil_progreso_modulos( $user->ID ); ?>
    <?php leyre_perfil_sesiones_calendly( $user ); ?>
    <?php
}

function leyre_perfil_progreso_modulos( $user_id ) {
    $modulos = get_posts([
        'post_type'   => 'leyre_modulo',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]);
    if ( empty( $modulos ) ) return;
    ?>
    <h3>Progreso por módulo</h3>
    <table class="wp-list-table widefat fixed" style="max-width:700px">
        <thead>
            <tr>
                <th>Módulo</th>
                <th>Lecciones</th>
                <th style="width:200px">Progreso</th>
                <th style="width:80px">%</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $modulos as $mod ) :
            $p = leyre_get_progreso_modulo( $user_id, $mod->ID );
        ?>
            <tr>
                <td><?php echo esc_html( $mod->post_title ); ?></td>
                <td><?php echo $p['completadas']; ?> / <?php echo $p['total']; ?></td>
                <td>
                    <div style="background:#eee;border-radius:4px;height:8px;width:100%">
                        <div style="background:<?php echo $p['porcentaje'] === 100 ? '#4CAF50' : '#C5A882'; ?>;width:<?php echo $p['porcentaje']; ?>%;height:100%;border-radius:4px"></div>
                    </div>
                </td>
                <td><strong><?php echo $p['porcentaje']; ?>%</strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}

function leyre_perfil_sesiones_calendly( $user ) {
    $api_key = get_option( 'leyre_calendly_api_key' );
    if ( ! $api_key ) {
        echo '<h3>Sesiones en Calendly</h3><p style="color:#888">Configura la API Key de Calendly en <a href="' . admin_url('admin.php?page=leyre-configuracion') . '">Leyre Torres &rsaquo; Configuración</a>.</p>';
        return;
    }

    $sesiones = leyre_get_sesiones_calendly( $user->user_email );
    ?>
    <h3>Sesiones en Calendly (<?php echo count( $sesiones ); ?>)</h3>
    <?php if ( empty( $sesiones ) ) : ?>
        <p style="color:#888">No se encontraron sesiones para <?php echo esc_html( $user->user_email ); ?>.</p>
    <?php else : ?>
    <table class="wp-list-table widefat fixed" style="max-width:800px">
        <thead>
            <tr>
                <th>Sesión</th>
                <th>Fecha y hora</th>
                <th>Estado</th>
                <th>Zoom</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $sesiones as $s ) :
            $pasada = strtotime( $s['start_time'] ) < time();
            $fecha  = date_i18n( 'd/m/Y H:i', strtotime( $s['start_time'] ) );
        ?>
            <tr>
                <td><?php echo esc_html( $s['name'] ?? '—' ); ?></td>
                <td><?php echo esc_html( $fecha ); ?></td>
                <td>
                    <?php if ( $pasada ) : ?>
                        <span style="color:#4CAF50;font-weight:700">✓ Completada</span>
                    <?php else : ?>
                        <span style="color:#C5A882;font-weight:700">Próxima</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ( ! empty( $s['zoom_link'] ) ) : ?>
                        <a href="<?php echo esc_url( $s['zoom_link'] ); ?>" target="_blank">Abrir</a>
                    <?php else : ?>
                        <span style="color:#aaa">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif;
}

function leyre_guardar_campos_perfil( $user_id ) {
    if ( ! current_user_can( 'edit_users' ) ) return;
    update_user_meta( $user_id, 'leyre_fecha_inicio',  sanitize_text_field( $_POST['leyre_fecha_inicio'] ?? '' ) );
    update_user_meta( $user_id, 'leyre_fecha_fin',     sanitize_text_field( $_POST['leyre_fecha_fin']    ?? '' ) );
    update_user_meta( $user_id, 'leyre_acceso_activo', isset( $_POST['leyre_acceso_activo'] ) ? '1' : '' );
}
