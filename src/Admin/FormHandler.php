<?php
class SimpleForms_FormHandler
{

  public function __construct()
  {
    add_action('wp_ajax_simpleforms_save_form', [$this, 'handle_save_form']); // Endpoint para guardar formulario
    add_action('wp_ajax_simpleforms_delete_form', [$this, 'handle_delete_form']); // Endpoint para eliminar formulario
    add_action('admin_post_simpleforms_export_csv', [$this, 'export_csv']); // Endpoint para descargar el reporte en csv
  }

  /**
   * Guarda el formulario en la base de datos
   */
  public function handle_save_form()
  {

    if (!isset($_POST['form'])) {
      wp_send_json_error("No data received");
    }

    $form = $_POST['form'];

    // Llamar al repositorio
    $repo = new SimpleForms_FormsRepository();
    $repo->save_form($form);

    wp_send_json_success("Formulario guardado correctamente");
  }

  /**
   * Maneja la eliminación de formularios (con borrado en cascada)
   */
  public function handle_delete_form()
  {
    if (!current_user_can('manage_options')) {
      wp_send_json_error("No tienes permisos");
    }

    if (!isset($_POST['form_id'])) {
      wp_send_json_error("Falta form_id");
    }

    $form_id = intval($_POST['form_id']);

    $repo = new SimpleForms_FormsRepository();

    // Primero obtener el id del formualrio (la función delete usa el ID)
    global $wpdb;
    $table_forms = $wpdb->prefix . TABLE_PREFIX . TABLE_FORMS_SCHEMAS;

    $form_id = $wpdb->get_var(
      $wpdb->prepare("SELECT id FROM {$table_forms} WHERE id = %d", $form_id)
    );

    if (!$form_id) {
      wp_send_json_error("El formulario no existe");
    }

    $deleted = $repo->delete_form_cascade_by_id($form_id);

    if ($deleted) {
      wp_send_json_success("Formulario eliminado correctamente");
    } else {
      wp_send_json_error("No se pudo eliminar el formulario");
    }
  }

  public static function export_csv()
  {

    if (!current_user_can('manage_options')) {
      wp_die('No tienes permisos suficientes.');
    }

    if (!isset($_GET['form_id'])) {
      wp_die('Falta el form_id.');
    }

    global $wpdb;

    $form_id = intval($_GET['form_id']);

    $table_entries = $wpdb->prefix . TABLE_PREFIX . TABLE_RECORDS;
    $table_meta    = $wpdb->prefix . TABLE_PREFIX . TABLE_ENTRY_META;
    $table_files   = $wpdb->prefix . TABLE_PREFIX . TABLE_FILES;
    $table_forms   = $wpdb->prefix . TABLE_PREFIX . TABLE_FORMS_SCHEMAS;

    // Obtener información del formulario
    $form = $wpdb->get_row(
      $wpdb->prepare("SELECT * FROM {$table_forms} WHERE id = %d", $form_id),
      ARRAY_A
    );

    if (!$form) {
      wp_die('El formulario no existe.');
    }

    $filename = sanitize_title($form['form_title']) . '-reporte-' . date('Y-m-d') . '.csv';

    // CSV headers
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen("php://output", "w");

    // Agregar BOM UTF-8
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // 1. Obtener todos los registros del formulario
    $entries = $wpdb->get_results(
      $wpdb->prepare("SELECT * FROM {$table_entries} WHERE form_id = %d ORDER BY id ASC", $form_id),
      ARRAY_A
    );

    if (empty($entries)) {
      fputcsv($output, ['No hay registros']);
      exit;
    }

    // 2. Reunir todos los field_label que existan
    $all_labels = [];

    foreach ($entries as $entry) {

      $meta = $wpdb->get_results(
        $wpdb->prepare("SELECT field_label FROM {$table_meta} WHERE entry_id = %d", $entry['id']),
        ARRAY_A
      );

      foreach ($meta as $m) {
        $label = trim($m['field_label']);
        $all_labels[$label] = $label;
      }
    }

    // Ordenar labels alfabéticamente
    ksort($all_labels);

    // 3. Armar encabezado
    $header = [
      'ID Registro',
      'Fecha',
      'URL de Envío',
      'Archivos Enviados'
    ];

    $header = array_merge($header, array_values($all_labels));
    fputcsv($output, $header);

    // 4. Generar filas
    foreach ($entries as $entry) {

      // Obtener metadatos (campos del formulario)
      $meta = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT field_label, field_value FROM {$table_meta} WHERE entry_id = %d",
          $entry['id']
        ),
        ARRAY_A
      );

      // Convertir meta a key-value por label
      $meta_data = [];
      foreach ($meta as $m) {
        $value = maybe_unserialize($m['field_value']);
        if (is_array($value)) {
          $value = implode(', ', $value);
        }
        $meta_data[$m['field_label']] = $value;
      }

      // Obtener archivos del registro
      $files = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT file_url FROM {$table_files} WHERE entry_id = %d",
          $entry['id']
        ),
        ARRAY_A
      );

      $file_list = [];
      foreach ($files as $f) {
        $file_list[] = $f['file_url'];
      }

      $file_list_text = implode(' | ', $file_list);

      // Armar la fila completa
      $row = [
        $entry['id'],
        $entry['created_at'],
        $entry['url_register'],
        $file_list_text
      ];

      // Añadir campos dinámicos ordenados por label
      foreach ($all_labels as $label) {
        $row[] = isset($meta_data[$label]) ? $meta_data[$label] : '';
      }

      fputcsv($output, $row);
    }

    fclose($output);
    exit;
  }
}
