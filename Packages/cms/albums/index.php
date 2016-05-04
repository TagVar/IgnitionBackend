<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$album_query = "SELECT name, id, data FROM `$cms_table` WHERE type='album'";
$album_result = $connection->query($album_query);
$rendered_albums = [];
if (isset($_GET["delete"])) {
    $delete_album_query = "DELETE FROM `$cms_table` WHERE id='" . $_GET["which"] . "'";
    $connection->query($delete_album_query);
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
      <span class="heading">Albums</span>
      <br />
      <br />
      <?php
        if (mysqli_num_rows($album_result) != 0) {
          echo "
          <table>
            <tr>
              <th>
                Album Name
              </th>
              <th>
                Photos in Album
              </th>
              <th>
                Actions
              </th>
            </tr>
          ";
          while ($album = mysqli_fetch_array($album_result)) {
            if (!in_array($album["name"], $rendered_albums)) {
                $rendered_albums[] = $album["name"];
                $data_string = $album["data"];
                if ((substr_count($data_string, ":") == 0) && ($data_string == "")) {
                    $image_count = 0;
                } elseif (substr_count($data_string, ":") == 0) {
                    $image_count = 1;
                } else {
                    $image_count = count(explode(":", $data_string));
                }
                echo "<tr>
                        <td>"; 
                    echo $album["name"];
                echo "</td>
                     <td>
                        $image_count
                     </td>
                     <td>";
                    echo "<a href='edit.php?which=" . $album["id"] . "'>View/Edit</a><br />";
                    echo "<a href='add.php?which=" . $album["id"] . "'>Add Image</a><br />";
                    echo "<a onclick='return confirm(\"Are you sure you want to delete this album?\");' href='?delete=true&which=" . $album["id"] . "'>Delete</a>";
                echo "</td>
                    </tr>";
              }
          }
          echo "</table>";
        } else {
          echo "
            <div class='heading'>There are no albums to display.</div>
          ";
        }
      ?>
      <form action="edit.php">
        <input type="submit" value="Add Album">
      </form>
      <form action="../">
        <input type="submit" value="Back To CMS Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>