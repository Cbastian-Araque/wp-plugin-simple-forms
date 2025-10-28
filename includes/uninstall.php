<?php // Al desinstalar
function simple_forms_on_uninstall()
{
  $migration = new SimpleForms_Migration();
  $migration->drop_tables();
  SimpleForms_Options::delete_options();
}