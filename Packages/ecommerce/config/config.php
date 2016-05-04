<?php
  $home_directory = "ecommerce";
  $button_value = "Ecommerce Administration";
  $install_button = "Install Package: Ecommerce";
  $install_directory = "/config/install.php";

  $ecom_tables = [];

  if (count($ecom_tables) < 4) {
    $requires_install = true;
  } else {
    $requires_install = false;
  }

  $use_stripe = "";
  $stripe_secret_key = "";
  $stripe_public_key = "";
 ?>
