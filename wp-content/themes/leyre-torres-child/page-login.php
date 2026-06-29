<?php
/**
 * Template: Login corporativo — Leonas en Tacones
 * Ruta: /login/ (registrada vía rewrite en routing.php)
 */
defined( 'ABSPATH' ) || exit;

// Redirigir si ya tiene acceso al programa
if ( is_user_logged_in() && function_exists( 'leyre_tiene_acceso' ) && leyre_tiene_acceso() ) {
    wp_redirect( home_url( '/area-privada/' ) );
    exit;
}

$redirect_to = esc_url_raw( $_GET['redirect_to'] ?? home_url( '/area-privada/' ) );
$error       = '';

// Procesar formulario
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['_leyre_login_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['_leyre_login_nonce'], 'leyre_login' ) ) {
        $login = sanitize_user( wp_unslash( $_POST['log'] ?? '' ) );
        $pass  = wp_unslash( $_POST['pwd'] ?? '' );

        if ( ! $login || ! $pass ) {
            $error = 'Por favor, rellena todos los campos.';
        } else {
            $user = wp_signon([
                'user_login'    => $login,
                'user_password' => $pass,
                'remember'      => ! empty( $_POST['rememberme'] ),
            ], false );

            if ( is_wp_error( $user ) ) {
                $error = 'Usuario o contraseña incorrectos.';
            } else {
                wp_redirect( $redirect_to );
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al programa — <?php bloginfo( 'name' ); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body.leyre-login-page {
            min-height: 100vh;
            display: flex;
            font-family: 'Inter', sans-serif;
            background: #18160F;
        }

        /* ── Panel izquierdo (marca) ── */
        .ll-brand {
            width: 42%;
            min-height: 100vh;
            background: #18160F;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 52px;
            position: relative;
            overflow: hidden;
        }

        .ll-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 30% 60%, rgba(197,168,130,.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .ll-brand__logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--c-brand, #C5A882);
        }

        .ll-brand__body { flex: 1; display: flex; flex-direction: column; justify-content: center; }

        .ll-brand__titulo {
            margin-bottom: 28px;
        }

        .ll-brand__titulo img {
            max-width: 260px;
            width: 100%;
            height: auto;
            display: block;
        }

        .ll-brand__titulo em {
            font-style: normal;
            display: block;
        }

        .ll-brand__sub {
            font-size: 15px;
            color: rgba(255,255,255,.45);
            line-height: 1.65;
            max-width: 320px;
        }

        .ll-brand__footer {
            font-size: 11px;
            color: rgba(255,255,255,.2);
            letter-spacing: .05em;
        }

        /* Decoración */
        .ll-brand__deco {
            position: absolute;
            bottom: -60px;
            right: -60px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 1px solid rgba(197,168,130,.1);
            pointer-events: none;
        }
        .ll-brand__deco::before {
            content: '';
            position: absolute;
            inset: 30px;
            border-radius: 50%;
            border: 1px solid rgba(197,168,130,.07);
        }

        /* ── Panel derecho (formulario) ── */
        .ll-form-panel {
            flex: 1;
            background: #FDFAF7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .ll-form-box {
            width: 100%;
            max-width: 400px;
        }

        .ll-form-box__kicker {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--c-brand, #C5A882);
            margin-bottom: 10px;
        }

        .ll-form-box__titulo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            color: #18160F;
            margin-bottom: 6px;
        }

        .ll-form-box__sub {
            font-size: 14px;
            color: #888;
            margin-bottom: 36px;
        }

        /* Error */
        .ll-error {
            background: #FDF2F1;
            border: 1px solid #F5C6C2;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #a03030;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Campos */
        .ll-field {
            margin-bottom: 16px;
        }

        .ll-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            letter-spacing: .03em;
        }

        .ll-field input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #E4DDD6;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: #18160F;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .ll-field input:focus {
            border-color: var(--c-brand, #C5A882);
            box-shadow: 0 0 0 3px rgba(197,168,130,.15);
        }

        /* Recuérdame */
        .ll-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #666;
            cursor: pointer;
        }

        .ll-remember input { width: auto; accent-color: var(--c-brand, #C5A882); }

        /* Botón */
        .ll-submit {
            width: 100%;
            padding: 15px;
            background: #18160F;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }

        .ll-submit:hover { background: #2C261E; }
        .ll-submit:active { transform: scale(.98); }

        /* Links */
        .ll-links {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: #999;
        }

        .ll-links a {
            color: var(--c-brand, #C5A882);
            text-decoration: none;
            font-weight: 600;
        }

        .ll-links a:hover { text-decoration: underline; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            body.leyre-login-page { flex-direction: column; }

            .ll-brand {
                width: 100%;
                min-height: auto;
                padding: 32px 24px 28px;
            }

            .ll-brand__titulo { font-size: clamp(36px, 10vw, 56px); margin-bottom: 12px; }
            .ll-brand__sub { display: none; }
            .ll-brand__deco { display: none; }
            .ll-brand__footer { display: none; }

            .ll-form-panel { padding: 32px 24px 48px; }
        }
    </style>
</head>
<body class="leyre-login-page">
<?php wp_body_open(); ?>

<!-- Panel izquierdo -->
<div class="ll-brand">
    <div class="ll-brand__logo">Leyre Torres</div>

    <div class="ll-brand__body">
        <div class="ll-brand__titulo">
            <?php
            $logo_id  = get_theme_mod( 'custom_logo' );
            $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
            if ( $logo_url ) :
            ?>
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>">
            <?php else : ?>
                <span style="font-family:'Edwardian Script ITC','Great Vibes',cursive;font-size:clamp(48px,6vw,80px);font-weight:400;color:#C5A882;line-height:1.1;display:block;">Leonas en Tacones</span>
            <?php endif; ?>
        </div>
        <p class="ll-brand__sub">Tu espacio privado de mentoría, formación y crecimiento personal. Bienvenida de vuelta.</p>
    </div>

    <p class="ll-brand__footer">© <?php echo date('Y'); ?> Leyre Torres · Todos los derechos reservados</p>

    <div class="ll-brand__deco"></div>
</div>

<!-- Panel derecho: formulario -->
<div class="ll-form-panel">
    <div class="ll-form-box">

        <p class="ll-form-box__kicker">Área privada</p>
        <h2 class="ll-form-box__titulo">Bienvenida de vuelta</h2>
        <p class="ll-form-box__sub">Accede con tus credenciales para continuar tu programa.</p>

        <?php if ( $error ) : ?>
        <div class="ll-error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php echo esc_html( $error ); ?>
        </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'leyre_login', '_leyre_login_nonce' ); ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">

            <div class="ll-field">
                <label for="ll-log">Usuario o email</label>
                <input type="text" id="ll-log" name="log" value="<?php echo esc_attr( $_POST['log'] ?? '' ); ?>"
                       autocomplete="username" autocapitalize="off" spellcheck="false" required>
            </div>

            <div class="ll-field">
                <label for="ll-pwd">Contraseña</label>
                <input type="password" id="ll-pwd" name="pwd" autocomplete="current-password" required>
            </div>

            <label class="ll-remember">
                <input type="checkbox" name="rememberme" value="forever"> Mantener sesión iniciada
            </label>

            <button type="submit" class="ll-submit">Iniciar sesión →</button>
        </form>

        <div class="ll-links">
            <a href="<?php echo esc_url( wp_lostpassword_url( home_url( '/login/' ) ) ); ?>">¿Olvidaste tu contraseña?</a>
        </div>

    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
