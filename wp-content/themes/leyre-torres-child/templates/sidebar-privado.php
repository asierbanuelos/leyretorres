<?php
defined( 'ABSPATH' ) || exit;

$user        = wp_get_current_user();
$logout_url  = wp_logout_url( home_url( '/acceso' ) );
$leyre_page  = get_query_var( 'leyre_page' );

if      ( is_page( 'area-privada' ) )  $current = 'dashboard';
elseif  ( is_page( 'mis-cursos' )   )  $current = 'cursos';
elseif  ( is_page( 'mis-sesiones' ) )  $current = 'sesiones';
elseif  ( is_page( 'recursos' )     )  $current = 'recursos';
elseif  ( $leyre_page === 'audios'  )  $current = 'audios';
elseif  ( is_page( 'mi-perfil' )    )  $current = 'perfil';
else                                    $current = '';

$nav = [
    'dashboard' => [
        'url'   => home_url( '/area-privada/' ),
        'label' => 'Inicio',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>',
    ],
    'cursos' => [
        'url'   => home_url( '/mis-cursos/' ),
        'label' => 'Mis cursos',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>',
    ],
    'sesiones' => [
        'url'   => home_url( '/mis-sesiones/' ),
        'label' => 'Sesiones',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
    ],
    'recursos' => [
        'url'   => home_url( '/recursos/' ),
        'label' => 'Recursos',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>',
    ],
    'audios' => [
        'url'   => home_url( '/audios/' ),
        'label' => 'Audios',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3v5zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3v5z"/></svg>',
    ],
    'perfil' => [
        'url'   => home_url( '/mi-perfil/' ),
        'label' => 'Mi perfil',
        'icon'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
    ],
];
?>

<!-- Overlay móvil -->
<div class="leyre-sb-overlay" id="leyre-sb-overlay"></div>

<!-- Sidebar -->
<aside class="leyre-sb" id="leyre-sb" aria-label="Navegación del área privada">

    <div class="leyre-sb__header">
        <span class="leyre-sb__brand">Leonas en Tacones</span>
        <button class="leyre-sb__close" id="leyre-sb-close" aria-label="Cerrar menú">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="leyre-sb__user">
        <div class="leyre-sb__avatar"><?php echo mb_substr( $user->display_name, 0, 1 ); ?></div>
        <p class="leyre-sb__nombre"><?php echo esc_html( $user->display_name ); ?></p>
    </div>

    <nav class="leyre-sb__nav">
        <?php foreach ( $nav as $key => $item ) : ?>
        <a href="<?php echo esc_url( $item['url'] ); ?>"
           class="leyre-sb__item<?php echo $current === $key ? ' active' : ''; ?>">
            <?php echo $item['icon']; ?>
            <span><?php echo esc_html( $item['label'] ); ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="leyre-sb__footer">
        <a href="<?php echo esc_url( $logout_url ); ?>" class="leyre-sb__item leyre-sb__item--logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            <span>Cerrar sesión</span>
        </a>
    </div>

</aside>

<!-- Toggle hamburguesa (móvil) -->
<button class="leyre-sb-toggle" id="leyre-sb-toggle" aria-label="Abrir menú">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    <span>Menú</span>
</button>

<script>
(function () {
    var sb      = document.getElementById('leyre-sb');
    var overlay = document.getElementById('leyre-sb-overlay');
    var toggle  = document.getElementById('leyre-sb-toggle');
    var close   = document.getElementById('leyre-sb-close');

    function open()  { sb.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function shut()  { sb.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }

    toggle.addEventListener('click', open);
    close.addEventListener('click', shut);
    overlay.addEventListener('click', shut);
})();
</script>
