if ($('[name="root_checkbox"]').is(":checked")) {
  $('#permissions-container').hide();
}
$('[name="root_checkbox"]').click(function() {
    $('#permissions-container').toggle();
});