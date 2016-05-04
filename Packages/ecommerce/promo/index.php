<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$codes_query = "SELECT * FROM `" . $ecom_tables['promo'] . "`";
$codes_result = $connection->query($codes_query);
if (isset($_GET["delete"])) {
  $identifier = $_GET["which"];
  $delete_code_query = "DELETE FROM `" . $ecom_tables['promo'] . "` WHERE id=$identifier";
  $connection->query($delete_code_query);
  header('Location: index.php');
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
      if (mysqli_num_rows($codes_result) != 0) {
        echo "
        <table>
          <tr>
            <th>
              Promotional Code
            </th>
            <th>
              Number of Uses
            </th>
            <th>
              Actions
            </th>
          </tr>
        ";
        while ($code = mysqli_fetch_array($codes_result)) {
          echo "<tr>
                  <td>";
              echo $code["code"];
            echo "</td>
                  <td>";
              echo $code["uses"];
            echo "</td>
                  <td>";
              echo "<a href='edit.php?edit=true&which=" . $code["id"] . "'>Edit</a><br />";
              echo "<a onclick='return confirm(\"Are you sure you want to delete this promotional code?\");' href='?delete=true&which=" . $code["id"] . "'>Delete</a>";
            echo "</td>
                </tr>";
        }
        echo "</table>";
      } else {
        echo "
          <div class='heading'>There are no promotional codes to display.</div>
        ";
      }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Promotional Code">
      </form>
      <form action="../">
        <input type="submit" value="Back to Ecommerce Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/stripe_keys.js"></script>
  </body>
</html>