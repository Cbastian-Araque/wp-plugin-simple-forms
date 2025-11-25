<?php

if (!defined('ABSPATH')) exit;

class SimpleForms_FormSubmit
{
  public function __construct()
  {
    // Soporte logged-in + visitantes
    add_action('admin_post_simple_forms_submit', [$this, 'handle']);
    add_action('admin_post_nopriv_simple_forms_submit', [$this, 'handle']);
  }

  public function handle()
  {
    if (!isset($_POST['simple_form_submit'])) {
      wp_die("Error: Formulario inválido.");
    }

    global $wpdb;

    // Tablas
    $table_forms   = $wpdb->prefix . TABLE_PREFIX . TABLE_FORMS_SCHEMAS;
    $table_entries = $wpdb->prefix . TABLE_PREFIX . TABLE_RECORDS;
    $table_meta    = $wpdb->prefix . TABLE_PREFIX . TABLE_ENTRY_META;
    $table_files   = $wpdb->prefix . TABLE_PREFIX . TABLE_FILES;

    // Nombre del formulario enviado
    $form_name = sanitize_text_field($_POST['simple_form_id']);

    // Obtener formulario
    $form = $wpdb->get_row(
      $wpdb->prepare("SELECT * FROM {$table_forms} WHERE form_name = %s", $form_name)
    );

    if (!$form) {
      wp_die("Error: el formulario no existe en la base de datos.");
    }

    // Crear registro principal
    $wpdb->insert(
      $table_entries,
      [
        'form_id' => $form->id,
        'url_register' => esc_url_raw($_SERVER['HTTP_REFERER'])
      ]
    );

    $entry_id = $wpdb->insert_id;

    // Guardar campos enviados
    foreach ($_POST as $key => $value) {

      if (in_array($key, ['simple_form_id', 'simple_form_submit', 'action'])) {
        continue;
      }

      if (is_array($value)) {
        $value = maybe_serialize($value);
      }

      $wpdb->insert(
        $table_meta,
        [
          'entry_id'    => $entry_id,
          'field_name'  => sanitize_text_field($key),
          'field_value' => sanitize_text_field($value),
        ]
      );
    }

    // Guardar archivos
    if (!empty($_FILES)) {

      require_once ABSPATH . 'wp-admin/includes/file.php';

      foreach ($_FILES as $field => $file) {

        if ($file['error'] !== UPLOAD_ERR_OK) continue;

        $upload = wp_handle_upload($file, ['test_form' => false]);

        if (!isset($upload['url'])) continue;

        $wpdb->insert(
          $table_files,
          [
            'entry_id'   => $entry_id,
            'field_name' => sanitize_text_field($field),
            'file_url'   => esc_url_raw($upload['url']),
            'file_path'  => sanitize_text_field($upload['file']),
            'file_size'  => $file['size'],
            'file_type'  => $file['type'],
          ]
        );
      }
    }

    // Redirección con éxito
    wp_redirect(add_query_arg('enviado', '1', $_SERVER['HTTP_REFERER']));
    exit;
  }
}
