<?php
function simple_forms_listing_cb()
{
  global $wpdb;
  
  $json_file = PLUGIN_PATH . 'src/Database/data.json';
  $json_data = file_get_contents($json_file);

  if ($json_data === false) {
    die('<h1>Error al leer el archivo JSON.</h1>');
  }

  if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error al decodificar JSON: ' . json_last_error_msg());
  }

  $forms = json_decode($json_data, true);

  $section = '
  <section class="vs-forms-list-container wrap">
  <h1>Listado de Formularios</h1>
  <a href="' . admin_url('admin.php?page=simple-forms') . '" class="button button-primary">Crear Nuevo Formulario</a>
  <table class="wp-list-table widefat fixed striped" id="table_vs_form_list">
    <thead>
      <tr>
        <th class="" colspan="">ID del formulario</th>
        <th class="" colspan="">Título</th>
        <th class="" colspan="">Shortcode</th>
        <th class="" colspan="">Campos</th>
        <th class="" colspan="">Acciones</th>
      </tr>
    </thead>

    <tbody>';

  // Aquí cargarás los formularios almacenados
  if (file_exists($json_file)) {
    foreach ($forms as $form) {
      $section .= '<tr>
            <td>' . $form['id'] . '</td>
            <td>' . $form['settings']['titulo'] . '</td>
            <td class="shortcode_form">[vs_form id="' . $form['id'] . '" thank_you_page="/" ]</td>
            <td>' . count($form['fields']) . '</td>
            <td>
              <a href="admin.php?page=simple-forms&form_id=' . $form['id'] . '&form_title=' . $form['settings']['titulo'] . '">Editar</a>
              <a href="#">Desactivar</a>
            </td>
          </tr>';
    }
  }

  '</tbody>
  </table>';
  '</section>';

  echo $section;
}
