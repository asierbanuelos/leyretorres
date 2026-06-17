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

// ─── Página: Alumnas activas ──────────────────────────────────────────────────

function leyre_pagina_alumnas() {
    $usuarios = get_users([
        'meta_key'   => 'leyre_acceso_activo',
        'meta_value' => '1',
        'orderby'    => 'display_name',
        'order'      => 'ASC',
    ]);
    ?>
    <div class="wrap">
        <h1>Leonas en Tacones — Alumnas activas</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Día del programa</th>
                    <th>Progreso global</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $usuarios ) ) : ?>
                <tr><td colspan="7">No hay alumnas activas.</td></tr>
            <?php else : ?>
                <?php foreach ( $usuarios as $u ) :
                    $dia      = leyre_get_dia_programa( $u->ID );
                    $duracion = (int) get_option( 'leyre_duracion_programa', 90 );
                    $progreso = leyre_get_progreso_global( $u->ID );
                    $fin      = get_user_meta( $u->ID, 'leyre_fecha_fin', true );
                    $caducada = $fin && strtotime( $fin ) < strtotime( 'today' );
                ?>
                <tr>
                    <td><strong><?php echo esc_html( $u->display_name ); ?></strong></td>
                    <td><?php echo esc_html( $u->user_email ); ?></td>
                    <td><?php echo esc_html( get_user_meta( $u->ID, 'leyre_fecha_inicio', true ) ); ?></td>
                    <td style="<?php echo $caducada ? 'color:red' : ''; ?>"><?php echo esc_html( $fin ); ?><?php echo $caducada ? ' (caducada)' : ''; ?></td>
                    <td><?php echo $dia !== null ? "Día {$dia} de {$duracion}" : '—'; ?></td>
                    <td>
                        <div style="background:#eee;border-radius:4px;height:12px;width:120px;display:inline-block;vertical-align:middle">
                            <div style="background:#C5A882;width:<?php echo $progreso['porcentaje']; ?>%;height:100%;border-radius:4px"></div>
                        </div>
                        <?php echo $progreso['porcentaje']; ?>%
                        <span style="color:#888;font-size:11px">(<?php echo $progreso['completadas']; ?>/<?php echo $progreso['total']; ?> lecciones)</span>
                    </td>
                    <td><a href="<?php echo get_edit_user_link( $u->ID ); ?>">Editar perfil</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
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
