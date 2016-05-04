<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
if (isset($_POST["save_settings"])) {
  $stripe_secret_value = $_POST["stripe_secret"];
  $stripe_public_value = $_POST["stripe_public"];
  $use_stripe_value = $_POST["use_stripe"];
  $config_path = "../config/config.php";
  $config_contents = file_get_contents($config_path);
  $ecom_path = $_SERVER['DOCUMENT_ROOT'] . "/nodes/config/ecommerce_config.php";
  $ecom_contents = file_get_contents($ecom_path);
  if ($use_stripe_value == "use") {
    $use_stripe_checked = "checked";
    $new_use_stripe = "true";
    $config_contents = str_replace('$stripe_secret_key = "' . $stripe_secret_key . '";', '$stripe_secret_key = "' . $stripe_secret_value . '";', $config_contents);
    $config_contents = str_replace('$stripe_public_key = "' . $stripe_public_key . '";', '$stripe_public_key = "' . $stripe_public_value . '";', $config_contents);
    $ecom_contents = str_replace('$stripe_secret_key = "' . $stripe_secret_key . '";', '$stripe_secret_key = "' . $stripe_secret_value . '";', $ecom_contents);
    $ecom_contents = str_replace('$stripe_public_key = "' . $stripe_public_key . '";', '$stripe_public_key = "' . $stripe_public_value . '";', $ecom_contents);
  } else {
    $new_use_stripe = "false";
    $config_contents = str_replace('$stripe_secret_key = "' . $stripe_secret_key . '";', '$stripe_secret_key = "";', $config_contents);
    $config_contents = str_replace('$stripe_public_key = "' . $stripe_public_key . '";', '$stripe_public_key = "";', $config_contents);
    $ecom_contents = str_replace('$stripe_secret_key = "' . $stripe_secret_key . '";', '$stripe_secret_key = "";', $ecom_contents);
    $ecom_contents = str_replace('$stripe_public_key = "' . $stripe_public_key . '";', '$stripe_public_key = "";', $ecom_contents);
  }
  $config_contents = str_replace('$use_stripe = ' . var_export($use_stripe, true) . ";", '$use_stripe = ' . $new_use_stripe . ";", $config_contents);
  $ecom_contents = str_replace('$use_stripe = ' . var_export($use_stripe, true) . ";", '$use_stripe = ' . $new_use_stripe . ";", $ecom_contents);
  file_put_contents($config_path, $config_contents);
  file_put_contents($ecom_path, $ecom_contents);
  header("location: ../");
} elseif ($use_stripe === true) {
  $use_stripe_checked = "checked";
  $stripe_secret_value = $stripe_secret_key;
  $stripe_public_value = $stripe_public_key;
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/ecom.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <form action="" method="post">
        <div class="heading">
          Configure Ecommerce Settings
        </div>
        <br />
        <input type="checkbox" value="use" name="use_stripe" <?php echo $use_stripe_checked; ?>/> Use Stripe to Process Payments
        <div id="stripe-inputs">
          <input type="text" name="stripe_secret" value="<?php echo $stripe_secret_value; ?>" placeholder="Secret Key"/>
          <input type="text" name="stripe_public" value="<?php echo $stripe_public_value; ?>" placeholder="Publishable Key"/>
        </div>
        <input name="save_settings" type="submit" value="Save Settings">
      </form>
      <form action="../">
        <input type="submit" value="Back to Ecommerce Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/stripe_keys.js"></script>
  </body>
</html>
