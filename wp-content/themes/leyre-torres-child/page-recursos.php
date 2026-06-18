<?php
/**
 * Template Name: Área Privada — Recursos
 */
defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();
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
<?php get_template_part( 'templates/sidebar-privado' ); ?>

<main class="leyre-main leyre-main--with-sidebar">

    <!-- ── Page Header ──────────────────────────────────────────────────────── -->
    <div class="leyre-page-header">
        <div class="leyre-container">
            <div class="leyre-page-header__kicker">
                <span class="leyre-page-header__kicker-label">Recursos</span>
                <span class="leyre-page-header__kicker-nombre"><?php echo esc_html( $user->display_name ); ?></span>
            </div>
            <h1 class="leyre-page-header__titulo">Materiales descargables</h1>
        </div>
    </div>

    <div class="leyre-container" style="padding-top:var(--sp-2)">

        <!-- Filtros por módulo -->
        <div class="leyre-recursos-filtros" id="leyre-recursos-filtros" style="display:none">
            <button class="leyre-filtro-btn active" data-modulo="todos"
                    onclick="leyreFiltrarRecursos(this,'todos')">Todos</button>
        </div>

        <!-- Lista de recursos -->
        <div class="leyre-recursos-list" id="leyre-recursos-wrap">
            <?php for ( $i = 0; $i < 4; $i++ ) : ?>
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

    leyreAPI.get('recursos').then(function (recursos) {
        todosRecursos = recursos;
        var wrap = document.getElementById('leyre-recursos-wrap');

        if (!recursos.length) {
            wrap.innerHTML = '';
            document.getElementById('leyre-recursos-vacio').style.display = 'block';
            return;
        }

        // Filtros por módulo (solo si hay más de uno)
        var modulos = {};
        recursos.forEach(function (r) {
            if (r.modulo_id && !modulos[r.modulo_id]) {
                modulos[r.modulo_id] = r.modulo_titulo || ('Módulo ' + r.modulo_id);
            }
        });

        var filtrosEl = document.getElementById('leyre-recursos-filtros');
        if (Object.keys(modulos).length > 1) {
            filtrosEl.style.display = 'flex';
            filtrosEl.style.marginBottom = '24px';
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
            '<p style="color:var(--c-muted,#8A8080);padding:60px 0;text-align:center">No se pudieron cargar los recursos. Recarga la página.</p>';
    });

    window.leyreFiltrarRecursos = function (btn, modulo) {
        document.querySelectorAll('.leyre-filtro-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var filtrados = modulo === 'todos'
            ? todosRecursos
            : todosRecursos.filter(function(r) { return String(r.modulo_id) === String(modulo); });
        renderRecursos(filtrados);
    };

    function renderRecursos(lista) {
        var wrap = document.getElementById('leyre-recursos-wrap');

        if (!lista.length) {
            wrap.innerHTML = '<p style="color:var(--c-muted,#8A8080);padding:20px 0">No hay recursos en esta categoría.</p>';
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
                html += '<h3 class="leyre-recurso-row__grupo">' + g.titulo + '</h3>';
            }
            g.items.forEach(function (r) {
                var tipo = r.tipo || 'otro';
                var tipoLabel = tipo === 'pdf' ? 'PDF' : tipo === 'plantilla' ? 'Plantilla' : 'Archivo';
                var meta = tipoLabel;
                if (r.modulo_titulo) meta += ' · ' + r.modulo_titulo;

                html += '<div class="leyre-recurso-row" data-modulo="' + (r.modulo_id || '') + '">' +
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
                        '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">' +
                            '<path d="M12 5v14M5 12l7 7 7-7"/>' +
                        '</svg>' +
                    '</a>' +
                '</div>';
            });
        });

        wrap.innerHTML = html;
    }

})();
</script>
</body>
</html>
