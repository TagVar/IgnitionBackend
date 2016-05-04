<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  include("../config/config.php");
  if (isset($_GET["edit"])) {
    $is_edit = true;
    $identifier = $_GET["which"];
  } else {
    $is_edit = false;
  }
  $operator_array = [
    "Reduce by Percentage" => "",
    "Reduce by Fixed Amount" => "",
    "Free With Purchase" => "",
    "Buy One Get One Free" => "",
    "Free Shipping" => ""
  ];
  $conditions_array = [
    "Total Order Cost Must Be Over Amount" => "",
    "Order Must Contain Item" => "",
    "Order Must Contain More Than A Certain Number of Items" => "",
    "Applies to Category" => ""
  ];
  if ($is_edit === true) {
    $code_query = "SELECT * FROM `" . $ecom_tables['promo'] . "` WHERE id=$identifier";
    $code_result = $connection->query($code_query);
    $code = $code_result->fetch_assoc();
    $promo_code_value = $code["code"];
    $operator_value = $code["operation"];
    $operator_array[$operator_value] = "selected";
    $post_variable_value = $code["variable"];
    $has_condition_value = $code["has-condition"];
    if ($has_condition_value == "1") {
      $has_condition_checked = "checked";
    }
    $condition_value = $code["condition"];
    if (trim($condition_value) != "") {
      $conditions_array[$condition_value] = "selected";
    }
    $condition_variable_value = $code["condition-variable"];
    $has_expiration_value = $code["has-expiration"];
    $expiration_date_value = date('m-d-Y', strtotime($code["expiration-date"]));
    if ($has_expiration_value == "1") {
      $has_expiration_checked = "checked";
    } else {
      $expiration_date_value = "";
    }
    $stackable_value = $code["stackable"];
    if ($stackable_value == "1") {
      $stackable_checked = "checked";
    }
    $single_use_value = $code["single-use"];
    if ($single_use_value == "1") {
      $single_use_checked = "checked";
    }
  }
  if (isset($_POST["submit_code"])) {
    $promo_code_value = $_POST["promo_code"];
    $operator_value = $_POST["operation"];
    foreach($operator_array as $operator_key => $operator_selected) {
      $operator_array[$operator_key] = "";
    }
    $operator_array[$operator_value] = "selected";
    $post_variable_value = $_POST["variable_value"];
    $has_condition_value = $_POST["has_condition"];
    if ($has_condition_value == "1") {
      $has_condition_checked = "checked";
    }
    $condition_value = $_POST["condition"];
    foreach($conditions_array as $condition_key => $condition_selected) {
      $conditions_array[$condition_key] = "";
    }
    $conditions_array[$condition_value] = "selected";
    $condition_variable_value = $_POST["condition_variable"];
    $has_expiration_value = $_POST["has_expiration"];
    if ($has_expiration_value == "1") {
      $has_expiration_checked = "checked";
    }
    $expiration_date_value = $_POST["expiration_date"];
    $stackable_value = $_POST["stackable"];
    if ($stackable_value == "1") {
      $stackable_checked = "checked";
    }
    $single_use_value = $_POST["single_use"];
    if ($single_use_value == "1") {
      $single_use_checked = "checked";
    }
    function validate_currency($currency_input) {
      $currency_parts = explode(".", $currency_input);
      if (ctype_digit($currency_parts[0])) {
        if ((ctype_digit(trim($currency_parts[1]))) && (strlen(trim($currency_parts[1])) == 2)) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }
    function item_exists($item_name, $table, $function_connection) {
      $item_exists_result = $function_connection->query("SELECT * FROM " . $table . " WHERE `product` = '" . $item_name . "'");
      if (mysqli_num_rows($item_exists_result) > 0) {
        return true;
      } else {
        return false;
      }
    }
    function check_stock($item_name, $integer, $table, $function_connection) {
      $item_result = $function_connection->query("SELECT * FROM " . $table . " WHERE `product` = '" . $item_name . "'");
      $item_data = mysqli_fetch_assoc($item_result);
      if ($item_data["unlimited-stock"] != "1") {
        if ($item_data["stock"] >= $integer) {
          return true;
        } else {
          return false;
        }
      } else {
        return true;
      }
    }
    if (str_replace(" ", "", $promo_code_value) != "") {
      if (!preg_match('/\s/', $promo_code_value)) {
        $duplicate_result = $connection->query("SELECT * FROM " . $ecom_tables["promo"] . " WHERE `code` = '" . $promo_code_value . "'");
        if ($is_edit) {
          if (($promo_code_value == $code["code"]) || (mysqli_num_rows($duplicate_result) <= 0)) {
            $duplicate_validated = true;
          }
        } elseif ($is_edit == false) {
          if (mysqli_num_rows($duplicate_result) <= 0) {
            $duplicate_validated = true;
          }
        }
        if ($duplicate_validated) {
          if (array_search($operator_value, array_keys($operator_array)) == 0) {
            if ((ctype_digit(trim($post_variable_value))) && (intval(trim($post_variable_value)) >= 1) && (intval(trim($post_variable_value)) <= 100)) {
              $operator_validated = true;
            } else {
              $operator_error = "Percentage off must be a natural number between 1 and 100.";
            }
          } elseif (array_search($operator_value, array_keys($operator_array)) == 1) {
            if (validate_currency($post_variable_value)) {
              $operator_validated = true;
            } else {
              $operator_error = "You must supply an amount to reduce in D.CC format.";
            }
          } elseif (array_search($operator_value, array_keys($operator_array)) == 2) {
            if (item_exists($post_variable_value, $ecom_tables["product"], $connection)) {
              if (check_stock($post_variable_value, 1, $ecom_tables["product"], $connection)) {
                $operator_validated = true;
              } else {
                $operator_error = "The item you specified as a free addition to the order is out of stock.";
              }
            } else {
              $operator_error = "The item you specified as a free addition to the order does not exist.";
            }
          } elseif (array_search($operator_value, array_keys($operator_array)) == 3) {
            if (item_exists($post_variable_value, $ecom_tables["product"], $connection)) {
              if (check_stock($post_variable_value, 1, $ecom_tables["product"], $connection)) {
                if (check_stock($post_variable_value, 2, $ecom_tables["product"], $connection)) {
                  $operator_validated = true;
                } else {
                  $operator_error = "The item you specified for buy-one-get-one-free does not have enoguh stock to satisfy the promotional code.";
                }
              } else {
                $operator_error = "The item you specified for buy-one-get-one-free is out of stock.";
              }
            } else {
              $operator_error = "The item you specified for buy-one-get-one-free does not exist.";
            }
          } else {
            $operator_validated = true;
          }
          if ($operator_validated) {
            if ($has_condition_value == "1") {
              if (array_search($condition_value, array_keys($conditions_array)) == 0) {
                if (validate_currency($condition_variable_value)) {
                  $condition_validated = true;
                } else {
                  $condition_error = "You must supply a threshold amount in D.CC format.";
                }
              } elseif (array_search($condition_value, array_keys($conditions_array)) == 1) {
                if (item_exists($condition_variable_value, $ecom_tables["product"], $connection)) {
                  if (check_stock($condition_variable_value, 1, $ecom_tables["product"], $connection)) {
                    $condition_validated = true;
                  } else {
                    $condition_error = "The required item you specified is out of stock.";
                  }
                } else {
                  $condition_error = "The required item you specified does not exist.";
                }
              } elseif (array_search($condition_value, array_keys($conditions_array)) == 2) {
                if (ctype_digit(trim($condition_variable_value))) {
                  $condition_validated = true;
                } else {
                  $condition_error = "You must supply a numeric threshold item amount.";
                }
              } elseif (array_search($condition_value, array_keys($conditions_array)) == 3) {
                $category_exists_result = $connection->query("SELECT * FROM " . $ecom_tables['category'] . " WHERE `category` = '" . $condition_variable_value . "'");
                if (mysqli_num_rows($category_exists_result) > 0) {
                  $condition_validated = true;
                } else {
                  $condition_error = "The required category you specified does not exist.";
                }
              }
            } else {
              $has_condition_value = "0";
              $condition_value = "";
              $condition_variable_value = "";
              $condition_validated = true;
            }
            if ($condition_validated) {
              if ($has_expiration_value == "1") {
                $date_parts = explode("-", $expiration_date_value);
                if ((strlen(trim($date_parts[0])) == 2) && (checkdate($date_parts[0], $date_parts[1], $date_parts[2]))) {
                  if (strtotime($expiration_date_value) > time()) {
                    $expiration_validated = true;
                  } else {
                    $expiration_error = "Please pick a future date.";
                  }
                } else {
                  $expiration_error = "Please supply a valid Gregorian date in MM-DD-YYYY format.";
                }
              } else {
                $has_expiration_value = "0";
                $expiration_date_value = "NULL";
                $expiration_validated = true;
              }
              if ($expiration_validated) {
                if ($stackable_value != "1") {
                  $stackable_value = "0";
                }
                if ($single_use_value != "1") {
                  $single_use_value = "0";
                }
                if ($operator_value == "Free Shipping") {
                  $post_variable_value = "";
                }
                if ($is_edit) {
                  $promo_code_query = "UPDATE " . $ecom_tables["promo"] . " SET `code` = '" . $promo_code_value . "', `condition` = '" . $condition_value . "', `condition-variable` = '" . trim($condition_variable_value) . "', `expiration-date` = '" . date("Y-m-d", strtotime(trim($expiration_date_value))) . "', `has-condition` = '" . $has_condition_value . "', `has-expiration` = '" . $has_expiration_value . "', `operation` = '" . $operator_value . "', `single-use` = '" . $single_use_value . "', `stackable` = '" . $stackable_value . "', `uses` = '0', `variable` = '" . trim($post_variable_value) . "' WHERE `id` = '" . $identifier . "'";
                } elseif ($is_edit === false) {
                  $promo_code_query = "INSERT INTO " . $ecom_tables["promo"] . " (`code`, `condition`, `condition-variable`, `expiration-date`, `has-condition`, `has-expiration`, `operation`, `single-use`, `stackable`, `uses`, `variable`) VALUES ('" . $promo_code_value . "', '" . $condition_value . "', '" . trim($condition_variable_value) . "', '" . date("Y-m-d", strtotime(trim($expiration_date_value))) . "', '" . $has_condition_value . "', '" . $has_expiration_value . "', '" . $operator_value . "', '" . $single_use_value . "', '" . $stackable_value . "', '0', '". trim($post_variable_value) . "')";
                }
                if ($connection->query($promo_code_query)) {
                  header("location: index.php");
                } else {
                  $error = "An error occured. Please try again.";
                }
              } else {
                $error = $expiration_error;
              }
            } else {
              $error = $condition_error;
            }
          } else {
            $error = $operator_error;
          }
        } else {
          $error = "A promotional code with your desired code already exists.";
        }
      } else {
        $error = "Your promotional code cannot contain spaces.";
      }
    } else {
      $error = "You must provide a promotional code.";
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/ecom.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading"><?php if ($is_edit) { echo "Edit Promotional Code"; } elseif ($is_edit === false) { echo "Add Promotional Code"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" value="<?php echo $promo_code_value; ?>" name="promo_code" placeholder="Desired Promotional Code"/>
        <select id="operation" name="operation" >
          <?php
            foreach($operator_array as $operator=>$selected) {
              echo "<option value='" . $operator . "' $selected>$operator</option>\n";
            }
          ?>
        </select>
        <input type="text" value="<?php echo $post_variable_value; ?>" id="variable" name="variable_value" />
        <br />
        <br />
        <input type="checkbox" name="has_condition" value="1" <?php echo $has_condition_checked; ?>/> This Promotional Code Is Conditional
        <div id="conditional-container">
          <select id="condition" name="condition" >
            <?php
              foreach($conditions_array as $condition=>$selected) {
                echo "<option value='" . $condition . "' $selected>$condition</option>\n";
              }
            ?>
          </select>
          <input type="text" value="<?php echo $condition_variable_value; ?>" id="condition_variable" name="condition_variable" />
        </div>
        <br />
        <br />
        <input type="checkbox" name="has_expiration" value="1" <?php echo $has_expiration_checked; ?>/> This Promotional Code Expires
        <input type ="text" value="<?php echo $expiration_date_value; ?>" name="expiration_date" placeholder="Date of Expiration (MM-DD-YYYY)") />
        <br />
        <br />
        <input type="checkbox" name="stackable" value="1" <?php echo $stackable_checked; ?>/> This Promotional Code Can be Used in Combination With Other Promotional Codes
        <br />
        <br />
        <input type="checkbox" name="single_use" value="1" <?php echo $single_use_checked; ?>/> This Promotional Code Can Only be Used Once
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="submit_code" value="<?php if ($is_edit) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add Promotional Code"; } ?>" />
      </form>
      <form action=".">
        <input type="submit" value="Back To Promotional Code Selection">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/promo.js"></script>
  </body>
</html>
