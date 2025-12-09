<?php
// Rutas
define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ).'/' ); // plugin path
define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // plugin url

// Versionamiento
$plugin_data = get_plugin_data(PLUGIN_FILE); 
define('PLUGIN_VERSION', $plugin_data['Version']); // Versionamiento de css y js
define('DB_VERSION', '1.0.0'); // Versión de la base de datos


// Constantes para la Base de datos
define('TABLE_PREFIX', 'simple_forms_');
define('TABLE_FORMS_SCHEMAS', 'schemas'); // TABLE DB
define('TABLE_RECORDS', 'records'); // TABLE DB
define('TABLE_ENTRY_META', 'records_meta'); // TABLE DB
define('TABLE_FILES', 'files'); // TABLE DB