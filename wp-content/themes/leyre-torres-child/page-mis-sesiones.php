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
    <div class="leyre-container">

        <div class="leyre-page-header">
            <h1 class="leyre-page-title">Mis sesiones</h1>
        </div>

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

    function esPasada(iso) {
        return iso && new Date(iso) < new Date();
    }

    leyreAPI.get('sesiones').then(function (data) {
        const tipos    = data.tipos_1a1         || [];
        const eventos  = data.sesiones_calendly || [];

        // Ahora: separar eventos en 1:1 y grupales.
        // Heurística: si el nombre del evento coincide (case-insensitive) con algún tipo 1:1 → es 1:1.
        const tiposNombres = tipos.map(t => (t.nombre || '').toLowerCase());

        const eventos1a1 = {};   // clave: nombre normalizado del tipo → evento Calendly
        const eventosGrupales = [];

        eventos.forEach(function (e) {
            const nombreEvento = (e.nombre || '').toLowerCase();
            const tipoIdx = tiposNombres.findIndex(tn => nombreEvento.includes(tn) || (tn && tn.includes(nombreEvento)));
            if (tipoIdx >= 0) {
                // Guardar la sesión más reciente (futura primero, luego pasada)
                const key = tiposNombres[tipoIdx];
                if (!eventos1a1[key] || (!esPasada(e.inicio) && esPasada(eventos1a1[key].inicio))) {
                    eventos1a1[key] = e;
                }
            } else {
                eventosGrupales.push(e);
            }
        });

        renderProxima(eventos);
        render1a1(tipos, eventos1a1);
        renderGrupales(eventosGrupales);
    }).catch(function () {
        document.getElementById('leyre-proxima-wrap').innerHTML =
            '<p style="color:var(--leyre-muted)">No se pudieron cargar las sesiones.</p>';
    });

    // ── Próxima sesión ─────────────────────────────────────────────────────
    function renderProxima(eventos) {
        const ahora   = new Date();
        const proxima = eventos.find(e => new Date(e.inicio) > ahora);
        const wrap    = document.getElementById('leyre-proxima-wrap');

        if (!proxima) {
            wrap.innerHTML = `<div class="leyre-card-sesion">
                <p class="leyre-card-sesion__tipo">Próxima sesión</p>
                <p class="leyre-card-sesion__titulo" style="opacity:.6">No tienes sesiones programadas próximamente</p>
            </div>`;
            return;
        }

        wrap.innerHTML = `<div class="leyre-card-sesion">
            <p class="leyre-card-sesion__tipo">Próxima sesión</p>
            <p class="leyre-card-sesion__titulo">${proxima.nombre || 'Sesión'}</p>
            <p class="leyre-card-sesion__fecha">${formatFecha(proxima.inicio)}</p>
            ${proxima.zoom_link
                ? `<a href="${proxima.zoom_link}" target="_blank" rel="noopener" class="leyre-btn leyre-btn--beige">Unirme por Zoom</a>`
                : `<span class="leyre-btn leyre-btn--beige leyre-btn--disabled">Link próximamente</span>`
            }
        </div>`;
    }

    // ── Sesiones 1:1 ──────────────────────────────────────────────────────
    function render1a1(tipos, matchedEventos) {
        const lista = document.getElementById('leyre-lista-1a1');

        if (!tipos.length) {
            lista.innerHTML = '<li class="leyre-sesion-item" style="color:var(--leyre-muted)">Las sesiones se publicarán pronto.</li>';
            return;
        }

        lista.innerHTML = tipos.map(function (tipo) {
            const evento   = matchedEventos[(tipo.nombre || '').toLowerCase()];
            const pasada   = evento && esPasada(evento.inicio);
            const futura   = evento && !pasada;

            let estadoHtml = '';
            let accionHtml = '';

            if (pasada) {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--ok">Completada</span>`;
            } else if (futura) {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--fecha">${formatFecha(evento.inicio)}</span>`;
                if (evento.zoom_link) {
                    accionHtml = `<a href="${evento.zoom_link}" target="_blank" rel="noopener" class="leyre-sesion-link">Unirme →</a>`;
                }
            } else {
                estadoHtml = `<span class="leyre-sesion-badge leyre-sesion-badge--pendiente">Por agendar</span>`;
                if (tipo.calendly_link) {
                    accionHtml = `<a href="${tipo.calendly_link}" target="_blank" rel="noopener" class="leyre-sesion-link">Agendar →</a>`;
                }
            }

            return `<li class="leyre-sesion-item${pasada ? ' leyre-sesion-item--completada' : ''}">
                <div class="leyre-sesion-item__numero">${tipo.numero || '·'}</div>
                <div class="leyre-sesion-item__body">
                    <p class="leyre-sesion-item__nombre">${tipo.nombre}</p>
                    ${estadoHtml}
                </div>
                <div class="leyre-sesion-item__accion">${accionHtml}</div>
            </li>`;
        }).join('');
    }

    // ── Mentorías grupales ─────────────────────────────────────────────────
    function renderGrupales(eventos) {
        const lista = document.getElementById('leyre-lista-grupales');

        if (!eventos.length) {
            lista.innerHTML = '<li class="leyre-sesion-item" style="color:var(--leyre-muted)">Las mentorías grupales se publicarán pronto.</li>';
            return;
        }

        lista.innerHTML = eventos.map(function (e) {
            const pasada = esPasada(e.inicio);
            return `<li class="leyre-sesion-item${pasada ? ' leyre-sesion-item--completada' : ''}">
                <div class="leyre-sesion-item__numero">●</div>
                <div class="leyre-sesion-item__body">
                    <p class="leyre-sesion-item__nombre">${e.nombre || 'Mentoría grupal'}</p>
                    ${pasada
                        ? `<span class="leyre-sesion-badge leyre-sesion-badge--ok">Completada</span>`
                        : `<span class="leyre-sesion-badge leyre-sesion-badge--fecha">${formatFecha(e.inicio)}</span>`
                    }
                </div>
                <div class="leyre-sesion-item__accion">
                    ${!pasada && e.zoom_link
                        ? `<a href="${e.zoom_link}" target="_blank" rel="noopener" class="leyre-sesion-link">Unirme →</a>`
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
