<?php

/**
 * CRUD para formularios
 */
if (!defined('ABSPATH')) exit;

class SimpleForms_FormsRepository
{
  private $prefix;
  private $table_schemas;

  public function __construct()
  {
    global $wpdb;
    $this->prefix = $wpdb->prefix . TABLE_PREFIX;
    $this->table_schemas = $this->prefix . TABLE_FORMS_SCHEMAS;
  }

  /**
   * Guarda o actualiza un formulario en la DB
   */
  public function save_form($form)
  {
    global $wpdb;

    $data = [
      'form_name'   => sanitize_text_field($form['id']),
      'form_title' => sanitize_text_field($form['settings']['titulo']),
      'version'     => intval($form['version']),
      'form_fields' => wp_json_encode($form['fields'], JSON_UNESCAPED_UNICODE),
      'shortcode'   => '[simple_form id="' . sanitize_text_field($form['id']) . '"]',
      'updated_at'  => current_time('mysql'),
    ];

    $exists = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT id FROM {$this->table_schemas} WHERE form_name = %s",
        $data['form_name']
      )
    );

    if ($exists) {
      $wpdb->update(
        $this->table_schemas,
        $data,
        ['id' => $exists],
        ['%s', '%s', '%d', '%s', '%s', '%s'],
        ['%d']
      );
    } else {
      $data['created_at'] = current_time('mysql');
      $wpdb->insert(
        $this->table_schemas,
        $data,
        ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
      );
    }
  }

  /**
   * Obtiene todos los formularios
   */
  public function get_all_forms()
  {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$this->table_schemas}", ARRAY_A);
  }

  /**
   * Obtiene un formulario por su form_name
   */
  public function get_form($form_name)
  {
    global $wpdb;

    $row = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT * FROM {$this->table_schemas} WHERE form_name = %s",
        $form_name
      ),
      ARRAY_A
    );

    if ($row) {
      $row['form_fields'] = json_decode($row['form_fields'], true);
    }

    return $row;
  }

  /**
   * Elimina un formulario + todos sus registros asociados (cascada)
   */
  public function delete_form_cascade($form_name)
  {
    global $wpdb;

    // 1. Obtener el formulario para saber su ID
    $form = $this->get_form($form_name);
    if (!$form) {
      return false; // no existe
    }

    $form_id = intval($form['id']);

    // Tablas secundarias
    $table_records      = $this->prefix . TABLE_PREFIX . TABLE_RECORDS;
    $table_records_meta = $this->prefix . TABLE_PREFIX . TABLE_ENTRY_META;
    $table_files        = $this->prefix . TABLE_PREFIX . TABLE_FILES;

    // 2. Obtener todos los registros (entries)
    $entries = $wpdb->get_col(
      $wpdb->prepare("SELECT id FROM {$table_records} WHERE form_id = %d", $form_id)
    );

    if (!empty($entries)) {
      $entries_list = implode(',', array_map('intval', $entries));

      // 3. Eliminar metadata de registros
      $wpdb->query("DELETE FROM {$table_records_meta} WHERE entry_id IN ($entries_list)");

      // 4. Eliminar archivos relacionados
      $wpdb->query("DELETE FROM {$table_files} WHERE entry_id IN ($entries_list)");

      // 5. Eliminar registros
      $wpdb->query("DELETE FROM {$table_records} WHERE id IN ($entries_list)");
    }

    // 6. Eliminar el formulario final
    return $wpdb->delete(
      $this->table_schemas,
      ['form_name' => $form_name],
      ['%s']
    );
  }
}
