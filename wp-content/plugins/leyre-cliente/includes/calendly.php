<?php
defined( 'ABSPATH' ) || exit;

// ─── B-04: Integración Calendly API v2 ───────────────────────────────────────

function leyre_get_sesiones_calendly( $user_email ) {
    $cache_key = 'leyre_sesiones_' . md5( $user_email );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) return $cached;

    $api_key = get_option( 'leyre_calendly_api_key' );
    if ( ! $api_key ) return [];

    // Obtener URI del usuario de Calendly (necesaria para filtrar por invitado)
    $user_uri = leyre_get_calendly_user_uri( $api_key );
    if ( ! $user_uri ) return [];

    $response = wp_remote_get(
        add_query_arg([
            'user'          => $user_uri,
            'invitee_email' => $user_email,
            'status'        => 'active',
            'sort'          => 'start_time:asc',
            'count'         => 50,
        ], 'https://api.calendly.com/scheduled_events' ),
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 10,
        ]
    );

    if ( is_wp_error( $response ) ) return [];

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $data = $body['collection'] ?? [];

    // Añadir link Zoom a cada sesión
    foreach ( $data as &$evento ) {
        $evento['zoom_link'] = leyre_get_zoom_link_de_evento( $api_key, $evento['uri'] );
    }
    unset( $evento );

    set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
    return $data;
}

function leyre_get_proxima_sesion_calendly( $user_email ) {
    $sesiones = leyre_get_sesiones_calendly( $user_email );
    $ahora    = current_time( 'timestamp' );
    foreach ( $sesiones as $s ) {
        if ( strtotime( $s['start_time'] ) > $ahora ) return $s;
    }
    return null;
}

function leyre_get_calendly_user_uri( $api_key ) {
    $cache_key = 'leyre_calendly_user_uri';
    $cached    = get_transient( $cache_key );
    if ( $cached ) return $cached;

    $response = wp_remote_get(
        'https://api.calendly.com/users/me',
        [ 'headers' => [ 'Authorization' => 'Bearer ' . $api_key ], 'timeout' => 10 ]
    );
    if ( is_wp_error( $response ) ) return null;
    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $uri  = $body['resource']['uri'] ?? null;
    if ( $uri ) set_transient( $cache_key, $uri, HOUR_IN_SECONDS );
    return $uri;
}

function leyre_get_zoom_link_de_evento( $api_key, $event_uri ) {
    // El URI del evento es tipo https://api.calendly.com/scheduled_events/{uuid}
    $uuid     = basename( $event_uri );
    $response = wp_remote_get(
        "https://api.calendly.com/scheduled_events/{$uuid}/invitees",
        [ 'headers' => [ 'Authorization' => 'Bearer ' . $api_key ], 'timeout' => 10 ]
    );
    if ( is_wp_error( $response ) ) return null;
    $body      = json_decode( wp_remote_retrieve_body( $response ), true );
    $invitees  = $body['collection'] ?? [];
    return $invitees[0]['zoom_join_url'] ?? ( $invitees[0]['location']['join_url'] ?? null );
}

/**
 * Invalida la caché de sesiones de un usuario (útil al modificar desde admin).
 */
function leyre_invalidar_cache_sesiones( $user_email ) {
    delete_transient( 'leyre_sesiones_' . md5( $user_email ) );
}
