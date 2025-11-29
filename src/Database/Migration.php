<?php

/**
 * Clase encargada de crear las tablas del plugin (migraciones)
 */

class SimpleForms_Migration
{
  private $wpdb;
  private $charset;
  private $prefix;
  private $db_version = '1.0.1'; // Incrementar cuando se cambie la estructura

  public function __construct()
  {
    global $wpdb;
    $this->wpdb = $wpdb;
    $this->charset = $wpdb->get_charset_collate();
    $this->prefix = $wpdb->prefix . TABLE_PREFIX;
  }

  /**
   * Ejecuta todas las migraciones
   */
  public function run()
  {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql_tables = [
      $this->get_table_forms_sql(),
      $this->get_table_entries_sql(),
      $this->get_table_entry_meta_sql(),
      $this->get_table_files_sql()
    ];

    foreach ($sql_tables as $sql) {
      dbDelta($sql);
    }

    // Guardamos versión de DB
    update_option(TABLE_PREFIX . 'db_version', $this->db_version);
  }

  /**
   * Ejecuta una actualización inteligente (solo si cambia la versión)
   */
  public function smart_upgrade()
  {
    $installed_version = get_option(TABLE_PREFIX . 'db_version', DB_VERSION);

    if (version_compare($installed_version, $this->db_version, '<')) {
      $this->run();
    }
  }

  /**
   * Elimina TODAS las tablas del plugin (usado al desinstalar)
   */
  public function drop_tables()
  {
    $tables = [
      $this->prefix . TABLE_FORMS_SCHEMAS,
      $this->prefix . TABLE_RECORDS,
      $this->prefix . TABLE_ENTRY_META,
      $this->prefix . TABLE_FILES
    ];

    foreach ($tables as $table_name) {
      $this->wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    // Registro opcional en el log
    error_log('SimpleForms_Migration: todas las tablas del plugin fueron eliminadas correctamente.');
  }

  /**
   * Tabla de formularios (estructura general del formulario)
   */
  private function get_table_forms_sql()
  {
    $table_name = $this->prefix . TABLE_FORMS_SCHEMAS;

    return "CREATE TABLE {$table_name} (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      form_name VARCHAR(255) NOT NULL,
      form_title VARCHAR(255) NOT NULL,
      version INT DEFAULT 1,
      form_fields LONGTEXT NOT NULL, -- JSON del formulario
      shortcode VARCHAR(255) DEFAULT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY form_name_unique (form_name)
    ) {$this->charset};";
  }

  /**
   * Tabla de registros enviados
   */
  private function get_table_entries_sql()
  {
    $table_name = $this->prefix . TABLE_RECORDS;

    return "CREATE TABLE {$table_name} (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      form_id BIGINT(20) UNSIGNED NOT NULL,
      url_register VARCHAR(255) DEFAULT NULL,
      submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY form_id (form_id)
    ) {$this->charset};";
  }

  /**
   * Tabla de metadatos (valores individuales de los campos enviados)
   */
  private function get_table_entry_meta_sql()
  {
    $table_name = $this->prefix . TABLE_ENTRY_META;

    return "CREATE TABLE {$table_name} (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      entry_id BIGINT(20) UNSIGNED NOT NULL,
      field_name VARCHAR(255) NOT NULL,
      field_label VARCHAR(255) NOT NULL,
      field_value LONGTEXT,
      PRIMARY KEY (id),
      KEY entry_id (entry_id)
    ) {$this->charset};";
  }

  /**
   * Tabla de archivos (para guardar metadatos de uploads)
   */
  private function get_table_files_sql()
  {
    $table_name = $this->prefix . TABLE_FILES;

    return "CREATE TABLE {$table_name} (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      entry_id BIGINT(20) UNSIGNED NOT NULL,
      field_name VARCHAR(255) NOT NULL, -- nombre del campo de tipo file
      file_url VARCHAR(255) NOT NULL,   -- URL pública del archivo
      file_path VARCHAR(255) DEFAULT NULL, -- Ruta física en el servidor
      file_size BIGINT(20) DEFAULT NULL,
      file_type VARCHAR(100) DEFAULT NULL,
      uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      KEY entry_id (entry_id)
    ) {$this->charset};";
  }
}
