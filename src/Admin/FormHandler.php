<?php
class SimpleForms_FormHandler
{

  public function __construct()
  {
    add_action('wp_ajax_simpleforms_save_form', [$this, 'handle_save_form']); // Endpoint para guardar formulario
    add_action('wp_ajax_simpleforms_delete_form', [$this, 'handle_delete_form']); // Endpoint para eliminar formulario
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
}
