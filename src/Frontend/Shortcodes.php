<?php

/**
 * Registrar los shortcodes y renderizar formularios
 */

namespace SimpleForms\Frontend;

use SimpleForms\Database\FormsRepository;

class Shortcodes
{
  public function __construct()
  {
    add_shortcode('simple_form', [$this, 'render_shortcode']);
  }

  public function render_shortcode($atts)
  {
    $atts = shortcode_atts([
      'id' => null,
    ], $atts);

    if (empty($atts['id'])) {
      return "<p>Error: Falta el atributo ID en el shortcode.</p>";
    }

    // Instanciar correctamente el repositorio SIN namespace
    $repo = new \SimpleForms_FormsRepository();

    $form = $repo->get_form($atts['id']);

    if (!$form) {
      return "<p>Formulario no encontrado.</p>";
    }

    // Campos desde JSON
    $fields = $form['form_fields']; // ya es array

    if (!is_array($fields)) {
      return "<p>Error interno: Formulario inválido.</p>";
    }

    // Renderizar el formulario
    $renderer = new FormRenderer();

    return $renderer->render($form, $fields);
  }
}

