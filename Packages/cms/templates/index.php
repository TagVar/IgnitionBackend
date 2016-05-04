<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$template_query = "SELECT name, id FROM `$cms_table` WHERE type='template'";
$template_result = $connection->query($template_query);
$rendered_templates = [];
if (isset($_GET["delete"])) {
    $delete_template_query = "DELETE FROM `$cms_table` WHERE id='" . $_GET["which"] . "'";
    $connection->query($delete_template_query);
    header("location: ./");
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/cms.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Templates</span>
      <br />
      <br />
      <?php
        if (mysqli_num_rows($template_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Template
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($template = mysqli_fetch_array($template_result)) {
            if (!in_array($template["name"], $rendered_templates)) {
                $rendered_templates[] = $template["name"];
                echo "<tr>
                        <td>";
                    echo $template["name"];
                echo "</td>
                        <td>";
                    echo "<a href='edit.php?which=" . $template["id"] . "'>Edit</a><br />";
                    echo "<a onclick='return confirm(\"Are you sure you want to delete this template?\");' href='?delete=true&which=" . $template["id"] . "'>Delete</a>";
                echo "</td>
                    </tr>";
              }
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no templates to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Template">
      </form>
      <form action="../">
        <input type="submit" value="Back To CMS Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>