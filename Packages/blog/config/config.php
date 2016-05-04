<?php
  $home_directory = "blog";
  $button_value = "Blog Administration";
  $install_button = "Install Package: Blog";
  $install_directory = "/config/install.php";

  $blog_table = "";
  if ($blog_table == "") {
      $requires_install = true;
  } else {
    $requires_install = false;
  }

 ?>
