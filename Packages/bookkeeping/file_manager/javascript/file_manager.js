$('input[name="filename"]').hide();
if ($('input[name="new_name"]').is(":checked")) {
  $('input[name="filename"]').show();
}
$('input[name="new_name"]').on("click", function() {
  $('input[name="filename"]').toggle();
});