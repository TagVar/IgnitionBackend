<?php
include("../../../functions/login_check.php");
include("../../../config/main_config.php");
include("../config/config.php");
if (empty($_SESSION["temporary_result_parameters"])) {
    header("location: index.php");
} else {
    $client_filter = $_SESSION["temporary_result_parameters"]["client_filter"];
    $date_filter = $_SESSION["temporary_result_parameters"]["date_filter"];
    $client_list = $_SESSION["temporary_result_parameters"]["client_list"];
    $start_date = $_SESSION["temporary_result_parameters"]["start_date"];
    $end_date = $_SESSION["temporary_result_parameters"]["end_date"];
}
$user_info_query = "SELECT records FROM `$bookkeeping_user_table` WHERE user_id = " . $_SESSION['user_id'];
$user_info_result = $connection->query($user_info_query);
$user_info = $user_info_result->fetch_assoc();
$user_records_string = $user_info["records"];
$user_records = explode(">", $user_records_string);
foreach($user_records as $key=>$record) {
    $user_records[$key] = explode(":", $record);
    foreach($user_records[$key] as $record_key=>$record_data) {
        $user_records[$key][$record_key] = str_replace("&#62;", ">", str_replace("&#x0003A;", ":", $record_data));
    }
}
function dateInRange($date, $start, $end) {
    $nonstring_date = date('Y-m-d', strtotime($date));
    $nonstring_start_date = date('Y-m-d', strtotime($start));
    $nonstring_end_date = date('Y-m-d', strtotime($end));
    if (($nonstring_date >= $nonstring_start_date) && ($nonstring_date <= $nonstring_end_date)) {
        return true;
    } else {
        return false;
    }
}
function filterClients($record_array, $client_array) {
    $output_array = [];
    foreach($record_array as $record_key=>$record) {
        if  (in_array($record[0], $client_array)) {
            $output_array[] = $record_key;
        }
    }
    return $output_array;
}
function filterDates($record_array, $start_date, $end_date) {
    $output_array = [];
    foreach($record_array as $record_key=>$record) {
        if (dateInRange($record[1], $start_date, $end_date) || dateInRange($record[2], $start_date, $end_date)) {
            $output_array[] = $record_key;
        }
    }
    return $output_array;
}
if (($client_filter == "checked") && ($date_filter == "checked")) {
    $records_to_render = array_unique(array_merge(filterClients($user_records, $client_list), filterDates($user_records, $start_date, $end_date)));
    $sort_required = true;
} elseif ($client_filter == "checked") {
    $records_to_render = filterClients($user_records, $client_list);
    $sort_required = true;
} elseif ($date_filter == "checked") {
    $records_to_render = filterDates($user_records, $start_date, $end_date);
    $sort_required = true;
} else {
    $record_data_to_render = $user_records;
    $sort_required = false;
}
if ($sort_required) {
    sort($records_to_render);
    $record_data_to_render = [];
    foreach ($records_to_render as $record_key) {
        $record_data_to_render[] = $user_records[$record_key];
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
      <?php
        if (count($record_data_to_render) > 0) {
          echo "
          <table id='record-table'>
            <tr>
              <th>
                <input type='checkbox' name='select_all' value='checked' $select_all/>
              </th>
              <th>
                Client
              </th>
              <th>
                Time In
              </th>
              <th>
                Time Out
              </th>
              <th>
                Hours
              </th>
              <th width='300px'>
                Work Completed
              </th>
            </tr>
          ";
          foreach($record_data_to_render as $record_key=>$record) {
            if (in_array($record_key, $_POST["selected_records"])) {
                $record_selected = "checked";
            }
            $time_allotted = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $record[3]);
            sscanf($time_allotted, "%d:%d:%d", $hours, $minutes, $seconds);
            $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
            $time_alloted = round ($time_seconds / 3600, 2);
            echo "<tr>
                    <td>
                        <input type='checkbox' name='selected_records[]' value='$record_key' $record_selected/>
                    </td>
		    <td>
                        " . $record[0] . "
                    </td>
		    <td>
                        " . $record[1] . "
                    </td>
		    <td>
                        " . $record[2] . "
                    </td>
                    <td>
                        " . $time_alloted . "
                    </td>
                    <td>
                        " . $record[4] . "
                    </td>
                  </tr>";
          }
          echo "</table>";
        } else {
          echo "<div class='heading'>Your search did not yield any results.</div>";
        }
      ?>
      <form action="./">
        <input type="submit" value="Back To Record Management Landing">
      </form>
    </div>
    <script type="text/javascript" src="../javascript/jquery.js"></script>
  </body>
</html>