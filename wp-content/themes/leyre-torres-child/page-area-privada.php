<?php
/**
 * Template Name: Área Privada — Dashboard
 */
defined( 'ABSPATH' ) || exit;

$user_id    = get_current_user_id();
$user       = wp_get_current_user();
$dia        = leyre_get_dia_programa( $user_id );
$duracion   = (int) get_option( 'leyre_duracion_programa', 90 );
$dia_show   = $dia ?? 0;
$porcentaje = ( $duracion > 0 && $dia !== null ) ? min( 100, round( ( $dia / $duracion ) * 100 ) ) : 0;
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
            <div class="leyre-hero__progreso">
                <div class="leyre-hero__progreso-labels">
                    <span>Tu progreso en el programa</span>
                    <?php if ( $dia !== null ) : ?>
                        <span>Día <?php echo $dia; ?> de <?php echo $duracion; ?></span>
                    <?php else : ?>
                        <span style="opacity:.7">Programa por comenzar</span>
                    <?php endif; ?>
                </div>
                <div class="leyre-progress-bar leyre-progress-bar--hero">
                    <div class="leyre-progress-bar__fill leyre-progress-bar__fill--blanco" style="width:<?php echo $porcentaje; ?>%"></div>
                </div>
            </div>
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
            <div class="leyre-recursos-list" id="leyre-recursos-dashboard">
                <?php for ( $i = 0; $i < 3; $i++ ) : ?>
                <div class="leyre-recurso-row">
                    <div class="leyre-recurso-row__icono">
                        <div class="leyre-skeleton" style="width:18px;height:22px;border-radius:3px"></div>
                    </div>
                    <div class="leyre-recurso-row__info">
                        <div class="leyre-skeleton" style="height:14px;width:48%;margin-bottom:8px"></div>
                        <div class="leyre-skeleton" style="height:11px;width:28%"></div>
                    </div>
                    <div class="leyre-skeleton" style="height:11px;width:80px;margin-left:auto"></div>
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
        const fecha = new Date(sesion.fecha).toLocaleString('es-ES', {
            weekday: 'long', day: 'numeric', month: 'long',
            hour: '2-digit', minute: '2-digit', timeZone: 'Europe/Madrid'
        });
        const tipoLabel = sesion.tipo_sesion === 'grupal' ? 'Mentoría grupal' : '1:1 con Leyre';
        el.innerHTML = `<div class="leyre-card-sesion">
            <div class="leyre-card-sesion__icono-wrap">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            </div>
            <div class="leyre-card-sesion__info">
                <p class="leyre-card-sesion__tipo">Próxima sesión · ${tipoLabel}</p>
                <p class="leyre-card-sesion__titulo">${sesion.nombre || 'Sesión'}</p>
                <p class="leyre-card-sesion__fecha">${fecha} (CET)</p>
            </div>
            ${sesion.enlace_reunion
                ? `<a href="${sesion.enlace_reunion}" target="_blank" rel="noopener" class="leyre-btn leyre-btn--beige leyre-btn--sm">Unirse a la sesión</a>`
                : `<span class="leyre-btn leyre-btn--outline leyre-btn--sm leyre-btn--disabled">Enlace próximamente</span>`
            }
        </div>`;
    }

    function recursoTipoConfig(tipo) {
        var cfg = {
            pdf:      { color:'#B23A2F', bg:'#FDF2F1', label:'PDF',      svg:'<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FDF2F1" stroke="#B23A2F" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#B23A2F" stroke-width="1.5" fill="none"/><rect x="2" y="17" width="18" height="10" rx="2" fill="#B23A2F"/><text x="11" y="25" font-family="Arial" font-size="7" font-weight="700" fill="white" text-anchor="middle">PDF</text></svg>' },
            plantilla:{ color:'#7A5C1E', bg:'#FBF5E8', label:'Plantilla',svg:'<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FBF5E8" stroke="#C5A882" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#C5A882" stroke-width="1.5"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#C5A882" stroke-width="1.5" stroke-linecap="round"/></svg>' },
            otro:     { color:'#3A5C7A', bg:'#EEF4F9', label:'Archivo',  svg:'<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#EEF4F9" stroke="#3A5C7A" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#3A5C7A" stroke-width="1.5"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#3A5C7A" stroke-width="1.5" stroke-linecap="round"/></svg>' }
        };
        return cfg[tipo] || cfg.otro;
    }

    function renderRecursosDashboard(recursos) {
        var list = document.getElementById('leyre-recursos-dashboard');
        if (!recursos.length) {
            list.innerHTML = '<p style="color:var(--c-muted,#8A8080);font-size:14px;padding:8px 0">Los recursos se añadirán próximamente.</p>';
            return;
        }
        list.innerHTML = recursos.map(function(r) {
            var tipo = r.tipo || 'otro';
            var tipoLabel = tipo === 'pdf' ? 'PDF' : tipo === 'plantilla' ? 'Plantilla' : 'Archivo';
            var meta = tipoLabel + (r.modulo_titulo ? ' · ' + r.modulo_titulo : '');
            return '<div class="leyre-recurso-row">' +
                '<div class="leyre-recurso-row__icono">' +
                    '<svg width="18" height="22" viewBox="0 0 24 28" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
                        '<path d="M4 2h10l6 6v18a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z"/>' +
                        '<path d="M14 2v6h6"/>' +
                        '<path d="M7 13h10M7 17h7"/>' +
                    '</svg>' +
                '</div>' +
                '<div class="leyre-recurso-row__info">' +
                    '<p class="leyre-recurso-row__titulo">' + r.titulo + '</p>' +
                    '<p class="leyre-recurso-row__meta">' + meta + '</p>' +
                '</div>' +
                '<a href="' + r.url_descarga + '" class="leyre-recurso-row__btn">' +
                    '<span>Descargar</span>' +
                    '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>' +
                '</a>' +
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
