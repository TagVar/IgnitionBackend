<?php
  $home_directory = "bookkeeping";
  $button_value = "Bookkeeping";
  $install_button = "Install Package: Bookkeeping";
  $install_directory = "/config/install.php";

  $bookkeeping_user_table = "";
  $bookkeeping_client_table = "";
  if (($bookkeeping_user_table == "") || ($bookkeeping_client_table == "")) {
    $requires_install = true;
  } else {
    $requires_install = false;
  }
 ?>
