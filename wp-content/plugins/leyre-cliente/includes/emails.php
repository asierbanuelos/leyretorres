<?php
defined( 'ABSPATH' ) || exit;

// ─── B-08: Email de bienvenida ────────────────────────────────────────────────

/**
 * Email con usuario + contraseña para alumnas creadas manualmente desde el admin.
 */
function leyre_enviar_email_credenciales( $user_id, $password ) {
    $user      = get_userdata( $user_id );
    $fecha_ini = get_user_meta( $user_id, 'leyre_fecha_inicio', true );
    $fecha_fin = get_user_meta( $user_id, 'leyre_fecha_fin',    true );
    $headers   = [ 'Content-Type: text/html; charset=UTF-8' ];

    wp_mail(
        $user->user_email,
        'Tus credenciales de acceso — Leonas en Tacones',
        leyre_plantilla_email_credenciales( $user->display_name, $user->user_email, $password, home_url( '/login/' ), $fecha_ini, $fecha_fin ),
        $headers
    );
}

function leyre_plantilla_email_credenciales( $nombre, $email, $password, $login_url, $fecha_ini, $fecha_fin ) {
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Inter', Arial, sans-serif; background: #F5F0EA; margin: 0; padding: 0; }
  .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .hero { background: #18160F; padding: 40px 32px; text-align: center; }
  .hero h1 { font-family: Georgia, serif; font-style: italic; color: #C5A882; font-size: 28px; margin: 0 0 8px; }
  .hero p { color: rgba(255,255,255,.7); margin: 0; font-size: 15px; }
  .body { padding: 32px; color: #333; font-size: 15px; line-height: 1.6; }
  .datos { background: #F5F0EA; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
  .datos p { margin: 6px 0; font-size: 14px; }
  .datos strong { color: #18160F; }
  .datos code { background: #e8ddd4; padding: 2px 6px; border-radius: 4px; font-size: 13px; letter-spacing: .03em; }
  .btn { display: inline-block; background: #C5A882; color: #18160F; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; margin-top: 20px; font-size: 15px; }
  .footer { padding: 20px 32px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1>Bienvenida, <?php echo esc_html( $nombre ); ?></h1>
    <p>Leonas en Tacones — Área privada</p>
  </div>
  <div class="body">
    <p>Hola <?php echo esc_html( $nombre ); ?>,</p>
    <p>Tu acceso al programa <strong>Leonas en Tacones</strong> ha sido activado. A continuación tienes tus credenciales para entrar:</p>
    <div class="datos">
      <p><strong>Email de acceso:</strong> <code><?php echo esc_html( $email ); ?></code></p>
      <p><strong>Contraseña:</strong> <code><?php echo esc_html( $password ); ?></code></p>
      <?php if ( $fecha_ini ) : ?><p><strong>Inicio del programa:</strong> <?php echo esc_html( $fecha_ini ); ?></p><?php endif; ?>
      <?php if ( $fecha_fin ) : ?><p><strong>Fin del programa:</strong> <?php echo esc_html( $fecha_fin ); ?></p><?php endif; ?>
    </div>
    <p>Guarda bien tu contraseña. Puedes cambiarla en cualquier momento desde tu perfil dentro del área privada.</p>
    <a href="<?php echo esc_url( $login_url ); ?>" class="btn">Acceder al programa →</a>
    <p style="margin-top:28px; color:#888; font-size:13px">Si tienes cualquier duda, responde a este email y te ayudamos.</p>
  </div>
  <div class="footer">
    &copy; <?php echo date('Y'); ?> Leyre Torres. Todos los derechos reservados.
  </div>
</div>
</body>
</html>
    <?php
    return ob_get_clean();
}

function leyre_enviar_email_bienvenida( $user_id ) {
    $user       = get_userdata( $user_id );
    $fecha_ini  = get_user_meta( $user_id, 'leyre_fecha_inicio', true );
    $fecha_fin  = get_user_meta( $user_id, 'leyre_fecha_fin',    true );
    $reset_link = wp_lostpassword_url();
    $area_link  = home_url( '/area-privada' );

    $asunto  = 'Ya eres parte de Leonas en Tacones';
    $cuerpo  = leyre_plantilla_email_bienvenida( $user->display_name, $user->user_login, $reset_link, $area_link, $fecha_ini, $fecha_fin );
    $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

    wp_mail( $user->user_email, $asunto, $cuerpo, $headers );
}

function leyre_plantilla_email_bienvenida( $nombre, $usuario, $reset_link, $area_link, $fecha_ini, $fecha_fin ) {
    ob_start();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Inter', Arial, sans-serif; background: #F5F0EA; margin: 0; padding: 0; }
  .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
  .hero { background: #C5A882; padding: 40px 32px; text-align: center; }
  .hero h1 { font-family: Georgia, serif; font-style: italic; color: #fff; font-size: 28px; margin: 0 0 8px; }
  .hero p { color: #fff; margin: 0; font-size: 15px; }
  .body { padding: 32px; color: #333; font-size: 15px; line-height: 1.6; }
  .body h2 { font-family: Georgia, serif; font-style: italic; color: #C5A882; }
  .datos { background: #F5F0EA; border-radius: 6px; padding: 16px 20px; margin: 20px 0; }
  .datos p { margin: 4px 0; }
  .btn { display: inline-block; background: #1A1A1A; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 4px; font-weight: bold; margin-top: 20px; }
  .footer { padding: 20px 32px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1>Bienvenida, <?php echo esc_html( $nombre ); ?></h1>
    <p>Ya eres parte de <strong>Leonas en Tacones</strong></p>
  </div>
  <div class="body">
    <h2>Tu programa empieza hoy</h2>
    <p>Estamos encantadas de tenerte con nosotras. A continuación tienes todos los datos para acceder a tu área privada:</p>
    <div class="datos">
      <p><strong>Usuario:</strong> <?php echo esc_html( $usuario ); ?></p>
      <p><strong>Fecha de inicio:</strong> <?php echo esc_html( $fecha_ini ); ?></p>
      <p><strong>Fecha de fin:</strong> <?php echo esc_html( $fecha_fin ); ?></p>
    </div>
    <p>Si es la primera vez que accedes, establece tu contraseña haciendo clic aquí:</p>
    <a href="<?php echo esc_url( $reset_link ); ?>" class="btn">Establecer contraseña</a>
    <p style="margin-top:24px">Una vez tengas tu contraseña, accede a tu programa:</p>
    <a href="<?php echo esc_url( $area_link ); ?>" class="btn">Entrar al área privada</a>
    <p style="margin-top:24px; color:#888; font-size:13px">Si tienes cualquier duda, responde a este email y te ayudamos.</p>
  </div>
  <div class="footer">
    &copy; <?php echo date('Y'); ?> Leyre Torres. Todos los derechos reservados.
  </div>
</div>
</body>
</html>
    <?php
    return ob_get_clean();
}
