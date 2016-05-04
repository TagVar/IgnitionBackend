$("#image-alert").hide();
$("input[name=upload_input]").hide();
$(".upload-thumbnail").hide();
if (!window.location.origin) {
  window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
}
var uploadType = "outside";
var previousUpload = "";
if (!$("input[name='requires_shipping']").is(":checked")) {
	$("input[name='shipping_cost']").hide();
}
$("input[name='requires_shipping']").click(function() {
  $("input[name='shipping_cost']").toggle();
});
if ($("input[name='unlimited_stock']").is(":checked")) {
  $("input[name='stock']").hide();
}
$("input[name='unlimited_stock']").click(function() {
  $("input[name='stock']").toggle();
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
var pictureCount = 0;
$(window).load(function () {
    pictureCount += $(".thumbnail-container").length;
    $("#add-picture").toggle(pictureCount != 5);
});
var addImageFilename = function(imagePath) {
	var newImagePath = imagePath.replace(">", "&#62;").replace(":", "&#x0003A;");
	if ($("input[name=image_filenames]").val().indexOf(">") == -1) {
		if ($("input[name=image_filenames]").val() != "") {
			$("input[name=image_filenames]").val(newImagePath + ">" + $("input[name=image_filenames]").val());
		} else {
			$("input[name=image_filenames]").val(newImagePath);
		}
	} else {
		var imagePathArray = $("input[name=image_filenames]").val().split(">");
		imagePathArray.unshift(newImagePath);
		var newImageString = imagePathArray.join(">");
		$("input[name=image_filenames]").val(newImageString);
	}
};
var removeImageFilename = function(containerIndex) {
	if ($("input[name=image_filenames]").val().indexOf(">") == -1) {
		$("input[name=image_filenames]").val("");
	} else {
		var imagePathArray = $("input[name=image_filenames]").val().split(">");
		imagePathArray.splice(containerIndex, 1);
		var newImageString = imagePathArray.join(">");
		$("input[name=image_filenames]").val(newImageString);
	}
};
var makeMainImage = function(imagePathIndex) {
	var imagePathNoMain = $("input[name=image_filenames]").val().replace(":main", "");
	if (imagePathNoMain.indexOf(">") == -1) {
		$("input[name=image_filenames]").val(imagePathNoMain + ":main");
	} else {
		var imagePathArray = imagePathNoMain.split(">");
		imagePathArray[imagePathIndex] = imagePathArray[imagePathIndex] + ":main";
		var newImageString = imagePathArray.join(">");
		$("input[name=image_filenames]").val(newImageString);
	}
};
var removeAddButton = function (operator) {
    if (operator == "+") {
        pictureCount++;
    } else if (operator == "-") {
        pictureCount--;
    }
    $("#add-picture").toggle(pictureCount != 5);
};

$(document.body).on('click', '.preview-thumbnail', function(){
	$(".thumbnail-container").css("border", "0");
	$(this).parent().css("border", "5px solid #006bfa");
	makeMainImage($(this).parent().index());
});
$(document.body).on('mouseenter', '.thumbnail-container', function(){
	$(this).children(".remove-thumbnail").show();
});
$(document.body).on('mouseleave', '.thumbnail-container', function(){
	$(this).children(".remove-thumbnail").hide();
});
$(document.body).on('click', '.remove-thumbnail', function(){
	removeImageFilename($(this).parent().index());
	$(this).parent().remove();
	removeAddButton("-");
});

$("#add-picture").click(function() {
	$("#image-alert").show();
});
$('input[name=image_src]').click(function(){
  if ($(this).val() == "upload") {
    $("input[name=upload_input]").show();
    $("input[name=url_box]").hide();
    $(".upload-thumbnail").hide();
    uploadType = "upload";
  } else if ($(this).val() == "outside") {
    $("input[name=url_box]").show();
    $("input[name=upload_input]").hide();
    $(".upload-thumbnail").hide();
    uploadType = "outside";
  } else if ($(this).val() == "previous") {
    $(".upload-thumbnail").show();
    $("input[name=upload_input]").hide();
    $("input[name=url_box]").hide();
    uploadType = "previous";
  }
});

$("button[name=cancel_image_upload]").click(function(){
  $("#image-alert").hide();
});

var uploadAlert = function(input) {
  $("#upload").prop("checked", true);
  $("input[name=upload_input]").show();
  $("input[name=url_box]").hide();
  $(".upload-thumbnail").hide();
  uploadType = "upload";
  alert(input);
};

$(".upload-thumbnail").click(function(){
  $(".upload-thumbnail").css("border", "0");
  $(this).css("border", "2px solid #333");
  previousUpload = $(this).attr("src");
});

$("form[name=image_form]").submit(function(event){
  if (uploadType == "upload") {
    var image_post_requires_shipping;
    var image_post_unlimited_stock;
    if ($("input[name=requires_shipping]").is(":checked")) {
      image_post_requires_shipping = "1";
    } else {
      image_post_requires_shipping = "0";
    }
    if ($("input[name=image_post_unlimited_stock]").is(":checked")) {
      image_post_unlimited_stock = "1";
    } else {
      image_post_unlimited_stock = "0";
    }
    $("input[name=image_post_product_name]").val($("input[name=product_name]").val());
    $("input[name=image_post_cost]").val($("input[name=cost]").val());
    $("input[name=image_post_requires_shipping]").val(image_post_requires_shipping);
    $("input[name=image_post_shipping_cost]").val($("input[name=shipping_cost]").val());
    $("input[name=image_post_product_caption]").val($("textarea[name=product_caption]").val());
    $("input[name=image_post_product_description]").val($("textarea[name=product_description]").val());
    $("input[name=image_post_stock]").val($("input[name=stock]").val());
    $("input[name=image_post_unlimited_stock]").val(image_post_unlimited_stock);
    $("input[name=image_post_image_filenames]").val($("input[name=image_filenames]").val());
  	for(var i = 1; i <= attributeCounter; i++) {
  		$("#hidden-attribute-form-" + i + " >  input[name='hidden_attribute_names[]']").val($("#attribute-form-" + i + " >  input[name='attribute_names[]']").val());
  		$("#hidden-attribute-form-" + i + " >  input[name='hidden_attribute_contents[]']").val($("#attribute-form-" + i + " >  textarea[name='attribute_contents[]']").val());
  	}
  } else if (uploadType == "outside") {
    event.preventDefault();
    if ($("input[name=url_box]").val().replace(" ", "") != "") {
	  $("#picture-container").prepend('<div class="thumbnail-container"><img class="preview-thumbnail" src="' + $("input[name=url_box]").val() + '" /><div class="remove-thumbnail">X</div></div>');
	  addImageFilename($("input[name=url_box]").val());
	  $("input[name=url_box]").val("");
	  $("#image-alert").hide();
	  removeAddButton("+");
    } else {
      alert("You did not enter an URL.");
    }
  } else if (uploadType == "previous") {
    event.preventDefault();
    if (previousUpload.replace(" ", "") != "") {
      $("#picture-container").prepend('<div class="thumbnail-container"><img class="preview-thumbnail" src="' + document.location.origin + previousUpload + '" /><div class="remove-thumbnail">X</div></div>');
      $(".upload-thumbnail").css("border", "0");
      addImageFilename(document.location.origin + previousUpload);
      previousUpload = "";
      $("#image-alert").hide();
      removeAddButton("+");
    } else {
      alert("You did not select a picture.");
    }
  }
});

$(document).ready(function() {
    if ($("input[name=image_post_name]").val() != "") {
      $("#picture-container").prepend('<div class="thumbnail-container"><img class="preview-thumbnail" src="' + document.location.origin + "/nodes/images/" + $("input[name=image_post_name]").val() + '" /><div class="remove-thumbnail">X</div></div>');
      addImageFilename(document.location.origin + "/nodes/images/" + $("input[name=image_post_name]").val());
    } else if ($("input[name=image_reshow]").val() == "true") {
      $("#image-alert").show();
    }
});
