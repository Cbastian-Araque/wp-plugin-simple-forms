<?php
class SimpleForms_FormHandler
{

  public function __construct()
  {
    add_action('wp_ajax_simpleforms_save_form', [$this, 'handle_save_form']);
  }

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
}
