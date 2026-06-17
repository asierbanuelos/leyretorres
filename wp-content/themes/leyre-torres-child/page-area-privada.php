<?php
/**
 * Template Name: Área Privada — Dashboard
 */
defined( 'ABSPATH' ) || exit;

// La protección de acceso está en el plugin (hook template_redirect en access.php)
// Si llegamos aquí, el usuario está logueado y tiene acceso activo.

$user_id    = get_current_user_id();
$user       = wp_get_current_user();
$dia        = leyre_get_dia_programa( $user_id );
$duracion   = (int) get_option( 'leyre_duracion_programa', 90 );
$porcentaje = $duracion > 0 ? min( 100, round( ( $dia / $duracion ) * 100 ) ) : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Área privada — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<!-- ── Contenido principal ─────────────────────────────────────────────── -->
<main class="leyre-main">
    <div class="leyre-container">

        <!-- Hero / progreso -->
        <div class="leyre-hero">
            <p class="leyre-hero__saludo">Hola, <?php echo esc_html( $user->display_name ); ?></p>
            <p class="leyre-hero__sub">Bienvenida a Leonas en Tacones</p>
            <?php if ( $dia !== null ) : ?>
            <div>
                <div class="leyre-progress-bar">
                    <div class="leyre-progress-bar__fill" style="width:<?php echo $porcentaje; ?>%"></div>
                </div>
                <p class="leyre-progress-label">Día <?php echo $dia; ?> de <?php echo $duracion; ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mis sesiones (resumen) -->
        <section class="leyre-section" id="leyre-sesiones">
            <div class="leyre-section__header">
                <h2 class="leyre-section__title">Mis sesiones</h2>
                <a href="<?php echo home_url( '/mis-sesiones' ); ?>" class="leyre-section__link">Ver todo →</a>
            </div>
            <div id="leyre-proxima-sesion">
                <!-- Cargado via JS desde /wp-json/leyre/v1/dashboard -->
                <div class="leyre-card-sesion">
                    <div class="leyre-skeleton" style="height:14px;width:140px;margin-bottom:10px"></div>
                    <div class="leyre-skeleton" style="height:22px;width:60%;margin-bottom:8px"></div>
                    <div class="leyre-skeleton" style="height:14px;width:40%;margin-bottom:20px"></div>
                    <div class="leyre-skeleton" style="height:44px;width:160px;border-radius:4px"></div>
                </div>
            </div>
        </section>

        <!-- Mis cursos (resumen) -->
        <section class="leyre-section" id="leyre-cursos">
            <div class="leyre-section__header">
                <h2 class="leyre-section__title">Mis cursos</h2>
                <a href="<?php echo home_url( '/mis-cursos' ); ?>" class="leyre-section__link">Ver todo →</a>
            </div>
            <div class="leyre-grid-modulos" id="leyre-grid-modulos">
                <!-- Skeleton: 3 cards -->
                <?php for ( $i = 0; $i < 3; $i++ ) : ?>
                <div class="leyre-card-modulo" style="pointer-events:none">
                    <div class="leyre-card-modulo__thumb leyre-skeleton" style="aspect-ratio:16/9"></div>
                    <div class="leyre-card-modulo__body">
                        <div class="leyre-skeleton" style="height:11px;width:70px;margin-bottom:8px"></div>
                        <div class="leyre-skeleton" style="height:18px;width:80%;margin-bottom:12px"></div>
                        <div class="leyre-skeleton" style="height:10px;width:50%"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- Recursos (CTA) -->
        <section class="leyre-section">
            <div class="leyre-section__header">
                <h2 class="leyre-section__title">Recursos</h2>
                <a href="<?php echo home_url( '/recursos' ); ?>" class="leyre-section__link">Ver todo →</a>
            </div>
            <p style="color:var(--leyre-muted)">Accede a todos tus materiales descargables del programa.</p>
            <a href="<?php echo home_url( '/recursos' ); ?>" class="leyre-btn leyre-btn--outline">Ver recursos</a>
        </section>

        <!-- Comunidad -->
        <section class="leyre-section">
            <div style="background:var(--leyre-dark);border-radius:var(--leyre-radius);padding:40px 32px;text-align:center;color:var(--leyre-white)">
                <h2 style="font-family:var(--leyre-font-title);font-style:italic;font-size:24px;margin:0 0 8px">No recorres este camino sola</h2>
                <p style="color:rgba(255,255,255,.7);margin-bottom:24px">Únete a la comunidad y conecta con el resto de leonas.</p>
                <?php $wa_url = get_option( 'leyre_whatsapp_url' ); ?>
                <?php if ( $wa_url ) : ?>
                <a href="<?php echo esc_url( $wa_url ); ?>" target="_blank" rel="noopener" class="leyre-btn leyre-btn--beige">Entrar a la comunidad</a>
                <?php else : ?>
                <span class="leyre-btn leyre-btn--beige leyre-btn--disabled">Próximamente</span>
                <?php endif; ?>
            </div>
        </section>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function() {
    // Cargar datos del dashboard
    fetch(leyreConfig.apiUrl + 'dashboard', {
        headers: { 'X-WP-Nonce': leyreConfig.nonce }
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data) return;
        renderProximaSesion(data.proxima_sesion);
    })
    .catch(() => {});

    fetch(leyreConfig.apiUrl + 'modulos', {
        headers: { 'X-WP-Nonce': leyreConfig.nonce }
    })
    .then(r => r.ok ? r.json() : [])
    .then(modulos => renderModulos(modulos.slice(0, 3)))
    .catch(() => {});

    function renderProximaSesion(sesion) {
        const el = document.getElementById('leyre-proxima-sesion');
        if (!sesion) {
            el.innerHTML = '<div class="leyre-card-sesion"><p style="color:rgba(255,255,255,.6);margin:0">No tienes sesiones programadas próximamente.</p></div>';
            return;
        }
        const fecha = new Date(sesion.inicio).toLocaleString('es-ES', { weekday:'long', day:'numeric', month:'long', hour:'2-digit', minute:'2-digit', timeZone:'Europe/Madrid' });
        el.innerHTML = `
        <div class="leyre-card-sesion">
            <p class="leyre-card-sesion__tipo">Próxima sesión</p>
            <p class="leyre-card-sesion__titulo">${sesion.nombre || 'Sesión'}</p>
            <p class="leyre-card-sesion__fecha">${fecha} (CET)</p>
            ${sesion.zoom_link
                ? `<a href="${sesion.zoom_link}" target="_blank" rel="noopener" class="leyre-btn leyre-btn--beige">Unirme por Zoom</a>`
                : `<span class="leyre-btn leyre-btn--beige leyre-btn--disabled">Link próximamente</span>`
            }
        </div>`;
    }

    function renderModulos(modulos) {
        const grid = document.getElementById('leyre-grid-modulos');
        if (!modulos.length) {
            grid.innerHTML = '<p style="color:var(--leyre-muted)">Aún no hay módulos disponibles.</p>';
            return;
        }
        grid.innerHTML = modulos.map(m => {
            const locked = !m.desbloqueado;
            const progreso = m.progreso;
            let estadoLabel = 'Sin empezar';
            if (progreso.completadas === progreso.total && progreso.total > 0) estadoLabel = 'Completado ✓';
            else if (progreso.completadas > 0) estadoLabel = `${progreso.porcentaje}% · ${progreso.completadas} de ${progreso.total} lecciones`;

            return `<a href="${locked ? '#' : '/mis-cursos/modulo-' + m.id}" class="leyre-card-modulo${locked ? ' leyre-card-modulo--locked' : ''}">
                <div class="leyre-card-modulo__thumb">
                    ${m.thumbnail ? `<img src="${m.thumbnail}" alt="">` : ''}
                    <div class="leyre-card-modulo__play">
                        ${locked
                            ? '<svg viewBox="0 0 24 24"><path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3-9H9V6a3 3 0 0 1 6 0v2z"/></svg>'
                            : '<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>'
                        }
                    </div>
                </div>
                <div class="leyre-card-modulo__body">
                    <p class="leyre-card-modulo__etiqueta">Módulo ${m.numero}</p>
                    <p class="leyre-card-modulo__titulo">${m.titulo}</p>
                    <p class="leyre-card-modulo__estado">${locked ? '🔒 Disponible próximamente' : estadoLabel}</p>
                    ${!locked && progreso.total > 0 ? `
                    <div class="leyre-progress-bar" style="background:rgba(197,168,130,.2)">
                        <div class="leyre-progress-bar__fill" style="width:${progreso.porcentaje}%;background:var(--leyre-beige)"></div>
                    </div>` : ''}
                </div>
            </a>`;
        }).join('');
    }
})();
</script>
</body>
</html>
