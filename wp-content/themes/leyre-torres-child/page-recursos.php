<?php
/**
 * Template Name: Área Privada — Recursos
 */
defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recursos — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<main class="leyre-main">

    <!-- ── Hero ──────────────────────────────────────────────────────────── -->
    <div class="leyre-hero leyre-hero--sm">
        <div class="leyre-hero__inner">
            <div class="leyre-section__kicker">Recursos</div>
            <h1 class="leyre-hero__saludo">Tus materiales</h1>
            <p class="leyre-hero__sub">Todos los documentos y plantillas de tu programa</p>
        </div>
    </div>

    <div class="leyre-container">

        <!-- Filtros por módulo -->
        <div class="leyre-recursos-filtros" id="leyre-recursos-filtros" style="display:none">
            <button class="leyre-filtro-btn active" data-modulo="todos"
                    onclick="leyreFiltrarRecursos(this,'todos')">Todos</button>
        </div>

        <!-- Grid de recursos -->
        <div class="leyre-recursos-grid" id="leyre-recursos-wrap">
            <?php for ( $i = 0; $i < 4; $i++ ) : ?>
            <div class="leyre-recurso-card leyre-recurso-card--skeleton">
                <div class="leyre-recurso-card__icono">
                    <div class="leyre-skeleton" style="width:28px;height:34px;border-radius:4px"></div>
                </div>
                <div class="leyre-recurso-card__info">
                    <div class="leyre-skeleton" style="height:11px;width:80px;margin-bottom:10px"></div>
                    <div class="leyre-skeleton" style="height:16px;width:70%;margin-bottom:6px"></div>
                    <div class="leyre-skeleton" style="height:11px;width:50%"></div>
                </div>
                <div class="leyre-recurso-card__accion">
                    <div class="leyre-skeleton" style="height:40px;width:120px;border-radius:8px"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Estado vacío -->
        <div id="leyre-recursos-vacio" style="display:none">
            <div class="leyre-empty-state">
                <div class="leyre-empty-state__icon">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="#F0E8DC"/>
                        <path d="M14 12h14l8 8v18a2 2 0 01-2 2H14a2 2 0 01-2-2V14a2 2 0 012-2z" stroke="#C5A882" stroke-width="1.5" fill="none"/>
                        <path d="M28 12v8h8" stroke="#C5A882" stroke-width="1.5" fill="none"/>
                        <path d="M18 26h12M18 31h8" stroke="#C5A882" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 class="leyre-empty-state__titulo">Los recursos se añadirán próximamente</h3>
                <p class="leyre-empty-state__desc">Aquí encontrarás todas las guías, plantillas y materiales de tu programa.</p>
            </div>
        </div>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {

    var todosRecursos = [];
    var moduloActivo  = 'todos';

    leyreAPI.get('recursos').then(function (recursos) {
        todosRecursos = recursos;
        var wrap = document.getElementById('leyre-recursos-wrap');

        if (!recursos.length) {
            wrap.innerHTML = '';
            document.getElementById('leyre-recursos-vacio').style.display = 'block';
            return;
        }

        // Construir filtros por módulo
        var modulos = {};
        recursos.forEach(function (r) {
            if (r.modulo_id && !modulos[r.modulo_id]) {
                modulos[r.modulo_id] = r.modulo_titulo || ('Módulo ' + r.modulo_id);
            }
        });

        var filtrosEl = document.getElementById('leyre-recursos-filtros');
        if (Object.keys(modulos).length > 1) {
            filtrosEl.style.display = 'flex';
            filtrosEl.style.marginBottom = '32px';
            Object.entries(modulos).forEach(function (entry) {
                var id = entry[0], nombre = entry[1];
                var btn = document.createElement('button');
                btn.className = 'leyre-filtro-btn';
                btn.dataset.modulo = id;
                btn.textContent = nombre;
                btn.onclick = function () { leyreFiltrarRecursos(btn, id); };
                filtrosEl.appendChild(btn);
            });
        }

        renderRecursos(recursos);
    }).catch(function () {
        document.getElementById('leyre-recursos-wrap').innerHTML =
            '<p style="color:var(--c-muted,#8A8080);text-align:center;padding:60px 0;grid-column:1/-1">No se pudieron cargar los recursos. Recarga la página.</p>';
    });

    window.leyreFiltrarRecursos = function (btn, modulo) {
        document.querySelectorAll('.leyre-filtro-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        moduloActivo = modulo;
        var filtrados = modulo === 'todos'
            ? todosRecursos
            : todosRecursos.filter(function(r) { return String(r.modulo_id) === String(modulo); });
        renderRecursos(filtrados);
    };

    function tipoConfig(tipo) {
        var configs = {
            pdf: {
                color: '#B23A2F',
                bg:    '#FDF2F1',
                label: 'PDF',
                svg: '<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FDF2F1" stroke="#B23A2F" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#B23A2F" stroke-width="1.5" fill="none"/><rect x="2" y="17" width="18" height="10" rx="2" fill="#B23A2F"/><text x="11" y="25" font-family="Arial" font-size="7" font-weight="700" fill="white" text-anchor="middle">PDF</text></svg>'
            },
            plantilla: {
                color: '#7A5C1E',
                bg:    '#FBF5E8',
                label: 'Plantilla',
                svg: '<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#FBF5E8" stroke="#C5A882" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#C5A882" stroke-width="1.5" fill="none"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#C5A882" stroke-width="1.5" stroke-linecap="round"/></svg>'
            },
            otro: {
                color: '#3A5C7A',
                bg:    '#EEF4F9',
                label: 'Archivo',
                svg: '<svg width="32" height="38" viewBox="0 0 28 34" fill="none"><path d="M4 2h14l8 8v22a2 2 0 01-2 2H4a2 2 0 01-2-2V4a2 2 0 012-2z" fill="#EEF4F9" stroke="#3A5C7A" stroke-width="1.5"/><path d="M18 2v8h8" stroke="#3A5C7A" stroke-width="1.5" fill="none"/><path d="M7 18h14M7 22h10M7 26h12" stroke="#3A5C7A" stroke-width="1.5" stroke-linecap="round"/></svg>'
            }
        };
        return configs[tipo] || configs.otro;
    }

    function renderRecursos(lista) {
        var wrap = document.getElementById('leyre-recursos-wrap');

        if (!lista.length) {
            wrap.innerHTML = '<p style="color:var(--c-muted,#8A8080);padding:20px 0;grid-column:1/-1">No hay recursos en esta categoría.</p>';
            return;
        }

        // Agrupar por módulo
        var grupos = {};
        lista.forEach(function (r) {
            var key = r.modulo_id ? ('mod-' + r.modulo_id) : 'general';
            if (!grupos[key]) grupos[key] = { titulo: r.modulo_titulo || 'General', items: [] };
            grupos[key].items.push(r);
        });

        var html = '';
        var multiGrupo = Object.keys(grupos).length > 1;

        Object.values(grupos).forEach(function (g) {
            if (multiGrupo) {
                html += '<div class="leyre-recursos-grupo-header" style="grid-column:1/-1"><h3 class="leyre-recursos-grupo-titulo">' + g.titulo + '</h3></div>';
            }

            g.items.forEach(function (r) {
                var cfg = tipoConfig(r.tipo || 'otro');
                var moduloTag = r.modulo_titulo
                    ? '<span class="leyre-recurso-card__modulo">' + r.modulo_titulo + '</span>'
                    : '';

                html += '<div class="leyre-recurso-card" data-modulo="' + (r.modulo_id || '') + '">' +
                    '<div class="leyre-recurso-card__icono" style="background:' + cfg.bg + ';border-color:' + cfg.bg + '">' +
                        cfg.svg +
                    '</div>' +
                    '<div class="leyre-recurso-card__info">' +
                        '<div class="leyre-recurso-card__badges">' +
                            '<span class="leyre-recurso-card__tipo" style="color:' + cfg.color + ';background:' + cfg.bg + '">' + cfg.label + '</span>' +
                            moduloTag +
                        '</div>' +
                        '<p class="leyre-recurso-card__titulo">' + r.titulo + '</p>' +
                    '</div>' +
                    '<div class="leyre-recurso-card__accion">' +
                        '<a href="' + r.url_descarga + '" class="leyre-recurso-card__btn">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/><path d="M3 20h18"/></svg>' +
                            'Descargar' +
                        '</a>' +
                    '</div>' +
                '</div>';
            });
        });

        wrap.innerHTML = html;
    }

})();
</script>
</body>
</html>
