<?php
defined( 'ABSPATH' ) || exit;

// ─── B-03: Progreso del usuario ───────────────────────────────────────────────

function leyre_get_dia_programa( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    $fecha_inicio = get_user_meta( $user_id, 'leyre_fecha_inicio', true );
    if ( ! $fecha_inicio ) return null;
    $inicio = new DateTime( $fecha_inicio );
    $hoy    = new DateTime( 'today' );
    return $inicio->diff( $hoy )->days + 1;
}

function leyre_marcar_leccion_completada( $user_id, $leccion_id ) {
    $completadas = get_user_meta( $user_id, 'leyre_lecciones_completadas', true );
    if ( ! is_array( $completadas ) ) $completadas = [];
    $leccion_id = (int) $leccion_id;
    if ( ! in_array( $leccion_id, $completadas, true ) ) {
        $completadas[] = $leccion_id;
        update_user_meta( $user_id, 'leyre_lecciones_completadas', $completadas );
    }
}

function leyre_get_lecciones_completadas( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    $completadas = get_user_meta( $user_id, 'leyre_lecciones_completadas', true );
    return is_array( $completadas ) ? $completadas : [];
}

function leyre_get_progreso_modulo( $user_id, $modulo_id ) {
    $lecciones = get_posts([
        'post_type'      => 'leyre_leccion',
        'numberposts'    => -1,
        'meta_key'       => '_leyre_modulo_id',
        'meta_value'     => $modulo_id,
        'fields'         => 'ids',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ]);
    $completadas   = leyre_get_lecciones_completadas( $user_id );
    $n_completadas = count( array_intersect( $lecciones, array_map( 'intval', $completadas ) ) );
    $total         = count( $lecciones );
    return [
        'completadas' => $n_completadas,
        'total'       => $total,
        'porcentaje'  => $total > 0 ? round( ( $n_completadas / $total ) * 100 ) : 0,
    ];
}

function leyre_get_progreso_global( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    $modulos = get_posts([
        'post_type'   => 'leyre_modulo',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]);
    $total_lecciones    = 0;
    $total_completadas  = 0;
    foreach ( $modulos as $modulo ) {
        $p = leyre_get_progreso_modulo( $user_id, $modulo->ID );
        $total_lecciones   += $p['total'];
        $total_completadas += $p['completadas'];
    }
    return [
        'total'      => $total_lecciones,
        'completadas'=> $total_completadas,
        'porcentaje' => $total_lecciones > 0 ? round( ( $total_completadas / $total_lecciones ) * 100 ) : 0,
    ];
}

/**
 * Comprueba si un módulo está desbloqueado para el usuario (drip content).
 */
function leyre_modulo_desbloqueado( $user_id, $modulo_id ) {
    $drip_dias    = (int) get_post_meta( $modulo_id, '_leyre_drip_dias', true );
    if ( $drip_dias === 0 ) return true;
    $dia_programa = leyre_get_dia_programa( $user_id );
    if ( $dia_programa === null ) return false;
    return $dia_programa >= $drip_dias;
}
