<?php
/**
 * Template Name: Área Privada — Mis cursos
 */
defined( 'ABSPATH' ) || exit;

$user_id  = get_current_user_id();
$user     = wp_get_current_user();
$progreso = leyre_get_progreso_global( $user_id );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis cursos — <?php bloginfo( 'name' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'leyre-area-privada' ); ?>>
<?php wp_body_open(); ?>

<?php get_template_part( 'templates/navbar' ); ?>

<main class="leyre-main">
    <div class="leyre-container">

        <!-- Cabecera -->
        <div class="leyre-page-header">
            <h1 class="leyre-page-title">Mis cursos</h1>
            <div class="leyre-progreso-resumen">
                <span><?php echo $progreso['completadas']; ?> de <?php echo $progreso['total']; ?> lecciones completadas</span>
                <div class="leyre-progress-bar leyre-progress-bar--sm">
                    <div class="leyre-progress-bar__fill" style="width:<?php echo $progreso['porcentaje']; ?>%;background:var(--leyre-beige)"></div>
                </div>
                <strong><?php echo $progreso['porcentaje']; ?>%</strong>
            </div>
        </div>

        <!-- Grid de módulos -->
        <div class="leyre-grid-modulos leyre-grid-modulos--full" id="leyre-grid-modulos-full">
            <!-- Skeleton mientras carga -->
            <?php for ( $i = 0; $i < 6; $i++ ) : ?>
            <div class="leyre-card-modulo" style="pointer-events:none">
                <div class="leyre-card-modulo__thumb leyre-skeleton" style="aspect-ratio:16/9"></div>
                <div class="leyre-card-modulo__body">
                    <div class="leyre-skeleton" style="height:11px;width:70px;margin-bottom:8px"></div>
                    <div class="leyre-skeleton" style="height:18px;width:80%;margin-bottom:12px"></div>
                    <div class="leyre-skeleton" style="height:10px;width:55%;margin-bottom:10px"></div>
                    <div class="leyre-skeleton leyre-progress-bar" style="height:6px"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Estado vacío (oculto por defecto, JS lo muestra si no hay módulos) -->
        <div id="leyre-cursos-vacio" style="display:none;text-align:center;padding:60px 0;color:var(--leyre-muted)">
            <p style="font-size:18px">Los módulos están siendo preparados para ti.</p>
            <p>Vuelve pronto.</p>
        </div>

    </div>
</main>

<?php wp_footer(); ?>

<script>
(function () {
    leyreAPI.get('modulos').then(function (modulos) {
        const grid = document.getElementById('leyre-grid-modulos-full');

        if (!modulos.length) {
            grid.style.display = 'none';
            document.getElementById('leyre-cursos-vacio').style.display = 'block';
            return;
        }

        grid.innerHTML = modulos.map(function (m) {
            const locked   = !m.desbloqueado;
            const p        = m.progreso;
            const completo = p.completadas === p.total && p.total > 0;
            const enCurso  = p.completadas > 0 && !completo;

            let estadoLabel = 'Sin empezar';
            let estadoClass = '';
            if (completo)  { estadoLabel = 'Completado'; estadoClass = 'leyre-badge--ok'; }
            if (enCurso)   { estadoLabel = p.porcentaje + '% &middot; ' + p.completadas + ' de ' + p.total + ' lecciones'; }

            const href = locked ? '#' : '/mis-cursos/modulo-' + m.id;
            const clsLocked = locked ? ' leyre-card-modulo--locked' : '';

            return `<a href="${href}" class="leyre-card-modulo${clsLocked}" ${locked ? 'onclick="return false"' : ''}>
                <div class="leyre-card-modulo__thumb">
                    ${m.thumbnail ? `<img src="${m.thumbnail}" alt="${m.titulo}" loading="lazy">` : '<div style="width:100%;height:100%;background:var(--leyre-beige)"></div>'}
                    <div class="leyre-card-modulo__play">
                        ${locked
                            ? `<svg viewBox="0 0 24 24"><path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3-9H9V6a3 3 0 0 1 6 0v2z"/></svg>`
                            : `<svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>`
                        }
                    </div>
                    ${completo ? '<div class="leyre-card-modulo__badge">✓ Completado</div>' : ''}
                </div>
                <div class="leyre-card-modulo__body">
                    <p class="leyre-card-modulo__etiqueta">Módulo ${m.numero}</p>
                    <p class="leyre-card-modulo__titulo">${m.titulo}</p>
                    ${m.descripcion ? `<p class="leyre-card-modulo__desc">${m.descripcion}</p>` : ''}
                    <p class="leyre-card-modulo__estado ${estadoClass}">${locked ? '🔒 Disponible próximamente' : estadoLabel}</p>
                    ${!locked && p.total > 0 ? `
                    <div class="leyre-progress-bar leyre-progress-bar--sm" style="margin-top:8px">
                        <div class="leyre-progress-bar__fill" style="width:${p.porcentaje}%;background:${completo ? '#4CAF50' : 'var(--leyre-beige)'}"></div>
                    </div>` : ''}
                </div>
            </a>`;
        }).join('');
    }).catch(function () {
        document.getElementById('leyre-grid-modulos-full').innerHTML =
            '<p style="color:var(--leyre-muted)">No se pudieron cargar los módulos. Recarga la página.</p>';
    });
})();
</script>
</body>
</html>
