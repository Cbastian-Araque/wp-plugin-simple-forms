<?php
// Rutas
define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ).'/' ); // plugin path
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // plugin url

// Versionamiento
$plugin_data = get_plugin_data(PLUGIN_FILE); 
define('PLUGIN_VERSION', $plugin_data['Version']); // Versionamiento de css y js
define('DB_VERSION', '1.0.0'); // Versión de la base de datos


// 
define('ACTION', 'simpleforms');
define('TABLE_PREFIX', 'simple_forms_');
define('TABLE_FORMS_SHEMAS', 'shemas'); // TABLE DB
define('TABLE_RECORDS', 'records'); // TABLE DB
define('TABLE_ENTRY_META', 'records_meta'); // TABLE DB
define('TABLE_FILES', 'files'); // TABLE DB
define('POST_CONFIG_NAME', 'Crear formularios y utilizalos con el Shortcode generado');

