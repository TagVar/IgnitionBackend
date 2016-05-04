<?php
  $home_directory = "newsletter";
  $button_value = "Newsletter Administration";
  $install_button = "Install Package: Newsletter";
  $install_directory = "/config/install.php";

  $newsletter_table = "";
  if ($newsletter_table == "") {
    $requires_install = true;
  } else {
    $requires_install = false;
  }

 ?>
