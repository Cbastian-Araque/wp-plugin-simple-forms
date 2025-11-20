<?php
if (!defined('ABSPATH')) exit;

// Registrar y cargar scripts/estilos
function simpleforms_enqueue_scripts()
{

  wp_register_style(
    'form_styles',
    PLUGIN_URL . '/assets/src/css/main.min.css',
    [],
    PLUGIN_VERSION
  );
  wp_enqueue_style('form_styles');

  wp_register_script(
    'form_scripts',
    PLUGIN_URL . '/assets/dist/build.js',
    [],
    PLUGIN_VERSION,
    true
  );
  wp_enqueue_script('form_scripts');

  wp_localize_script(
    'form_scripts',
    'SimpleFormsAjax',
    [
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('simpleforms_nonce')
    ]
  );
}

// Hook: cargar scripts del front-end
add_action('admin_enqueue_scripts', 'simpleforms_enqueue_scripts');
