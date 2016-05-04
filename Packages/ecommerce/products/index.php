<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$categories_query = "SELECT * FROM `" . $ecom_tables['category'] . "`";
$categories_result = $connection->query($categories_query);
if (isset($_GET["delete"])) {
  $category_id = $_GET["which"];
  $get_products_query = "SELECT `products` FROM `" . $ecom_tables['category'] . "` WHERE id=$category_id";
  $get_products_result = $connection->query($get_products_query);
  $get_products_value = mysqli_fetch_assoc($get_products_result);
  if ($get_products_value["products"] != "") {
    $delete_products_query = "DELETE FROM `" . $ecom_tables['product'] . "` WHERE id IN (" . $get_products_value["products"] . ")";
    if ($connection->query($delete_products_query)) {
      $deleted_products = true;
    } else {
      $deleted_products = false;
    }
  } else {
    $deleted_products = true;
  }
  if ($deleted_products) {
    $delete_category_query = "DELETE FROM `" . $ecom_tables['category'] . "` WHERE id=$category_id";
    $connection->query($delete_category_query);
  }
  header('Location: .');
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
      if (mysqli_num_rows($categories_result) != 0) {
        echo "
        <table>
          <tr>
            <th>
              Category
            </th>
            <th>
              Number of Products
            </th>
            <th>
              Actions
            </th>
          </tr>
        ";
        while ($category = mysqli_fetch_array($categories_result)) {
          $category_products_count = count(explode(",", $category["products"]));
          if (empty($category["products"])) {
            $category_products_count--;
          }
          echo "<tr>
                  <td>";
              echo $category["category"];
            echo "</td>
                  <td>";
              echo $category_products_count;
            echo "</td>
                  <td>";
              echo "<a href='category.php?category=" . $category["category"] . "'>View Products</a><br />";
              echo "<a href='edit_category.php?edit=true&which=" . $category["id"] . "'>Edit</a><br />";
              echo "<a onclick='return confirm(\"Are you sure you want to delete this category? Doing so will delete all products contained in this category.\");' href='?delete=true&which=" . $category["id"] . "'>Delete</a>";
            echo "</td>
                </tr>";
        }
        echo "</table>";
      } else {
        echo "
          <div class='heading'>There are no categories to display.</div>
        ";
      }
      ?>
      <form action="edit_category.php" method="get">
        <input type="submit" value="Add Category" />
      </form>
      <form action="../">
        <input type="submit" value="Back to Ecommerce Landing">
      </form>
    </div>
  </body>
</html>