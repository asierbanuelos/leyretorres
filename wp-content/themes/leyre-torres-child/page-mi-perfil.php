<?php
/**
 * Template Name: Área Privada — Mi perfil
 */
defined( 'ABSPATH' ) || exit;

$user_id    = get_current_user_id();
$user       = wp_get_current_user();
$fecha_ini  = get_user_meta( $user_id, 'leyre_fecha_inicio',  true );
$fecha_fin  = get_user_meta( $user_id, 'leyre_fecha_fin',     true );
$dia        = leyre_get_dia_programa( $user_id );
$duracion   = (int) get_option( 'leyre_duracion_programa', 90 );
$progreso   = leyre_get_progreso_global( $user_id );
$porcentaje_dia = ( $dia && $duracion ) ? min( 100, round( ( $dia / $duracion ) * 100 ) ) : 0;

$dias_restantes = null;
if ( $fecha_fin ) {
    $diff = ( new DateTime( $fecha_fin ) )->diff( new DateTime( 'today' ) );
    if ( ! $diff->invert ) {
        $dias_restantes = 0; // caducada
    } else {
        $dias_restantes = $diff->days;
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi perfil — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/sidebar-privado' ); ?>

<main class="leyre-main leyre-main--with-sidebar">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <div class="leyre-hero leyre-hero--sm">
        <div class="leyre-hero__inner">
            <div class="leyre-section__kicker">Mi perfil</div>
            <h1 class="leyre-hero__saludo"><?php echo esc_html( $user->display_name ); ?></h1>
            <?php if ( $dia !== null ) : ?>
            <p class="leyre-hero__sub">Día <?php echo $dia; ?> de <?php echo $duracion; ?> &middot; <?php echo $progreso['porcentaje']; ?>% completado</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="leyre-container leyre-container--narrow">

        <div class="leyre-perfil-grid">

            <!-- ── Columna principal ──────────────────────────────────── -->
            <div class="leyre-perfil-main">

                <!-- Datos de acceso -->
                <div class="leyre-perfil-card">
                    <h2 class="leyre-perfil-card__titulo">Tu programa</h2>

                    <div class="leyre-perfil-stat-row">
                        <div class="leyre-perfil-stat">
                            <span class="leyre-perfil-stat__label">Día del programa</span>
                            <span class="leyre-perfil-stat__valor leyre-perfil-stat__valor--grande">
                                <?php echo $dia !== null ? $dia : '—'; ?>
                                <small>de <?php echo $duracion; ?></small>
                            </span>
                        </div>
                        <div class="leyre-perfil-stat">
                            <span class="leyre-perfil-stat__label">Lecciones completadas</span>
                            <span class="leyre-perfil-stat__valor leyre-perfil-stat__valor--grande">
                                <?php echo $progreso['completadas']; ?>
                                <small>de <?php echo $progreso['total']; ?></small>
                            </span>
                        </div>
                        <div class="leyre-perfil-stat">
                            <span class="leyre-perfil-stat__label">Progreso global</span>
                            <span class="leyre-perfil-stat__valor leyre-perfil-stat__valor--grande"><?php echo $progreso['porcentaje']; ?><small>%</small></span>
                        </div>
                    </div>

                    <!-- Barra progreso del programa (días) -->
                    <?php if ( $dia !== null ) : ?>
                    <div style="margin-top:20px">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--leyre-muted);margin-bottom:6px">
                            <span>Inicio <?php echo esc_html( $fecha_ini ); ?></span>
                            <span>Fin <?php echo esc_html( $fecha_fin ); ?></span>
                        </div>
                        <div class="leyre-progress-bar">
                            <div class="leyre-progress-bar__fill" style="width:<?php echo $porcentaje_dia; ?>%;background:var(--leyre-beige)"></div>
                        </div>
                        <?php if ( $dias_restantes !== null ) : ?>
                        <p style="font-size:12px;color:var(--leyre-muted);margin-top:6px;text-align:right">
                            <?php if ( $dias_restantes === 0 ) : ?>
                                <span style="color:#e53935;font-weight:700">Tu acceso ha caducado</span>
                            <?php else : ?>
                                <?php echo $dias_restantes; ?> días restantes de programa
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Progreso por módulo -->
                <div class="leyre-perfil-card">
                    <h2 class="leyre-perfil-card__titulo">Progreso por módulo</h2>
                    <div id="leyre-progreso-modulos">
                        <?php
                        $modulos = get_posts([
                            'post_type'   => 'leyre_modulo',
                            'numberposts' => -1,
                            'orderby'     => 'menu_order',
                            'order'       => 'ASC',
                            'post_status' => 'publish',
                            'meta_query'  => [ [ 'key' => '_leyre_activo', 'value' => '1' ] ],
                        ]);
                        if ( empty( $modulos ) ) :
                        ?>
                            <p style="color:var(--leyre-muted)">Los módulos se publicarán próximamente.</p>
                        <?php else : ?>
                            <?php foreach ( $modulos as $mod ) :
                                $p           = leyre_get_progreso_modulo( $user_id, $mod->ID );
                                $completo    = $p['total'] > 0 && $p['completadas'] === $p['total'];
                                $desbloqueado = leyre_modulo_desbloqueado( $user_id, $mod->ID );
                            ?>
                            <div class="leyre-perfil-modulo-row">
                                <div class="leyre-perfil-modulo-row__info">
                                    <span class="leyre-perfil-modulo-row__numero">Módulo <?php echo $mod->menu_order; ?></span>
                                    <span class="leyre-perfil-modulo-row__titulo"><?php echo esc_html( $mod->post_title ); ?></span>
                                    <?php if ( ! $desbloqueado ) : ?>
                                        <span class="leyre-sesion-badge leyre-sesion-badge--pendiente">🔒 Bloqueado</span>
                                    <?php elseif ( $completo ) : ?>
                                        <span class="leyre-sesion-badge leyre-sesion-badge--ok">✓ Completado</span>
                                    <?php endif; ?>
                                </div>
                                <div class="leyre-perfil-modulo-row__barra">
                                    <div class="leyre-progress-bar leyre-progress-bar--sm">
                                        <div class="leyre-progress-bar__fill" style="width:<?php echo $p['porcentaje']; ?>%;background:<?php echo $completo ? '#4CAF50' : 'var(--leyre-beige)'; ?>"></div>
                                    </div>
                                    <span style="font-size:12px;color:var(--leyre-muted);min-width:60px;text-align:right"><?php echo $p['completadas']; ?>/<?php echo $p['total']; ?> lecciones</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- ── Columna lateral ────────────────────────────────────── -->
            <aside class="leyre-perfil-aside">

                <!-- Avatar y datos personales -->
                <div class="leyre-perfil-card">
                    <div class="leyre-perfil-avatar">
                        <?php echo get_avatar( $user_id, 80, '', '', [ 'class' => 'leyre-perfil-avatar__img' ] ); ?>
                    </div>
                    <h3 class="leyre-perfil-nombre"><?php echo esc_html( $user->display_name ); ?></h3>
                    <p class="leyre-perfil-email"><?php echo esc_html( $user->user_email ); ?></p>

                    <div class="leyre-perfil-acciones">
                        <a href="<?php echo wp_lostpassword_url(); ?>" class="leyre-btn leyre-btn--outline leyre-btn--sm" style="width:100%;text-align:center;display:block">
                            Cambiar contraseña
                        </a>
                    </div>
                </div>

                <!-- Fechas de acceso -->
                <div class="leyre-perfil-card">
                    <h3 class="leyre-perfil-card__titulo leyre-perfil-card__titulo--sm">Detalles del programa</h3>
                    <dl class="leyre-perfil-dl">
                        <dt>Programa</dt>
                        <dd>Leonas en Tacones</dd>
                        <dt>Fecha de inicio</dt>
                        <dd><?php echo $fecha_ini ? esc_html( $fecha_ini ) : '—'; ?></dd>
                        <dt>Fecha de fin</dt>
                        <dd><?php echo $fecha_fin ? esc_html( $fecha_fin ) : '—'; ?></dd>
                        <dt>Duración</dt>
                        <dd><?php echo $duracion; ?> días</dd>
                    </dl>
                </div>

                <!-- Accesos rápidos -->
                <div class="leyre-perfil-card leyre-perfil-card--links">
                    <a href="<?php echo home_url( '/area-privada' ); ?>" class="leyre-perfil-quicklink">📋 Mi programa</a>
                    <a href="<?php echo home_url( '/mis-cursos' ); ?>"   class="leyre-perfil-quicklink">🎓 Mis cursos</a>
                    <a href="<?php echo home_url( '/mis-sesiones' ); ?>" class="leyre-perfil-quicklink">📅 Mis sesiones</a>
                    <a href="<?php echo home_url( '/recursos' ); ?>"     class="leyre-perfil-quicklink">📄 Recursos</a>
                    <a href="<?php echo wp_logout_url( home_url() ); ?>" class="leyre-perfil-quicklink leyre-perfil-quicklink--salir">↩ Cerrar sesión</a>
                </div>

            </aside>

        </div>

    </div>
</main>

<?php wp_footer(); ?>
</body>
</html>
