$("#image-alert").hide();
$("input[name=upload_input]").hide();
$(".upload-thumbnail").hide();
var uploadType = "outside";
var previousUpload = "";
$("#content-styling-bar td").click(function() {
  if ($(this).text().trim() == "Bold") {
    $("#content").val($("#content").val() +"<b></b>");
  } else if ($(this).text().trim() == "Italic") {
    $("#content").val($("#content").val() +"<i></i>");
  } else if ($(this).text().trim() == "Underline") {
    $("#content").val($("#content").val() +"<u></u>");
  } else if ($(this).text().trim() == "Main Heading") {
   $("#content").val($("#content").val() +"<h1></h1>");
 } else if ($(this).text().trim() == "Sub Heading") {
   $("#content").val($("#content").val() +"<h2></h2>");
 } else if ($(this).text().trim() == "Link") {
   $("#content").val($("#content").val() +'<a href=""></a>');
 } else if ($(this).text().trim() == "Unordered List") {
   $("#content").val($("#content").val() + '<ul><li></li></ul>');
 } else if ($(this).text().trim() == "Ordered List") {
   $("#content").val($("#content").val() + '<ol><li></li></ol>');
 } else if ($(this).text().trim() == "List Item") {
   $("#content").val($("#content").val() + '<li></li>');
 } else if ($(this).text().trim() == "Quote") {
   $("#content").val($("#content").val() +'<blockquote></blockquote>');
 } else if ($(this).text().trim() == "Image") {
   $("#image-alert").show();
 }
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
}

$(".upload-thumbnail").click(function(){
  $(".upload-thumbnail").css("border", "0");
  $(this).css("border", "2px solid #333");
  previousUpload = $(this).attr("src");
});

$("form[name=image_form]").submit(function(event){
  if (uploadType == "upload") {
    $("input[name=image_post_title]").val($("input[name=title]").val());
    $("input[name=image_post_description]").val($("textarea[name=description]").val());
    $("input[name=image_post_content]").val($("textarea[name=content]").val());
  } else if (uploadType == "outside") {
    event.preventDefault();
    if ($("input[name=url_box]").val().replace(" ", "") != "") {
      $("#content").val($("#content").val() +'<img src="' + $("input[name=url_box]").val() + '" />');
      $("input[name=url_box]").val("");
      $("#image-alert").hide();
    } else {
      alert("You did not enter an URL.");
    }
  } else if (uploadType == "previous") {
    event.preventDefault();
    if (previousUpload.replace(" ", "") != "") {
      $("#content").val($("#content").val() +'<img src="' + previousUpload + '" />');
      $(".upload-thumbnail").css("border", "0");
      previousUpload = "";
      $("#image-alert").hide();
    } else {
      alert("You did not select a picture.");
    }
  }
});
$(document).ready(function() {
    if ($("input[name=image_post_name]").val() != "") {
      $("#content").val($("#content").val() +'<img src="/nodes/images/' + $("input[name=image_post_name]").val() + '" />');
    } else if ($("input[name=image_reshow]").val() == "true") {
      $("#image-alert").show();
    }
});
