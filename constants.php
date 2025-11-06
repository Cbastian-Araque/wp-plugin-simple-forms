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
define('TABLE_FORMS_SCHEMAS', 'schemas'); // TABLE DB
define('TABLE_RECORDS', 'records'); // TABLE DB
define('TABLE_ENTRY_META', 'records_meta'); // TABLE DB
define('TABLE_FILES', 'files'); // TABLE DB
define('POST_CONFIG_NAME', 'Crear formularios y utilizalos con el Shortcode generado');

// Google KEYS/DATA
define('CAPTCHA_KEY_META', 'simple_forms_google_recaptcha_key');
define('CAPTCHA_SECRET_META', 'simple_forms_google_recaptcha_secret');
define('CAPTCHA_VERSION_META', 'simple_forms_recaptcha_version');
define('CAPTCHA_SCORE_META', 'simple_forms_google_recaptcha_score');
define('DISPATCH_MAILS_META', 'simple_forms_dispatch_mails');
define('DEFAULT_PREFOOTER_META', 'simple_forms_pre_footer_text');



define('ENCRYPTION_METHOD', 'AES-128-CTR');
// TODO: Estas claves deberian ser generadas al momento de instalar el plugin y deberian estar almacenadas en la BD
define('ENCRYPTION_KEY', 'simpleForms');
define('ENCRYPTION_IV', '2820166515491372');