<?php
function simple_forms_reports_details_cb()
{
  global $wpdb;

  // Tablas
  $table_forms   = $wpdb->prefix . TABLE_PREFIX . TABLE_FORMS_SCHEMAS;
  $table_records = $wpdb->prefix . TABLE_PREFIX . TABLE_RECORDS;
  $table_meta    = $wpdb->prefix . TABLE_PREFIX . TABLE_ENTRY_META;
  $table_files   = $wpdb->prefix . TABLE_PREFIX . TABLE_FILES;

  // Mostrar tamaños de los archivos legibles
  if (!function_exists('sf_human_filesize')) {
    function sf_human_filesize($bytes, $decimals = 2)
    {
      $sz = array('B', 'KB', 'MB', 'GB', 'TB');
      $factor = floor((strlen($bytes) - 1) / 3);
      if ($factor == 0) return $bytes . ' ' . $sz[$factor];
      return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sz[$factor];
    }
  }

  echo '<div class="wrap">';

  // ---------------------------
  // Vista detalle por formulario
  // ---------------------------
  if (isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {

    $form_id = intval($_GET['form_id']);

    /** Obtener el formulario */
    $form = $wpdb->get_row(
      $wpdb->prepare("SELECT * FROM {$table_forms} WHERE id = %d", $form_id),
      ARRAY_A
    );

    if (!$form) {
      echo "<h1>Error</h1><p>El formulario no existe.</p></div>";
      return;
    }

    echo "<h1 class='title-option-page'>Reporte del formulario: <strong>" . esc_html($form['form_title']) . "</strong></h1>";

    echo '<p><a href="' . esc_url(admin_url('admin.php?page=forms-reports')) . '" class="button">← Volver al listado</a></p>';

    /** Obtener registros del formulario */
    $entries = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT id, submitted_at 
                 FROM {$table_records} 
                 WHERE form_id = %d 
                 ORDER BY submitted_at DESC",
        $form_id
      ),
      ARRAY_A
    );

    if (!$entries) {
      echo "<p>No hay registros para este formulario.</p></div>";
      return;
    }

    echo '<table class="widefat fixed striped">';
    echo '<thead>
                <tr>
                    <th>ID Registro</th>
                    <th>Fecha</th>
                    <th>Campos enviados</th>
                    <th>Archivos</th>
                </tr>
              </thead>';
    echo '<tbody>';

    foreach ($entries as $entry) {

      /** Obtener campos del registro actual */
      $meta = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT field_name, field_value 
                     FROM {$table_meta} 
                     WHERE entry_id = %d",
          $entry['id']
        ),
        ARRAY_A
      );

      /** Obtener archivos relacionados */
      $files = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT field_name, file_url, file_path, file_size, file_type 
                     FROM {$table_files} 
                     WHERE entry_id = %d",
          $entry['id']
        ),
        ARRAY_A
      );

      echo "<tr>";
      echo "<td>" . intval($entry['id']) . "</td>";
      echo "<td>" . esc_html($entry['submitted_at']) . "</td>";

      // Campos
      echo "<td>";
      if ($meta) {
        echo "<ul style='margin:0;padding-left:18px;'>";
        foreach ($meta as $m) {
          echo "<li><strong>" . esc_html($m['field_name']) . ":</strong> " . esc_html($m['field_value']) . "</li>";
        }
        echo "</ul>";
      } else {
        echo "<em>No hay campos.</em>";
      }
      echo "</td>";

      // Archivos -> mostrar enlaces de descarga
      echo "<td>";
      if ($files) {
        echo "<ul style='margin:0;padding-left:18px;'>";
        foreach ($files as $f) {
          $url = $f['file_url'] ?? '';
          $path = $f['file_path'] ?? '';
          $size = isset($f['file_size']) ? intval($f['file_size']) : null;
          $type = $f['file_type'] ?? '';

          // Nombre de archivo legible
          $filename = $url ? basename($url) : ($path ? basename($path) : 'archivo');

          // Seguridad: validar URL y escapar
          $url_esc = esc_url($url);

          // Texto del enlace (nombre + tipo + tamaño)
          $extra = [];
          if ($type) $extra[] = esc_html($type);
          if ($size) $extra[] = sf_human_filesize($size);
          $extra_str = $extra ? ' (' . implode(' • ', $extra) . ')' : '';

          if ($url_esc) {
            echo '<li>';
            echo '<a href="' . $url_esc . '" target="_blank" rel="noopener noreferrer" download>' . esc_html($filename) . '</a>';
            echo esc_html($extra_str);
            echo ' <span style="color:#777;font-size:12px;"> — campo: ' . esc_html($f['field_name']) . '</span>';
            echo '</li>';
          } else {
            // Si no hay URL pero sí path local, intentar mostrar path
            if ($path) {
              echo '<li>' . esc_html(basename($path)) . ($size ? ' (' . sf_human_filesize($size) . ')' : '') . ' <em>(ruta guardada)</em></li>';
            } else {
              echo '<li><em>Archivo sin URL</em></li>';
            }
          }
        }
        echo "</ul>";
      } else {
        echo "<em>No hay archivos adjuntos.</em>";
      }
      echo "</td>";

      echo "</tr>";
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    return; // <-- terminar aquí
  }

  // ---------------------------
  // Vista lista general (si no viene form_id)
  // ---------------------------

  echo '<h1 class="title-option-page">Reporte de envíos de formularios</h1>';

  // Obtener formularios
  $forms = $wpdb->get_results("SELECT id, form_name, form_title FROM {$table_forms}", ARRAY_A);

  if (!$forms) {
    echo '<p>No hay registros para mostrar.</p>';
    echo '</div>';
    return;
  }

  echo '<table class="widefat fixed striped">';
  echo '<thead>
            <tr>
                <th>ID de formulario</th>
                <th>Título</th>
                <th>Total Registros</th>
                <th>Registros en los últimos 7 días</th>
                <th>Acciones</th>
            </tr>
          </thead>';
  echo '<tbody>';

  foreach ($forms as $f) {

    // Total registros
    $total_registros = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_records} WHERE form_id = %d",
        $f['id']
      )
    );

    // Registros últimos 7 días
    $registros_semana = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT COUNT(*) 
                   FROM {$table_records} 
                   WHERE form_id = %d 
                     AND submitted_at >= (NOW() - INTERVAL 7 DAY)",
        $f['id']
      )
    );

    echo '<tr>';
    echo '<td>' . esc_html($f['id']) . '</td>';
    echo '<td style="font-weight:600;font-size:18px;color:#000">' . esc_html($f['form_title']) . '</td>';
    echo '<td><strong>' . intval($total_registros) . '</strong></td>';
    echo '<td>' . intval($registros_semana) . '</td>';
    echo '<td>
            <a href="' . esc_url(admin_url('admin.php?page=forms-reports&form_id=' . intval($f['id']))) . '" class="button button-secondary">Ver reporte</a>
          </td>';
    echo '</tr>';
  }

  echo '</tbody>';
  echo '</table>';
  echo '</div>'; // wrap
}
