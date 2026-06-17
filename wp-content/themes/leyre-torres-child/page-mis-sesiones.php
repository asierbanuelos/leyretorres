<?php
/**
 * Template Name: Área Privada — Mis sesiones
 */
defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
$user_id = get_current_user_id();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis sesiones — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<main class="leyre-main">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <div class="leyre-hero leyre-hero--sm">
        <div class="leyre-hero__inner">
            <div class="leyre-section__kicker">Mis sesiones</div>
            <h1 class="leyre-hero__saludo">Tu acompañamiento</h1>
            <p class="leyre-hero__sub">Sesiones 1:1 y grupales con Leyre</p>
        </div>
    </div>

    <div class="leyre-container">

        <!-- ── Próxima sesión ──────────────────────────────────────────── -->
        <div id="leyre-proxima-wrap">
            <div class="leyre-card-sesion leyre-card-sesion--loading">
                <div class="leyre-skeleton" style="height:12px;width:120px;margin-bottom:10px"></div>
                <div class="leyre-skeleton" style="height:22px;width:55%;margin-bottom:8px"></div>
                <div class="leyre-skeleton" style="height:14px;width:35%;margin-bottom:20px"></div>
                <div class="leyre-skeleton" style="height:44px;width:160px;border-radius:4px"></div>
            </div>
        </div>

        <!-- ── Dos columnas: 1:1 y grupales ───────────────────────────── -->
        <div class="leyre-sesiones-grid">

            <!-- Sesiones 1:1 -->
            <div class="leyre-sesiones-col">
                <h2 class="leyre-sesiones-col__titulo">Sesiones individuales 1:1</h2>
                <p class="leyre-sesiones-col__desc">Tu programa incluye 6 sesiones individuales con Leyre.</p>
                <ul class="leyre-sesiones-lista" id="leyre-lista-1a1">
                    <?php for ( $i = 0; $i < 6; $i++ ) : ?>
                    <li class="leyre-sesion-item">
                        <div class="leyre-skeleton" style="height:13px;width:60%;margin-bottom:6px"></div>
                        <div class="leyre-skeleton" style="height:11px;width:40%"></div>
                    </li>
                    <?php endfor; ?>
                </ul>
            </div>

            <!-- Mentorías grupales -->
            <div class="leyre-sesiones-col">
                <h2 class="leyre-sesiones-col__titulo">Mentorías grupales</h2>
                <p class="leyre-sesiones-col__desc">Sesiones grupales incluidas en el programa.</p>
                <ul class="leyre-sesiones-lista" id="leyre-lista-grupales">
                    <li class="leyre-sesion-item" style="color:var(--leyre-muted)">
                        <div class="leyre-skeleton" style="height:13px;width:50%;margin-bottom:6px"></div>
                        <div class="leyre-skeleton" style="height:11px;width:35%"></div>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {

    function formatFecha(iso) {
        if (!iso) return '';
        return new Date(iso).toLocaleString('es-ES', {
            weekday: 'long', day: 'numeric', month: 'long',
            hour: '2-digit', minute: '2-digit', timeZone: 'Europe/Madrid'
        }) + ' (CET)';
    }

    leyreAPI.get('sesiones').then(function (data) {
        const sesiones       = data.sesiones || [];
        const sesiones1a1    = sesiones.filter(s => s.tipo_sesion !== 'grupal');
        const sesionesGrupal = sesiones.filter(s => s.tipo_sesion === 'grupal');

        renderProxima(sesiones);
        render1a1(sesiones1a1);
        renderGrupales(sesionesGrupal);
    }).catch(function () {
        document.getElementById('leyre-proxima-wrap').innerHTML =
            '<p style="color:var(--c-muted,#8A8080)">No se pudieron cargar las sesiones.</p>';
    });

    // ── Próxima sesión ─────────────────────────────────────────────────────
    function renderProxima(sesiones) {
        const ahora   = new Date();
        const proxima = sesiones.find(s => s.fecha && new Date(s.fecha) > ahora && s.estado !== 'completada');
        const wrap    = document.getElementById('leyre-proxima-wrap');

        if (!proxima) {
            wrap.innerHTML = `<div class="leyre-card-sesion">
                <p class="leyre-card-sesion__tipo">Próxima sesión</p>
                <p class="leyre-card-sesion__titulo" style="opacity:.6">No tienes sesiones programadas próximamente</p>
            </div>`;
            return;
        }

        const tipoLabel = proxima.tipo_sesion === 'grupal' ? 'Mentoría grupal' : '1:1 con Leyre';
        wrap.innerHTML = `<div class="leyre-card-sesion">
            <p class="leyre-card-sesion__tipo">Próxima sesión · ${tipoLabel}</p>
            <p class="leyre-card-sesion__titulo">${proxima.nombre || 'Sesión'}</p>
            <p class="leyre-card-sesion__fecha">${formatFecha(proxima.fecha)}</p>
            ${proxima.enlace_reunion
                ? `<a href="${proxima.enlace_reunion}" target="_blank" rel="noopener" class="leyre-btn leyre-btn--beige">Unirse a la sesión</a>`
                : `<span class="leyre-btn leyre-btn--beige leyre-btn--disabled">Enlace próximamente</span>`
            }
        </div>`;
    }

    // ── Sesiones 1:1 ──────────────────────────────────────────────────────
    function render1a1(sesiones) {
        const lista = document.getElementById('leyre-lista-1a1');

        if (!sesiones.length) {
            lista.innerHTML = '<li class="leyre-sesion-item" style="color:var(--c-muted,#8A8080)">Las sesiones se publicarán pronto.</li>';
            return;
        }

        const ahora = new Date();
        lista.innerHTML = sesiones.map(function (s) {
            const completada = s.estado === 'completada' || (s.fecha && new Date(s.fecha) < ahora);
            const futura     = s.fecha && !completada;

            let estadoHtml = '';
            let accionHtml = '';

            if (completada) {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--ok">Completada</span>`;
            } else if (futura) {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--fecha">${formatFecha(s.fecha)}</span>`;
                if (s.enlace_reunion) {
                    accionHtml = `<a href="${s.enlace_reunion}" target="_blank" rel="noopener" class="leyre-sesion-link">Unirme →</a>`;
                }
            } else {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--pendiente">Por agendar</span>`;
            }

            return `<li class="leyre-sesion-item${completada ? ' leyre-sesion-item--completada' : ''}">
                <div class="leyre-sesion-item__numero">${s.numero || '·'}</div>
                <div class="leyre-sesion-item__body">
                    <p class="leyre-sesion-item__nombre">${s.nombre}</p>
                    ${estadoHtml}
                </div>
                <div class="leyre-sesion-item__accion">${accionHtml}</div>
            </li>`;
        }).join('');
    }

    // ── Mentorías grupales ─────────────────────────────────────────────────
    function renderGrupales(sesiones) {
        const lista = document.getElementById('leyre-lista-grupales');

        if (!sesiones.length) {
            lista.innerHTML = '<li class="leyre-sesion-item" style="color:var(--c-muted,#8A8080)">Las mentorías grupales se publicarán pronto.</li>';
            return;
        }

        const ahora = new Date();
        lista.innerHTML = sesiones.map(function (s) {
            const completada = s.estado === 'completada' || (s.fecha && new Date(s.fecha) < ahora);
            const futura     = s.fecha && !completada;

            return `<li class="leyre-sesion-item${completada ? ' leyre-sesion-item--completada' : ''}">
                <div class="leyre-sesion-item__numero">●</div>
                <div class="leyre-sesion-item__body">
                    <p class="leyre-sesion-item__nombre">${s.nombre || 'Mentoría grupal'}</p>
                    ${completada
                        ? `<span class="leyre-sesion-badge leyre-sesion-badge--ok">Completada</span>`
                        : futura
                            ? `<span class="leyre-sesion-badge leyre-sesion-badge--fecha">${formatFecha(s.fecha)}</span>`
                            : `<span class="leyre-sesion-badge leyre-sesion-badge--pendiente">Por agendar</span>`
                    }
                </div>
                <div class="leyre-sesion-item__accion">
                    ${!completada && s.enlace_reunion
                        ? `<a href="${s.enlace_reunion}" target="_blank" rel="noopener" class="leyre-sesion-link">Unirme →</a>`
                        : ''
                    }
                </div>
            </li>`;
        }).join('');
    }

})();
</script>
</body>
</html>
