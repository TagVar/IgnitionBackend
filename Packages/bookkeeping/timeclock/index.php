<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$client_data_query = "SELECT name, finished FROM `$bookkeeping_client_table`";
$client_data_result = $connection->query($client_data_query);
$client_data_array = [];
while ($client_row = $client_data_result->fetch_assoc()) {
  $client_data_array[] = [
    $client_row["name"],
    $client_row["finished"]
  ];
}
$user_info_query = "SELECT * FROM `$bookkeeping_user_table` WHERE user_id = " . $_SESSION['user_id'];
$user_info_result = $connection->query($user_info_query);
if ($user_info_result->num_rows > 0) {
  $user_info = $user_info_result->fetch_assoc();
  if (is_null($user_info["start"])) {
    $display = "start";
  } elseif (is_null($user_info["end"])) {
    $display = "end";
  } else {
    $display = "notes";
  }
} else {
  $display = "start";
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/bookkeeping.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION["user_id"]; ?>" />
      <span class="heading">Timeclock</span>
      <br />
      <br />
      <?php
	if (!empty($client_data_array)) {
	  echo "<select name='client_selector'><option value='' default>Please select a client...</option>";
	    foreach($client_data_array as $client_data) {
	      if ($client_data[1] != "1") {
		echo "<option value='" . $client_data[0] . "'>" . $client_data[0] . "</option>";
	      }
	    }
	  echo "</select>";
	} else {
	  echo "No clients in database. Please add clients to continue.";
	}
      ?>
      <div id="input-container">
	<?php
	  if ($display == "start") {
	    echo '<input type="button" name="timeclock_input" value="Start Timeclock" />';
	  } elseif ($display = "end") {
	    echo '<input type="button" name="timeclock_input" value="Stop Timeclock" />';
	  } else {
	    echo '<div id="record-container"><textarea style="height: 200px;" name="timeclock_input" placeholder="Description of Work Completed"></textarea><br />';
	    echo '<input type="button" name="submit_record" value="Add Record" /></div>';
	  }
	?>
	<br />
	<br />
      </div>
      <form action="../">
        <input type="submit" value="Back To Bookkeeping Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="javascript/timeclock.js"></script>
  </body>
</html>