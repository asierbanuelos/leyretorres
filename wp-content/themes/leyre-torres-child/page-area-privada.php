<?php
/**
 * Template Name: Área Privada — Dashboard
 */
defined( 'ABSPATH' ) || exit;

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
    <title>Mi programa — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<main class="leyre-main">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <div class="leyre-hero">
        <div class="leyre-hero__inner">
            <div class="leyre-hero__texto">
                <h1 class="leyre-hero__saludo">Hola, <?php echo esc_html( $user->display_name ); ?></h1>
                <p class="leyre-hero__sub">Bienvenida a Leonas en Tacones</p>
            </div>
            <?php if ( $dia !== null ) : ?>
            <div class="leyre-hero__progreso">
                <div class="leyre-hero__progreso-labels">
                    <span>Tu progreso en el programa</span>
                    <span>Día <?php echo $dia; ?> de <?php echo $duracion; ?></span>
                </div>
                <div class="leyre-progress-bar leyre-progress-bar--hero">
                    <div class="leyre-progress-bar__fill leyre-progress-bar__fill--blanco" style="width:<?php echo $porcentaje; ?>%"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="leyre-container">

        <!-- ── Mis sesiones ───────────────────────────────────────────────── -->
        <section class="leyre-section">
            <div class="leyre-section__kicker">Mis sesiones</div>
            <div class="leyre-section__header">
                <h2 class="leyre-section__title--xl">Tu acompañamiento en directo</h2>
                <a href="<?php echo home_url( '/mis-sesiones' ); ?>" class="leyre-section__link">Ver todas →</a>
            </div>
            <div id="leyre-proxima-sesion">
                <div class="leyre-card-sesion leyre-card-sesion--skeleton">
                    <div class="leyre-card-sesion__icono-wrap"><div class="leyre-skeleton" style="width:40px;height:40px;border-radius:6px"></div></div>
                    <div class="leyre-card-sesion__info" style="flex:1">
                        <div class="leyre-skeleton" style="height:11px;width:160px;margin-bottom:10px"></div>
                        <div class="leyre-skeleton" style="height:20px;width:55%;margin-bottom:8px"></div>
                        <div class="leyre-skeleton" style="height:11px;width:40%"></div>
                    </div>
                    <div class="leyre-skeleton" style="height:40px;width:160px;border-radius:99px;flex-shrink:0"></div>
                </div>
            </div>
        </section>

        <!-- ── Mis cursos ────────────────────────────────────────────────── -->
        <section class="leyre-section">
            <div class="leyre-section__kicker">Mis cursos</div>
            <div class="leyre-section__header">
                <h2 class="leyre-section__title--xl">Tu formación</h2>
                <a href="<?php echo home_url( '/mis-cursos' ); ?>" class="leyre-section__link">Ver todos →</a>
            </div>
            <div class="leyre-grid-modulos" id="leyre-grid-modulos">
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

        <!-- ── Recursos ──────────────────────────────────────────────────── -->
        <section class="leyre-section">
            <div class="leyre-section__kicker">Recursos</div>
            <div class="leyre-section__header">
                <h2 class="leyre-section__title--xl">Tus materiales</h2>
                <a href="<?php echo home_url( '/recursos' ); ?>" class="leyre-section__link">Ver todos →</a>
            </div>
            <div class="leyre-recursos-grid" id="leyre-recursos-dashboard">
                <?php for ( $i = 0; $i < 2; $i++ ) : ?>
                <div class="leyre-recurso-card">
                    <div class="leyre-recurso-card__icono" style="background:#FDF2F1">
                        <div class="leyre-skeleton" style="width:32px;height:38px;border-radius:4px"></div>
                    </div>
                    <div class="leyre-recurso-card__info">
                        <div class="leyre-skeleton" style="height:11px;width:60px;margin-bottom:8px"></div>
                        <div class="leyre-skeleton" style="height:15px;width:75%"></div>
                    </div>
                    <div class="leyre-skeleton" style="height:40px;width:110px;border-radius:8px;flex-shrink:0"></div>
                </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- ── Comunidad ──────────────────────────────────────────────────── -->
        <?php
        $wa_url   = get_option( 'leyre_whatsapp_url' );
        $img_id   = (int) get_option( 'leyre_comunidad_imagen_id', 0 );
        $img_url  = $img_id ? wp_get_attachment_image_url( $img_id, 'large' ) : '';
        $bg_style = $img_url
            ? "background:linear-gradient(rgba(26,26,26,.6),rgba(26,26,26,.6)),url('{$img_url}') center/cover no-repeat"
            : 'background:var(--leyre-dark)';
        ?>
        <section class="leyre-section leyre-section--last">
            <div class="leyre-comunidad-banner" style="<?php echo esc_attr( $bg_style ); ?>">
                <h2 class="leyre-comunidad-banner__titulo">No recorres este camino sola</h2>
                <p class="leyre-comunidad-banner__sub">Únete a la comunidad y conecta con el resto de leonas.</p>
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
(function () {
    leyreAPI.get('dashboard').then(function (data) {
        renderProximaSesion(data.proxima_sesion);
    }).catch(function () {});

    leyreAPI.get('modulos').then(function (modulos) {
        renderModulos(modulos.slice(0, 3));
    }).catch(function () {});

    leyreAPI.get('recursos').then(function (recursos) {
        renderRecursosDashboard(recursos.slice(0, 4));
    }).catch(function () {});

    function renderProximaSesion(sesion) {
        const el = document.getElementById('leyre-proxima-sesion');
        if (!sesion) {
            el.innerHTML = `<div class="leyre-card-sesion">
                <div class="leyre-card-sesion__icono-wrap">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                </div>
                <div class="leyre-card-sesion__info">
                    <p class="leyre-card-sesion__tipo">Próxima sesión · 1:1 con Leyre</p>
                    <p class="leyre-card-sesion__titulo" style="opacity:.5">No tienes sesiones programadas próximamente</p>
                </div>
            </div>`;
            return;
        }
        const fecha = new Date(sesion.inicio).toLocaleString('es-ES', {
            weekday: 'long', day: 'numeric', month: 'long',
            hour: '2-digit', minute: '2-digit', timeZone: 'Europe/Madrid'
        });
        el.innerHTML = `<div class="leyre-card-sesion">
            <div class="leyre-card-sesion__icono-wrap">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            </div>
            <div class="leyre-card-sesion__info">
                <p class="leyre-card-sesion__tipo">Próxima sesión · 1:1 con Leyre</p>
                <p class="leyre-card-sesion__titulo">${sesion.nombre || 'Sesión individual'}</p>
                <p class="leyre-card-sesion__fecha">${fecha} (CET)</p>
            </div>
            ${sesion.zoom_link
                ? `<a href="${sesion.zoom_link}" target="_blank" rel="noopener" class="leyre-btn leyre-btn--pill">Unirme por Zoom</a>`
                : `<span class="leyre-btn leyre-btn--pill leyre-btn--disabled">Link próximamente</span>`
            }
        </div>`;
    }

    function recursoTipoConfig(tipo) {
        var cfg = {
            pdf:      { color:'#B23A2F', bg:'#FDF2F1', label:'PDF',      svg:'<svg viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FDF2F1" stroke="#B23A2F" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#B23A2F" stroke-width="1.5" fill="none"/><rect x="2" y="17" width="18" height="10" rx="2" fill="#B23A2F"/><text x="11" y="25" font-family="Arial" font-size="7" font-weight="700" fill="white" text-anchor="middle">PDF</text></svg>' },
            plantilla:{ color:'#7A5C1E', bg:'#FBF5E8', label:'Plantilla',svg:'<svg viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FBF5E8" stroke="#C5A882" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#C5A882" stroke-width="1.5"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#C5A882" stroke-width="1.5" stroke-linecap="round"/></svg>' },
            otro:     { color:'#3A5C7A', bg:'#EEF4F9', label:'Archivo',  svg:'<svg viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#EEF4F9" stroke="#3A5C7A" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#3A5C7A" stroke-width="1.5"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#3A5C7A" stroke-width="1.5" stroke-linecap="round"/></svg>' }
        };
        return cfg[tipo] || cfg.otro;
    }

    function renderRecursosDashboard(recursos) {
        var grid = document.getElementById('leyre-recursos-dashboard');
        if (!recursos.length) {
            grid.innerHTML = '<p style="color:var(--c-muted,#8A8080);grid-column:1/-1;font-size:14px">Los recursos se añadirán próximamente.</p>';
            return;
        }
        grid.innerHTML = recursos.map(function(r) {
            var cfg = recursoTipoConfig(r.tipo || 'otro');
            return '<div class="leyre-recurso-card">' +
                '<div class="leyre-recurso-card__icono" style="background:' + cfg.bg + '">' + cfg.svg + '</div>' +
                '<div class="leyre-recurso-card__info">' +
                    '<div class="leyre-recurso-card__badges">' +
                        '<span class="leyre-recurso-card__tipo" style="color:' + cfg.color + ';background:' + cfg.bg + '">' + cfg.label + '</span>' +
                    '</div>' +
                    '<p class="leyre-recurso-card__titulo">' + r.titulo + '</p>' +
                '</div>' +
                '<div class="leyre-recurso-card__accion">' +
                    '<a href="' + r.url_descarga + '" class="leyre-recurso-card__btn">' +
                        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/><path d="M3 20h18"/></svg>' +
                        'Descargar' +
                    '</a>' +
                '</div>' +
            '</div>';
        }).join('');
    }

    function renderModulos(modulos) {
        const grid = document.getElementById('leyre-grid-modulos');
        if (!modulos.length) {
            grid.innerHTML = '<p style="color:var(--leyre-muted)">Los módulos se publicarán próximamente.</p>';
            return;
        }
        grid.innerHTML = modulos.map(function (m) {
            const locked = !m.desbloqueado;
            const p = m.progreso;
            let estado = 'Sin empezar';
            if (p.completadas === p.total && p.total > 0) estado = 'Completado ✓';
            else if (p.completadas > 0) estado = p.porcentaje + '% · ' + p.completadas + ' de ' + p.total + ' lecciones';

            return `<a href="${locked ? '#' : '/mis-cursos/modulo-' + m.id}" class="leyre-card-modulo${locked ? ' leyre-card-modulo--locked' : ''}">
                <div class="leyre-card-modulo__thumb">
                    ${m.thumbnail ? `<img src="${m.thumbnail}" alt="${m.titulo}" loading="lazy">` : '<div style="width:100%;height:100%;background:var(--leyre-beige-light)"></div>'}
                    <div class="leyre-card-modulo__play">
                        ${locked
                            ? `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3-9H9V6a3 3 0 0 1 6 0v2z"/></svg>`
                            : `<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`
                        }
                    </div>
                </div>
                <div class="leyre-card-modulo__body">
                    <p class="leyre-card-modulo__etiqueta">Módulo ${m.numero}</p>
                    <p class="leyre-card-modulo__titulo">${m.titulo}</p>
                    <p class="leyre-card-modulo__estado">${locked ? '🔒 Disponible próximamente' : estado}</p>
                    ${!locked && p.total > 0 ? `<div class="leyre-progress-bar leyre-progress-bar--sm" style="margin-top:8px"><div class="leyre-progress-bar__fill" style="width:${p.porcentaje}%;background:${p.porcentaje===100?'#4CAF50':'var(--leyre-beige)'}"></div></div>` : ''}
                </div>
            </a>`;
        }).join('');
    }
})();
</script>
</body>
</html>
