if ($('input[name=already_selected]').val() != "") {
    $(".add-upload-thumbnail").eq($('input[name=already_selected]').val()).css("border", "2px solid #333");
    $("input[name=previous_image]").val($(".add-upload-thumbnail").eq($('input[name=already_selected]').val()).attr("src").replace("/nodes/images/", ""));
}
if ($('#upload').is(':checked')) {
  $("input[name=upload_input]").show();
  $("input[name=url_box]").hide();
  $(".add-upload-thumbnail").hide();
uploadType = "upload";
} else if ($('#outside').is(':checked')) {
  $("input[name=url_box]").show();
  $("input[name=upload_input]").hide();
  $(".add-upload-thumbnail").hide();
} else if ($('#previous').is(':checked')) {
  $(".add-upload-thumbnail").show();
  $("input[name=upload_input]").hide();
  $("input[name=url_box]").hide();
}
$('input[name=image_src]').click(function(){
  if ($(this).val() == "upload") {
    $("input[name=upload_input]").show();
    $("input[name=url_box]").hide();
    $(".add-upload-thumbnail").hide();
    uploadType = "upload";
  } else if ($(this).val() == "outside") {
    $("input[name=url_box]").show();
    $("input[name=upload_input]").hide();
    $(".add-upload-thumbnail").hide();
  } else if ($(this).val() == "previous") {
    $(".add-upload-thumbnail").show();
    $("input[name=upload_input]").hide();
    $("input[name=url_box]").hide();
  }
});

$(".add-upload-thumbnail").click(function(){
  $(".add-upload-thumbnail").css("border", "0");
  $(this).css("border", "2px solid #333");
  $("input[name=previous_image]").val($(this).attr("src").replace("/nodes/images/", ""));
  $("input[name=already_selected]").val($(".add-upload-thumbnail").index(this));
});

var removeAttributeForm = function(identifier) {
	$("#attribute-form-" + identifier).remove();
	$("#hidden-attribute-form-" + identifier).remove();
};
var attributeCounter = $('.buttoned-input').length;
$("input[name='add_attribute']").click(function() {
	attributeCounter++;
	$("#attribute-container").append('<div id="attribute-form-' + attributeCounter + '"><input name="attribute_names[]" class="buttoned-input" type="text" placeholder="Attribute Name" /><div onclick="removeAttributeForm(' + attributeCounter + ')" class="remove-button">X</div><textarea name="attribute_contents[]" class="short-textarea" placeholder="Attribute Content"></textarea><br /><br /></div>');
	$("#hidden-attribute-container").append('<div id="hidden-attribute-form-' + attributeCounter + '"><input name="hidden_attribute_names[]" type="hidden" /><input type="hidden" name="hidden_attribute_contents[]" /></div>');
});