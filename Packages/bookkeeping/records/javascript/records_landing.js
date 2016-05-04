if ($('input[name=client_filter]').is(":checked")) {
  $('#client-filter-container').show();
} else {
  $('#client-filter-container').hide();
}
if ($('input[name=date_filter]').is(":checked")) {
  $('#date-filter-container').show();
} else {
  $('#date-filter-container').hide();
}
$('input[name=client_filter]').on("click", function() {
  $('#client-filter-container').toggle();
});
$('input[name=date_filter]').on("click", function() {
  $('#date-filter-container').toggle();
});
