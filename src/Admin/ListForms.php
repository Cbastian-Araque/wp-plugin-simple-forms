<?php
function simple_forms_listing_cb()
{
  $get_forms = new SimpleForms_FormsRepository;
  $results = $get_forms->get_all_forms();

  if (empty($results)) {
    echo "<h3>No hay formularios para mostrar.</h3>";
    return;
  }

  // Convertir cada form_fields (que es JSON en la DB)
  foreach ($results as &$form) {
    $form['form_fields'] = json_decode($form['form_fields'], true);
  }

  $forms = $results;
  
  $section = '
    <section class="simple-forms-list-container wrap">
  <h1>Listado de Formularios</h1>
  <a href="' . admin_url('admin.php?page=simple-forms') . '" class="button button-primary">Crear Nuevo Formulario</a>
  <table class="wp-list-table widefat fixed striped" id="table_simple_form_list">
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
      if ( $forms) {
        foreach ($forms as $form) {
          $section .= '<tr>
            <td>' . $form['id'] . '</td>
            <td>' . $form['form_name'] . '</td>
            <td class="shortcode_form">'. $form['shortcode'] .'</td>
            <td class="cccc">' . $form['form_fields'] . '</td>
            <td>
                <a href="admin.php?page=simple-forms&form_id=' . $form['id'] . '&form_title=' . $form['form_name'] . '">Editar</a>
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
