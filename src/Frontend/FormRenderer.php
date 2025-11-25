<?php
/**
 * Clase que Renderiza el JSON (desde DB) del formulario
 */

namespace SimpleForms\Frontend;

class FormRenderer
{
    public function render($form, $fields)
    {
        if (!is_array($fields)) {
            return "<p>Error: los campos del formulario no son válidos.</p>";
        }

        ob_start();

        echo '<form class="simple-form" method="post" enctype="multipart/form-data">';

        foreach ($fields as $field_id => $field) {

            // Valores seguros y con fallback
            $type        = $field['type']        ?? 'text';
            $label       = strip_tags($field['label'] ?? '');
            $required    = (!empty($field['required']) && $field['required'] === 'true') ? 'required' : '';
            $placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
            $value       = isset($field['value']) ? esc_attr($field['value']) : '';
            $class       = isset($field['class']) ? esc_attr($field['class']) : '';

            echo "<div class='sf-field'>";

            if ($label) {
                echo "<label for='$field_id'>$label</label>";
            }

            switch ($type) {

                /* ---------------------------
                   CAMPOS DE TEXTO BÁSICOS
                ---------------------------- */
                case 'text':
                case 'email':
                case 'number':
                case 'url':
                case 'tel':
                case 'password':
                case 'date':
                    echo "<input 
                            type='$type' 
                            id='$field_id' 
                            name='$field_id'
                            value='$value'
                            placeholder='$placeholder'
                            class='$class'
                            $required
                          >";
                break;


                /* ---------------------------
                   TEXTAREA
                ---------------------------- */
                case 'textarea':
                    echo "<textarea 
                            id='$field_id' 
                            name='$field_id' 
                            placeholder='$placeholder'
                            class='$class'
                            $required
                          >$value</textarea>";
                break;


                /* ---------------------------
                   SELECT
                ---------------------------- */
                case 'select':
                    $options = $field['options'] ?? [];

                    echo "<select id='$field_id' name='$field_id' class='$class' $required>";
                    echo "<option value=''>Seleccione...</option>";

                    foreach ($options as $opt_value => $opt_label) {
                        $opt_value = esc_attr($opt_value);
                        $opt_label = esc_html($opt_label);
                        $selected  = ($value == $opt_value) ? 'selected' : '';

                        echo "<option value='$opt_value' $selected>$opt_label</option>";
                    }

                    echo "</select>";
                break;


                /* ---------------------------
                   CHECKBOX
                ---------------------------- */
                case 'checkbox':
                    $checked = ($value === 'on' || $value === '1') ? 'checked' : '';

                    echo "<input 
                            type='checkbox' 
                            id='$field_id' 
                            name='$field_id'
                            class='$class'
                            $checked $required
                          >";
                break;


                /* ---------------------------
                   RADIO GROUP
                ---------------------------- */
                case 'radio':
                    $options = $field['options'] ?? [];

                    echo "<div class='sf-radio-group $class'>";

                    foreach ($options as $opt_value => $opt_label) {
                        $checked = ($value == $opt_value) ? 'checked' : '';
                        $opt_value = esc_attr($opt_value);
                        $opt_label = esc_html($opt_label);

                        echo "
                            <label class='sf-radio-option'>
                                <input type='radio' 
                                       name='$field_id' 
                                       value='$opt_value'
                                       $checked 
                                       $required>
                                $opt_label
                            </label>
                        ";
                    }

                    echo "</div>";
                break;


                /* ---------------------------
                   ARCHIVOS
                ---------------------------- */
                case 'file':
                    echo "<input 
                            type='file' 
                            id='$field_id' 
                            name='$field_id'
                            class='$class'
                            $required
                          >";
                break;


                /* ---------------------------
                   DEFAULT
                ---------------------------- */
                default:
                    echo "<p>Tipo de campo no soportado: $type</p>";
            }

            echo "</div>"; // fin sf-field
        }

        echo '<button type="submit">Enviar</button>';
        echo '</form>';

        return ob_get_clean();
    }
}
