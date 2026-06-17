<?php
defined( 'ABSPATH' ) || exit;
$user = wp_get_current_user();
$cur  = get_query_var( 'pagename' );
if ( get_query_var( 'leyre_modulo_id' ) ) $cur = 'mis-cursos';
$inicial = strtoupper( mb_substr( $user->display_name, 0, 1 ) );
?>
<nav class="leyre-navbar">
    <a href="<?php echo home_url( '/area-privada' ); ?>" class="leyre-navbar__logo">Leyre Torres</a>

    <button class="leyre-navbar__toggle" aria-label="Menú" id="leyre-menu-toggle">
        <span></span><span></span><span></span>
    </button>

    <ul class="leyre-navbar__nav" id="leyre-nav">
        <li><a href="<?php echo home_url( '/area-privada' ); ?>"  class="<?php echo $cur === 'area-privada' ? 'active' : ''; ?>">Mi programa</a></li>
        <li><a href="<?php echo home_url( '/mis-cursos' ); ?>"    class="<?php echo $cur === 'mis-cursos'   ? 'active' : ''; ?>">Mis cursos</a></li>
        <li><a href="<?php echo home_url( '/recursos' ); ?>"      class="<?php echo $cur === 'recursos'     ? 'active' : ''; ?>">Recursos</a></li>
        <li><a href="<?php echo home_url( '/mis-sesiones' ); ?>"  class="<?php echo $cur === 'mis-sesiones' ? 'active' : ''; ?>">Mis sesiones</a></li>
    </ul>

    <div class="leyre-navbar__user">
        <div class="leyre-navbar__avatar"><?php echo esc_html( $inicial ); ?></div>
        <span class="leyre-navbar__nombre">Hola, <?php echo esc_html( $user->display_name ); ?></span>
        <a href="<?php echo wp_logout_url( home_url() ); ?>" class="leyre-navbar__salir">Salir</a>
    </div>
</nav>
<script>
document.getElementById('leyre-menu-toggle').addEventListener('click', function () {
    document.getElementById('leyre-nav').classList.toggle('open');
});
</script>
