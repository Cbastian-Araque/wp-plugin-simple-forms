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

  public function export_csv()
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

    // Headers para forzar descarga
    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename={$filename}");
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen("php://output", "w");
    // BOM UTF-8 para Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // 1. Obtener todos los entry_id (registros) del formulario
    $entries = $wpdb->get_results(
      $wpdb->prepare("SELECT id, submitted_at FROM {$table_entries} WHERE form_id = %d ORDER BY id ASC", $form_id),
      ARRAY_A
    );

    if (empty($entries)) {
      fputcsv($output, ['No hay registros']);
      fclose($output);
      exit;
    }

    // 2. Obtener lista de labels (columnas) en orden de aparición
    // usamos JOIN para tomar el primer entry donde aparece cada label y ordenar por esa aparición
    $labels_rows = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT COALESCE(m.field_label, m.field_name) AS label, MIN(m.entry_id) AS first_entry
               FROM {$table_meta} m
               INNER JOIN {$table_entries} e ON e.id = m.entry_id
               WHERE e.form_id = %d
               GROUP BY COALESCE(m.field_label, m.field_name)
               ORDER BY first_entry ASC",
        $form_id
      ),
      ARRAY_A
    );

    $labels = [];
    foreach ($labels_rows as $r) {
      $lbl = trim($r['label']);
      if ($lbl === '') continue;
      $labels[] = $lbl;
    }
    
    // Header CSV: ID registro, Fecha, <labels...>
    $header = array_merge(['ID Registro', 'Fecha del registro'], $labels);
    fputcsv($output, $header);

    // 3. Para cada entry, construir fila usando field_label como columna
    foreach ($entries as $entry) {
      $meta = $wpdb->get_results(
        $wpdb->prepare("SELECT field_label, field_name, field_value FROM {$table_meta} WHERE entry_id = %d", $entry['id']),
        ARRAY_A
      );

      // Mapear label => value
      $row_map = [];
      foreach ($meta as $m) {
        $label = $m['field_label'] ?: $m['field_name'];
        $value = $m['field_value'];

        // Si value está serializado -> deserializar
        if (is_serialized($value)) {
          $un = maybe_unserialize($value);
          if (is_array($un)) {
            // convertir a texto legible
            $value = implode(', ', array_map('strval', $un));
          } else {
            $value = strval($un);
          }
        }

        // Limpieza mínima para CSV
        $row_map[$label] = $value;
      }

      // Construir fila en el orden del header
      $row = [];
      $row[] = $entry['id'];
      $row[] = $entry['submitted_at'] ?? '';

      foreach ($labels as $lbl) {
        $row[] = isset($row_map[$lbl]) ? $row_map[$lbl] : '';
      }

      fputcsv($output, $row);
    }

    fclose($output);
    exit;
  }
}
