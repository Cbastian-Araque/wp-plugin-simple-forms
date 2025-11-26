jQuery(document).ready(function ($) {

  $(".delete-form").on("click", function (e) {
    e.preventDefault();
    
    if (!confirm("¿Seguro que deseas eliminar este formulario y TODOS sus registros?")) {
      return;
    }
    
    let formID = $(this).data("id");

    $.post(ajaxurl, {
      action: "simpleforms_delete_form",
      form_id: formID
    }, function (response) {

      if (response.success) {
        alert("Formulario eliminado correctamente.");
        location.reload();
      } else {
        alert("Error: " + response.data);
      }

    });
  });

});
