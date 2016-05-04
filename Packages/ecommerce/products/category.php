<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$identifier = $_GET["category"];
$products_query = "SELECT * FROM `" . $ecom_tables['product'] . "` WHERE `category` = '" . $identifier . "'";
$products_result = $connection->query($products_query);
if (isset($_GET["delete"])) {
  $product_id = $_GET["which"];
  $product_category = $_GET["which_category"];
  $db_category_query = "SELECT * FROM `" . $ecom_tables['category'] . "` WHERE `category`='$product_category'";
  $db_category_result = $connection->query($db_category_query);
  $db_category = $db_category_result->fetch_assoc();
  $db_category_products = $db_category["products"];
  $db_category_products_array = array_filter(explode(",", $db_category_products));
  unset($db_category_products_array[array_search($product_id, $db_category_products_array)]);
  $new_product_list = implode(",", $db_category_products_array);
  $update_category_query = "UPDATE `" . $ecom_tables['category'] . "` SET `products`='" . $new_product_list . "' WHERE `category`='$product_category'";
  if ($connection->query($update_category_query)) {
    $delete_product_query = "DELETE FROM `" . $ecom_tables['product'] . "` WHERE id=$product_id";
    $connection->query($delete_product_query);
    header('Location: category.php?category=' . $product_category);
  }
}
?>
<html>
  <head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/ecom.css">
  </head>
  <body>
    <div id="main-container">
    <?php
      if (mysqli_num_rows($products_result) != 0) {
        echo "
        <table>
          <tr>
            <th>
              Product Name
            </th>
            <th>
              Cost
            </th>
            <th>
              Actions
            </th>
          </tr>
        ";
        while ($product = mysqli_fetch_array($products_result)) {
          echo "<tr>
                  <td>";
              echo $product["product"];
            echo "</td>
                  <td>$";
              echo $product["cost"];
            echo "</td>
                  <td>";
              echo "<a href='edit.php?edit=true&which=" . $product["id"] . "&category=" . $identifier . "'>Edit</a><br />";
              echo "<a onclick='return confirm(\"Are you sure you want to delete this product?\");' href='?delete=true&category=" . $identifier . "&which=" . $product["id"] . "&which_category=" . $product["category"] . "'>Delete</a>";
            echo "</td>
                </tr>";
          }
          echo "</table>";
        } else {
          echo "
          <div class='heading'>There are no products to display.</div>
          ";
        }
      ?>
      <form action="edit.php" method="get">
        <input type="submit" value="Add Product" />
        <input type="hidden" name="category" value="<?php echo $identifier; ?>" />
      </form>
      <form action=".">
        <input type="submit" value="Return to Category Selection">
      </form>
    </div>
  </body>
</html>