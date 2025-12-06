<?php // Callback que renderiza el contenido de la página
function simple_forms_render_page()
{
  $forms = [];
  $form_id = null;

  // Tipos de campos
  $arr_type_fields = [
    "text" => "Texto",
    "email" => "Correo",
    "number" => "Número",
    "radio" => "Radio",
    "checkbox" => "Caja de verificación",
    "textarea" => "Texto largo",
    "select" => "Selección",
    "date" => "Fecha",
    "time" => "Tiempo",
    "file" => "Archivo",
  ];
  asort($arr_type_fields);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sf_handle_form_submission($_POST);
  }

  if (isset($_GET['form_id'])) :
    global $wpdb;

    $table = $wpdb->prefix . TABLE_PREFIX . TABLE_FORMS_SCHEMAS;
    $form_id = $_GET['form_id'];

    // Obtener los formularios desde la base de datos
    $results = $wpdb->get_results("SELECT * FROM {$table} WHERE id = {$form_id}", ARRAY_A);

    if (empty($results)) {
      echo "<h3>No hay formularios para mostrar.</h3>";
      return;
    }

    // Convertir cada form_fields (que es JSON en la DB)
    foreach ($results as &$form) {
      $form['form_fields'] = json_decode($form['form_fields'], true);
    }

    $forms = $results;

  endif;
?>

  <div class="wrapper-form wrapper-create-form">
    <div class="container-head">
      <h1><?php echo isset($_GET['form_id']) ? 'Editando' : 'Crear' ?> formulario</h1>
    </div>

    <div class="preview-form">
      <?php
      $jsonInit = '';
      foreach ($forms as $form) {
        if ($form['id'] === $form_id) {
          $jsonInit .= json_encode($form);
        }
      }
      ?>
      <form method="POST" action="" id="form-generate-json" data-json-form="<?php echo htmlspecialchars($jsonInit, ENT_QUOTES, 'UTF-8') ?>">
        <div>
          <input type="text" placeholder="Nombre del formulario" class="title-form info-form" id="title-form" value="<?php echo isset($_GET['form_title']) ? sanitize_text_field($_GET['form_title']) : null ?>" <?php echo isset($_GET['form_title']) ? 'disabled' : '' ?>>
        </div>
        <div class="fields-added">
          <?php foreach ($forms as $form) {
            if ($form['id'] === $form_id) {

              $field_schema = "";
              $data_field = [];

              // Recorrer los campos del formulario
              if ($form['form_fields']) {
                foreach ($form['form_fields'] as $field_id => $field_data) {
                  $data_field = [];  // Alamcenar el data de cada campo

                  // Convierte el array a JSON
                  $field_json = json_encode($field_data);

                  // Manejo de errores en la codificación JSON (IMPORTANTE)
                  if ($field_json === false) {
                    error_log("Error al codificar JSON para el campo " . $field_id . ": " . json_last_error_msg());
                    $field_json = '{"error": "Error al procesar datos"}'; // Valor por defecto en caso de error
                  }

                  $data_field[$field_id] = json_decode($field_json);

                  $icon_up = PLUGIN_URL . 'assets/src/images/up.svg';
                  $icon_down = PLUGIN_URL . 'assets/src/images/down.svg';
                  $icon_directions = PLUGIN_URL . 'assets/src/images/directions.svg';

                  $field_schema .= '
                        <div class="field-form" draggable="true" data-field-id="' . $field_id . '" data-info-field="' . htmlspecialchars(json_encode($data_field)) . '">
                          <div class="form-field">
                            <div class="field-label">' . $field_data['label'] . '</div>
                            <span class="field-type">' . $field_data['type'] . '</span>
                          </div>
                          <button class="btn-primary btn-edit-field" type="button">Editar</button>
                          <button class="btn-caution btn-delete-field" type="button">Borrar</button>
                        </div>';
                }
              }

              echo $field_schema;

              break;
            }
          }
          ?>
        </div>

        <div class="fields-hidden">
          <input type="hidden" id="simpleforms-edit-form-id" value="<?php echo isset($_GET['form_id']) ? esc_attr($_GET['form_id']) : '' ?>">
          <!-- <div class="form-settings form-emails">
            <span>Correos de notificación</span>
            <input
              type="text"
              placeholder="correos separados por coma (,)"
              class="emails-form info-form"
              id="emails-form"
              value="">
          </div> -->

          <!-- <div class="form-settings form-btn-label">
            <span>Etiqueta del botón</span>
            <input
              type="text"
              placeholder="Enviar"
              class="label-button info-form"
              id="label-button"
              value="">
          </div> -->
        </div>

        <?php wp_nonce_field('simple_forms_save_action', 'simple_forms_nonce'); ?>

        <button type="submit" class="save-json-form btn-primary">Guardar Formulario</button>
      </form>

      <sidebar class="sidebar-settings-fields">
        <h4>Agregar campo</h4>

        <fieldset class="container-add-field">
          <div class="basic-field">
            <label>Etiqueta del campo</label>
            <?php
            $args  =  array(
              'textarea_rows'  =>  5,
              'teeny'  =>  true,
              'quicktags'  =>  false
            );
            wp_editor('', 'labelfield', $args); ?>
            <!-- <textarea id="label-field" placeholder="Etiqueta del campo" rows="3"></textarea> -->
          </div>
          <div class="basic-field">
            <label>Tipo de campo</label>
            <select id="type-field">
              <?php foreach ($arr_type_fields as $key => $value) : ?>
                <option value="<?php echo $key ?>" <?php echo $key === 'text' ? 'selected' : '' ?>><?php echo $value ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="flex-row basic-field">
            <input type="checkbox" id="required-field">
            <label for="required-field">Requerido</label>
          </div>
          <div class="basic-field">
            <label for="class-field">Clases personalizada</label>
            <input type="text" placeholder="clase-1 clase-2" id="class-field">
          </div>

          <div class="extra-fields">
            <div class="extra extra-text">
              <div class="flex-extra">
                <p>
                  <label>Validaciones del texto</label>
                <div class="flex-row basic-field">
                  <input type="checkbox" name="rules_validation" value="LETERS_ONLY">
                  <label>Solo letras</label>
                </div>
                <div class="flex-row basic-field">
                  <input type="checkbox" name="rules_validation" value="PHONE">
                  <label>Teléfono</label>
                </div>
                <div class="flex-row basic-field">
                  <input type="checkbox" name="rules_validation" value="LETERS_NUMBERS_ONLY">
                  <label>Letras y números</label>
                </div>
                <div class="flex-row basic-field">
                  <input type="checkbox" name="rules_validation" value="DIGITS">
                  <label>Dígitos</label>
                </div>
                </p>
              </div>
            </div>
            <div class="extra extra-select">
              <div class="flex-extra">
                <p>
                  <label for="default-option">Opción por defecto</label>
                  <input type="text" placeholder="Opción por defecto" id="default-option" class="default-select" value="--Selecciona una opción">
                </p>
              </div>
              <button class="btn-black btn-add-option">Agregar opción</button>
              <div class="options-select wrapper-items-fields"></div>
            </div>
            <div class="extra extra-checkbox">
              <p>
                <input type="checkbox" class="enable-tyc"> ¿Campo para términos y condiciones?
              </p>
            </div>
            <div class="extra extra-radio extra-checkbox">
              <button class="btn-black btn-add-checkradio">Agregar elemento</button>
              <div class="options-checkbox-radio options-checkbox options-radio wrapper-items-fields"></div>
            </div>
            <div class="extra extra-file">
              <div class="flex-extra">
                <p>
                  <label>Tipos de archivo</label>
                  <input type="text" value="pdf,png" placeholder="MimeTypes separados por (,)" class="file-mimetypes">
                </p>
                <p>
                  <label>Tamaño del archivo en MB</label>
                  <input type="number" value="2" placeholder="Ejemplo: 2" class="file-size">
                </p>
              </div>
              <p>
                <input type="checkbox" class="file-multiple">
                Múltiples archivos
              </p>
            </div>
            <div class="extra extra-textarea">
              <div class="flex-extra">
                <p>
                  <label for="default-textarea">Valor por defecto</label>
                  <input type="text" id="default-textarea" placeholder="Valor por defecto para la caja de texto" class="textarea-value-default">
                </p>
                <p>
                  <label>Columnas</label>
                  <input type="number" value="8" placeholder="Número de columnas" class="textarea-cols">
                </p>
                <p>
                  <label>Filas</label>
                  <input type="number" value="3" placeholder="Número de filas" class="textarea-rows">
                </p>
              </div>
            </div>
            <div class="extra extra-date">
              <div class="flex-extra">
                <label>Restricciones para el campo</label>
                <select id="validate-date">
                  <option value="">Ningúna</option>
                  <option value="FUTURE_DATES_ONLY">Solo fechas futuras</option>
                  <option value="PREVIOUS_DATES_ONLY">Solo fechas anteriores</option>
                </select>
              </div>
            </div>
          </div>

          <button class="btn-add-field btn-secondary">Crear campo</button>
          <div class="actions-edit-field">
            <button class="btn-edit btn btn-warning">Editar campo</button>
            <button class="btn-cancel  btn btn-secondary">Cancelar</button>
          </div>
        </fieldset>
      </sidebar>
    </div>
  </div>

<?php }


function sf_handle_form_submission($data)
{
  // Sanitizar los campos básicos
  $form_id     = sanitize_text_field($data['form_id']);
  $form_title  = sanitize_text_field($data['form_title']);
  $form_email  = sanitize_text_field($data['form_email']);

  // Decodificar el JSON de los campos
  $form_fields = json_decode(stripslashes($data['form_fields']), true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    wp_die('El JSON de los campos es inválido.');
  }

  // Estructurar el formulario para guardar
  $form = [
    'id'       => $form_id,
    'version'  => 1,
    'fields'   => $form_fields,
    'settings' => [
      'titulo'      => $form_title,
      'email_list'  => $form_email
    ]
  ];

  // Incluir el repositorio si no está cargado
  if (!class_exists('SimpleForms_FormsRepository')) {
    require_once plugin_dir_path(__FILE__) . 'src/Database/FormsRepository.php';
  }

  // Guardar el formulario
  $repo = new SimpleForms_FormsRepository();
  $repo->save_form($form);

  // Redirigir al listado después de guardar
  wp_redirect(admin_url('admin.php?page=form-listing&status=saved'));
  exit;
}
