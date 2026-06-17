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
    <div class="leyre-container">

        <div class="leyre-page-header">
            <h1 class="leyre-page-title">Recursos</h1>
            <p style="color:var(--leyre-muted);margin:0;font-size:14px">Todos tus materiales descargables del programa.</p>
        </div>

        <!-- Filtro por módulo -->
        <div class="leyre-recursos-filtros" id="leyre-recursos-filtros" style="display:none">
            <button class="leyre-filtro-btn active" data-modulo="todos" onclick="leyreFiltrarRecursos(this,'todos')">Todos</button>
        </div>

        <!-- Listado de recursos -->
        <div id="leyre-recursos-wrap">
            <!-- Skeleton -->
            <?php for ( $i = 0; $i < 4; $i++ ) : ?>
            <div class="leyre-recurso-item">
                <div class="leyre-skeleton" style="width:40px;height:40px;border-radius:6px;flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="leyre-skeleton" style="height:14px;width:55%;margin-bottom:6px"></div>
                    <div class="leyre-skeleton" style="height:11px;width:35%"></div>
                </div>
                <div class="leyre-skeleton" style="height:36px;width:100px;border-radius:4px"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Estado vacío -->
        <div id="leyre-recursos-vacio" style="display:none;text-align:center;padding:60px 0">
            <p style="font-size:40px;margin-bottom:12px">📄</p>
            <p style="font-size:18px;color:var(--leyre-dark);font-weight:600">Los recursos se añadirán próximamente</p>
            <p style="color:var(--leyre-muted)">Vuelve pronto para descargar tus materiales.</p>
        </div>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {

    let todosRecursos = [];
    let moduloActivo  = 'todos';

    const iconos = { pdf: '📄', plantilla: '📋', otro: '📎' };

    leyreAPI.get('recursos').then(function (recursos) {
        todosRecursos = recursos;
        const wrap = document.getElementById('leyre-recursos-wrap');

        if (!recursos.length) {
            wrap.innerHTML = '';
            document.getElementById('leyre-recursos-vacio').style.display = 'block';
            return;
        }

        // Construir filtros por módulo
        const modulos = {};
        recursos.forEach(function (r) {
            if (r.modulo_id && !modulos[r.modulo_id]) {
                modulos[r.modulo_id] = r.modulo_titulo || 'Módulo ' + r.modulo_id;
            }
        });

        const filtrosEl = document.getElementById('leyre-recursos-filtros');
        if (Object.keys(modulos).length > 1) {
            filtrosEl.style.display = 'flex';
            Object.entries(modulos).forEach(function ([id, nombre]) {
                const btn = document.createElement('button');
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
            '<p style="color:var(--leyre-muted);text-align:center;padding:40px 0">No se pudieron cargar los recursos. Recarga la página.</p>';
    });

    window.leyreFiltrarRecursos = function (btn, modulo) {
        document.querySelectorAll('.leyre-filtro-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        moduloActivo = modulo;

        const filtrados = modulo === 'todos'
            ? todosRecursos
            : todosRecursos.filter(r => String(r.modulo_id) === String(modulo));

        renderRecursos(filtrados);
    };

    function renderRecursos(lista) {
        const wrap = document.getElementById('leyre-recursos-wrap');

        if (!lista.length) {
            wrap.innerHTML = '<p style="color:var(--leyre-muted);padding:20px 0">No hay recursos en esta categoría.</p>';
            return;
        }

        // Agrupar por módulo
        const grupos = {};
        lista.forEach(function (r) {
            const key = r.modulo_id || 'general';
            if (!grupos[key]) grupos[key] = { titulo: r.modulo_titulo || 'General', items: [] };
            grupos[key].items.push(r);
        });

        wrap.innerHTML = Object.values(grupos).map(function (g) {
            const itemsHtml = g.items.map(function (r) {
                const icono = iconos[r.tipo] || '📎';
                const tipoLabel = (r.tipo || 'pdf').toUpperCase();
                const moduloLabel = r.modulo_titulo ? `${tipoLabel} · ${r.modulo_titulo}` : tipoLabel;

                return `<div class="leyre-recurso-item" data-modulo="${r.modulo_id || ''}">
                    <div class="leyre-recurso-item__icon">${icono}</div>
                    <div class="leyre-recurso-item__info">
                        <p class="leyre-recurso-item__titulo">${r.titulo}</p>
                        <p class="leyre-recurso-item__meta">${moduloLabel}</p>
                    </div>
                    <a href="${r.url_descarga}" class="leyre-btn leyre-btn--primary leyre-btn--sm">
                        Descargar
                    </a>
                </div>`;
            }).join('');

            // Solo mostrar encabezado de grupo si hay más de un grupo
            const mostrarGrupo = Object.keys(grupos).length > 1;
            return `${mostrarGrupo ? `<h3 class="leyre-recursos-grupo">${g.titulo}</h3>` : ''}${itemsHtml}`;
        }).join('');
    }

})();
</script>
</body>
</html>
