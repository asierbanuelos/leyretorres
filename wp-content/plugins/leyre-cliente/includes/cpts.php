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

    // ── CPT: Tipo de sesión 1:1 ───────────────────────────────────────────────
    register_post_type( 'leyre_sesion_tipo', [
        'labels'       => [
            'name'               => 'Tipos de sesión',
            'singular_name'      => 'Tipo de sesión',
            'add_new_item'       => 'Añadir tipo de sesión',
            'edit_item'          => 'Editar tipo de sesión',
            'menu_name'          => 'Sesiones 1:1',
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
    add_meta_box( 'leyre_sesion_tipo_meta','Detalles del tipo de sesión','leyre_mb_sesion_tipo','leyre_sesion_tipo','normal', 'high' );
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
    if ( ! $post || $post->post_type !== 'leyre_recurso' ) return;
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

// ── Metabox: Tipo de sesión 1:1 ──────────────────────────────────────────────

function leyre_mb_sesion_tipo( $post ) {
    wp_nonce_field( 'leyre_sesion_tipo_save', 'leyre_sesion_tipo_nonce' );
    $numero          = get_post_meta( $post->ID, '_leyre_numero_sesion',    true );
    $calendly_link   = get_post_meta( $post->ID, '_leyre_calendly_link',    true );
    ?>
    <p>
        <label><strong>Número de sesión</strong> (1–6)</label><br>
        <input type="number" name="leyre_numero_sesion" value="<?php echo esc_attr( $numero ); ?>" min="1" max="6" style="width:80px">
    </p>
    <p>
        <label><strong>Link de Calendly para agendar</strong></label><br>
        <input type="url" name="leyre_calendly_link" value="<?php echo esc_attr( $calendly_link ); ?>" placeholder="https://calendly.com/leyre/sesion-1" style="width:100%">
    </p>
    <?php
}

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

add_action( 'save_post', 'leyre_guardar_meta_sesion_tipo' );
function leyre_guardar_meta_sesion_tipo( $post_id ) {
    if ( ! isset( $_POST['leyre_sesion_tipo_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['leyre_sesion_tipo_nonce'], 'leyre_sesion_tipo_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( get_post_type( $post_id ) !== 'leyre_sesion_tipo' ) return;

    update_post_meta( $post_id, '_leyre_numero_sesion',  absint( $_POST['leyre_numero_sesion']          ?? 0 ) );
    update_post_meta( $post_id, '_leyre_calendly_link',  esc_url_raw( $_POST['leyre_calendly_link']    ?? '' ) );
}
