<?php
if (!defined('ABSPATH')) exit;

// Registrar y cargar scripts/estilos para el admin
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
    PLUGIN_URL . '/assets/dist/main.js',
    ['jquery'],
    PLUGIN_VERSION,
    true
  );
  wp_enqueue_script('form_scripts');

  wp_register_script(
    'form_ajax',
    PLUGIN_URL . '/assets/dist/ajax.js',
    ['jquery'],
    PLUGIN_VERSION,
    true
  );
  wp_enqueue_script('form_ajax');

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


/**
 * CSS para el frontend (shortcodes)
 */
function simple_forms_frontend_assets()
{
  // Solo cargar si aparece el shortcode simple_form
  global $post;

  if (isset($post->post_content) && has_shortcode($post->post_content, 'simple_form')) {
    wp_enqueue_style(
      'simple-forms-frontend',
      plugin_dir_url(__FILE__) . 'assets/src/frontend/simple-forms.css',
      [],
      PLUGIN_VERSION
    );
  }
}
add_action('wp_enqueue_scripts', 'simple_forms_frontend_assets');