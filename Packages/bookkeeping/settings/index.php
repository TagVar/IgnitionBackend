<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$regions = [
  "Africa" => DateTimeZone::AFRICA,
  "America" => DateTimeZone::AMERICA,
  "Antarctica" => DateTimeZone::ANTARCTICA,
  "Asia" => DateTimeZone::ASIA,
  "Atlantic" => DateTimeZone::ATLANTIC,
  "Australia" => DateTimeZone::AUSTRALIA,
  "Europe" => DateTimeZone::EUROPE,
  "Indian" => DateTimeZone::INDIAN,
  "Pacific" => DateTimeZone::PACIFIC,
];
$timezone_array = [];
foreach ($regions as $key=>$region) {
  $timezone_array[strtoupper($key)] = DateTimeZone::listIdentifiers($region);
}
$timezone_offset_array = [];
foreach ($regions as $key=>$region) {
  $timezone_offset_array[strtoupper($key)] = [];
  foreach($timezone_array[strtoupper($key)] as $timezone) {
    $date_time_zone = new DateTimeZone($timezone);
    $timezone_now = new DateTime('now', $date_time_zone);
    $offset = $date_time_zone->getOffset( $timezone_now ) / 3600;
    $timezone_offset_array[strtoupper($key)][] = "GMT" . ($offset < 0 ? $offset : "+" . $offset);
  }
}
$current_timezone_query = "SELECT timezone from `$bookkeeping_user_table` WHERE user_id = " . $_SESSION["user_id"];
$current_timezone_result = $connection->query($current_timezone_query);
$current_timezone_array = $current_timezone_result->fetch_assoc();
$current_timezone = $current_timezone_array["timezone"];
if (isset($_POST["submit"])) {
  if ($_POST["timezone_selector"] != "") {
    if ($current_timezone_result->num_rows > 0) {
      $update_timezone_query = "UPDATE `$bookkeeping_user_table` SET timezone='" . $_POST["timezone_selector"] . "'";
    } else {
      $update_timezone_query = "INSERT INTO `$bookkeeping_user_table` (timezone, user_id) VALUES ('" . $_POST["timezone_selector"] . "', '" . $_SESSION["user_id"] . "')";
    }
    $connection->query($update_timezone_query);
    $current_timezone = $_POST["timezone_selector"];
    $error = "Timezone updated succesfully.";
  } else {
    $error = "You did not select a timezone.";
  }
}
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/bookkeeping.css">
  </head>
  <body>
    <div id="main-container" class="content-border">
      <span class="heading">Settings</span>
      <br />
      <br />
      Timezone
      <?php
	if ($current_timezone != "") {
	$current_date_time_zone = new DateTimeZone($current_timezone);
	$current_timezone_now = new DateTime('now', $current_date_time_zone);
	$current_offset = $current_date_time_zone->getOffset( $current_timezone_now ) / 3600;
	$current_formatted_offset = "GMT" . ($current_offset < 0 ? $current_offset : "+" . $current_offset);
	  echo "Current Timezone: " . $current_timezone . " (" . $current_formatted_offset . ")";
	} else {
	  echo "Current Timezone: Default (" . date_default_timezone_get() . ")";
	}
      ?>
      <form action="#" method="post">
	<select name="region_selector">
	<option value="" default>Please choose a region...</option>
	<?php
	  foreach ($regions as $key=>$region) {
	    if ($_POST["region_selector"] == strtoupper($key)) {
	      $selected = "selected";
	    } else {
	      $selected = "";
	    }
	    echo "<option value='" . strtoupper($key) . "' $selected>$key</option>";
	  }
	?>
	</select>
	<br />
	<select name="timezone_selector">
	  <option value="" default>Please select a timezone...</option>
	</select>
	<input type="submit" value="Update Timezone" name="submit" />
      </form>
      <br />
      <br />
      <form action="../">
        <input type="submit" value="Back To Bookkeeping Landing">
      </form>
    </div> 
    <script type="text/javascript" src="javascript/jquery.js"></script>
    <script type="text/javascript">
      $("select[name='timezone_selector']").hide();
      var timezoneArray = <?php echo json_encode($timezone_array); ?>;
      var timezoneOffsetArray = <?php echo json_encode($timezone_offset_array); ?>;
      var timezoneSelectorPost = "<?php echo $_POST["timezone_selector"]; ?>";
      var postError = "<?php echo $error; ?>";
      if (postError != "") {
	alert(postError);
      }
    </script>
    <script type="text/javascript" src="javascript/settings.js"></script>
  </body>
</html>