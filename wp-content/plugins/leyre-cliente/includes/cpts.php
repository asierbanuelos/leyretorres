<?php
defined( 'ABSPATH' ) || exit;

// ─── B-02: Custom Post Types ──────────────────────────────────────────────────

add_action( 'init', 'leyre_registrar_cpts' );

function leyre_registrar_cpts() {

    // ── CPT: Módulo ──────────────────────────────────────────────────────────
    register_post_type( 'leyre_modulo', [
        'labels'       => [
            'name'               => 'Módulos',
            'singular_name'      => 'Módulo',
            'add_new_item'       => 'Añadir módulo',
            'edit_item'          => 'Editar módulo',
            'menu_name'          => 'Módulos',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'leyre-admin',
        'show_in_rest' => false,
        'supports'     => [ 'title', 'thumbnail', 'page-attributes' ],
        'menu_icon'    => 'dashicons-welcome-learn-more',
        'rewrite'      => false,
    ]);

    // ── CPT: Lección ─────────────────────────────────────────────────────────
    register_post_type( 'leyre_leccion', [
        'labels'       => [
            'name'               => 'Lecciones',
            'singular_name'      => 'Lección',
            'add_new_item'       => 'Añadir lección',
            'edit_item'          => 'Editar lección',
            'menu_name'          => 'Lecciones',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'leyre-admin',
        'show_in_rest' => false,
        'supports'     => [ 'title', 'page-attributes' ],
        'rewrite'      => false,
    ]);

    // ── CPT: Recurso ─────────────────────────────────────────────────────────
    register_post_type( 'leyre_recurso', [
        'labels'       => [
            'name'               => 'Recursos',
            'singular_name'      => 'Recurso',
            'add_new_item'       => 'Añadir recurso',
            'edit_item'          => 'Editar recurso',
            'menu_name'          => 'Recursos',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'leyre-admin',
        'show_in_rest' => false,
        'supports'     => [ 'title' ],
        'rewrite'      => false,
    ]);

    // ── CPT: Audio ───────────────────────────────────────────────────────────
    register_post_type( 'leyre_audio', [
        'labels'       => [
            'name'          => 'Audios',
            'singular_name' => 'Audio',
            'add_new_item'  => 'Añadir audio',
            'edit_item'     => 'Editar audio',
            'menu_name'     => 'Audios',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'leyre-admin',
        'show_in_rest' => false,
        'supports'     => [ 'title', 'page-attributes' ],
        'menu_icon'    => 'dashicons-format-audio',
        'rewrite'      => false,
    ]);

    // ── CPT: Sesión ───────────────────────────────────────────────────────────
    register_post_type( 'leyre_sesion_tipo', [
        'labels'       => [
            'name'               => 'Sesiones',
            'singular_name'      => 'Sesión',
            'add_new_item'       => 'Añadir sesión',
            'edit_item'          => 'Editar sesión',
            'menu_name'          => 'Sesiones',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => 'leyre-admin',
        'show_in_rest' => false,
        'supports'     => [ 'title', 'page-attributes' ],
        'rewrite'      => false,
    ]);
}

// ─── Metaboxes ────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', 'leyre_add_meta_boxes' );

function leyre_add_meta_boxes() {
    add_meta_box( 'leyre_modulo_meta',     'Detalles del módulo',       'leyre_mb_modulo',     'leyre_modulo',     'normal', 'high' );
    add_meta_box( 'leyre_leccion_meta',    'Detalles de la lección',    'leyre_mb_leccion',    'leyre_leccion',    'normal', 'high' );
    add_meta_box( 'leyre_recurso_meta',    'Detalles del recurso',      'leyre_mb_recurso',    'leyre_recurso',    'normal', 'high' );
    add_meta_box( 'leyre_audio_meta',      'Archivo de audio',          'leyre_mb_audio',      'leyre_audio',      'normal', 'high' );
    add_meta_box( 'leyre_sesion_tipo_meta','Detalles de la sesión','leyre_mb_sesion_tipo','leyre_sesion_tipo','normal', 'high' );
}

// ── Metabox: Módulo ──────────────────────────────────────────────────────────

function leyre_mb_modulo( $post ) {
    wp_nonce_field( 'leyre_modulo_save', 'leyre_modulo_nonce' );
    $descripcion = get_post_meta( $post->ID, '_leyre_descripcion',         true );
    $activo      = get_post_meta( $post->ID, '_leyre_activo',              true );
    $drip_dias   = get_post_meta( $post->ID, '_leyre_drip_dias',           true );
    ?>
    <p>
        <label><strong>Descripción</strong></label><br>
        <textarea name="leyre_descripcion" rows="3" style="width:100%"><?php echo esc_textarea( $descripcion ); ?></textarea>
    </p>
    <p>
        <label>
            <input type="checkbox" name="leyre_activo" value="1" <?php checked( $activo, '1' ); ?>>
            <strong>Módulo activo</strong> (visible para alumnas)
        </label>
    </p>
    <p>
        <label><strong>Drip: días desde inicio del programa para desbloquear</strong></label><br>
        <input type="number" name="leyre_drip_dias" value="<?php echo esc_attr( $drip_dias ); ?>" min="0" style="width:80px"> días
        <span style="color:#888">(0 = disponible desde el primer día)</span>
    </p>
    <?php
}

// ── Metabox: Lección ─────────────────────────────────────────────────────────

function leyre_mb_leccion( $post ) {
    wp_nonce_field( 'leyre_leccion_save', 'leyre_leccion_nonce' );
    $modulo_id  = get_post_meta( $post->ID, '_leyre_modulo_id',    true );
    $vimeo_id   = get_post_meta( $post->ID, '_leyre_vimeo_id',     true );
    $duracion   = get_post_meta( $post->ID, '_leyre_duracion',     true );
    $contenido  = get_post_meta( $post->ID, '_leyre_contenido',    true );

    $modulos = get_posts([
        'post_type'      => 'leyre_modulo',
        'numberposts'    => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ]);
    ?>
    <p>
        <label><strong>Módulo padre</strong></label><br>
        <select name="leyre_modulo_id" style="width:100%">
            <option value="">— Selecciona módulo —</option>
            <?php foreach ( $modulos as $m ) : ?>
                <option value="<?php echo $m->ID; ?>" <?php selected( $modulo_id, $m->ID ); ?>><?php echo esc_html( $m->post_title ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label><strong>ID de vídeo Vimeo</strong></label><br>
        <input type="text" name="leyre_vimeo_id" value="<?php echo esc_attr( $vimeo_id ); ?>" placeholder="ej: 123456789" style="width:100%">
    </p>
    <p>
        <label><strong>Duración estimada</strong></label><br>
        <input type="text" name="leyre_duracion" value="<?php echo esc_attr( $duracion ); ?>" placeholder="ej: 12 min" style="width:150px">
    </p>
    <p>
        <label><strong>Contenido adicional / texto de la lección</strong></label><br>
        <textarea name="leyre_contenido" rows="5" style="width:100%"><?php echo esc_textarea( $contenido ); ?></textarea>
    </p>
    <?php
}

// ── Metabox: Recurso ─────────────────────────────────────────────────────────

add_action( 'admin_enqueue_scripts', function( $hook ) {
    global $post;
    if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
    if ( ! $post || ! in_array( $post->post_type, [ 'leyre_recurso', 'leyre_audio' ] ) ) return;
    wp_enqueue_media();
} );

function leyre_mb_recurso( $post ) {
    wp_nonce_field( 'leyre_recurso_save', 'leyre_recurso_nonce' );
    $modulo_id    = get_post_meta( $post->ID, '_leyre_modulo_id',    true );
    $tipo         = get_post_meta( $post->ID, '_leyre_tipo',         true );
    $archivo_id   = (int) get_post_meta( $post->ID, '_leyre_archivo_id', true );

    // Datos del archivo ya seleccionado
    $archivo_nombre = '';
    $archivo_url    = '';
    if ( $archivo_id ) {
        $archivo_nombre = basename( get_attached_file( $archivo_id ) );
        $archivo_url    = wp_get_attachment_url( $archivo_id );
    }

    $modulos = get_posts([
        'post_type'   => 'leyre_modulo',
        'numberposts' => -1,
        'orderby'     => 'menu_order',
        'order'       => 'ASC',
        'post_status' => 'publish',
    ]);
    ?>
    <p>
        <label><strong>Módulo asociado</strong></label><br>
        <select name="leyre_modulo_id" style="width:100%">
            <option value="">— General —</option>
            <?php foreach ( $modulos as $m ) : ?>
                <option value="<?php echo $m->ID; ?>" <?php selected( $modulo_id, $m->ID ); ?>><?php echo esc_html( $m->post_title ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label><strong>Tipo</strong></label><br>
        <select name="leyre_tipo">
            <option value="pdf"      <?php selected( $tipo, 'pdf' );      ?>>PDF</option>
            <option value="plantilla"<?php selected( $tipo, 'plantilla'); ?>>Plantilla</option>
            <option value="otro"     <?php selected( $tipo, 'otro');      ?>>Otro</option>
        </select>
    </p>
    <p>
        <label><strong>Archivo</strong></label><br>

        <input type="hidden" name="leyre_archivo_id" id="leyre_archivo_id" value="<?php echo esc_attr( $archivo_id ); ?>">

        <div id="leyre-archivo-preview" style="margin:8px 0;padding:10px 12px;background:#f6f7f7;border:1px solid #ddd;border-radius:4px;display:<?php echo $archivo_id ? 'flex' : 'none'; ?>;align-items:center;gap:10px">
            <span id="leyre-archivo-nombre" style="flex:1;font-weight:600"><?php echo esc_html( $archivo_nombre ); ?></span>
            <?php if ( $archivo_url ) : ?>
            <a href="<?php echo esc_url( $archivo_url ); ?>" target="_blank" style="font-size:12px;color:#2271b1">Ver archivo</a>
            <?php endif; ?>
            <button type="button" id="leyre-quitar-archivo" style="background:none;border:none;color:#a00;cursor:pointer;font-size:12px;font-weight:600;padding:0">✕ Quitar</button>
        </div>

        <button type="button" id="leyre-subir-archivo" class="button button-secondary" style="margin-top:4px">
            <?php echo $archivo_id ? '🔄 Cambiar archivo' : '📎 Seleccionar o subir archivo'; ?>
        </button>
        <span style="color:#888;font-size:12px;margin-left:8px">PDF, Word, Excel, imagen…</span>
    </p>

    <script>
    jQuery(function($) {
        var frame;

        $('#leyre-subir-archivo').on('click', function(e) {
            e.preventDefault();

            if ( frame ) { frame.open(); return; }

            frame = wp.media({
                title:    'Seleccionar o subir archivo',
                button:   { text: 'Usar este archivo' },
                multiple: false,
            });

            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                $('#leyre_archivo_id').val(a.id);
                $('#leyre-archivo-nombre').text(a.filename);
                $('#leyre-archivo-preview').show().css('display','flex');
                // Actualizar enlace "Ver archivo"
                var verLink = $('#leyre-archivo-preview a');
                if (verLink.length) {
                    verLink.attr('href', a.url);
                } else {
                    $('#leyre-archivo-nombre').after('<a href="' + a.url + '" target="_blank" style="font-size:12px;color:#2271b1">Ver archivo</a>');
                }
                $('#leyre-subir-archivo').text('🔄 Cambiar archivo');
            });

            frame.open();
        });

        $('#leyre-quitar-archivo').on('click', function(e) {
            e.preventDefault();
            $('#leyre_archivo_id').val('');
            $('#leyre-archivo-preview').hide();
            $('#leyre-subir-archivo').text('📎 Seleccionar o subir archivo');
            frame = null;
        });
    });
    </script>
    <?php
}

// ── Metabox: Audio ───────────────────────────────────────────────────────────

function leyre_mb_audio( $post ) {
    wp_nonce_field( 'leyre_audio_save', 'leyre_audio_nonce' );
    $audio_id    = (int) get_post_meta( $post->ID, '_leyre_audio_file_id', true );
    $audio_url   = $audio_id ? wp_get_attachment_url( $audio_id ) : '';
    $audio_nombre = $audio_id ? basename( get_attached_file( $audio_id ) ) : '';
    $descripcion = get_post_meta( $post->ID, '_leyre_audio_descripcion', true );
    $duracion    = get_post_meta( $post->ID, '_leyre_audio_duracion', true );
    $categoria   = get_post_meta( $post->ID, '_leyre_audio_categoria', true );
    ?>
    <p>
        <label><strong>Archivo de audio</strong></label><br>

        <input type="hidden" name="leyre_audio_file_id" id="leyre_audio_file_id" value="<?php echo esc_attr( $audio_id ); ?>">

        <div id="leyre-audio-preview" style="margin:8px 0;padding:10px 12px;background:#f6f7f7;border:1px solid #ddd;border-radius:4px;display:<?php echo $audio_id ? 'block' : 'none'; ?>">
            <p style="margin:0 0 8px;font-weight:600" id="leyre-audio-nombre"><?php echo esc_html( $audio_nombre ); ?></p>
            <?php if ( $audio_url ) : ?>
            <audio controls src="<?php echo esc_url( $audio_url ); ?>" style="width:100%;height:36px" id="leyre-audio-player"></audio>
            <?php else : ?>
            <audio controls style="width:100%;height:36px;display:none" id="leyre-audio-player"></audio>
            <?php endif; ?>
            <button type="button" id="leyre-quitar-audio" style="margin-top:6px;background:none;border:none;color:#a00;cursor:pointer;font-size:12px;font-weight:600;padding:0">✕ Quitar</button>
        </div>

        <button type="button" id="leyre-subir-audio" class="button button-secondary" style="margin-top:4px">
            <?php echo $audio_id ? '🔄 Cambiar audio' : '🎵 Seleccionar o subir audio'; ?>
        </button>
        <span style="color:#888;font-size:12px;margin-left:8px">MP3, WAV, M4A…</span>
    </p>
    <p>
        <label><strong>Descripción breve</strong></label><br>
        <input type="text" name="leyre_audio_descripcion" value="<?php echo esc_attr( $descripcion ); ?>" placeholder="Ej: Ejercicio de respiración para antes de una reunión" style="width:100%">
    </p>
    <p>
        <label><strong>Duración</strong></label><br>
        <input type="text" name="leyre_audio_duracion" value="<?php echo esc_attr( $duracion ); ?>" placeholder="ej: 8 min" style="width:120px">
    </p>
    <p>
        <label><strong>Categoría</strong></label><br>
        <input type="text" name="leyre_audio_categoria" value="<?php echo esc_attr( $categoria ); ?>" placeholder="ej: Meditaciones, Ejercicios…" style="width:100%">
        <span style="color:#888;font-size:12px">Opcional. Se usa para agrupar los audios en la plataforma.</span>
    </p>

    <script>
    jQuery(function($) {
        var frame;

        $('#leyre-subir-audio').on('click', function(e) {
            e.preventDefault();
            if ( frame ) { frame.open(); return; }

            frame = wp.media({
                title:    'Seleccionar o subir audio',
                button:   { text: 'Usar este audio' },
                library:  { type: 'audio' },
                multiple: false,
            });

            frame.on('select', function() {
                var a = frame.state().get('selection').first().toJSON();
                $('#leyre_audio_file_id').val(a.id);
                $('#leyre-audio-nombre').text(a.filename);
                $('#leyre-audio-player').attr('src', a.url).show();
                $('#leyre-audio-preview').show();
                $('#leyre-subir-audio').text('🔄 Cambiar audio');
            });

            frame.open();
        });

        $('#leyre-quitar-audio').on('click', function(e) {
            e.preventDefault();
            $('#leyre_audio_file_id').val('');
            $('#leyre-audio-preview').hide();
            $('#leyre-audio-player').removeAttr('src');
            $('#leyre-subir-audio').text('🎵 Seleccionar o subir audio');
            frame = null;
        });
    });
    </script>
    <?php
}

// ── Metabox: Sesión ───────────────────────────────────────────────────────────

function leyre_mb_sesion_tipo( $post ) {
    wp_nonce_field( 'leyre_sesion_tipo_save', 'leyre_sesion_tipo_nonce' );
    $numero       = get_post_meta( $post->ID, '_leyre_numero_sesion',   true );
    $tipo_sesion  = get_post_meta( $post->ID, '_leyre_tipo_sesion',     true ) ?: '1a1';
    $estado       = get_post_meta( $post->ID, '_leyre_estado',          true ) ?: 'pendiente';
    $fecha        = get_post_meta( $post->ID, '_leyre_fecha_sesion',    true );
    $enlace       = get_post_meta( $post->ID, '_leyre_enlace_reunion',  true );
    $usuario_id   = (int) get_post_meta( $post->ID, '_leyre_usuario_id', true );

    $usuarios = get_users([
        'orderby' => 'display_name',
        'order'   => 'ASC',
        'number'  => 500,
    ]);
    ?>
    <table class="form-table" role="presentation">
        <tr>
            <th><label><strong>Número</strong></label></th>
            <td>
                <input type="number" name="leyre_numero_sesion" value="<?php echo esc_attr( $numero ); ?>" min="1" max="20" style="width:70px">
                <span class="description">Para ordenar la lista (1, 2, 3…)</span>
            </td>
        </tr>
        <tr>
            <th><label><strong>Tipo</strong></label></th>
            <td>
                <select name="leyre_tipo_sesion" id="leyre_tipo_sesion">
                    <option value="1a1"    <?php selected( $tipo_sesion, '1a1' );    ?>>Individual 1:1</option>
                    <option value="grupal" <?php selected( $tipo_sesion, 'grupal' ); ?>>Grupal / Mentoría</option>
                </select>
            </td>
        </tr>
        <tr id="leyre-fila-alumna">
            <th><label for="leyre_usuario_id"><strong>Alumna asignada</strong></label></th>
            <td>
                <input type="text" id="leyre-buscar-alumna" placeholder="Buscar por nombre o email…"
                       autocomplete="off"
                       style="width:100%;padding:6px 10px;border:1px solid #ddd;border-radius:3px 3px 0 0;border-bottom:none;font-size:13px;box-sizing:border-box">
                <select name="leyre_usuario_id" id="leyre_usuario_id" size="6"
                        style="width:100%;border:1px solid #ddd;border-radius:0 0 3px 3px;font-size:13px;padding:2px">
                    <option value="">— Sin asignar —</option>
                    <?php foreach ( $usuarios as $u ) : ?>
                        <option value="<?php echo $u->ID; ?>" <?php selected( $usuario_id, $u->ID ); ?>>
                            <?php echo esc_html( $u->display_name . '  ·  ' . $u->user_email ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top:6px">Selecciona la alumna para esta sesión 1:1. Para grupales, deja sin asignar.</p>
            </td>
        </tr>
        <tr>
            <th><label><strong>Estado</strong></label></th>
            <td>
                <select name="leyre_estado">
                    <option value="pendiente"  <?php selected( $estado, 'pendiente' );  ?>>Pendiente (por agendar)</option>
                    <option value="programada" <?php selected( $estado, 'programada' ); ?>>Programada</option>
                    <option value="completada" <?php selected( $estado, 'completada' ); ?>>Completada</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><strong>Fecha y hora</strong></label></th>
            <td>
                <input type="datetime-local" name="leyre_fecha_sesion" value="<?php echo esc_attr( $fecha ); ?>">
                <p class="description">Cuando se programe la sesión, introduce la fecha para que la alumna la vea.</p>
            </td>
        </tr>
        <tr>
            <th><label><strong>Enlace de la reunión</strong></label></th>
            <td>
                <input type="url" name="leyre_enlace_reunion" value="<?php echo esc_attr( $enlace ); ?>" placeholder="https://meet.google.com/xxx-xxxx-xxx" style="width:100%">
                <p class="description">Google Meet, Zoom, Teams… Crea la reunión y pega aquí el enlace.</p>
            </td>
        </tr>
    </table>

    <script>
    jQuery(function($) {
        var $buscar  = $('#leyre-buscar-alumna');
        var $select  = $('#leyre_usuario_id');
        var $fila    = $('#leyre-fila-alumna');
        var $tipo    = $('#leyre_tipo_sesion');
        var $opciones = $select.find('option').clone();

        // Mostrar nombre del seleccionado al cargar
        var seleccionado = $select.find('option:selected');
        if (seleccionado.val()) {
            $buscar.val(seleccionado.text().trim());
        }

        // Filtrar lista al escribir
        $buscar.on('input', function() {
            var term    = $(this).val().toLowerCase().trim();
            var actual  = $select.val();
            $select.empty();
            $opciones.each(function() {
                if (!$(this).val() || !term || $(this).text().toLowerCase().includes(term)) {
                    $select.append($(this).clone());
                }
            });
            if ($select.find('option[value="' + actual + '"]').length) {
                $select.val(actual);
            }
        });

        // Mostrar/ocultar selector según tipo de sesión
        function toggleAlumna() {
            $fila.toggle($tipo.val() === '1a1');
        }
        $tipo.on('change', toggleAlumna);
        toggleAlumna();
    });
    </script>
    <?php
}

// ── Columnas en la lista de sesiones ─────────────────────────────────────────

add_filter( 'manage_leyre_sesion_tipo_posts_columns', function( $cols ) {
    $nuevo = [];
    foreach ( $cols as $k => $v ) {
        $nuevo[$k] = $v;
        if ( $k === 'title' ) {
            $nuevo['leyre_alumna'] = 'Alumna';
            $nuevo['leyre_tipo']   = 'Tipo';
            $nuevo['leyre_estado'] = 'Estado';
            $nuevo['leyre_fecha']  = 'Fecha';
        }
    }
    return $nuevo;
});

add_action( 'manage_leyre_sesion_tipo_posts_custom_column', function( $col, $post_id ) {
    switch ( $col ) {
        case 'leyre_alumna':
            $uid = (int) get_post_meta( $post_id, '_leyre_usuario_id', true );
            if ( $uid ) {
                $u = get_userdata( $uid );
                echo $u ? esc_html( $u->display_name ) : '<span style="color:#999">—</span>';
            } else {
                echo '<span style="color:#999">Grupal</span>';
            }
            break;
        case 'leyre_tipo':
            $tipo = get_post_meta( $post_id, '_leyre_tipo_sesion', true );
            echo $tipo === 'grupal' ? 'Grupal' : '1:1';
            break;
        case 'leyre_estado':
            $estado = get_post_meta( $post_id, '_leyre_estado', true ) ?: 'pendiente';
            $colores = [ 'pendiente' => '#888', 'programada' => '#2271b1', 'completada' => '#5a9e72' ];
            $color   = $colores[$estado] ?? '#888';
            echo '<span style="color:' . $color . ';font-weight:600;text-transform:capitalize">' . esc_html( $estado ) . '</span>';
            break;
        case 'leyre_fecha':
            $fecha = get_post_meta( $post_id, '_leyre_fecha_sesion', true );
            echo $fecha ? esc_html( date( 'd/m/Y H:i', strtotime( $fecha ) ) ) : '<span style="color:#999">—</span>';
            break;
    }
}, 10, 2 );

// ─── Guardado de metaboxes ────────────────────────────────────────────────────

add_action( 'save_post', 'leyre_guardar_meta_modulo' );
function leyre_guardar_meta_modulo( $post_id ) {
    if ( ! isset( $_POST['leyre_modulo_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_modulo_nonce'], 'leyre_modulo_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_modulo' ) return;

    update_post_meta( $post_id, '_leyre_descripcion', sanitize_textarea_field( $_POST['leyre_descripcion'] ?? '' ) );
    update_post_meta( $post_id, '_leyre_activo',      isset( $_POST['leyre_activo'] ) ? '1' : '' );
    update_post_meta( $post_id, '_leyre_drip_dias',   absint( $_POST['leyre_drip_dias'] ?? 0 ) );
}

add_action( 'save_post', 'leyre_guardar_meta_leccion' );
function leyre_guardar_meta_leccion( $post_id ) {
    if ( ! isset( $_POST['leyre_leccion_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_leccion_nonce'], 'leyre_leccion_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_leccion' ) return;

    update_post_meta( $post_id, '_leyre_modulo_id',  absint( $_POST['leyre_modulo_id'] ?? 0 ) );
    update_post_meta( $post_id, '_leyre_vimeo_id',   sanitize_text_field( $_POST['leyre_vimeo_id']  ?? '' ) );
    update_post_meta( $post_id, '_leyre_duracion',   sanitize_text_field( $_POST['leyre_duracion']  ?? '' ) );
    update_post_meta( $post_id, '_leyre_contenido',  sanitize_textarea_field( $_POST['leyre_contenido'] ?? '' ) );
}

add_action( 'save_post', 'leyre_guardar_meta_recurso' );
function leyre_guardar_meta_recurso( $post_id ) {
    if ( ! isset( $_POST['leyre_recurso_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_recurso_nonce'], 'leyre_recurso_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_recurso' ) return;

    update_post_meta( $post_id, '_leyre_modulo_id',  absint( $_POST['leyre_modulo_id'] ?? 0 ) );
    update_post_meta( $post_id, '_leyre_tipo',        sanitize_text_field( $_POST['leyre_tipo'] ?? 'pdf' ) );
    update_post_meta( $post_id, '_leyre_archivo_id',  absint( $_POST['leyre_archivo_id'] ?? 0 ) );
}

add_action( 'save_post', 'leyre_guardar_meta_audio' );
function leyre_guardar_meta_audio( $post_id ) {
    if ( ! isset( $_POST['leyre_audio_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_audio_nonce'], 'leyre_audio_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_audio' ) return;

    $file_id = absint( $_POST['leyre_audio_file_id'] ?? 0 );
    update_post_meta( $post_id, '_leyre_audio_file_id',    $file_id );
    update_post_meta( $post_id, '_leyre_audio_file_url',   $file_id ? wp_get_attachment_url( $file_id ) : '' );
    update_post_meta( $post_id, '_leyre_audio_descripcion', sanitize_text_field( $_POST['leyre_audio_descripcion'] ?? '' ) );
    update_post_meta( $post_id, '_leyre_audio_duracion',    sanitize_text_field( $_POST['leyre_audio_duracion']    ?? '' ) );
    update_post_meta( $post_id, '_leyre_audio_categoria',   sanitize_text_field( $_POST['leyre_audio_categoria']   ?? '' ) );
}

add_action( 'save_post', 'leyre_guardar_meta_sesion_tipo' );
function leyre_guardar_meta_sesion_tipo( $post_id ) {
    if ( ! isset( $_POST['leyre_sesion_tipo_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_sesion_tipo_nonce'], 'leyre_sesion_tipo_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_sesion_tipo' ) return;

    update_post_meta( $post_id, '_leyre_numero_sesion',  absint( $_POST['leyre_numero_sesion'] ?? 0 ) );
    update_post_meta( $post_id, '_leyre_tipo_sesion',    sanitize_text_field( $_POST['leyre_tipo_sesion'] ?? '1a1' ) );
    update_post_meta( $post_id, '_leyre_estado',         sanitize_text_field( $_POST['leyre_estado'] ?? 'pendiente' ) );
    update_post_meta( $post_id, '_leyre_fecha_sesion',   sanitize_text_field( $_POST['leyre_fecha_sesion'] ?? '' ) );
    update_post_meta( $post_id, '_leyre_enlace_reunion', esc_url_raw( $_POST['leyre_enlace_reunion'] ?? '' ) );
    update_post_meta( $post_id, '_leyre_usuario_id',     absint( $_POST['leyre_usuario_id'] ?? 0 ) );
}
