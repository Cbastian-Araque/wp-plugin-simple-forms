<?php
function simple_forms_listing_cb()
{
  $get_forms = new SimpleForms_FormsRepository;
  $results = $get_forms->get_all_forms();

  if (empty($results)) {
    printf("<h3>%s</h3>",__('No hay formularios para mostrar.', 'simple-forms'));
    return;
  }

  // Convertir cada form_fields (que es JSON en la DB)
  foreach ($results as $key => $form) {
    $results[$key]['form_fields'] = json_decode($form['form_fields'], true);
  }

  $forms = $results;
?>

  <section class="simple-forms-list-container wrap">
    <?php printf('<h1>%s</h1>', __('Listado de Formularios', 'simple-forms')) ?>

    <a href="<?php echo admin_url('admin.php?page=simple-forms') ?>" class="button button-primary"><?php echo __('Crear Nuevo Formulario','simple-forms') ?></a>
    <table class="wp-list-table widefat fixed striped" id="table_simple_form_list">
      <thead>
        <tr>
          <th class="" colspan=""><?php echo __('ID del formulario','simple-forms') ?></th>
          <th class="" colspan=""><?php echo __('Título','simple-forms') ?></th>
          <th class="" colspan=""><?php echo __('Shortcode','simple-forms') ?></th>
          <th class="" colspan=""><?php echo __('Campos','simple-forms') ?></th>
          <th class="" colspan=""><?php echo __('Acciones','simple-forms') ?></th>
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
                <a href="admin.php?page=simple-forms&form_id=<?php echo $form['id'] . '&form_title=' . $form['form_name'] ?>"><?php echo __('Editar','simple-forms') ?></a>
                <a href="#"><?php echo __('Desactivar','simple-forms') ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </section>
<?php
}
