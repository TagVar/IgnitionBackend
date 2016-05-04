<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
$error = "";
$user_info_query = "SELECT records FROM `$bookkeeping_user_table` WHERE user_id = " . $_SESSION['user_id'];
$user_info_result = $connection->query($user_info_query);
$user_info = $user_info_result->fetch_assoc();
$user_records_string = $user_info["records"];
$user_records = explode(">", $user_records_string);
$user_client_array = [];
foreach($user_records as $key=>$record) {
    $user_records[$key] = explode(":", $record);
    foreach($user_records[$key] as $record_key=>$record_data) {
        $user_records[$key][$record_key] = str_replace("&#62;", ">", str_replace("&#x0003A;", ":", $record_data));
    }
    $user_client_array[] = $user_records[$key][0];
}
$user_client_array = array_unique($user_client_array);
if (isset($_POST["submit"])) {
    $client_filter = $_POST["client_filter"];
    $date_filter = $_POST["date_filter"];
    $client_list = $_POST["client_list"];
    $start_month = $_POST["start_month"];
    $start_day = $_POST["start_day"];
    $start_year =$_POST["start_year"];
    $end_month = $_POST["end_month"];
    $end_day = $_POST["end_day"];
    $end_year = $_POST["end_year"];
    if ($date_filter == "checked") {
        if (checkdate($start_month, $start_day, $start_year)) {
          if (!checkdate($end_month, $end_day, $end_year)) {
            $error = "The end date you entered was invalid.";
          }
        } else {
          $error = "The start date you entered was invalid.";
        }
    }
    if ($error == "") {
      $_SESSION["temporary_result_parameters"] = [
        "client_filter" => $client_filter,
        "date_filter" => $date_filter,
        "client_list" => $client_list,
        "start_date" => $start_month . "/" . $start_day . "/" . $start_year,
        "end_date" => $end_month . "/" . $end_day . "/" . $end_year
      ];
      header("location: ./results.php");
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
      <span class="heading">Record Manager</span>
      <br />
      <br />
      <form action="" method="post">
        <input type="checkbox" name="client_filter" value="checked" <?php echo $client_filter; ?>/> Filter By Client
        <br />
        <div class="filter-container" id="client-filter-container">
            <?php
                if (!empty($user_client_array)) {
                    foreach($user_client_array as $user_client) {
                        if(isset($_POST["submit"])) {
                            if (in_array($user_client, $client_list)) {
                                $user_client_checked = "checked";
                            }
                        }
                        echo "<input type='checkbox' name='client_list[]' value='$user_client' $user_client_checked/> $user_client<br />";
                    }
                } else {
                    echo "No clients found in database.";
                }
            ?>
        </div>
        <input type="checkbox" name="date_filter" value="checked" <?php echo $date_filter; ?>/> Filter By Date
        <div class="filter-container" id="date-filter-container">
            Start Date:
            <br />
            <input type="text" name="start_month" value="<?php echo $start_month; ?>" placeholder="MM"/>&nbsp;/&nbsp;
            <input type="text" name="start_day" value="<?php echo $start_day; ?>" placeholder="DD"/>&nbsp;/&nbsp;
            <input type="text" name="start_year" value="<?php echo $start_year; ?>" placeholder="YYYY"/>
            <br />
            <br />
            End Date:
            <br />
            <input type="text" name="end_month" value="<?php echo $end_month; ?>" placeholder="MM"/>&nbsp;/&nbsp;
            <input type="text" name="end_day" value="<?php echo $end_day; ?>" placeholder="DD"/>&nbsp;/&nbsp;
            <input type="text" name="end_year" value="<?php echo $end_year; ?>" placeholder="YYYY"/>
        </div>
        <?php
            if ($error != "") {
                echo "<font color='red'>$error</font><br /><br />";
            }
        ?>
        <input name="submit" type="submit" value="Search Records">
      </form>
      <form action="../">
        <input type="submit" value="Back To Bookkeeping Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="javascript/records_landing.js"></script>
  </body>
</html>