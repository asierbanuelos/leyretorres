<?php
defined( 'ABSPATH' ) || exit;

// ─── B-07: Panel de administración ───────────────────────────────────────────

add_action( 'admin_menu', 'leyre_registrar_menu_admin' );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( strpos( $hook, 'leyre' ) === false ) return;
    wp_enqueue_media();
    wp_enqueue_script( 'jquery' );
});

function leyre_registrar_menu_admin() {
    add_menu_page(
        'Leyre Torres',
        'Leyre Torres',
        'manage_options',
        'leyre-admin',
        'leyre_pagina_alumnas',
        'dashicons-groups',
        30
    );

    add_submenu_page( 'leyre-admin', 'Alumnas activas', 'Alumnas activas', 'manage_options', 'leyre-admin',           'leyre_pagina_alumnas' );
    add_submenu_page( 'leyre-admin', 'Configuración',   'Configuración',   'manage_options', 'leyre-configuracion',   'leyre_pagina_configuracion' );
}

// ─── Página: Alumnas ─────────────────────────────────────────────────────────

function leyre_pagina_alumnas() {
    $notice = '';

    // ── Procesar acciones POST ────────────────────────────────────────────────
    if ( isset( $_POST['_leyre_nonce'] ) && wp_verify_nonce( $_POST['_leyre_nonce'], 'leyre_acceso_action' ) ) {

        $accion = sanitize_key( $_POST['leyre_accion'] ?? '' );

        // Crear nueva alumna
        if ( $accion === 'crear_alumna' ) {
            $nombre = sanitize_text_field( $_POST['leyre_nombre'] ?? '' );
            $email  = sanitize_email( $_POST['leyre_email'] ?? '' );
            $enviar = ! empty( $_POST['leyre_enviar_email'] );

            if ( ! $nombre || ! $email || ! is_email( $email ) ) {
                $notice = "<div class='notice notice-error is-dismissible'><p>Nombre y email válido son obligatorios.</p></div>";
            } elseif ( email_exists( $email ) ) {
                $notice = "<div class='notice notice-error is-dismissible'><p>Ya existe una usuaria con ese email. Búscala en la lista y activa su acceso desde ahí.</p></div>";
            } else {
                $password = wp_generate_password( 12, false );
                $uid      = wp_insert_user([
                    'user_login'   => $email,
                    'user_email'   => $email,
                    'display_name' => $nombre,
                    'first_name'   => explode( ' ', $nombre )[0],
                    'user_pass'    => $password,
                    'role'         => 'alumno',
                ]);

                if ( is_wp_error( $uid ) ) {
                    $notice = "<div class='notice notice-error is-dismissible'><p>" . esc_html( $uid->get_error_message() ) . "</p></div>";
                } else {
                    leyre_activar_acceso( $uid );
                    if ( $enviar ) {
                        leyre_enviar_email_credenciales( $uid, $password );
                    }
                    $fin = get_user_meta( $uid, 'leyre_fecha_fin', true );
                    $notice = "<div class='notice notice-success is-dismissible'><p>✓ Alumna <strong>" . esc_html( $nombre ) . "</strong> creada con acceso hasta <strong>{$fin}</strong>." . ( $enviar ? ' Credenciales enviadas a <strong>' . esc_html( $email ) . '</strong>.' : '' ) . "</p></div>";
                }
            }
        }

        // Ampliar individual
        if ( $accion === 'ampliar_individual' ) {
            $uid  = absint( $_POST['leyre_uid'] );
            $dias = absint( $_POST['leyre_dias'] );
            if ( $uid && $dias ) {
                $fin_actual  = get_user_meta( $uid, 'leyre_fecha_fin', true );
                $base        = $fin_actual ? max( strtotime( $fin_actual ), strtotime( 'today' ) ) : strtotime( 'today' );
                $nueva_fin   = date( 'Y-m-d', strtotime( "+{$dias} days", $base ) );
                leyre_actualizar_acceso( $uid, $nueva_fin );
                $u           = get_userdata( $uid );
                $notice      = "<div class='notice notice-success is-dismissible'><p>Acceso de <strong>" . esc_html( $u->display_name ) . "</strong> ampliado hasta <strong>{$nueva_fin}</strong>.</p></div>";
            }
        }

        // Ampliar bulk
        if ( $accion === 'ampliar_bulk' ) {
            $uids = array_map( 'absint', $_POST['leyre_uids'] ?? [] );
            $dias = absint( $_POST['leyre_dias_bulk'] );
            if ( $uids && $dias ) {
                foreach ( $uids as $uid ) {
                    $fin_actual = get_user_meta( $uid, 'leyre_fecha_fin', true );
                    $base       = $fin_actual ? max( strtotime( $fin_actual ), strtotime( 'today' ) ) : strtotime( 'today' );
                    $nueva_fin  = date( 'Y-m-d', strtotime( "+{$dias} days", $base ) );
                    leyre_actualizar_acceso( $uid, $nueva_fin );
                }
                $notice = "<div class='notice notice-success is-dismissible'><p>" . count( $uids ) . " alumna(s) ampliadas " . esc_html( $dias ) . " días más.</p></div>";
            }
        }

        // Reactivar (nuevo período desde hoy)
        if ( $accion === 'reactivar' ) {
            $uid      = absint( $_POST['leyre_uid'] );
            $duracion = (int) get_option( 'leyre_duracion_programa', 90 );
            $nueva_fin = date( 'Y-m-d', strtotime( "+{$duracion} days" ) );
            update_user_meta( $uid, 'leyre_fecha_fin',     $nueva_fin );
            update_user_meta( $uid, 'leyre_acceso_activo', '1' );
            $u      = get_userdata( $uid );
            $notice = "<div class='notice notice-success is-dismissible'><p>Acceso de <strong>" . esc_html( $u->display_name ) . "</strong> reactivado hasta <strong>{$nueva_fin}</strong>.</p></div>";
        }

        // Revocar acceso
        if ( $accion === 'revocar' ) {
            $uid = absint( $_POST['leyre_uid'] );
            update_user_meta( $uid, 'leyre_acceso_activo', '' );
            $u      = get_userdata( $uid );
            $notice = "<div class='notice notice-warning is-dismissible'><p>Acceso de <strong>" . esc_html( $u->display_name ) . "</strong> revocado.</p></div>";
        }
    }

    // ── Obtener usuarios ──────────────────────────────────────────────────────
    $con_inicio = get_users([
        'meta_key'     => 'leyre_fecha_inicio',
        'meta_compare' => 'EXISTS',
        'orderby'      => 'display_name',
        'order'        => 'ASC',
    ]);

    $activas   = [];
    $caducadas = [];
    foreach ( $con_inicio as $u ) {
        if ( leyre_tiene_acceso( $u->ID ) ) {
            $activas[] = $u;
        } else {
            $caducadas[] = $u;
        }
    }

    $duracion_global = (int) get_option( 'leyre_duracion_programa', 90 );
    ?>
    <div class="wrap">
        <h1>Leonas en Tacones — Alumnas</h1>
        <?php echo $notice; ?>

        <style>
            .leyre-tabla-alumnas { border-collapse:collapse; width:100% }
            .leyre-tabla-alumnas th { background:#f9f5f1; padding:10px 12px; text-align:left; border-bottom:2px solid #e0d6cc; font-weight:600; white-space:nowrap }
            .leyre-tabla-alumnas td { padding:10px 12px; border-bottom:1px solid #f0ebe5; vertical-align:middle }
            .leyre-tabla-alumnas tr:hover td { background:#fdfbf9 }
            .leyre-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em }
            .leyre-badge--ok { background:#eaf5ee; color:#3a7a52 }
            .leyre-badge--warn { background:#fff8e6; color:#a07800 }
            .leyre-badge--off { background:#f5e6e6; color:#a03030 }
            .leyre-barra { background:#e8ddd4; border-radius:4px; height:8px; width:100px; display:inline-block; vertical-align:middle }
            .leyre-barra__fill { height:100%; border-radius:4px; background:#C5A882 }
            .leyre-dias-restantes { font-weight:700; font-size:13px }
            .leyre-bulk-bar { background:#fff; border:1px solid #ddd; border-radius:6px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:12px; flex-wrap:wrap }
        </style>

        <!-- ── NUEVA ALUMNA ──────────────────────────────────────────────── -->
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px;margin-bottom:28px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <h2 style="margin:0 0 16px;font-size:15px;font-weight:700;color:#18160F">➕ Nueva alumna</h2>
            <form method="post" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
                <?php wp_nonce_field( 'leyre_acceso_action', '_leyre_nonce' ); ?>
                <input type="hidden" name="leyre_accion" value="crear_alumna">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:4px">Nombre completo *</label>
                    <input type="text" name="leyre_nombre" placeholder="Ej. María García" required class="regular-text" style="width:220px">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:4px">Email *</label>
                    <input type="email" name="leyre_email" placeholder="Ej. maria@email.com" required class="regular-text" style="width:240px">
                </div>
                <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px">
                    <input type="checkbox" name="leyre_enviar_email" id="leyre_enviar_email" value="1" checked style="margin:0">
                    <label for="leyre_enviar_email" style="font-size:13px;color:#555;cursor:pointer">Enviar credenciales por email</label>
                </div>
                <div>
                    <button type="submit" class="button button-primary">Crear alumna →</button>
                </div>
            </form>
            <p style="margin:10px 0 0;font-size:12px;color:#888">Se crea la cuenta con rol <em>Alumna</em>, se activa el acceso por <?php echo $duracion_global; ?> días y (si está marcado) se envía el email con usuario y contraseña.</p>
        </div>

        <!-- ── ALUMNAS ACTIVAS ──────────────────────────────────────────── -->
        <h2 style="margin-top:24px">Activas (<?php echo count( $activas ); ?>)</h2>

        <form method="post" id="leyre-form-activas">
            <?php wp_nonce_field( 'leyre_acceso_action', '_leyre_nonce' ); ?>
            <input type="hidden" name="leyre_accion" value="ampliar_bulk">

            <div class="leyre-bulk-bar">
                <label><strong>Con las seleccionadas:</strong></label>
                <input type="number" name="leyre_dias_bulk" value="10" min="1" max="365" style="width:70px" class="small-text"> días más
                <button type="submit" class="button button-primary" onclick="return leyreBulkCheck()">Ampliar acceso →</button>
                <span style="color:#888;font-size:12px" id="leyre-bulk-count">Selecciona alumnas con el checkbox</span>
            </div>

            <table class="wp-list-table widefat fixed leyre-tabla-alumnas">
                <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" id="leyre-check-all" title="Seleccionar todas"></th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Inicio</th>
                        <th>Fin / Días restantes</th>
                        <th>Progreso</th>
                        <th>Ampliar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $activas ) ) : ?>
                    <tr><td colspan="8" style="color:#888;text-align:center;padding:24px">No hay alumnas con acceso activo.</td></tr>
                <?php else : ?>
                    <?php foreach ( $activas as $u ) :
                        $fin      = get_user_meta( $u->ID, 'leyre_fecha_fin', true );
                        $inicio   = get_user_meta( $u->ID, 'leyre_fecha_inicio', true );
                        $dias_q   = $fin ? (int) ceil( ( strtotime( $fin ) - strtotime( 'today' ) ) / 86400 ) : null;
                        $dia_prog = leyre_get_dia_programa( $u->ID );
                        $progreso = leyre_get_progreso_global( $u->ID );
                        $alerta   = $dias_q !== null && $dias_q <= 7;
                    ?>
                    <tr>
                        <td><input type="checkbox" name="leyre_uids[]" value="<?php echo $u->ID; ?>" class="leyre-check-alumna"></td>
                        <td>
                            <strong><?php echo esc_html( $u->display_name ); ?></strong>
                            <?php if ( $dia_prog !== null ) echo '<br><span style="color:#888;font-size:11px">Día ' . $dia_prog . ' de ' . $duracion_global . '</span>'; ?>
                        </td>
                        <td style="font-size:12px;color:#666"><?php echo esc_html( $u->user_email ); ?></td>
                        <td style="font-size:12px"><?php echo esc_html( $inicio ?: '—' ); ?></td>
                        <td>
                            <?php if ( $fin ) : ?>
                                <span style="font-size:12px;color:#666"><?php echo esc_html( $fin ); ?></span><br>
                                <span class="leyre-dias-restantes" style="color:<?php echo $alerta ? '#c0392b' : '#2ecc71' ?>">
                                    <?php echo $alerta ? '⚠ ' : ''; ?><?php echo $dias_q; ?> días restantes
                                </span>
                            <?php else : ?>
                                <span style="color:#aaa">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="leyre-barra"><div class="leyre-barra__fill" style="width:<?php echo $progreso['porcentaje']; ?>%"></div></div>
                            <span style="font-size:12px;margin-left:6px"><?php echo $progreso['porcentaje']; ?>%</span>
                        </td>
                        <td>
                            <?php /* Ampliar individual inline */ ?>
                            <form method="post" style="display:inline-flex;gap:4px;align-items:center">
                                <?php wp_nonce_field( 'leyre_acceso_action', '_leyre_nonce' ); ?>
                                <input type="hidden" name="leyre_accion" value="ampliar_individual">
                                <input type="hidden" name="leyre_uid" value="<?php echo $u->ID; ?>">
                                <input type="number" name="leyre_dias" value="10" min="1" max="365" style="width:55px" class="small-text">
                                <button type="submit" class="button button-small">+días</button>
                            </form>
                        </td>
                        <td style="white-space:nowrap">
                            <a href="<?php echo get_edit_user_link( $u->ID ); ?>" class="button button-small">Editar</a>
                            &nbsp;
                            <form method="post" style="display:inline">
                                <?php wp_nonce_field( 'leyre_acceso_action', '_leyre_nonce' ); ?>
                                <input type="hidden" name="leyre_accion" value="revocar">
                                <input type="hidden" name="leyre_uid" value="<?php echo $u->ID; ?>">
                                <button type="submit" class="button button-small" style="color:#a00"
                                        onclick="return confirm('¿Revocar acceso a <?php echo esc_js( $u->display_name ); ?>?')">Revocar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </form>

        <!-- ── CADUCADAS / SIN ACCESO ───────────────────────────────────── -->
        <?php if ( ! empty( $caducadas ) ) : ?>
        <h2 style="margin-top:40px">Caducadas / Sin acceso (<?php echo count( $caducadas ); ?>)</h2>
        <table class="wp-list-table widefat fixed leyre-tabla-alumnas">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Progreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $caducadas as $u ) :
                $fin      = get_user_meta( $u->ID, 'leyre_fecha_fin', true );
                $inicio   = get_user_meta( $u->ID, 'leyre_fecha_inicio', true );
                $progreso = leyre_get_progreso_global( $u->ID );
                $dias_q   = $fin ? (int) floor( ( strtotime( 'today' ) - strtotime( $fin ) ) / 86400 ) : null;
            ?>
            <tr>
                <td><strong><?php echo esc_html( $u->display_name ); ?></strong></td>
                <td style="font-size:12px;color:#666"><?php echo esc_html( $u->user_email ); ?></td>
                <td style="font-size:12px"><?php echo esc_html( $inicio ?: '—' ); ?></td>
                <td>
                    <span style="font-size:12px;color:#c0392b"><?php echo esc_html( $fin ?: '—' ); ?></span>
                    <?php if ( $dias_q !== null ) echo '<br><span style="font-size:11px;color:#888">Hace ' . $dias_q . ' días</span>'; ?>
                </td>
                <td>
                    <div class="leyre-barra"><div class="leyre-barra__fill" style="width:<?php echo $progreso['porcentaje']; ?>%"></div></div>
                    <span style="font-size:12px;margin-left:6px"><?php echo $progreso['porcentaje']; ?>%</span>
                </td>
                <td style="white-space:nowrap">
                    <form method="post" style="display:inline">
                        <?php wp_nonce_field( 'leyre_acceso_action', '_leyre_nonce' ); ?>
                        <input type="hidden" name="leyre_accion" value="reactivar">
                        <input type="hidden" name="leyre_uid" value="<?php echo $u->ID; ?>">
                        <button type="submit" class="button button-primary button-small">Reactivar (<?php echo $duracion_global; ?> días)</button>
                    </form>
                    &nbsp;
                    <a href="<?php echo get_edit_user_link( $u->ID ); ?>" class="button button-small">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <script>
    document.getElementById('leyre-check-all').addEventListener('change', function() {
        document.querySelectorAll('.leyre-check-alumna').forEach(function(c) { c.checked = this.checked; }.bind(this));
        actualizarContador();
    });
    document.querySelectorAll('.leyre-check-alumna').forEach(function(c) {
        c.addEventListener('change', actualizarContador);
    });
    function actualizarContador() {
        var n = document.querySelectorAll('.leyre-check-alumna:checked').length;
        document.getElementById('leyre-bulk-count').textContent = n ? n + ' alumna(s) seleccionada(s)' : 'Selecciona alumnas con el checkbox';
    }
    function leyreBulkCheck() {
        var n = document.querySelectorAll('.leyre-check-alumna:checked').length;
        if (!n) { alert('Selecciona al menos una alumna.'); return false; }
        return confirm('¿Ampliar acceso a ' + n + ' alumna(s)?');
    }
    </script>
    <?php
}

// ─── Página: Configuración ────────────────────────────────────────────────────

add_action( 'admin_init', 'leyre_registrar_settings' );

function leyre_registrar_settings() {
    register_setting( 'leyre_options', 'leyre_duracion_programa',  [ 'type' => 'integer', 'default' => 90 ] );
    register_setting( 'leyre_options', 'leyre_producto_id',        [ 'type' => 'integer', 'default' => 0 ] );
    register_setting( 'leyre_options', 'leyre_calendly_api_key',     [ 'type' => 'string',  'default' => '' ] );
    register_setting( 'leyre_options', 'leyre_whatsapp_url',         [ 'type' => 'string',  'default' => '' ] );
    register_setting( 'leyre_options', 'leyre_comunidad_imagen_id',  [ 'type' => 'integer', 'default' => 0 ] );
}

function leyre_pagina_configuracion() {
    if ( isset( $_POST['leyre_guardar'] ) && check_admin_referer( 'leyre_save_config' ) ) {
        update_option( 'leyre_duracion_programa',    absint( $_POST['leyre_duracion_programa']    ?? 90 ) );
        update_option( 'leyre_producto_id',          absint( $_POST['leyre_producto_id']          ?? 0 ) );
        update_option( 'leyre_calendly_api_key',     sanitize_text_field( $_POST['leyre_calendly_api_key'] ?? '' ) );
        update_option( 'leyre_whatsapp_url',         esc_url_raw( $_POST['leyre_whatsapp_url']    ?? '' ) );
        update_option( 'leyre_comunidad_imagen_id',  absint( $_POST['leyre_comunidad_imagen_id']  ?? 0 ) );
        echo '<div class="notice notice-success is-dismissible"><p>Configuración guardada.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Leyre Torres — Configuración</h1>
        <form method="post">
            <?php wp_nonce_field( 'leyre_save_config' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="leyre_duracion_programa">Duración del programa (días)</label></th>
                    <td>
                        <input type="number" name="leyre_duracion_programa" id="leyre_duracion_programa"
                               value="<?php echo esc_attr( get_option( 'leyre_duracion_programa', 90 ) ); ?>"
                               min="1" class="small-text">
                        <p class="description">Por defecto: 90. Solo afecta a nuevas compras.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="leyre_producto_id">ID del producto WooCommerce del programa</label></th>
                    <td>
                        <input type="number" name="leyre_producto_id" id="leyre_producto_id"
                               value="<?php echo esc_attr( get_option( 'leyre_producto_id', 0 ) ); ?>"
                               class="regular-text">
                        <p class="description">El ID del producto que activa el acceso al área privada.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="leyre_calendly_api_key">API Key de Calendly</label></th>
                    <td>
                        <input type="password" name="leyre_calendly_api_key" id="leyre_calendly_api_key"
                               value="<?php echo esc_attr( get_option( 'leyre_calendly_api_key', '' ) ); ?>"
                               class="regular-text" autocomplete="off">
                        <p class="description">Personal Access Token de Calendly (v2).</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="leyre_whatsapp_url">Link de WhatsApp (comunidad)</label></th>
                    <td>
                        <input type="url" name="leyre_whatsapp_url" id="leyre_whatsapp_url"
                               value="<?php echo esc_attr( get_option( 'leyre_whatsapp_url', '' ) ); ?>"
                               class="regular-text" placeholder="https://chat.whatsapp.com/...">
                    </td>
                </tr>
                <tr>
                    <th><label for="leyre_comunidad_imagen_id">Imagen de fondo — sección comunidad</label></th>
                    <td>
                        <?php
                        $img_id  = (int) get_option( 'leyre_comunidad_imagen_id', 0 );
                        $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
                        ?>
                        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                            <?php if ( $img_url ) : ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" style="height:80px;border-radius:4px;object-fit:cover" id="leyre-comunidad-preview">
                            <?php else : ?>
                            <img src="" style="height:80px;display:none;border-radius:4px;object-fit:cover" id="leyre-comunidad-preview">
                            <?php endif; ?>
                            <div>
                                <input type="hidden" name="leyre_comunidad_imagen_id" id="leyre_comunidad_imagen_id" value="<?php echo $img_id; ?>">
                                <button type="button" class="button" id="leyre-seleccionar-imagen">Seleccionar imagen</button>
                                <?php if ( $img_id ) : ?>
                                <button type="button" class="button" id="leyre-quitar-imagen" style="margin-left:8px">Quitar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="description">Se muestra como fondo en el banner de comunidad del área privada.</p>
                        <script>
                        jQuery(function($) {
                            var frame;
                            $('#leyre-seleccionar-imagen').on('click', function(e) {
                                e.preventDefault();
                                if (frame) { frame.open(); return; }
                                frame = wp.media({ title: 'Imagen comunidad', button: { text: 'Usar esta imagen' }, multiple: false });
                                frame.on('select', function() {
                                    var att = frame.state().get('selection').first().toJSON();
                                    $('#leyre_comunidad_imagen_id').val(att.id);
                                    $('#leyre-comunidad-preview').attr('src', att.url).show();
                                });
                                frame.open();
                            });
                            $('#leyre-quitar-imagen').on('click', function() {
                                $('#leyre_comunidad_imagen_id').val('');
                                $('#leyre-comunidad-preview').hide();
                            });
                        });
                        </script>
                    </td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="leyre_guardar" class="button-primary" value="Guardar cambios"></p>
        </form>
    </div>
    <?php
}
