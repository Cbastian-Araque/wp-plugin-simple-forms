<?php 
wp_register_style( 'form_styles', PLUGIN_URL . '/assets/src/css/main.min.css', false, PLUGIN_VERSION);
wp_enqueue_style( 'form_styles' );

wp_register_script( 'form_scripts', PLUGIN_URL . '/assets/dist/build.js', false, PLUGIN_VERSION);
wp_enqueue_script( 'form_scripts' );