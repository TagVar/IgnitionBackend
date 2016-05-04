<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  if (isset($_POST["install_submit"])) {
    $use_stripe_value = $_POST["use_stripe"];
    $secret_key_value = $_POST["secret_key"];
    $publishable_key_value = $_POST["publishable_key"];
    if ($use_stripe_value == "use") {
      $use_stripe_checked = "checked";
      $use_stripe_config = "true";
    } else {
      $use_stripe_config = "false";
    }
    $requested_tables = [
      "product_category_table_name" => $_POST["product_category_table_name"],
      "product_table_name" => $_POST["product_table_name"],
      "promo_table_name" => $_POST["promo_table_name"],
      "records_table_name" => $_POST["records_table_name"]
    ];
    $error = [];
    $requested_tables_count = array_count_values($requested_tables);
    foreach($requested_tables as $key => $requested_table_name) {
      $table_exists_query = "SELECT 1 FROM `$requested_table_name` LIMIT 1";
      $table_exists_result = $connection->query($table_exists_query);
      if($table_exists_result !== false) {
        $error[$key] = "The table name you specified already exists.";
      } elseif (strlen($requested_table_name) > 50) {
        $error[$key] = "The table name you specified is longer than 50 characters";
      } elseif (str_replace(" ", "", $requested_table_name) == "") {
        $error[$key] = "You must provide a table name.";
      } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $requested_table_name)) {
        $error[$key] = "The table name you specified contained illegal characters";
      } elseif($requested_tables_count[$requested_table_name] > 1) {
        $error[$key] = "Each table name must be unique.";
      }
    }
    if (empty($error)) {
      $table_creation_queries = [
        "product_category" =>
          'CREATE TABLE `' . $requested_tables["product_category_table_name"] . '` (
          id INT(11) AUTO_INCREMENT PRIMARY KEY,
          `category` TEXT CHARACTER SET binary,
          `products` TEXT
          )',
        "product" =>
          'CREATE TABLE `' . $requested_tables["product_table_name"] . '` (
          id INT(11) AUTO_INCREMENT PRIMARY KEY,
          `product` TEXT,
          `category` TEXT,
          `images` TEXT,
          `caption` TEXT,
          `description` TEXT,
          `reviews` TEXT,
          `cost` DECIMAL(6,2),
          `requires-shipping` TINYINT(1),
          `shipping` DECIMAL(6,2),
          `attribute` TEXT,
          `stock` INT(11),
          `unlimited-stock` TINYINT(1)
          )',
        "promo" =>
          'CREATE TABLE `' . $requested_tables["promo_table_name"] . '` (
          id INT(11) AUTO_INCREMENT PRIMARY KEY,
          `code` TEXT,
          `operation` TEXT,
          `variable` TEXT,
          `uses` INT(11),
          `has-expiration` TINYINT(1),
          `has-condition` TINYINT(1),
          `expiration-date` DATE,
          `stackable` TINYINT(1),
          `single-use` TINYINT(1),
          `condition` TEXT,
          `condition-variable` TEXT
          )',
        "records" =>
          'CREATE TABLE `' . $requested_tables["records_table_name"] . '` (
          id INT(11) AUTO_INCREMENT PRIMARY KEY,
          `name` TEXT,
          `email` TEXT,
          `product-names` TEXT,
          `payment` DECIMAL(12,2),
          `payment-date` DATE,
          `refunded` TINYINT(1),
          `address` TEXT,
          `refund-reason` TINYINT(1),
          `refund-amount` DECIMAL(6,2),
          `refund-date` DATE,
          `stripe-charge-id` TEXT,
          `shipped` TINYINT(1),
          `shipping-date` DATE,
          `shipping-cost` DECIMAL(6,2),
          `tracking` TEXT,
          `payment-provider` TINYINT(1),
          `order-number` INT(11)
          )'
      ];
      $failed_table_creations = 0;
      foreach($table_creation_queries as $table_creation_query) {
        if (!$connection->query($table_creation_query)) {
          $failed_table_creations++;
        }
      }
      if ($failed_table_creations == 0) {
        $config_file = "config.php";
        $new_table_array = '$ecom_tables = [
          "category" => "' . $requested_tables["product_category_table_name"] . '",
          "product" => "' . $requested_tables["product_table_name"] . '",
          "promo" => "' . $requested_tables["promo_table_name"] . '",
          "records" => "' . $requested_tables["records_table_name"] . '"
  ];';
        if ($use_stripe_value == "use") {
          file_put_contents($config_file, str_replace('$use_stripe = "";', '$use_stripe = ' . $use_stripe_config . ';', str_replace('$ecom_tables = [];', $new_table_array, str_replace('$stripe_secret_key = ""', '$stripe_secret_key = "' . $secret_key_value . '"', str_replace('$stripe_public_key = ""', '$stripe_public_key = "' . $publishable_key_value . '"', file_get_contents($config_file))))));
        } else {
          file_put_contents($config_file, str_replace('$use_stripe = "";', '$use_stripe = ' . $use_stripe_config . ';', str_replace('$ecom_tables = [];', $new_table_array, file_get_contents($config_file))));
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes")) {
          mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes");
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/nodes/images")) {
          mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/images");
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php")) {
          if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/nodes/config")) {
            mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/config");
          }
          $node_config_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/config/config.php", "w");
          $node_config_file_contents = '<?php
  $node_sql_host = "' . $sql_host . '";
  $node_sql_username = "' . $sql_username . '";
  $node_sql_password = "' . $sql_password . '";
  $node_sql_database = "' . $sql_database . '";
  $node_connection = new mysqli($node_sql_host, $node_sql_username, $node_sql_password, $node_sql_database);
?>';
          fwrite($node_config_file, $node_config_file_contents);
          fclose($node_config_file);
        }
        $node_file = fopen($_SERVER['DOCUMENT_ROOT'] . "/nodes/ecommerce.php", "w");
        $node_file_contents = '
<?php
include($_SERVER["DOCUMENT_ROOT"] . "/nodes/config/config.php");
include($_SERVER["DOCUMENT_ROOT"] . "/nodes/config/ecommerce_config.php");
function add_product($product, $params = []) {
  global $node_connection;
  $defaults = ["reference" => "product", "quantity" => "1", "cookie-name" => md5($_SERVER["SERVER_ADDR"]), "key" => md5($_SERVER["SERVER_ADDR"])];
  $params = array_merge($defaults, $params);
  if (ctype_digit($params["quantity"])) {
    $item_exists = $node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] . '` WHERE `" . $params["reference"] . "` = \'" . $product . "\'");
    if (mysqli_num_rows($item_exists) > 0) {
      $item_data = mysqli_fetch_assoc($item_exists);
      $new_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
      $new_product = $item_data["id"] . ":" . $params["quantity"];
      if (isset($_COOKIE[$params["cookie-name"]])) {
        $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
        $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
        $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
        $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
        if (strpos($decrypted_data, ">") !== false) {
          $current_cookie_array = explode(">", $decrypted_data);
        } else {
          $current_cookie_array = [$decrypted_data];
        }
      } else {
        $current_cookie_array = array();
      }
      $no_quantity_cookie_array = array();
      foreach($current_cookie_array as $cookie_key => $cookie_item) {
        $cookie_item_array = explode(":", $cookie_item);
        $no_quantity_cookie_array[$cookie_key] = $cookie_item_array[0];
      }
      if (in_array($item_data["id"], $no_quantity_cookie_array)) {
        $product_key = array_search($item_data["id"], $no_quantity_cookie_array);
        $current_product = explode(":", $current_cookie_array[$product_key]);
        $current_product[1] = $current_product[1] + $params["quantity"];
        $updated_product = implode(":", $current_product);
        $current_cookie_array[$product_key] = $updated_product;
      } else {
        $current_cookie_array[] = $new_product;
      }
      $new_cookie = base64_encode($new_iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $params["key"], implode(">", $current_cookie_array), MCRYPT_MODE_CBC, $new_iv));
      setcookie($params["cookie-name"], $new_cookie, time() + 2592000);
      return true;
    } else {
      return "E2";
    }
  } else {
    return "E1";
  }
}
function delete_product($product, $params = []) {
  global $node_connection;
  $defaults = ["reference" => "product", "cookie-name" => md5($_SERVER["SERVER_ADDR"]), "key" => md5($_SERVER["SERVER_ADDR"])];
  $params = array_merge($defaults, $params);
  $item_data = mysqli_fetch_assoc($node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] . '` WHERE `" . $params["reference"] . "` = \'" . $product . "\'"));
  if (isset($_COOKIE[$params["cookie-name"]])) {
    $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
    $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
    $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
    $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
    if (strpos($decrypted_data, ">") !== false) {
      $current_cookie_array = explode(">", $decrypted_data);
    } else {
      $current_cookie_array = [$decrypted_data];
    }
    $no_quantity_cookie_array = array();
    foreach($current_cookie_array as $cookie_key => $cookie_item) {
      $cookie_item_array = explode(":", $cookie_item);
      $no_quantity_cookie_array[$cookie_key] = $cookie_item_array[0];
    }
    if (in_array($item_data["id"], $no_quantity_cookie_array)) {
      $product_key = array_search($item_data["id"], $no_quantity_cookie_array);
      unset($current_cookie_array[$product_key]);
      $new_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
      $new_cookie = base64_encode($new_iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $params["key"], implode(">", $current_cookie_array), MCRYPT_MODE_CBC, $new_iv));
      setcookie($params["cookie-name"], $new_cookie, time() + 2592000);
      return true;
    } else {
      return "E2";
    }
  } else {
    return "E1";
  }
}
function set_quantity($product, $quantity, $params = []) {
  global $node_connection;
  $defaults = ["reference" => "product", "cookie-name" => md5($_SERVER["SERVER_ADDR"]), "key" => md5($_SERVER["SERVER_ADDR"])];
  $params = array_merge($defaults, $params);
  $item_data = mysqli_fetch_assoc($node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] . '` WHERE `" . $params["reference"] . "` = \'" . $product . "\'"));
  if (isset($_COOKIE[$params["cookie-name"]])) {
    $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
    $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
    $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
    $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
    if (strpos($decrypted_data, ">") !== false) {
      $current_cookie_array = explode(">", $decrypted_data);
    } else {
      $current_cookie_array = [$decrypted_data];
    }
    $no_quantity_cookie_array = array();
    foreach($current_cookie_array as $cookie_key => $cookie_item) {
      $cookie_item_array = explode(":", $cookie_item);
      $no_quantity_cookie_array[$cookie_key] = $cookie_item_array[0];
    }
    if (in_array($item_data["id"], $no_quantity_cookie_array)) {
      if (ctype_digit($quantity)) {
        $product_key = array_search($item_data["id"], $no_quantity_cookie_array);
        $current_product = explode(":", $current_cookie_array[$product_key]);
        $current_product[1] = $quantity;
        $current_cookie_array[$product_key] = implode(":", $current_product);
        $new_iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
        $new_cookie = base64_encode($new_iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $params["key"], implode(">", $current_cookie_array), MCRYPT_MODE_CBC, $new_iv));
        setcookie($params["cookie-name"], $new_cookie, time() + 2592000);
        return true;
      } else {
        return "E3";
      }
    } else {
      return "E2";
    }
  } else {
    return "E1";
  }
}
function render_items($template, $params) {
  global $node_connection;
  $defaults = ["cookie-name" => md5($_SERVER["SERVER_ADDR"]), "key" => md5($_SERVER["SERVER_ADDR"]), "in-stock" => "In Stock", "out-of-stock" => "Out Of Stock", "limited-stock" => "Limited Stock - {{ stock-quantity }} In Stock"];
  $params = array_merge($defaults, $params);
  if (isset($_COOKIE[$params["cookie-name"]])) {
    $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
    $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
    $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
    $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
    if (strpos($decrypted_data, ">") !== false) {
      $current_cookie_array = explode(">", $decrypted_data);
    } else {
      $current_cookie_array = [$decrypted_data];
    }
    $no_quantity_cookie_array = array();
    foreach($current_cookie_array as $cookie_key => $cookie_item) {
      $cookie_item_array = explode(":", $cookie_item);
      $no_quantity_cookie_array[$cookie_key] = $cookie_item_array[0];
    }
    $final_render = "";
    foreach($no_quantity_cookie_array as $item_to_render_key => $item_to_render) {
      $item_data = mysqli_fetch_assoc($node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] . '` WHERE `id` = \'" . $item_to_render . "\'"));
      $product_image_array = explode(">", $item_data["images"]);
      foreach($product_image_array as $image_name) {
        if (strpos($image_name, \':main\') !== false) {
          $main_image_source = str_replace(":main", "", $image_name);
        }
      }
      $item_render = str_replace("{{ name }}", $item_data["product"], $template);
      $item_render = str_replace("{{ cost }}", $item_data["cost"], $item_render);
      $item_render = str_replace("{{ id }}", $item_data["id"], $item_render);
      $item_render = str_replace("{{ caption }}", $item_data["caption"], $item_render);
      $item_render = str_replace("{{ main-image }}", "<img src=\'" . $main_image_source . "\' alt=\'" . $item_data["product"] . "\'/>", $item_render);
      $item_to_render_array = explode(":", $cookie_item_array[$item_to_render_key]);
      $item_to_render_quantity = $item_to_render_array[1];
      if (($item_data["stock"] >= $item_to_render_quantity) || ($item_data["unlimited-stock"] == "1")) {
        $item_render = str_replace("{{ stock }}", $params["in-stock"], $item_render);
      } elseif (($item_data["stock"] > 0) && ($item_data["stock"] < $item_to_render_quantity)) {
        $item_render = str_replace("{{ stock }}", $params["limited-stock"], $item_render);
      } else {
        $item_render = str_replace("{{ stock }}", $params["out-of-stock"], $item_render);
      }
      if ($item_data["unlimited-stock"] == "1") {
        $item_render = str_replace("{{ stock-quantity }}", "Unlimited", $item_render);
      } else {
        $item_render = str_replace("{{ stock-quantity }}", $item_data["stock"], $item_render);
      }
      $final_render = $final_render . $item_render;
    }
    echo $final_render;
  } else {
    return false;
  }
}
function validate_promotional_code($code, $params = []) {
  global $node_connection;
  $defaults = ["reference" => "code", "input-type" => "string", "delimiter" => ","];
  $params = array_merge($defaults, $params);
  $operator_array = [
    "Free With Purchase",
    "Buy One Get One Free"
  ];
  if ($params["input-type"] == "string") {
    if (strpos($code, $params["delimiter"]) !== false) {
      $codes_array = explode($params["delimiter"], str_replace(" ", "", $code));
    } else {
      $codes_array = [$code];
    }
  } else {
    $codes_array = $code;
  }
  $code_count = count($codes_array);
  $promo_errors = [];
  foreach($codes_array as $individual_code) {
    $code_query = $node_connection->prepare("SELECT `operation`, `condition`, `variable`, `condition-variable`, `has-expiration`, `expiration-date`, `stackable` FROM `' . $requested_tables["promo_table_name"] . '` WHERE `" . $params["reference"] . "` = ?");
    $code_query -> bind_param("s", $individual_code);
    $code_query -> execute();
    $code_query -> store_result();
    $code_query -> bind_result($code_data["operation"], $code_data["condition"], $code_data["variable"], $code_data["condition-variable"], $code_data["has-expiration"], $code_data["expiration-date"], $code_data["stackable"]);
    $code_query -> fetch();
    if ($code_query -> num_rows > 0) {
      $code_data = mysqli_fetch_assoc($code_result);
      if (in_array($code_data["operation"], $operator_array)) {
        $item_exists_result = $node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] .'` WHERE `product` = \'" . $code_data["variable"] . "\'");
        if (mysqli_num_rows($item_exists_result) > 0) {
          $item_data = mysqli_fetch_assoc($item_exists_result);
          if ($item_data["unlimited-stock"] != "1") {
            if ($item_data["stock"] > 0) {
              $operator_validated = true;
            } else {
              $promo_errors[$individual_code] = "E2";
              continue;
            }
          } else {
            $operator_validated = true;
          }
        } else {
          $promo_errors[$individual_code] = "E1";
          continue;
        }
      } else {
        $operator_validated = true;
      }
      if ($operator_validated) {
        if ($code_data["condition"] == "Order Must Contain Item") {
          $condition_item_exists_result = $node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] .'` WHERE `product` = \'" . $code_data["condition-variable"] . "\'");
          if (mysqli_num_rows($condition_item_exists_result) > 0) {
            $condition_item_data = mysqli_fetch_assoc($condition_item_exists_result);
            if ($condition_item_data["unlimited-stock"] != "1") {
              if ($condition_item_data["stock"] > 0) {
                $condition_validated = true;
              } else {
                $promo_errors[$individual_code] = "E4";
                continue;
              }
            } else {
              $condition_validated = true;
            }
          } else {
            $promo_errors[$individual_code] = "E3";
            continue;
          }
        } elseif ($code_data["condition"] == "Applies to Category") {
          $category_exists_result = $node_connection->query("SELECT * FROM ' . $requested_tables["product_category_table_name"] .' WHERE `category` = \'" . $code_data["condition-variable"] . "\'");
          if (mysqli_num_rows($category_exists_result) > 0) {
            $condition_validated = true;
          } else {
            $promo_errors[$individual_code] = "E5";
            continue;
          }
        } else {
          $condition_validated = true;
        }
        if ($condition_validated) {
          if ($code_data["has-expiration"] == "1") {
            if (strtotime($code_data["expiration-date"]) < time()) {
              $promo_errors[$individual_code] = "E6";
              continue;
            } else {
              $expiration_validated = true;
            }
          } else {
              $expiration_validated = true;
          }
          if ($expiration_validated) {
            if ($code_count > 1) {
              if ($code_data["stackable"] != "1") {
                $promo_errors[$individual_code] = "E7";
                continue;
              } else {
                $code_occurences = array_count_values($codes_array);
                if ($code_occurences[$individual_code] > 1) {
                  $promo_errors[$individual_code] = "E8";
                  $promo_errors = array_unique($promo_errors);
                  continue;
                }
              }
            }
          }
        }
      }
    } else {
      $promo_errors[$code] = "E0";
    }
    if (empty($promo_errors)) {
      return true;
    } else {
      return $promo_errors;
    }
  }
}
function validate_order_promotional_codes($code, $params=[]) {
  global $node_connection;
  $defaults = ["reference" => "code", "input-type" => "string", "delimiter" => ",", "cookie-name" => md5($_SERVER["SERVER_ADDR"]), "key" => md5($_SERVER["SERVER_ADDR"])];
  $params = array_merge($defaults, $params);
  $preliminary_params = [
    "reference" => $params["reference"],
    "input-type" => $params["input_type"],
    "delimiter" => $params["delimiter"]
  ];
  $preliminary_validation = validate_promo_code($code, $preliminary_params);
  $order_validation_errors = array();
  if ($params["input-type"] == "array") {
    $codes_to_validate = $code;
  } else {
    if (strpos($code, $params["delimiter"]) !== false) {
      $codes_to_validate = explode($params["delimiter"], str_replace(" ", "", $code));
    } else {
      $codes_to_validate = [$code];
    }
  }
  if (!$preliminary_validation === true) {
    foreach($preliminary_validation as $preliminary_error_key => $preliminary_error) {
      if ($unset_key = array_search($preliminary_error_key, $codes_to_validate) !== false) {
        unset($codes_to_validate[$unset_key]);
      }
      if ($preliminary_error == "E8") {
        if ($duplicate_unset_key = array_search($preliminary_error_key, $codes_to_validate) !== false) {
          unset($codes_to_validate[$duplicate_unset_key]);
        }
      }
      $order_validation_errors[$preliminary_error_key] = $preliminary_error;
    }
  }
  if (empty($codes_to_validate)) {
    return $order_validation_errors;
  } else {
    $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
    $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
    $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
    $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
    if (strpos($decrypted_data, \'>\') !== false) {
      $current_cookie_array = explode(">", $decrypted_data);
    } else {
      $current_cookie_array = [$decrypted_data];
    }
    $current_cookie_data = array();
    foreach($current_cookie_array as $item_in_cart) {
      $current_cookie_data[] = explode(":", $item_in_cart);
    }
    foreach($codes_to_validate as $code_to_validate) {
      $code_data = array();
      $code_query = $node_connection->prepare("SELECT `operation`, `condition`, `variable`, `condition-variable`, `has-expiration`, `expiration-date`, `stackable` FROM `' . $requested_tables["promo_table_name"] . '` WHERE `" . $params["reference"] . "` = ?");
      $code_query -> bind_param(\'s\', $code_to_validate);
      $code_query -> execute();
      $code_query -> store_result();
      $code_query -> bind_result($code_data["operation"], $code_data["condition"], $code_data["variable"], $code_data["condition-variable"], $code_data["has-expiration"], $code_data["expiration-date"], $code_data["stackable"]);
      $code_query -> fetch();
      if ($code_data["operation"] == "Buy One Get One Free") {
        $item_stock_result = $node_connection->query("SELECT `id`, `unlimited-stock`, `stock` FROM `' . $requested_tables["product_table_name"] . '` WHERE `product` = \'" . $code_data["variable"] . "\'");
        $item_stock_data = mysqli_fetch_assoc($item_stock_result);
        $current_cookie_products_count = count($current_cookie_data);
        $product_not_found_count = 0;
        foreach($current_cookie_data as $current_cookie_product) {
          if ($current_cookie_product[0] != $code_data["variable"]) {
            $product_not_found_count++;
          } else {
            $operation_product_quantity = $current_cookie_product[1];
          }
        }
        if ($product_not_found_count < $current_cookie_products_count) {
          if ((($operation_product_quantity * 2) <= $item_stock_data["stock"]) || ($item_stock_data["unlimited-stock"] == "1")) {
            $conditions_array = [
              "Total Order Cost Must Be Over Amount",
              "Order Must Contain Item",
              "Order Must Contain More Than A Certain Number of Items",
              "Applies to Category"
            ];
            if ($code_data["condition"] == $conditions_array[0]) {
              $total_cost = 0;
              foreach($current_cookie_data as $individual_product) {
                $item_cost_result = $node_connection->query("SELECT `cost` FROM `' . $requested_tables["product_table_name"] . '` WHERE `product` = \'" . $individual_product[0] . "\'");
                $item_cost = mysqli_fetch_assoc($item_cost_result);
                $total_cost = $total_cost + $item_cost["cost"];
              }
              if ($total_cost >= $code_data["condition-variable"]) {
                $condition_validated = true;
              } else {
                $order_validation_errors[$code_to_validate] = "E11";
                continue;
              }
            } elseif ($code_data["condition"] == $conditions_array[1]) {
              $item_not_in_cart_count = 0;
              foreach($current_cookie_data as $individual_product) {
                if ($individual_product[0] != $code_data["condition-variable"]) {
                  $item_not_in_cart_count++;
                }
              }
              if ($current_cookie_products_count > $item_not_in_cart_count) {
                $condition_validated = true;
              } else {
                $order_validation_errors[$code_to_validate] = "E12";
                continue;
              }
            } elseif ($code_data["condition"] == $conditions_array[2]) {
              if ($current_cookie_products_count >= $code_data["condition-variable"]) {
                $condition_validated = true;
              } else {
                $order_validation_errors[$code_to_validate] = "E13";
                continue;
              }
            } elseif ($code_data["condition"] == $conditions_array[3]) {
              $item_belongs_result = $node_connection->query("SELECT * FROM `' . $requested_tables["product_table_name"] . '` WHERE `category` = \'" . $code_data["condition-variable"] . "\'");
              if (mysqli_num_rows($item_belongs_result) > 0) {
                $condition_validated = true;
              } else {
                $order_validation_errors[$code_to_validate] = "E14";
                continue;
              }
            }
          } else {
            $order_validation_errors[$code_to_validate] = "E10";
            continue;
          }
        } else {
          $order_validation_errors[$code_to_validate] = "E9";
          continue;
        }
      }
    }
    if (empty($order_validation_errors)) {
      return true;
    } else {
      return $order_validation_errors;
    }
  }
}
function render_products($template, $params) {
  global $node_connection;
}
function render_product($template, $identifer, $params) {
  global $node_connection;
}
function add_review($input, $identifier, $params) {
  global $node_connection;
}
function render_reviews() {
  global $node_connection;
}
function search_products($identifer, $params) {
  global $node_connection;
}
?>';
        fwrite($node_file, $node_file_contents);
        fclose($node_file);
        $ecommerce_config_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/config/ecommerce_config.php", "w");
        $ecommerce_config_file_contents = '<?php
    $use_stripe = "";
    $stripe_secret_key = "";
    $stripe_public_key = "";
?>';
        if ($use_stripe_value == "use") {
          $ecommerce_config_file_contents = str_replace('$use_stripe = "";', '$use_stripe = ' . $use_stripe_config . ';', str_replace('$stripe_secret_key = ""', '$stripe_secret_key = "' . $secret_key_value . '"', str_replace('$stripe_public_key = ""', '$stripe_public_key = "' . $publishable_key_value . '"', $ecommerce_config_file_contents)));
        } else {
          $ecommerce_config_file_contents = str_replace('$use_stripe = "";', '$use_stripe = ' . $use_stripe_config . ';', $ecommerce_config_file_contents);
        }
        fwrite($ecommerce_config_file, $ecommerce_config_file_contents);
        fclose($ecommerce_config_file);
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/nodes/resources")) {
          mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/resources");
        }
        $functions_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/resources/ecommerce/functions.php", "w");
        $functions_file_contents = '<?php
include($_SERVER[\'DOCUMENT_ROOT\']. "/nodes/config/ecommerce_config.php");
if (isset($_POST["get_publishable"])) {
  if ($use_stripe === true) {
    echo $stripe_public_key;
  } else {
    echo "E1";
  }
} elseif (isset($_POST["stripe_charge_customer"])) {
  require_once($_SERVER[\'DOCUMENT_ROOT\']. "/nodes/resources/ecommerce/stripe/init.php");
  $arguments_array = [
    "email" => $_POST["email"],
    "token" => $_POST["token"]
  ];
  if ($stripe_secret_key == "") {
    echo "E7";
  } else {
    Stripe::setApiKey($stripe_secret_key);
  }
  $cookie_key = $_POST["cookie_key"];
  $promo_codes = $_POST["promo_codes"];
  $promo_code_delimiter = $_POST["promo_code_delimiter"];
  include($_SERVER["DOCUMENT_ROOT"] . "/nodes/ecommerce.php");
  $current_cookie_string = base64_decode($_COOKIE[$params["cookie-name"]]);
  $current_iv = substr($current_cookie_string, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));
  $current_data = substr($current_cookie_string, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($current_cookie_string));
  $decrypted_data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $params["key"], $current_data, MCRYPT_MODE_CBC, $current_iv);
  \'GET PRICE/ITEMS/QUANTITY HERE\'
  $arguments_array["price"] = $final_price * 100;
  function final_stripe_charge($arguments) {
    try {
      $charge = Stripe_Charge::create(array(
            "amount" => $arguments_array["price"],
            "currency" => "usd",
            "card" => $arguments_array["token"],
            "description" => $arguments_array["email"])
      );
      $name = $_POST["name"];
      $address = $_POST["address"];

      \'ADD RECORD TO DATABASE HERE\'
    }
    catch (Stripe_Error) {
      return "E8"
    }
  }
  echo final_stripe_charge($arguments_array);
} elseif (isset($_POST["cookie_key"])) {
  echo md5($_SERVER["SERVER_ADDR"]);
}
?>';
        fwrite($functions_file, $functions_file_contents);
        fclose($functions_file);
        rename("stripe", $_SERVER['DOCUMENT_ROOT'] . "/nodes/resources/ecommerce/stripe");
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/nodes/javascript")) {
          mkdir($_SERVER['DOCUMENT_ROOT'] . "/nodes/javascript");
        }
        $javascsript_file = fopen($_SERVER['DOCUMENT_ROOT']. "/nodes/javascript/ecommerce.js", "w");
        $javascsript_file_contents = '
var jQueryScript = document.createElement("script");
jQueryScript.src = "//code.jquery.com/jquery-1.11.3.min.js";
jQueryScript.type = "text/javascript";
var jQueryScript = document.createElement("script");
stripeScript.src = "https://js.stripe.com/v2/";
stripeScript.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(stripeScript);
var stripePayment = function(userParams) {
  var defaultParams = {
    "cookieKey" : "default"
  };
  var params = $.extend(defaultParams, userParams);
  if (params.cookieKey == "default") {
    $.post(
      "/nodes/resources/ecommerce/functions.php",
      {
        cookie_key: "true";
      },
      function(data) {
          params.cookieKey = data;
      }
    ).fail(function() {
      returnError = "E2";
    });
  }
  var returnError = "";
  $.post(
    "/nodes/resources/ecommerce/functions.php",
    {
      get_publishable: "true";
    },
    function(data) {
      if (data = "E1") {
        returnError = "E1";
      } else if (data == "") {
        returnError = "E3";
      } else {
        Stripe.setPublishableKey(data);
      }
    }
  ).fail(function() {
    returnError = "E2";
  });
  if (returnError != "") {
    return returnError;
  } else {
    Stripe.createToken({
      number: $("#" . params.cardNumber).val(),
      cvc: $("#" . params.cardCVC).val(),
      exp_month: $("#" . params.expirationMonth).val(),
      exp_year: $("#" . params.expirationYear).val()
    }, tokenResponse);
    var tokenResponse = function(status, response) {
      if (response.error) {
        returnError = "E4";
      } else {
        var paymentRequest = $.ajax ({
              type: "POST",
              url: "/nodes/resources/ecommerce/functions.php",
              dataType: "json",
              data: {
                "stripe_charge_customer" : "true",
                "token" : response.id,
                "name" : $("#" . params.name).val(),
                "email" : $("#" . params.email).val(),
                "cookie_key" : params.cookieKey,
                "address" : $("#" . params.address).val(),
                "promo_codes" : $("#" . params.promotionalCodes).val(),
                "promo_code_delimiter" : $("#" . params.promotionalCodeDelimiter).val(),
              }
        });
        paymentRequest.done(function(returnMessage) {
          if (returnMessage === "success") {
            returnError = true;
          } else {
            returnError = returnMessage;
          }
        });
        paymentRequest.fail(function() {
          returnError = "E6";
        });
      }
    };
  }
  return returnError;
};
';
        fwrite($javascsript_file, $javascsript_file_contents);
        fclose($javascsript_file);
        header("location: ../../../../landing.php");
      } else {
        foreach($requested_tables as $requested_table) {
          $drop_query = "DROP TABLE " . $requested_table;
          $connection->query($drop_query);
        }
        $error["main"] = "There was a problem installing this package. Please try again.";
      }
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../../css/ecom.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Install Package: Ecommerce</span>
      <br />
      <form method="post" action="">
        <p>
          This page will install the Ecommerce package. The information you enter below reflect the name of the tables housing all of this package's data within your database. If you elect to use Stripe to process your customer's payments (Highly Recommended), the information you provide will reflect the Stripe account which recieves payment from your customers.
          <br />
          <br />
          Table name selection is irreversible. You cannot alter the table names after installation. Please select your table names carefully.
          <br />
          <br />
          The table names you select may only contain letters, numbers, underscores, and may not be longer than 50 characters in total length.
          <br />
          <br />
          If you elect to use Stripe to process your customer's payments, please use the account keys provided by Stripe. If you are using Stripe it is highly recommended that you acquire your Stripe account keys before installing this package; however, you can edit current Stripe settings or elect to use Stripe after the installation.
          <br />
          <br />
          If you do not elect to use Stripe, some of this packages node functions will not be usable.
        </p>
        <input type="text" name="product_category_table_name" placeholder="Desired Product Category Table Name" value="<?php echo $requested_tables["product_category_table_name"]; ?>"/>
        <?php if ($error["product_category_table_name"] != null) { echo "<p><font color='red'>" . $error["product_category_table_name"] . "</font></p>"; } else { echo "<br />"; }?>
        <input type="text" name="product_table_name" placeholder="Desired Product Table Name" value="<?php echo $requested_tables["product_table_name"]; ?>"/>
        <?php if ($error["product_table_name"] != null) { echo "<p><font color='red'>" . $error["product_table_name"] . "</font></p>"; } else { echo "<br />"; }?>
        <input type="text" name="promo_table_name" placeholder="Desired Promotional Code Table Name" value="<?php echo $requested_tables["promo_table_name"]; ?>"/>
        <?php if ($error["promo_table_name"] != null) { echo "<p><font color='red'>" . $error["promo_table_name"] . "</font></p>"; } else { echo "<br />"; }?>
        <input type="text" name="records_table_name" placeholder="Desired Records Table Name" value="<?php echo $requested_tables["records_table_name"]; ?>"/>
        <?php if ($error["records_table_name"] != null) { echo "<p><font color='red'>" . $error["records_table_name"] . "</font></p>"; } else { echo "<br />"; }?>
        <br />
        <input type="checkbox" name="use_stripe" value="use" <?php echo $use_stripe_checked; ?>/> Use Stripe to Process Payments
        <p id="stripe-inputs">
          <input type="text" name="secret_key" placeholder="Secret Key" value="<?php echo $secret_key_value; ?>"/>
          <br />
          <input type="text" name="publishable_key" placeholder="Publishable Key" value="<?php echo $publishable_key_value; ?>"/>
        </p>
        <?php if ($error["main"] != null) { echo "<p><font color='red'>" . $error["main"] . "</font></p>"; } ?>
        <input type="submit" name="install_submit" value="Install" onclick='return confirm("Table creation is irreversible. Are you sure that you are satisfied with your table name?");'>
      </form>
      <form action="../../../../landing.php">
        <input type="submit" value="Back to Adminstration Selection">
      </form>
    </div>
    <script type="text/javascript" src="../../javascript/jquery.js"></script>
    <script type="text/javascript" src="../../javascript/stripe_keys.js"></script>
  </body>
</html>
