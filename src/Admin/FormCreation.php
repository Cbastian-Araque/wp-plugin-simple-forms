<?php // Callback que renderiza el contenido de la página
function simple_forms_render_page()
{

?>
  <div class="wrap">
    <h1>Simple Forms</h1>
    <p>Crea y edita formularios</p>

    <form action="">
      <input type="text" />

      <fieldset>
        <label>Etiqueta del campo</label>
        <span>Tipo: text</span>
      </fieldset>
    </form>

    <form action="">
        <h2>Creación de campos</h2>
      <div>
        <label>Etiqueta del campo</label>
        <input type="text" name="field-label" placeholder="Nombre del campo">
      </div>

      <label for="choose-field-type">Seleccionar el tipo de campo</label>
      <select name="field-type" id="choose-field-type">
        <option value="text">Texto</option>
        <option value="textarea">Área de texto</option>
        <option value="number">Número</option>
        <option value="tel">Teléfono</option>
        <option value="email">Correo electrónico</option>
        <option value="file">Archivo</option>
        <option value="date">Fecha</option>
        <option value="datetime">Hora y fecha</option>
        <option value="hidden">Oculto</option>
        <option value="radio">Radio</option>
        <option value="checkbox">Checkbox</option>
        <option value="select">Select</option>
      </select>
    </form>
  </div>
<?php
}