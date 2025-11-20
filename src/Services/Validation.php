<?php

/**
 * Servicio de formularios
 */

if (!defined('ABSPATH')) exit;

class SimpleForms_FormsService
{
  /** @var SimpleForms_FormsRepository */
  private $repository;

  public function __construct()
  {
    $this->repository = new SimpleForms_FormsRepository();
  }

  /**
   * Crea o actualiza un formulario
   */
  public function save($form_data)
  {
    // Validaciones básicas
    if (empty($form_data['id'])) {
      return new WP_Error('missing_id', 'El ID del formulario es obligatorio.');
    }

    if (empty($form_data['title'])) {
      $form_data['title'] = "Formulario sin título";
    }

    if (!isset($form_data['version'])) {
      $form_data['version'] = 1;
    }

    if (!isset($form_data['fields']) || !is_array($form_data['fields'])) {
      return new WP_Error('invalid_fields', 'El formulario debe contener un array de campos.');
    }

    // Sanitizar campos internos
    $form_data['fields'] = $this->sanitize_fields($form_data['fields']);

    // Guardar en el repositorio
    return $this->repository->save_form($form_data);
  }

  /**
   * Retorna todos los formularios
   */
  public function get_all()
  {
    return $this->repository->get_all_forms();
  }

  /**
   * Retorna un formulario individual
   */
  public function get($form_name)
  {
    return $this->repository->get_form($form_name);
  }

  /**
   * Elimina un formulario con cascada
   */
  public function delete($form_name)
  {
    return $this->repository->delete_form_cascade($form_name);
  }

  /**
   * Sanitiza el arreglo de campos del formulario
   */
  private function sanitize_fields($fields)
  {
    $sanitized = [];

    foreach ($fields as $key => $field) {
      $sanitized[$key] = [
        'name'        => sanitize_text_field($field['name'] ?? ''),
        'label'       => sanitize_text_field($field['label'] ?? ''),
        'type'        => sanitize_text_field($field['type'] ?? 'text'),
        'required'    => !empty($field['required']) ? 1 : 0,
        'options'     => isset($field['options']) ? $this->sanitize_options($field['options']) : [],
        'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
      ];
    }

    return $sanitized;
  }

  /**
   * Sanitización adicional para campos tipo select/radio
   */
  private function sanitize_options($options)
  {
    if (!is_array($options)) return [];

    $clean = [];
    foreach ($options as $opt) {
      $clean[] = sanitize_text_field($opt);
    }
    return $clean;
  }
}
