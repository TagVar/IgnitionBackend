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
  if ($is_edit === true) {
    $category_query = "SELECT * FROM `" . $ecom_tables['category'] . "` WHERE id=$identifier";
    $category_result = $connection->query($category_query);
    $category = $category_result->fetch_assoc();
    $category_name_value = $category["category"];
    $db_category_name_value = $category["category"];
    $db_category_products = $category["products"];
  }
  if (isset($_POST["submit_category"])) {
    $category_name_value = $_POST["category_name"];
    if (str_replace(" ", "", $category_name_value) != "") {
      $duplicate_category_query = "SELECT * FROM `" . $ecom_tables['category'] . "` WHERE `category`='$category_name_value'";
      $duplicate_category_result = $connection->query($duplicate_category_query);
      if (mysqli_num_rows($duplicate_category_result) <= 0) {
        if ($is_edit == true) {
          if ($db_category_products != "") {
            $update_product_query = "UPDATE " . $ecom_tables['product'] . " SET `category`='$category_name_value' WHERE `category`='$db_category_name_value'";
            if ($connection->query($update_product_query)) {
              $updated_products = true;
            } else {
              $updated_products = false;
            }
          } else {
            $updated_products = true;
          }
          if ($updated_products) {
            $update_category_query = "UPDATE " . $ecom_tables['category'] . " SET `category`='$category_name_value' WHERE `category`='$db_category_name_value'";
            if ($connection->query($update_category_query)) {
              header("location: .");
            } else {
              $error = "There was an error connecting to the database. Some data may be incorrect.";
            }
          } else {
            $error = "There was an error connecting to the database. Please try again.";
          }
        } else {
          $create_category_query = "INSERT INTO " . $ecom_tables['category'] . " (`category`) VALUES ('" . $category_name_value . "')";
          if ($connection->query($create_category_query)) {
            header("location: .");
          } else {
            $error = "There was an error connecting to the database. Please try again.";
          }
        }
      } else {
        if ($is_edit) {
          if (strtolower($db_category_name_value) == strtolower($category_name_value)) {
            $error = "You did not change the category name.";
          } else {
            $error = "A category with that name already exists.";
          }
        } else {
          $error = "A category with that name already exists.";
        }
      }
    } else {
      $error = "You must provide a category name.";
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
      <span class="heading"><?php if ($is_edit === true) { echo "Edit Category"; } elseif ($is_edit === false) { echo "Add Category"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" name="category_name" placeholder="Category Name" value="<?php echo $category_name_value; ?>" />
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="submit_category" value="<?php if ($is_edit === true) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add Category"; } ?>" />
      </form>
      <form action=".">
        <input type="submit" value="Back To Category Selection">
      </form>
    </div>
    <br />
    <br />
  </body>
</html>