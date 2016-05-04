<?php
include("../../../../functions/login_check.php");
include("../../../../config/main_config.php");
include("../../config/config.php");
$standard_error = "An error occured. Please try again.";
if (isset($_POST["action"])) {
  if ((str_replace(" ", "", $_POST["client"]) != "") || ($_POST["action"] != "record")) {
    if (isset($_POST["user_id"])) {
    $current_records_query = "SELECT * FROM `$bookkeeping_user_table` WHERE user_id = '" . $_POST["user_id"] . "'";
    $current_records_result = $connection->query($current_records_query);
    $current_records = $current_records_result->fetch_assoc();
    if ((is_null($current_records["timezone"])) || ($current_records["timezone"] == "Default") || (str_replace(" ", "", $current_records["timezone"]) == "")) {
      $timezone = date_default_timezone_get();
    } else {
      $timezone = $current_records["timezone"];
    }
    $datetime = new DateTime("now", new DateTimeZone($timezone));
    $datetime = $datetime->format('Y-m-d H:i');	
    if ($_POST["action"] == "start") {
      if ($current_records_result->num_rows > 0) {
	$update_records_query = "UPDATE `$bookkeeping_user_table` SET start='$datetime' WHERE user_id='" . $_POST["user_id"] . "'";
      } else {
	$update_records_query = "INSERT INTO `$bookkeeping_user_table` (user_id, start) VALUES ('" . $_POST["user_id"] . "', '$datetime')";
      }
    } elseif ($_POST["action"] == "stop") {
	$update_records_query = "UPDATE `$bookkeeping_user_table` SET end='$datetime' WHERE user_id='" . $_POST["user_id"] . "'";
    } elseif ($_POST["action"] == "record") {
      if (str_replace(" ", "", $_POST["record"]) != "") {
	$records = [];
	$split_records = explode(">", $current_records["records"]);
	foreach ($split_records as $key=>$split_record) {
	  $split_records[$key] = str_replace("&#62;", ">", $split_record);
	  $split_values = explode(":", $split_records[$key]);
	  foreach($split_values as $key=>$split_value) {
	    $split_values[$key] = str_replace("&#x0003A;", ":", $split_value);
	  }
	  $records[] = $split_values;
	}
	$interval = date_diff(new DateTime($current_records["start"]), new DateTime($current_records["end"]));
	$period_duration = $interval->format('%h:%i:%s');
	$records[] = [
	  $_POST["client"],
	  $current_records["start"],
	  $current_records["end"],
	  $period_duration,
	  $_POST["record"]
	];
	$new_split_records = [];
	foreach ($records as $key=>$record) {
	  foreach($record as $key=>$value) {
	    $record[$key] = str_replace(":", "&#x0003A;", str_replace(">", "&#62;", $value));
	  }
	  $new_split_records[] = implode(":", $record);
	}
	$final_record = implode(">", array_filter($new_split_records));
	$update_records_query = "UPDATE `$bookkeeping_user_table` SET end=NULL, start=NULL, records='$final_record' WHERE user_id='" . $_POST["user_id"] . "'";
      } else {
	echo "You did not provide a description of work for this period.";
      }
    } else {
      echo $standard_error;
    }
    } else {
      echo $standard_error;
    }
  } else {
    echo "You did not select a client.";
  }
} else {
  echo $standard_error;
}
if (!is_null($update_records_query)) {
  $connection->query($update_records_query);
  echo "success";
}
?>