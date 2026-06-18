<?php
defined( 'ABSPATH' ) || exit;

// ─── B-06: API REST ───────────────────────────────────────────────────────────

add_action( 'rest_api_init', 'leyre_registrar_endpoints' );

function leyre_registrar_endpoints() {
    $namespace = 'leyre/v1';
    $auth      = fn() => is_user_logged_in() && leyre_tiene_acceso();

    register_rest_route( $namespace, '/dashboard', [
        'methods'             => 'GET',
        'callback'            => 'leyre_endpoint_dashboard',
        'permission_callback' => $auth,
    ]);

    register_rest_route( $namespace, '/modulos', [
        'methods'             => 'GET',
        'callback'            => 'leyre_endpoint_modulos',
        'permission_callback' => $auth,
    ]);

    register_rest_route( $namespace, '/modulo/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'leyre_endpoint_modulo',
        'permission_callback' => $auth,
    ]);

    register_rest_route( $namespace, '/leccion/(?P<id>\d+)/completar', [
        'methods'             => 'POST',
        'callback'            => 'leyre_endpoint_completar_leccion',
        'permission_callback' => $auth,
    ]);

    register_rest_route( $namespace, '/sesiones', [
        'methods'             => 'GET',
        'callback'            => 'leyre_endpoint_sesiones',
        'permission_callback' => $auth,
    ]);

    register_rest_route( $namespace, '/recursos', [
        'methods'             => 'GET',
        'callback'            => 'leyre_endpoint_recursos',
        'permission_callback' => $auth,
    ]);
}

// ── GET /dashboard ────────────────────────────────────────────────────────────

function leyre_endpoint_dashboard() {
    $user_id = get_current_user_id();
    $user    = get_userdata( $user_id );

    // Próxima sesión: la más próxima en el futuro que no esté completada
    $ahora   = current_time( 'Y-m-d\TH:i' );
    $proximas = get_posts([
        'post_type'   => 'leyre_sesion_tipo',
        'numberposts' => 1,
        'post_status' => 'publish',
        'orderby'     => 'meta_value',
        'meta_key'    => '_leyre_fecha_sesion',
        'order'       => 'ASC',
        'meta_query'  => [
            'relation' => 'AND',
            [ 'key' => '_leyre_fecha_sesion', 'value' => $ahora, 'compare' => '>=', 'type' => 'CHAR' ],
            [ 'key' => '_leyre_estado',       'value' => 'completada', 'compare' => '!=' ],
            [
                'relation' => 'OR',
                [ 'key' => '_leyre_usuario_id', 'value' => $user_id, 'compare' => '=' ],
                [ 'key' => '_leyre_tipo_sesion', 'value' => 'grupal', 'compare' => '=' ],
            ],
        ],
    ]);

    $proxima = null;
    if ( ! empty( $proximas ) ) {
        $s       = $proximas[0];
        $proxima = [
            'nombre'         => $s->post_title,
            'fecha'          => get_post_meta( $s->ID, '_leyre_fecha_sesion',   true ),
            'enlace_reunion' => get_post_meta( $s->ID, '_leyre_enlace_reunion', true ),
            'tipo_sesion'    => get_post_meta( $s->ID, '_leyre_tipo_sesion',    true ) ?: '1a1',
        ];
    }

    return rest_ensure_response([
        'nombre'          => $user->display_name,
        'dia_programa'    => leyre_get_dia_programa( $user_id ),
        'duracion_total'  => (int) get_option( 'leyre_duracion_programa', 90 ),
        'fecha_fin'       => get_user_meta( $user_id, 'leyre_fecha_fin', true ),
        'progreso_global' => leyre_get_progreso_global( $user_id ),
        'proxima_sesion'  => $proxima,
    ]);
}

// ── GET /modulos ──────────────────────────────────────────────────────────────

function leyre_endpoint_modulos() {
    $user_id = get_current_user_id();
    $modulos = get_posts([
        'post_type'   => 'leyre_modulo',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'post_status' => 'publish',
        'meta_query'  => [ [ 'key' => '_leyre_activo', 'value' => '1' ] ],
    ]);

    $data = array_map( fn( $m ) => leyre_formatear_modulo( $m, $user_id ), $modulos );
    return rest_ensure_response( $data );
}

// ── GET /modulo/{id} ──────────────────────────────────────────────────────────

function leyre_endpoint_modulo( WP_REST_Request $request ) {
    $user_id   = get_current_user_id();
    $modulo_id = (int) $request['id'];
    $modulo    = get_post( $modulo_id );

    if ( ! $modulo || $modulo->post_type !== 'leyre_modulo' ) {
        return new WP_Error( 'not_found', 'Módulo no encontrado.', [ 'status' => 404 ] );
    }

    if ( ! leyre_modulo_desbloqueado( $user_id, $modulo_id ) ) {
        return new WP_Error( 'locked', 'Módulo no disponible aún.', [ 'status' => 403 ] );
    }

    $lecciones = get_posts([
        'post_type'   => 'leyre_leccion',
        'numberposts' => -1,
        'meta_key'    => '_leyre_modulo_id',
        'meta_value'  => $modulo_id,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
    ]);
    $completadas = leyre_get_lecciones_completadas( $user_id );

    $data = leyre_formatear_modulo( $modulo, $user_id );
    $data['lecciones'] = array_map( fn( $l ) => [
        'id'          => $l->ID,
        'titulo'      => $l->post_title,
        'vimeo_id'    => get_post_meta( $l->ID, '_leyre_vimeo_id', true ),
        'duracion'    => get_post_meta( $l->ID, '_leyre_duracion',  true ),
        'contenido'   => get_post_meta( $l->ID, '_leyre_contenido', true ),
        'completada'  => in_array( $l->ID, array_map( 'intval', $completadas ), true ),
    ], $lecciones );

    return rest_ensure_response( $data );
}

// ── POST /leccion/{id}/completar ──────────────────────────────────────────────

function leyre_endpoint_completar_leccion( WP_REST_Request $request ) {
    $user_id    = get_current_user_id();
    $leccion_id = (int) $request['id'];
    $leccion    = get_post( $leccion_id );

    if ( ! $leccion || $leccion->post_type !== 'leyre_leccion' ) {
        return new WP_Error( 'not_found', 'Lección no encontrada.', [ 'status' => 404 ] );
    }

    leyre_marcar_leccion_completada( $user_id, $leccion_id );
    $modulo_id = (int) get_post_meta( $leccion_id, '_leyre_modulo_id', true );

    return rest_ensure_response([
        'ok'              => true,
        'progreso_modulo' => leyre_get_progreso_modulo( $user_id, $modulo_id ),
        'progreso_global' => leyre_get_progreso_global( $user_id ),
    ]);
}

// ── GET /sesiones ─────────────────────────────────────────────────────────────

function leyre_endpoint_sesiones() {
    $user_id = get_current_user_id();

    // Sesiones 1:1 asignadas a este usuario
    $s_1a1 = get_posts([
        'post_type'   => 'leyre_sesion_tipo',
        'numberposts' => -1,
        'post_status' => 'publish',
        'orderby'     => 'meta_value_num',
        'meta_key'    => '_leyre_numero_sesion',
        'order'       => 'ASC',
        'meta_query'  => [
            [ 'key' => '_leyre_tipo_sesion', 'value' => '1a1',     'compare' => '=' ],
            [ 'key' => '_leyre_usuario_id',  'value' => $user_id,  'compare' => '=' ],
        ],
    ]);

    // Sesiones grupales (visibles para todas)
    $s_grupal = get_posts([
        'post_type'   => 'leyre_sesion_tipo',
        'numberposts' => -1,
        'post_status' => 'publish',
        'orderby'     => 'meta_value_num',
        'meta_key'    => '_leyre_numero_sesion',
        'order'       => 'ASC',
        'meta_query'  => [
            [ 'key' => '_leyre_tipo_sesion', 'value' => 'grupal', 'compare' => '=' ],
        ],
    ]);

    $sesiones = array_merge(
        array_map( 'leyre_formatear_sesion_item', $s_1a1 ),
        array_map( 'leyre_formatear_sesion_item', $s_grupal )
    );

    usort( $sesiones, fn( $a, $b ) => $a['numero'] <=> $b['numero'] );

    return rest_ensure_response([ 'sesiones' => $sesiones ]);
}

function leyre_formatear_sesion_item( $s ) {
    return [
        'id'             => $s->ID,
        'nombre'         => $s->post_title,
        'numero'         => (int) get_post_meta( $s->ID, '_leyre_numero_sesion',  true ),
        'tipo_sesion'    => get_post_meta( $s->ID, '_leyre_tipo_sesion',    true ) ?: '1a1',
        'estado'         => get_post_meta( $s->ID, '_leyre_estado',         true ) ?: 'pendiente',
        'fecha'          => get_post_meta( $s->ID, '_leyre_fecha_sesion',   true ),
        'enlace_reunion' => get_post_meta( $s->ID, '_leyre_enlace_reunion', true ),
    ];
}

// ── GET /recursos ─────────────────────────────────────────────────────────────

function leyre_endpoint_recursos() {
    $recursos = get_posts([
        'post_type'   => 'leyre_recurso',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]);

    $data = array_map( function( $r ) {
        $modulo_id = (int) get_post_meta( $r->ID, '_leyre_modulo_id', true );
        return [
            'id'            => $r->ID,
            'titulo'        => $r->post_title,
            'tipo'          => get_post_meta( $r->ID, '_leyre_tipo', true ),
            'modulo_id'     => $modulo_id,
            'modulo_titulo' => $modulo_id ? get_the_title( $modulo_id ) : null,
            'url_descarga'  => home_url( '/descargar-recurso/?id=' . $r->ID ),
        ];
    }, $recursos );

    return rest_ensure_response( $data );
}

// ─── Helpers de formato ────────────────────────────────────────────────────────

function leyre_formatear_modulo( $modulo, $user_id ) {
    $progreso    = leyre_get_progreso_modulo( $user_id, $modulo->ID );
    $desbloq     = leyre_modulo_desbloqueado( $user_id, $modulo->ID );
    $thumb_id    = get_post_thumbnail_id( $modulo->ID );
    return [
        'id'          => $modulo->ID,
        'titulo'      => $modulo->post_title,
        'numero'      => $modulo->menu_order,
        'descripcion' => get_post_meta( $modulo->ID, '_leyre_descripcion', true ),
        'thumbnail'   => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'medium' ) : null,
        'desbloqueado'=> $desbloq,
        'progreso'    => $progreso,
    ];
}

function leyre_formatear_sesion( $sesion ) {
    return [
        'uri'        => $sesion['uri']        ?? null,
        'nombre'     => $sesion['name']        ?? null,
        'inicio'     => $sesion['start_time']  ?? null,
        'fin'        => $sesion['end_time']    ?? null,
        'zoom_link'  => $sesion['zoom_link']   ?? null,
        'estado'     => $sesion['status']      ?? null,
    ];
}
