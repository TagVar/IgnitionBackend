<?php
$directory_list = scandir(dirname(__FILE__) . "/../packages");
$known_directories = [
  ".",
  ".."
];
$unknown_directories = array_diff($directory_list, $known_directories);
$installed_packages = [];
foreach ($unknown_directories as $directory) {
  if (file_exists(dirname(__FILE__) . "/../packages/" . $directory . "/config/config.php")) {
    include(dirname(__FILE__) . "/../packages/" . $directory . "/config/config.php");
    $package_information = [
      str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(__FILE__) . "/../packages/") . $home_directory,
      $button_value,
      $install_directory,
      $install_button,
      $requires_install,
      $home_directory
    ];
    $installed_packages[] = $package_information;
  }
}
$login_table_exists = $connection->query('select 1 from `logins` LIMIT 1');
if($login_table_exists === FALSE) {
  $connection->query('CREATE TABLE `logins` (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username TEXT,
    password TEXT,
    permissions TEXT,
    email TEXT,
    name TEXT,
    phone TEXT
  )');
}
?>
