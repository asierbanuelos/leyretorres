<?php
/**
 * Template para /mis-cursos/modulo-{id}
 * Cargado desde includes/routing.php via template_include.
 */
defined( 'ABSPATH' ) || exit;

$modulo_id = (int) get_query_var( 'leyre_modulo_id' );
$user_id   = get_current_user_id();
$user      = wp_get_current_user();

// Validación básica server-side (la REST API también valida)
if ( ! $modulo_id ) {
    wp_redirect( home_url( '/mis-cursos' ) );
    exit;
}

// Cargamos datos iniciales server-side para evitar flash
$modulo_data = null;
$resp = wp_remote_get( rest_url( 'leyre/v1/modulo/' . $modulo_id ), [
    'headers' => [
        'Cookie'      => $_SERVER['HTTP_COOKIE'] ?? '',
        'X-WP-Nonce'  => wp_create_nonce( 'wp_rest' ),
    ],
    'timeout' => 5,
]);
if ( ! is_wp_error( $resp ) && wp_remote_retrieve_response_code( $resp ) === 200 ) {
    $modulo_data = json_decode( wp_remote_retrieve_body( $resp ), true );
}

$titulo_modulo = $modulo_data['titulo'] ?? 'Módulo';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $titulo_modulo ); ?> — <?php bloginfo( 'name' ); ?></title>
    <script src="https://player.vimeo.com/api/player.js"></script>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada leyre-modulo-page' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/sidebar-privado' ); ?>

<main class="leyre-main leyre-main--modulo leyre-main--with-sidebar">
    <div class="leyre-container">

        <!-- Breadcrumb -->
        <nav class="leyre-breadcrumb">
            <a href="<?php echo home_url( '/mis-cursos' ); ?>">← Mis cursos</a>
            <span id="leyre-bc-modulo"><?php echo esc_html( $titulo_modulo ); ?></span>
        </nav>

        <div class="leyre-modulo-layout" id="leyre-modulo-layout">

            <!-- ── Sidebar: lista de lecciones ──────────────────────────── -->
            <aside class="leyre-modulo-sidebar" id="leyre-sidebar">
                <div class="leyre-modulo-sidebar__header">
                    <h2 id="leyre-sidebar-titulo" class="leyre-modulo-sidebar__modulo"><?php echo esc_html( $titulo_modulo ); ?></h2>
                    <div class="leyre-modulo-sidebar__progreso">
                        <div class="leyre-progress-bar leyre-progress-bar--sm">
                            <div class="leyre-progress-bar__fill" id="leyre-prog-fill" style="width:0;background:var(--leyre-beige)"></div>
                        </div>
                        <span id="leyre-prog-label" class="leyre-progress-label" style="color:var(--leyre-muted);font-size:12px">Cargando…</span>
                    </div>
                </div>

                <ul class="leyre-leccion-lista" id="leyre-leccion-lista">
                    <!-- Skeleton lecciones -->
                    <?php for ( $i = 0; $i < 5; $i++ ) : ?>
                    <li style="display:flex;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid #eee">
                        <div class="leyre-skeleton" style="width:28px;height:28px;border-radius:50%;flex-shrink:0"></div>
                        <div style="flex:1">
                            <div class="leyre-skeleton" style="height:13px;width:80%;margin-bottom:6px"></div>
                            <div class="leyre-skeleton" style="height:10px;width:40%"></div>
                        </div>
                    </li>
                    <?php endfor; ?>
                </ul>
            </aside>

            <!-- ── Área principal: player + contenido ───────────────────── -->
            <section class="leyre-modulo-content" id="leyre-modulo-content">

                <!-- Player Vimeo -->
                <div class="leyre-player-wrap" id="leyre-player-wrap">
                    <div id="leyre-vimeo-player" style="width:100%;height:100%"></div>
                </div>

                <!-- Cabecera de la lección -->
                <div class="leyre-leccion-header">
                    <div>
                        <p class="leyre-leccion-header__duracion" id="leyre-leccion-duracion"></p>
                        <h1 class="leyre-leccion-header__titulo" id="leyre-leccion-titulo">Cargando lección…</h1>
                    </div>
                    <button
                        class="leyre-btn leyre-btn--beige"
                        id="leyre-btn-completar"
                        onclick="leyreCompletarActual()"
                        style="display:none"
                    >Marcar como completada</button>
                </div>

                <!-- Contenido textual de la lección -->
                <div class="leyre-leccion-body" id="leyre-leccion-body"></div>

                <!-- Navegación prev/next -->
                <div class="leyre-nav-lecciones">
                    <button class="leyre-btn leyre-btn--outline" id="leyre-btn-prev" onclick="leyreNavLeccion(-1)" style="display:none">← Anterior</button>
                    <button class="leyre-btn leyre-btn--primary" id="leyre-btn-next" onclick="leyreNavLeccion(1)"  style="display:none">Siguiente →</button>
                </div>

                <!-- Sugerencia post-vídeo (oculta hasta que el vídeo termina) -->
                <div class="leyre-leccion-ended" id="leyre-leccion-ended" style="display:none">
                    <p>¿Has terminado esta lección?</p>
                    <button class="leyre-btn leyre-btn--beige" onclick="leyreCompletarActual()">Marcar como completada y continuar</button>
                </div>

            </section>

        </div>
    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {
    const MODULO_ID = <?php echo (int) $modulo_id; ?>;
    let modulo      = null;
    let lecciones   = [];
    let indiceActual = 0;
    let vimeoPlayer  = null;

    // ── Inicialización ────────────────────────────────────────────────────
    leyreAPI.get('modulo/' + MODULO_ID).then(function (data) {
        modulo    = data;
        lecciones = data.lecciones || [];

        document.getElementById('leyre-bc-modulo').textContent      = data.titulo;
        document.getElementById('leyre-sidebar-titulo').textContent  = data.titulo;

        renderSidebar();
        actualizarProgreso(data.progreso);

        // Determinar lección inicial: primera no completada, o la primera
        const primeraNoComp = lecciones.findIndex(function (l) { return !l.completada; });
        cargarLeccion(primeraNoComp >= 0 ? primeraNoComp : 0);
    }).catch(function () {
        document.getElementById('leyre-modulo-content').innerHTML =
            '<div class="leyre-sin-acceso"><h2>Módulo no disponible</h2><p>Este módulo aún no está desbloqueado o no existe.</p><a href="/mis-cursos" class="leyre-btn leyre-btn--primary">Volver a mis cursos</a></div>';
        document.getElementById('leyre-leccion-lista').innerHTML = '';
    });

    // ── Sidebar ───────────────────────────────────────────────────────────
    function renderSidebar() {
        const lista = document.getElementById('leyre-leccion-lista');
        if (!lecciones.length) {
            lista.innerHTML = '<li style="padding:20px;color:var(--leyre-muted);text-align:center">Sin lecciones cargadas aún.</li>';
            return;
        }
        lista.innerHTML = lecciones.map(function (l, idx) {
            const completada = l.completada;
            const icono = completada
                ? '<svg viewBox="0 0 24 24" style="width:14px;height:14px;fill:#fff"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>'
                : (idx === indiceActual ? '▶' : (idx + 1));
            return `<li class="leyre-leccion-item${completada ? ' leyre-leccion-item--completada' : ''}" id="leccion-item-${idx}" onclick="cargarLeccion(${idx})">
                <div class="leyre-leccion-item__estado">${icono}</div>
                <div class="leyre-leccion-item__info">
                    <div class="leyre-leccion-item__titulo">${l.titulo}</div>
                    ${l.duracion ? `<div class="leyre-leccion-item__duracion">${l.duracion}</div>` : ''}
                </div>
            </li>`;
        }).join('');
    }

    // ── Cargar lección ─────────────────────────────────────────────────────
    window.cargarLeccion = function (idx) {
        if (idx < 0 || idx >= lecciones.length) return;
        indiceActual = idx;
        const l = lecciones[idx];

        // Marcar activa en sidebar
        document.querySelectorAll('.leyre-leccion-item').forEach(function (el, i) {
            el.classList.toggle('leyre-leccion-item--actual', i === idx);
        });

        // Actualizar cabecera
        document.getElementById('leyre-leccion-titulo').textContent  = l.titulo;
        document.getElementById('leyre-leccion-duracion').textContent = l.duracion || '';
        document.getElementById('leyre-leccion-body').innerHTML       = l.contenido || '';

        // Botón "Marcar completada"
        const btnCompletar = document.getElementById('leyre-btn-completar');
        if (!l.completada) {
            btnCompletar.style.display = '';
            btnCompletar.disabled      = false;
            btnCompletar.textContent   = 'Marcar como completada';
        } else {
            btnCompletar.style.display = 'none';
        }

        // Ocultar sugerencia ended
        document.getElementById('leyre-leccion-ended').style.display = 'none';

        // Prev / Next
        const btnPrev = document.getElementById('leyre-btn-prev');
        const btnNext = document.getElementById('leyre-btn-next');
        btnPrev.style.display = idx > 0 ? '' : 'none';
        btnNext.style.display = idx < lecciones.length - 1 ? '' : 'none';

        // Player Vimeo
        cargarVimeo(l.vimeo_id);

        // Scroll al top del contenido en mobile
        if (window.innerWidth < 900) {
            document.getElementById('leyre-modulo-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // ── Player Vimeo ───────────────────────────────────────────────────────
    function cargarVimeo(vimeoId) {
        const wrap = document.getElementById('leyre-player-wrap');
        if (vimeoPlayer) {
            vimeoPlayer.destroy();
            vimeoPlayer = null;
        }
        if (!vimeoId) {
            wrap.style.display = 'none';
            return;
        }
        wrap.style.display = '';
        // Limpiar iframe anterior
        document.getElementById('leyre-vimeo-player').innerHTML = '';

        vimeoPlayer = new Vimeo.Player('leyre-vimeo-player', {
            id:         parseInt(vimeoId, 10),
            responsive: true,
            dnt:        true,
        });

        // Al terminar el vídeo → mostrar sugerencia de completar
        vimeoPlayer.on('ended', function () {
            const l = lecciones[indiceActual];
            if (!l.completada) {
                document.getElementById('leyre-leccion-ended').style.display = '';
            } else if (indiceActual < lecciones.length - 1) {
                cargarLeccion(indiceActual + 1);
            }
        });
    }

    // ── Marcar completada ─────────────────────────────────────────────────
    window.leyreCompletarActual = function () {
        const l = lecciones[indiceActual];
        if (l.completada) return;

        const btn = document.getElementById('leyre-btn-completar');
        btn.disabled    = true;
        btn.textContent = 'Guardando…';

        leyreCompletarLeccion(l.id, function (data) {
            lecciones[indiceActual].completada = true;
            document.getElementById('leyre-leccion-ended').style.display = 'none';
            btn.style.display = 'none';
            renderSidebar();
            actualizarProgreso(data.progreso_modulo);

            // Avanzar a la siguiente si existe
            if (indiceActual < lecciones.length - 1) {
                setTimeout(function () { cargarLeccion(indiceActual + 1); }, 500);
            }
        });
    };

    // ── Navegación prev/next ──────────────────────────────────────────────
    window.leyreNavLeccion = function (dir) {
        cargarLeccion(indiceActual + dir);
    };

    // ── Progreso ──────────────────────────────────────────────────────────
    function actualizarProgreso(p) {
        if (!p) return;
        document.getElementById('leyre-prog-fill').style.width = p.porcentaje + '%';
        document.getElementById('leyre-prog-label').textContent =
            p.completadas + ' de ' + p.total + ' lecciones (' + p.porcentaje + '%)';
    }

})();
</script>
</body>
</html>
