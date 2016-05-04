if (!$("input[name=use_stripe]").is(':checked')) {
  $("#stripe-inputs").hide();
}
$("input[name=use_stripe]").click(function() {
  $("#stripe-inputs").toggle();
});