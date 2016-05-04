<?php
  include("config/main_config.php");
  $error_message = "";
  if (isset($_POST["submit"])) {
      if (($_POST["username"] === $username) && (password_verify($_POST["password"], $password))) {
        session_start();
        $_SESSION["logged_in"] = true;
        $_SESSION["permissions"] = "root";
        $_SESSION["user_id"] = "0";
        header('Location: landing.php');
      } else {
	$user_query = 'SELECT * FROM `logins` WHERE username="' . $_POST["username"] . '"';
	$user_result = mysqli_query($connection, $user_query);
	$user_array = mysqli_fetch_assoc($user_result);
	if (password_verify($_POST["password"], $user_array["password"])) {
	  session_start();
	  $_SESSION["logged_in"] = true;
	  $_SESSION["user_id"] = $user_array["id"];
	  $_SESSION["permissions"] = $user_array["permissions"];
	  header('Location: landing.php');
	} else {
	  $error_message = "The login information you entered was not correct.";
	}
      }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
  </head>
  <body>
    <div id="portal">
      <form method="post" action="#">
        <div class="heading">
          Administrator Login:
        </div>
        <input type="text" name="username" value="<?php if (isset($_POST["username"])) { echo $_POST['username']; } ?>" placeholder="Username"/>
        <br />
        <input type="password" name="password" placeholder="Password"/>
        <br />
        <input type="submit" name="submit"/>
        <?php
          if ($error_message != "") {
            echo "<div id='portal-error'>" . $error_message . "</div>";
          }
        ?>
      </form>
    </div>
  </body>
</html>
