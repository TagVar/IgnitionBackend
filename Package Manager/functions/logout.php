<?php
  session_start();
  $_SESSION["logged_in"] = false;
  $_SESSION["username"] = null;
  $_SESSION["permissions"] = null;
  header('Location: ' . str_replace($_SERVER["DOCUMENT_ROOT"], "", realpath(dirname(__FILE__))) . '/../');
?>
