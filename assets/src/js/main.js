const $ = jQuery;
const d = document;

class SimpleField {
  static numberField = 1; // Si el nombre del campo es field_ usa esta variable
  static numberTextarea = 1; // Si el nombre del campo es textarea_ se usa esta variable

  constructor(type, label, required, extras, editing) {
    this.type = type;
    this.label = label;
    this.required = required;
    this.extras = extras;
    this.editing = editing;
    this.element = null;
  }

  // Obtiene le valor máximo de los field y textarea
  static inicializarNumerosMaximos() {
    const divs = document.querySelectorAll('div[data-info-field]');
    let maxField = 0;
    let maxTextarea = 0;

    divs.forEach(div => {
      const dataInfo = div.getAttribute('data-info-field');
      if (dataInfo) {
        try {
          const info = JSON.parse(dataInfo);
          for (const key in info) {
            if (key.startsWith('field_')) { // valor máximo de field
              const numero = parseInt(key.split('_')[1]);
              if (!isNaN(numero) && numero > maxField) {
                maxField = numero;
              }
            } else if (key.startsWith('textarea_')) { // valor máximo de textarea
              const numero = parseInt(key.split('_')[1]);
              if (!isNaN(numero) && numero > maxTextarea) {
                maxTextarea = numero;
              }
            }
          }
        } catch (error) {
          console.error("Error al parsear JSON:", error, "en el div:", div);
        }
      }
    });

    // Asigna los valores máximos a las variables estáticas, incrementando en 1 para el siguiente campo.
    SimpleField.numberField = maxField + 1;
    SimpleField.numberTextarea = maxTextarea + 1;
  }

  static CreateJSONField(type, label, required, extras, fieldName) {
    const fieldObjProperties = {}; // Objeto que guardará los datos del campo

    // Se construye el objeto del campo
    fieldObjProperties[fieldName] = {
      type: type,
      label: label,
      required: required
    };

    if (Object.keys(extras[0]).length > 0) { // Agregar valores adicionales
      fieldObjProperties[fieldName]['extras'] = extras[0] || {};
    }

    return JSON.stringify(fieldObjProperties, null, 0);
  }

  createField() {
    const fragmentField = d.createDocumentFragment(); // Fragmento que recibirá el HTML
    const tagFieldsetWrapper = d.createElement('div'); // Contenedor que recibirá todo el fragmento
    const detailsField = d.createElement('DIV'); // Label de input
    const btnEditField = d.createElement('button'); // Botón de editar
    const btnDeleteField = d.createElement('button'); // Botón para eliminar campo
    const btnTopPosition = d.createElement('img'); // Botón de editar
    const btnBottomPosition = d.createElement('img'); // Botón para eliminar campo

    $(tagFieldsetWrapper).addClass('field-form');
    $(tagFieldsetWrapper).attr('draggable', 'true');
    $(detailsField).addClass('form-field');

    // Botones editar/borrar/Subir/Bajar
    btnEditField.textContent = "Editar";
    btnEditField.classList.add('btn-primary', `btn-edit-field`);
    btnEditField.setAttribute('type', 'button');
    btnDeleteField.textContent = "Borrar";
    btnDeleteField.classList.add('btn-caution', `btn-delete-field`);
    btnDeleteField.setAttribute('type', 'button');

    let fieldName; // Se usará para guardar el nombre del cmapo (field_ o textarea_)
    let addNumber; // Número del campo para evitar repetir el campo o textarea (field_1, field_2, etc)

    if (!this.editing) {
      if (this.type === 'textarea' || this.type === 'file') {
        addNumber = SimpleField.numberTextarea;
        SimpleField.numberTextarea++;
        fieldName = `textarea_${addNumber}`;
      } else {
        addNumber = SimpleField.numberField;
        SimpleField.numberField++;
        fieldName = `field_${addNumber}`;
      }
    }

    let fieldJson;
    if (this.editing) {
      $(detailsField).html(`<div class="field-label">${this.label}</div><span class="field-type">${this.type}</span>`);
      fieldName = $('.is-editing').data('fieldId');
      fieldJson = SimpleField.CreateJSONField(this.type, this.label, this.required, this.extras, fieldName);

      $('.is-editing').find('.field-label').html(this.label);
      $('.is-editing').find('.field-type').text(this.type);
      $('.is-editing').attr('data-info-field', fieldJson);
    } else {
      fieldJson = SimpleField.CreateJSONField(this.type, this.label, this.required, this.extras, fieldName);

      $(tagFieldsetWrapper).attr('data-info-field', fieldJson); // JSON como atributo al contneedor del campo
      $(tagFieldsetWrapper).attr('data-field-id', fieldName); // Identificador para el campo

      // Añadir elementos al contenedor del campo 
      $(detailsField).html(`<div class="field-label">${this.label}</div><span class="field-type">${this.type}</span>`);
      fragmentField.appendChild(detailsField);
      fragmentField.append(btnEditField);
      fragmentField.append(btnDeleteField);
      fragmentField.append(btnTopPosition);
      fragmentField.append(btnBottomPosition);
      tagFieldsetWrapper.append(fragmentField);

      return tagFieldsetWrapper;
    }
  }
}

// Funciones para crear el formulario
jQuery(function ($) {
  const $formPreview = $('#form-generate-json');
  const basicInfoForm = $('.info-form'); // Los campos básicos del formulario
  const $btnSaveForm = $('.save-json-form');

  let $htmlTiny = tinyMCE.get('labelfield');
  let $selectField = $('#type-field');
  let $requiredField = $('#required-field');
  let $customClassField = $('#class-field');
  let $defaultSelect = $('.default-select');
  let $validationRules = $('input[name="rules_validation"]');
  let $validateDate = $('#validate-date');
  let $wrapperOptions = $('.wrapper-items-fields');
  let $textareaDefultValue = $('.textarea-value-default');
  let $textareaColsVal = $('.textarea-cols');
  let $textareaRowsVal = $('.textarea-rows');
  let $fileMimetypes = $('.file-mimetypes');
  let $fileSize = $('.file-size');
  let $fileMultiple = $('.file-multiple');
  let $checkedForTyc = $('.enable-tyc');
  let $btnAddField = $('.btn-add-field'); // Botón para crear campo
  let $containerButtonsEditing = $('.actions-edit-field'); // Botones (Editar / Cancelar)
  let $wrapperFields = $('.preview-form .fields-added');
  let $settingsField = $('.sidebar-settings-fields');
  let $btnAddOption = $('.btn-add-option'); // BOtón para agregar opciones (para el campo select)
  let $btnAddRadio = $('.btn-add-checkradio'); // BOtón para agregar opciones (para el campo radio)

  function printOptionElement(key = '', value = '') { // HTML para las opciones que se agregan al campo
    return `
    <div class="option-select option-radio option-checkbox option-field">
      <div>
        <label>Clave</label>
        <input type="text" placeholder="Dejar vacío para usar el valor" class="key-option" value="${key || ''}">
      </div>
      <div>
        <label>Valor</label>
        <input type="text" class="value-option" value="${value || 'Opción 1'}">
      </div>
      <button class="btn btn-caution">-</button>
    </div>
    `;
  }

  // Expresión que convierte a minúscula, elimina acentos (áéíóú => aeiou) y reemplaza " " por "_"
  function regexMinAcentos(string) {
    return string.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, ''); // Expresión regular 
  }

  function clearSettingsField() {
    // Limpiar Los datos básicos de los campos
    $htmlTiny.setContent(''); // Label
    $selectField.val('text').trigger('change'); // Select
    $validateDate.val('').trigger('change'); // Select
    $requiredField.prop('checked', false); // Campo requerido
    $customClassField.val(''); // Campo de clases personalizadas
    $fileMultiple.prop('checked', false);
    $checkedForTyc.prop('checked', false);
    $btnAddRadio.show();

    for (let i = 0; i < $validationRules.length; i++) { // Reglas de validación
      $($validationRules[i]).prop('checked', false);
    }
  }

  $('.btn-edit').on('click', function (e) { // Actualizar campo
    $containerButtonsEditing.removeClass('editing');
    $btnAddField.removeClass('no-show');
    add_field(true);
    clearSettingsField();
    $('.field-form').removeClass('is-editing');
  });

  // Cancelar edición
  $($containerButtonsEditing).find('.btn-cancel').on('click', function () {
    $containerButtonsEditing.removeClass('editing');
    $('.field-form').removeClass('is-editing');

    $btnAddField.removeClass('no-show');
    clearSettingsField();
  });

  $btnAddField.on('click', function () {
    add_field();
  });

  /**
   * Construye e inserta el campo dentro del formulario
   * @param {boolean} isEditing - Controla cuando el campo es nuevo o se está editando
   */
  function add_field(isEditing = false) {
    // Obtener los datos básicos para el campo (etiqueta, tipo, requerido)
    const labelInput = $htmlTiny.getContent();
    const typeField = $selectField.val();
    const requiredField = $requiredField[0].checked;
    const classField = $customClassField.val();

    const objExtras = {}; // Almacenar los valores extras para cada tipo de campo
    const defaultSelect = $defaultSelect.val(); // SOLO tipo Select
    const options = $('.extra-visible .option-field'); // SOLO tipo Select, radio y checkbox
    const isToS = $('.enable-tyc')[0].checked; // SOLO tipo checkbox
    const listMimetypes = $('.file-mimetypes').val(); // SOLO tipo File
    const maxSize = $('.file-size').val() ? $('.file-size').val() : 2; // SOLO tipo File
    const multipleFiles = $('.file-multiple')[0].checked; // SOLO tipo File
    const textareaValueDefault = $('.textarea-value-default').val(); // SOLO tipo Textarea
    const textareaRows = $('.textarea-rows').val() ? $('.textarea-rows').val() : 3; // SOLO tipo Textarea
    const textareaCols = $('.textarea-cols').val() ? $('.textarea-cols').val() : 8; // SOLO tipo Textarea

    const objOptions = {} // Opciones para el campo select / radio / checkbox
    const VALIDATION_RULES = []; // Reglas de validación

    if (options.length > 0) {
      $(options).each(function (i, el) {
        let key = $(el).find('.key-option').val();
        let value = $(el).find('.value-option').val();

        if (key === '') {
          key = regexMinAcentos(value);
        } else {
          key = key;
        }

        objOptions[key] = value;
        objExtras['options'] = objOptions;
      });
    }

    if (typeField === 'text') {
      for (let i = 0; i < $validationRules.length; i++) {
        if ($validationRules[i].checked) {
          VALIDATION_RULES.push($validationRules[i].value);
        }
      }

      if (VALIDATION_RULES.length > 0) {
        objExtras['validation_rules'] = VALIDATION_RULES;
      }
    }
    if (typeField === 'select') {
      objExtras['select_default'] = defaultSelect;
    }
    if (typeField === 'file') {
      objExtras['accept'] = listMimetypes;
      objExtras['max_size'] = maxSize;
      objExtras['multiple'] = multipleFiles;
    }
    if (typeField === 'checkbox') {
      objExtras['is_tos'] = isToS;
    }
    if (typeField === 'textarea') {
      objExtras['value'] = textareaValueDefault;
      objExtras['rows'] = textareaRows;
      objExtras['cols'] = textareaCols;
    }

    if (typeField === 'date') {
      if ($validateDate.val() !== '') {
        VALIDATION_RULES.push($validateDate.val());
      }

      objExtras['validation_rules'] = VALIDATION_RULES;
    }

    if (classField !== '') { // Agregar clase personalizada al campo
      objExtras['div_class'] = classField;
    }

    if ($htmlTiny.getContent().trim() !== '') {
      $('#wp-labelfield-wrap').closest('.basic-field').removeClass('no-empty');
    } else {
      $('#wp-labelfield-wrap').closest('.basic-field').addClass('no-empty');
    }

    if (listMimetypes == '') { // Si es tipo file, se debe agregar el tipo de archivo permitido
      $fileMimetypes.closest('p').addClass('no-empty');
    } else {
      $fileMimetypes.closest('p').removeClass('no-empty');
    }

    if (labelInput !== '' && listMimetypes !== '') {
      $($settingsField).removeClass('visible-settings')
      const newField = new SimpleField(typeField, labelInput, requiredField, [objExtras], isEditing);
      const fieldElement = newField.createField();

      // Añadir el nuevo campo al contenedor
      $wrapperFields.append(fieldElement);
      clearSettingsField(); // limpiar/reiniciar el formulario de creación/edición
    }
  }

  $($selectField).on('change', function (e) {
    let fieldSelected = $(e.target).val();
    $(`[class^="options-"]`).html(''); // Limpiar los campos adicionales (extra)
    $(`.extra`).removeClass('extra-visible');
    $(`.extra-${fieldSelected}`).addClass('extra-visible'); // Mostrar campos extras de campo seleccionado
  });

  $checkedForTyc.on('change', function () {
    if (this.checked) {
      $wrapperOptions.html('');
      $btnAddRadio.hide();
    } else {
      $btnAddRadio.show();
    }
  });

  // Agregar opciones al campo (select, checkbox, radio)
  function addOptionField() {
    // Agregar opción
    $($btnAddOption).on('click', function () {
      $('.options-select').append(printOptionElement())
    });
    // Agregar opción de radio o checkbox
    $($btnAddRadio).on('click', function () {
      $('.options-checkbox-radio').append(printOptionElement())
    });
  }

  function removeElement() { // Elimina un campo o elemento del campo (opción)
    $('body').on('click', '.btn-caution', function (e) {
      $(this).parent().remove();
    });
  }

  // Editar campo - Recupera la información del campo a editar y la muestra en el formulario de edición/creación de campo
  function edit_field() {
    $('body').click(function (e) {
      if ($(e.target).hasClass('btn-edit-field')) {
        const containerField = $(e.target).closest('.field-form');
        const jsonField = $(containerField).data('infoField');

        // Borrar la caché de jQuery para el elemento
        $(containerField).removeData('infoField');

        $containerButtonsEditing.addClass('editing');
        $btnAddField.addClass('no-show');
        $('.field-form').removeClass('is-editing');
        $(containerField).addClass('is-editing');

        for (const key in jsonField) { // Obtener los valores del campo
          if (jsonField.hasOwnProperty(key)) { // field_ o textarea_
            const campo = jsonField[key]; // Accede al objeto anidado

            $selectField.val(campo.type).trigger('change');
            $htmlTiny.setContent(campo.label);
            $requiredField.prop('checked', campo.required);

            if (campo.extras) { // Obtener los campos extras para editar
              const optionsToEdit = campo.extras.options;

              if (campo.type === 'select' || campo.type === 'checkbox' || campo.type === 'radio') {
                $defaultSelect.val(campo.extras.select_default); // Asignar el valor por defecto

                $checkedForTyc.prop('checked', campo.extras.is_tos);

                if (campo.extras.is_tos) {
                  $btnAddRadio.hide();
                }

                for (const key in optionsToEdit) {
                  if (optionsToEdit.hasOwnProperty(key)) {
                    const value = optionsToEdit[key];
                    $('.extra-visible').find($wrapperOptions).append(printOptionElement(key, value)); // Añadimos el contenedor al wrapper principal
                  }
                }
              }

              if (campo.type === 'text') {
                const opciones_rules = campo.extras.validation_rules;

                if (opciones_rules && Array.isArray(opciones_rules)) {
                  opciones_rules.forEach(valueChecked => {
                    $validationRules.each((idx, checkbox) => {
                      if (checkbox.value === valueChecked) { // Comprobar valor de la regla de validación
                        $(checkbox).prop('checked', true);
                        return false;
                      }
                    });
                  });
                }
              }

              if (campo.type === 'textarea') {
                $textareaDefultValue.val(campo.extras.value);
                $textareaColsVal.val(campo.extras.cols);
                $textareaRowsVal.val(campo.extras.rows);
              }

              if (campo.type === 'file') {
                $fileMimetypes.val(campo.extras.accept);
                $fileSize.val(campo.extras.max_size);
                $fileMultiple.prop('checked', campo.extras.multiple);
              }
              if (campo.type === 'date') {
                const validate_date = campo.extras.validation_rules;
                $validateDate.val(validate_date[0]).trigger('change');
              }

              $customClassField.val(campo.extras.div_class)
            }
          }
        }
      }
    });
  }

  function dragFields() {
    let $clone = null;

    $wrapperFields.on('dragstart', '.field-form', function (e) {
      $(this).addClass('dragging'); // Agrega una clase para efecto visual
      e.originalEvent.dataTransfer.setData('text/plain', $(this).index()); // Guarda el índice del elemento arrastrado

      // Crear un clon del elemento para seguir el cursor
      $clone = $(this).clone().css({
        position: 'absolute',
        top: '0',
        width: $(this).outerWidth(),
        height: $(this).outerHeight(),
        opacity: 0.8,
        pointerEvents: 'none',
        zIndex: 1000,
        border: '1px solid #a0c4ff',
        backgroundColor: '#f1f2f5',
        borderRadius: '10px',
        boxShadow: '0 2px 3px 0 rgba(0,0,0,0.2)',
      }).appendTo('body');

      setTimeout(() => $(this).css('opacity', '0'), 0); // al elemento que se está arrastrando se "oculta" mientras se arrastra

      // Si quieres que el elemento arrastrado "siga" el mouse, usa esto:
      let dragImage = $(this).clone().css({
        width: $(this).outerWidth(),
        height: $(this).outerHeight(),
        position: 'relative',
        opacity: 1,
        pointerEvents: 'none',
      })[0];

      e.originalEvent.dataTransfer.setDragImage(dragImage, 0, 0);
      e.originalEvent.dataTransfer.setDragImage(new Image(), 0, 0);
    });

    $wrapperFields.on('dragover', function (e) {
      e.preventDefault();

      $(this).addClass('dragover');
    });

    $wrapperFields.on('dragleave', function (e) {
      $(this).removeClass('dragover');
    });

    $wrapperFields.on('drop', function (e) {
      e.preventDefault();
      let fromIndex = e.originalEvent.dataTransfer.getData('text/plain');
      let $draggedField = $(this).find('.field-form').eq(fromIndex);

      let $dropTarget = $(e.target).closest('.field-form');
      if ($dropTarget.length && $draggedField[0] !== $dropTarget[0]) {
        let toIndex = $dropTarget.index();

        if (fromIndex < toIndex) {
          $dropTarget.after($draggedField);
        } else {
          $dropTarget.before($draggedField);
        }
      }

      $('.field-form').removeClass('dragging');
      $(this).removeClass('dragover');
    });

    $wrapperFields.on('dragend', '.field-form', function () {
      $(this).removeClass('dragging'); // Restablecer estilos al finalizar el arrastre
      $(this).css('opacity', '1').removeClass('dragging');

      if ($clone) {
        $clone.remove();
        $clone = null;
      }
    });

    $(document).on('drag', function (e) {
      if ($clone) {
        $clone.css({
          top: e.originalEvent.pageY + 5 + 'px',
          left: e.originalEvent.pageX + 5 + 'px'
        });
      }
    });
  }

  function notAllowFieldsEmpty(fields) { // Validar que los campos no estén vacíos
    let fieldValid = true;

    for (let i = 0; i < fields.length; i++) {
      const el = fields[i];
      if ($(el).val() === '') {
        $(el).closest('div').addClass('no-empty');
        fieldValid = false;
        // break;
      } else {
        $(el).closest('div').removeClass('no-empty');
      }
    }

    return fieldValid;
  }

  /**
 * Detectar si el formulario tiene cambios
 * @param  {Node} DOMNode Elemento a observar / detectar cambios
 */
  function listenerChangesForm(DOMNode) {
    let formModified = false;
    const form = DOMNode;

    function detectarCambios() {
      formModified = true;
    }

    // Detectar cambios en campos de información básica del formulario (Título, correos, prefooter, label de botón)
    form.find('input[type="text"], input[type="email"], input[type="checkbox"]').on('change input', detectarCambios);

    // Configurar el observer
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if ($(node).is('div.field-form')) {
            $(node).find('input, select, textarea').on('change input', detectarCambios);
            formModified = true; // Considera el formulario modificado si se añade un campo
          }
        });
      });
    });

    // Observar el contenedor de campos
    const targetNode = $('.fields-added')[0]; // Obtener el elemento DOM
    const config = { childList: true, subtree: true }; // Observar adiciones de nodos

    observer.observe(targetNode, config);

    // Detectar cambios en los campos ya creados
    $('.fields-added').on('click', '.btn-edit-field, .btn-delete-field', function () {
      formModified = true;
    });

    // Detectar cambios en el título del formulario
    $('#title-form').on('change input', detectarCambios);

    // Manejar el evento beforeunload - cuando se quiere recargar o redireccionar a otra página
    $(window).on('beforeunload', function (e) {
      if (formModified) {
        e.preventDefault();
        const mensaje = "¿Seguro que quieres salir sin guardar los cambios?";
        e.returnValue = mensaje; // Para Chrome y Firefox
        return mensaje; // Para Safari
      }
    });

    // Restablecer el flag al guardar el formulario
    $($btnSaveForm).on('click', function () {
      let isValid = notAllowFieldsEmpty(basicInfoForm); // Cuando se guarde el formulario, permitir salir de la página o recargar
      if (isValid) {
        formModified = false;
      }
    });
  }

  // Guardar/Editar JSON
  let formsArray = []; // Cadena JSON inicial
  const editingFormId = $('#simpleforms-edit-form-id').val() || null;
  $btnSaveForm.on('click', (e) => {
    e.preventDefault();

    const formTitle = $formPreview.find('.title-form').val();
    const formEmails = $formPreview.find('.emails-form').val();
    const formBtnLabel = $formPreview.find('.label-button').val();
    const formFields = $formPreview.find('.fields-added .field-form').toArray();

    // SI ES EDICIÓN se usa el ID recibido
    // SI ES NUEVO se genera uno a partir del título
    const formId = editingFormId
      ? editingFormId
      : regexMinAcentos(formTitle);

    // Si es creación, SÍ debe validarse que no exista el ID
    if (!editingFormId && formsArray.some(form => form.id === formId)) {
      alert(`El formulario con ID "${formId}" ya existe.`);
      return;
    }

    // Construcción de campos
    let dataFields = {};
    formFields.forEach(fieldset => {
      let fieldData = $(fieldset).data('infoField');
      dataFields = Object.assign({}, dataFields, fieldData);
    });

    const formData = {
      id: formId,
      version: 1,
      fields: dataFields,
      submit_btn: {
        submit_type: "send",
        label: formBtnLabel
      },
      settings: {
        titulo: formTitle,
        email_list: formEmails,
      }
    };

    // Validar campos
    let isValid = notAllowFieldsEmpty(basicInfoForm);
    if (!isValid) return;

    // 🔹 Importante: actualizar el JSON en la vista previa
    $formPreview.attr('data-json-form', JSON.stringify(formData));

    $.ajax({
      url: ajaxurl,
      method: "POST",
      data: {
        action: "simpleforms_save_form",
        form: formData,
        is_edit: editingFormId ? 1 : 0 // para que PHP sepa que es edición
      },
      success: function (response) {
        console.log("Guardado OK", response);
        alert("Formulario guardado correctamente.");
        window.location.reload();
      },
      error: function (err) {
        console.error("Error al guardar:", err);
        alert("Error al guardar el formulario");
      }
    });
  });

  // Inicializar las funciones
  $selectField.val('text').trigger('change'); // Esto se hace para que se muestren los campos extra para el campo "texto"
  SimpleField.inicializarNumerosMaximos();
  dragFields();
  removeElement();
  addOptionField();
  edit_field();
  listenerChangesForm($formPreview);
});