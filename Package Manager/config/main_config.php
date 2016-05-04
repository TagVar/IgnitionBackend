<?php

  $username = "";
  $password = password_hash('', PASSWORD_DEFAULT);

  $title = "";

  $sql_host = "";
  $sql_username = "";
  $sql_password = "";
  $sql_database = "";

  $connection = new mysqli($sql_host, $sql_username, $sql_password, $sql_database);

?>
