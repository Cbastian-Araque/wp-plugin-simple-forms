<?php
/**
 * Manejo centralizado de las opciones del plugin
 * Las opciones se almacenan en la base de datos en la tabla (prefijo)_options (wp_options)
 */
class SimpleForms_Options {

  /**
   * Lista de opciones para el plguin
   */
  public static function get_all() {
    return [
      TABLE_PREFIX . 'installed' => true, // estado de instalación del plugin
      TABLE_PREFIX . 'db_version' => DB_VERSION, // Versión de la base de datos
      TABLE_PREFIX . 'version' => PLUGIN_VERSION, // Versión del plugin
    ];
  }

  /**
   * Crear las opciones
   */
  public static function init_options() {
    foreach (self::get_all() as $key => $default_value) {
      add_option($key, $default_value);
    }
  }

  /**
   * Borra todas las opciones del plugin
   */
  public static function delete_options() {
    foreach (self::get_all() as $key => $value) {
      delete_option($key);
    }
  }
}