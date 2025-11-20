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
  foreach ($results as $key => $form) {
    $results[$key]['form_fields'] = json_decode($form['form_fields'], true);
  }

  $forms = $results;
?>

  <section class="simple-forms-list-container wrap">
    <h1>Listado de Formularios</h1>
    <a href="<?php echo admin_url('admin.php?page=simple-forms') ?>" class="button button-primary">Crear Nuevo Formulario</a>
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

      <tbody>
        <?php if ($forms) : // Aquí cargarás los formularios almacenados ?>
          <?php foreach ($forms as $form) : ?>
            <tr>
              <td><?php echo $form['id'] ?></td>
              <td><?php echo $form['form_title'] ?></td>
              <td class="shortcode_form"><?php echo $form['shortcode'] ?></td>
              <td class="cccc"><?php echo count($form['form_fields']) ?></td>
              <td>
                <a href="admin.php?page=simple-forms&form_id=<?php echo $form['id'] . '&form_title=' . $form['form_name'] ?>">Editar</a>
                <a href="#">Desactivar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
<?php
}
