<?php
/**
 * Página de opciones del plugin (panel WP)
 */

add_action('admin_menu', 'simple_forms_add_admin_menu');
function simple_forms_add_admin_menu()
{
  add_menu_page(
    'Simple Forms',            // Título de la página
    'Simple Forms',            // Texto en el menú
    'manage_options',          // Capability requerida
    'simple-forms',            // Slug único
    'simple_forms_render_page', // Callback que renderiza el contenido
    'dashicons-feedback',      // Icono (dashicon o URL a un SVG)
    25                         // Posición en el menú
  );
}

// Subpágina de opciones
add_action('admin_menu', 'simple_forms_subp_reports');
function simple_forms_subp_reports() {

    // Añadir subpágina bajo el menú principal con slug 'plugin-options'
    add_submenu_page(
        'simple-forms',        // Slug de la página padre
        'Consultar registros',              // Título de la página
        'Ver registros',              // Texto del menú
        'manage_options',        // Capacidad necesaria
        'forms-reports', // Slug único para la subpágina
        'simpleforms_reports_cb_view' // Función callback que renderiza la página
    );
    add_submenu_page(
        'simple-forms',        // Slug de la página padre
        'Listado de formularios',              // Título de la página
        'Ver formularios',              // Texto del menú
        'manage_options',        // Capacidad necesaria
        'form-listing', // Slug único para la subpágina
        'simple_forms_listing_cb' // Función callback que renderiza la página
    );
}

// Callback para mostrar el contenido de la subpágina
function simpleforms_reports_cb_view() {
    ?>
    <div class="wrap">
        <h1>Settings</h1>
        <p>Esta es la subpágina de opciones de tu plugin.</p>

        <form method="post" action="options.php">
            <?php
            // Aquí puedes registrar y mostrar tus ajustes de WordPress
            settings_fields('simple_forms_settings_group');
            do_settings_sections('plugin-options-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
