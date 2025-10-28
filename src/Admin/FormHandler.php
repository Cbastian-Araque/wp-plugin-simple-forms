<?php
/**
 * Guardar y editar formularios
 */

function create_edit_page_cb()
{
  // Tipos de campos
  $html_fields = [
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
  asort($html_fields);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_submit_form($_POST);
  }

  if (isset($_GET['form_id'])) :
    $forms_json = PLUGIN_PATH . 'Database/data.json';
    $forms_data = file_get_contents($forms_json);
    $form_id_to_edit = $_GET['form_id'];
    $forms = json_decode($forms_data, true);

    if ($forms_data === false) {
      die('Error al leer el archivo JSON.');
    }

    if (json_last_error() !== JSON_ERROR_NONE) {
      die('Error al decodificar JSON: ' . json_last_error_msg());
    }
  endif;
?>

  <div class="wrapper-form wrapper-create-form">
    <div class="container-head">
      <h1><?php echo isset($_GET['form_id']) ? 'Editar' : 'Crear' ?> formulario</h1>
      <img src="<?php echo PLUGIN_URL . 'assets/logo.png' ?>" alt="Logo plugin" class="logo-creator">
    </div>

    <div class="preview-form">
      <?php
      $jsonInit = '';
      foreach ($forms as $form) {
        if ($form['id'] === $form_id_to_edit) {
          $jsonInit .= json_encode($form);
        }
      }
      ?>
      <form method="POST" action="" id="form-generate-json" data-json-form="<?php echo htmlspecialchars($jsonInit, ENT_QUOTES, 'UTF-8') ?>">
        <div>
          <input type="text" placeholder="Nombre del formulario" class="title-form info-form" id="title-form" value="<?php echo $_GET['form_title'] ?>" <?php echo $_GET['form_title'] ? 'disabled' : '' ?>>
        </div>
        <div class="fields-added">
          <?php foreach ($forms as $form) {

            if ($form['id'] === $form_id_to_edit) {

              $form_html = "";
              $data_field = [];
              // Recorrer los campos del formulario
              foreach ($form['fields'] as $field_id => $field_data) {
                $data_field = [];  // Alamcenar el data de cada campo

                // Convierte el array a JSON
                $field_json = json_encode($field_data);

                // Manejo de errores en la codificación JSON (IMPORTANTE)
                if ($field_json === false) {
                  error_log("Error al codificar JSON para el campo " . $field_id . ": " . json_last_error_msg());
                  $field_json = '{"error": "Error al procesar datos"}'; // Valor por defecto en caso de error
                }
                $data_field[$field_id] = json_decode($field_json);

                $icon_up = PLUGIN_URL . 'assets/img/arrow-up.svg';
                $icon_down = PLUGIN_URL . 'assets/img/arrow-down.svg';

                $form_html .= '
                      <div class="field-form" draggable="true" data-field-id="' . $field_id . '" data-info-field="' . htmlspecialchars(json_encode($data_field)) . '">
                        <div class="vs-field">
                          <div class="field-label">' . $field_data['label'] . '</div>
                          <span class="field-type vs-field">' . $field_data['type'] . '</span>
                        </div>
                        <button class="btn-primary btn-edit-field" type="button">Editar</button>
                        <button class="btn-danger btn-delete-field" type="button">Borrar</button>
                      </div>';
              }

              echo $form_html;

              break;
            }
          }
          ?>
        </div>

        <div class="fields-hidden">

          <div class="vs-settings vs-prefooter">
            <span>¿Mostrar prefooter?</span>
            <input type="checkbox" <?php echo $form['settings']['no_pre_footer'] ? 'checked' : '' ?> class="prefooter-form info-form" name="prefooter-form" id="prefooter-form">
          </div>

          <div class="vs-settings vs-emails">
            <span>Correos de notificación</span>
            <input type="text" placeholder="correos separados por coma (,)" class="emails-form info-form" id="emails-form" value="<?php echo isset($_GET['form_id']) ? $form['settings']['email_list'] : ''  ?>">
          </div>

          <div class="vs-settings vs-btn-label">
            <span>Etiqueta del botón</span>
            <input type="text" placeholder="Enviar" class="label-button info-form" id="label-button" value="<?php echo isset($_GET['form_id']) ? $form['submit_btn']['label'] : ''  ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-primary save-json-form">Guardar Formulario</button>
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
              wp_editor ('', 'labelfield', $args); ?>
            <!-- <textarea id="label-field" placeholder="Etiqueta del campo" rows="3"></textarea> -->
          </div>
          <div class="basic-field">
            <label>Tipo de campo</label>
            <select id="type-field">
              <?php foreach($html_fields as $key => $value) : ?>
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
              <button class="btn btn-primary btn-add-option">Agregar opción</button>
              <div class="options-select wrapper-items-fields"></div>
            </div>
            <div class="extra extra-checkbox">
              <p>
                <input type="checkbox" class="enable-tyc"> ¿Campo para términos y condiciones?
              </p>
            </div>
            <div class="extra extra-radio extra-checkbox">
              <button class="btn btn-primary btn-add-checkradio">Agregar elemento</button>
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

          <button class="btn-add-field btn btn-warning">Crear campo</button>
          <div class="actions-edit-field">
            <button class="btn-edit btn btn-warning">Editar campo</button>
            <button class="btn-cancel  btn btn-secondary">Cancelar</button>
          </div>
        </fieldset>
      </sidebar>
    </div>
  </div>

<?php }

function handle_submit_form($data)
{
  $form_id = sanitize_text_field($data['form_id']);
  $form_title = sanitize_text_field($data['form_title']);
  $form_email = sanitize_text_field($data['form_email']);
  $form_fields = json_decode(stripslashes($data['form_fields']), true);

  var_dump($data);

  if (json_last_error() !== JSON_ERROR_NONE) {
    wp_die('El JSON de los campos es inválido.');
  }

  // Crear la definición del formulario
  $forms_code = "<?php\n\n\$vs_forms->add_form(\n    '$form_id',\n    1,\n    " . var_export($form_fields, true) . ",\n    array(\n        'submit_type' => 'send',\n        'label' => 'Enviar'\n    ),\n    array(\n        'titulo' => '$form_title',\n        'email_list' => '$form_email'\n    )\n);";

  // Guardar en un archivo
  $form_file = get_template_directory() . '/simple_forms/' . $form_id . '.php';
  file_put_contents($form_file, $forms_code);

  // Redirigir al listado
  wp_redirect(admin_url('admin.php?page=simple-forms'));
  exit;
}
