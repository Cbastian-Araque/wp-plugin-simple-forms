<?php

/**
 * Plugin Name: Simple Forms
 * Author: Sebastian Araque
 * Author URI: https://cbastian-araque.github.io/My-Portfolio/
 * Plugin URI: https://github.com/Cbastian-Araque/wp-plugin-simple-forms
 * Description: Create your form since a UI, copy the shortcode generated and paste on your page.
 * Version: 2.0.1
 * Requires at least: 6.5
 * Requires PHP: 8
 * License: GPLv3
 * Text Domain: simple-forms
 */

function to_slug($text)
{
    // Convertir a minúsculas
    $text = strtolower($text);

    // Reemplazar caracteres con acentos
    $text = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n'],
        $text
    );

    // Reemplazar espacios por guiones bajos
    $text = preg_replace('/\s+/', '_', $text);

    // Eliminar caracteres no permitidos (solo a-z, 0-9 y _)
    $text = preg_replace('/[^a-z0-9_]/', '', $text);

    return $text;
}

if (!defined('ABSPATH')) exit; // Evita acceso directo
define('PLUGIN_FILE', __FILE__); // Obtener la información del plugin (header)

// Constantes
require_once('constants.php');

// Archivos de encolamiento
require_once('enqueue.php');

// Incluir archivos
require_once plugin_dir_path(__FILE__) . 'src/Database/Options.php';
require_once plugin_dir_path(__FILE__) . 'src/Database/Migration.php';
require_once plugin_dir_path(__FILE__) . 'src/Database/FormRepository.php';
require_once plugin_dir_path(__FILE__) . 'src/Admin/Admin.php';
require_once plugin_dir_path(__FILE__) . 'src/Frontend/FormRenderer.php';
require_once plugin_dir_path(__FILE__) . 'src/Frontend/FormSubmit.php';
require_once plugin_dir_path(__FILE__) . 'src/Frontend/Shortcodes.php';

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/includes.php';

// Base Hooks
register_activation_hook(__FILE__, 'simple_forms_on_activate');
register_deactivation_hook(__FILE__, 'simple_forms_deactivate');
register_uninstall_hook(__FILE__, 'simple_forms_on_uninstall');

new SimpleForms_FormHandler();

// Inicializar shortcodes
add_action('init', function() {
  new \SimpleForms\Frontend\Shortcodes();
});

new SimpleForms_FormSubmit();

