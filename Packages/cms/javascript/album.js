$(document.body).on('mouseenter', '.thumbnail-container', function(){
	$(this).children(".remove-thumbnail").show();
        $(this).children(".edit-thumbnail").show();
});
$(document.body).on('mouseleave', '.thumbnail-container', function(){
	$(this).children(".remove-thumbnail").hide();
        $(this).children(".edit-thumbnail").hide();
});
$(document.body).on('click', '.remove-thumbnail', function() {
    var confirmDelete = confirm('Are you sure you wish to remove this image from the album?');
    if (confirmDelete) {
        var deleteIndex = $('.thumbnail-container').index($(this).closest('.thumbnail-container'));
        var albumIdentifier = $('input[name=which]').val();
        var linkToParent = $(this).parent();
        var imageCount = $('.thumbnail-container').length;
        $.post("../php/albums.php", {album: albumIdentifier, which: deleteIndex}, function(result){
            if (result == "True") {
                if ((imageCount - 1) == 0) {
                    linkToParent.replaceWith("There are no images in this album.");
                } else {
                    linkToParent.remove();
                }
            } else {
                alert("An error occured; please try again.");
            }
        });
    }
});
$(document.body).on('click', '.edit-thumbnail', function() {
    var imageIndex = $('.thumbnail-container').index($(this).closest('.thumbnail-container'));
    var albumIdentifier = $('input[name=which]').val();
    window.location.href = "./add.php?which=" + albumIdentifier + "&image=" + imageIndex;
});