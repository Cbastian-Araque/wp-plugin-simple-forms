<?php 
/**
 * CRUD para formularios
 */
if (!defined('ABSPATH')) exit; // Seguridad

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
   * Guarda o actualiza un formulario en la base de datos
   */
  public function save_form($form)
  {
    global $wpdb;

    $data = [
      'form_name'   => sanitize_text_field($form['id']),
      'version'     => intval($form['version']),
      'form_fields' => wp_json_encode($form['fields']),
      'shortcode'   => '[simple_form id="' . sanitize_text_field($form['id']) . '"]',
      'updated_at'  => current_time('mysql')
    ];

    $exists = $wpdb->get_var(
      $wpdb->prepare("SELECT id FROM {$this->table_schemas} WHERE form_name = %s", $data['form_name'])
    );

    if ($exists) {
      $wpdb->update(
        $this->table_schemas,
        $data,
        ['id' => $exists],
        ['%s', '%d', '%s', '%s', '%s'],
        ['%d']
      );
    } else {
      $data['created_at'] = current_time('mysql');
      $wpdb->insert(
        $this->table_schemas,
        $data,
        ['%s', '%d', '%s', '%s', '%s', '%s']
      );
    }
  }

  /**
   * Obtiene todos los formularios guardados
   */
  public function get_all_forms()
  {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM {$this->table_schemas}", ARRAY_A);
  }

  /**
   * Obtiene un formulario por su nombre
   */
  public function get_form($form_name)
  {
    global $wpdb;
    $row = $wpdb->get_row(
      $wpdb->prepare("SELECT * FROM {$this->table_schemas} WHERE form_name = %s", $form_name),
      ARRAY_A
    );
    if ($row) {
      $row['form_fields'] = json_decode($row['form_fields'], true);
    }
    return $row;
  }

  /**
   * Elimina un formulario por su nombre
   */
  public function delete_form($form_name)
  {
    global $wpdb;
    return $wpdb->delete($this->table_schemas, ['form_name' => $form_name], ['%s']);
  }
}
