<?php
/**
 * Template Name: Área Privada — Audios
 */
defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audios — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<main class="leyre-main">

    <!-- ── Page Header ──────────────────────────────────────────────────────── -->
    <div class="leyre-page-header">
        <div class="leyre-container">
            <div class="leyre-page-header__kicker">
                <span class="leyre-page-header__kicker-label">Audios</span>
                <span class="leyre-page-header__kicker-nombre"><?php echo esc_html( $user->display_name ); ?></span>
            </div>
            <h1 class="leyre-page-header__titulo">Biblioteca de audios</h1>
        </div>
    </div>

    <div class="leyre-container" style="padding-top:var(--sp-2)">

        <!-- Filtros por categoría -->
        <div class="leyre-recursos-filtros" id="leyre-audios-filtros" style="display:none">
            <button class="leyre-filtro-btn active" data-cat="todos"
                    onclick="leyreFiltrarAudios(this,'todos')">Todos</button>
        </div>

        <!-- Lista de audios -->
        <div class="leyre-recursos-list" id="leyre-audios-wrap">
            <?php for ( $i = 0; $i < 3; $i++ ) : ?>
            <div class="leyre-audio-row">
                <div class="leyre-audio-row__header">
                    <div class="leyre-audio-row__icono">
                        <div class="leyre-skeleton" style="width:18px;height:18px;border-radius:50%"></div>
                    </div>
                    <div class="leyre-audio-row__info">
                        <div class="leyre-skeleton" style="height:14px;width:40%;margin-bottom:8px"></div>
                        <div class="leyre-skeleton" style="height:11px;width:60%"></div>
                    </div>
                    <div class="leyre-skeleton" style="height:11px;width:48px;flex-shrink:0"></div>
                </div>
                <div class="leyre-audio-row__player">
                    <div class="leyre-skeleton" style="height:36px;width:100%;border-radius:18px"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Estado vacío -->
        <div id="leyre-audios-vacio" style="display:none">
            <div class="leyre-empty-state">
                <div class="leyre-empty-state__icon">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="12" fill="#F0E8DC"/>
                        <path d="M18 14v20l14-10-14-10z" stroke="#C5A882" stroke-width="1.5" stroke-linejoin="round" fill="none"/>
                        <path d="M12 24c0 6.627 5.373 12 12 12s12-5.373 12-12S30.627 12 24 12" stroke="#C5A882" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <h3 class="leyre-empty-state__titulo">Los audios se añadirán próximamente</h3>
                <p class="leyre-empty-state__desc">Aquí encontrarás meditaciones, ejercicios y materiales en audio de tu programa.</p>
            </div>
        </div>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {

    var todosAudios = [];

    leyreAPI.get('audios').then(function (audios) {
        todosAudios = audios;
        var wrap = document.getElementById('leyre-audios-wrap');

        if (!audios.length) {
            wrap.innerHTML = '';
            document.getElementById('leyre-audios-vacio').style.display = 'block';
            return;
        }

        // Filtros por categoría (solo si hay más de una)
        var cats = {};
        audios.forEach(function (a) {
            if (a.categoria && !cats[a.categoria]) cats[a.categoria] = true;
        });

        var filtrosEl = document.getElementById('leyre-audios-filtros');
        if (Object.keys(cats).length > 1) {
            filtrosEl.style.display = 'flex';
            filtrosEl.style.marginBottom = '24px';
            Object.keys(cats).forEach(function (cat) {
                var btn = document.createElement('button');
                btn.className = 'leyre-filtro-btn';
                btn.dataset.cat = cat;
                btn.textContent = cat;
                btn.onclick = function () { leyreFiltrarAudios(btn, cat); };
                filtrosEl.appendChild(btn);
            });
        }

        renderAudios(audios);
    }).catch(function () {
        document.getElementById('leyre-audios-wrap').innerHTML =
            '<p style="color:var(--c-muted,#8A8080);padding:60px 0;text-align:center">No se pudieron cargar los audios. Recarga la página.</p>';
    });

    window.leyreFiltrarAudios = function (btn, cat) {
        document.querySelectorAll('#leyre-audios-filtros .leyre-filtro-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        var filtrados = cat === 'todos'
            ? todosAudios
            : todosAudios.filter(function(a) { return a.categoria === cat; });
        renderAudios(filtrados);
    };

    function renderAudios(lista) {
        var wrap = document.getElementById('leyre-audios-wrap');

        if (!lista.length) {
            wrap.innerHTML = '<p style="color:var(--c-muted,#8A8080);padding:20px 0">No hay audios en esta categoría.</p>';
            return;
        }

        var html = '';
        lista.forEach(function (a) {
            var meta = a.categoria || '';
            if (a.duracion) meta += (meta ? ' · ' : '') + a.duracion;

            html +=
                '<div class="leyre-audio-row">' +
                    '<div class="leyre-audio-row__header">' +
                        '<div class="leyre-audio-row__icono">' +
                            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
                                '<circle cx="12" cy="12" r="10"/>' +
                                '<polygon points="10 8 16 12 10 16 10 8"/>' +
                            '</svg>' +
                        '</div>' +
                        '<div class="leyre-audio-row__info">' +
                            '<p class="leyre-audio-row__titulo">' + escHtml(a.titulo) + '</p>' +
                            (a.descripcion ? '<p class="leyre-audio-row__desc">' + escHtml(a.descripcion) + '</p>' : '') +
                            (meta ? '<p class="leyre-audio-row__meta">' + escHtml(meta) + '</p>' : '') +
                        '</div>' +
                        (a.duracion ? '<span class="leyre-audio-row__duracion">' + escHtml(a.duracion) + '</span>' : '') +
                    '</div>' +
                    (a.url ?
                        '<div class="leyre-audio-row__player">' +
                            '<audio controls controlsList="nodownload" oncontextmenu="return false" src="' + a.url + '" preload="none" style="width:100%"></audio>' +
                        '</div>'
                    : '<p class="leyre-audio-row__sin-archivo">Audio no disponible aún.</p>') +
                '</div>';
        });

        wrap.innerHTML = html;
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();
</script>
</body>
</html>
