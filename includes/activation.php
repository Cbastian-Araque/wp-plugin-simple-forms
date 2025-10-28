<?php // Al activar el plugin
function simple_forms_on_activate() {
  if (!current_user_can('activate_plugins')) {
    return;
  }

  $migration = new SimpleForms_Migration();
  $migration->run();
  SimpleForms_Options::init_options();
}