$('select[name="client_selector"]').hide();
if($('input[name="timeclock_input"]').length == 0) {
  var currentSubmit = $('input[name="submit_record"]');
} else {
  var currentSubmit = $('input[name="timeclock_input"]');
}
var inputValueArray = [
  "Start Timeclock",
  "Stop Timeclock",
  "Add Record"
];
$('#input-container').on( "click", "input", function() {
  if (currentSubmit.val() == inputValueArray[0]) {
    $.post( "php/record.php", { client: $('select[name="client_selector"]').val(), user_id: $('input[name="user_id"]').val(), action: "start" })
      .done(function( data ) {
	if (data == "success") {
	  $('input[name="timeclock_input"]').replaceWith('<input type="button" name="timeclock_input" value="Stop Timeclock" />');
	  currentSubmit = $('input[name="timeclock_input"]');
	} else {
	  alert(data);
	}
      });
  } else if (currentSubmit.val() == inputValueArray[1]) {
    $.post( "php/record.php", { client: $('select[name="client_selector"]').val(), user_id: $('input[name="user_id"]').val(), action: "stop" })
      .done(function( data ) {
	if (data == "success") {
	  $('input[name="timeclock_input"]').replaceWith('<div id="record-container"><textarea style="height: 200px;" name="timeclock_input" placeholder="Description of Work Completed"></textarea><br /><input type="button" name="submit_record" value="Add Record" /></div>');
	  currentSubmit = $('input[name="submit_record"]');
	  $('select[name="client_selector"]').show();
	} else {
	  alert(data);
	}
      });
  } else if (currentSubmit.val() == inputValueArray[2]) {
    $.post( "php/record.php", { client: $('select[name="client_selector"]').val(), user_id: $('input[name="user_id"]').val(), action: "record", record: $('textarea[name="timeclock_input"]').val() })
      .done(function( data ) {
	if (data == "success") {
	  $('#record-container').replaceWith('<input type="button" name="timeclock_input" value="Start Timeclock" />');
	  currentSubmit = $('input[name="timeclock_input"]');
	  $('select[name="client_selector"]').hide();
	  $('select[name="client_selector"]').find(":selected").attr("selected", false);
	  $('select[name="client_selector"]>option:eq(1)').attr("selected", true);
	  alert("Record created succesfully.");
	} else {
	  alert(data);
	}
      });
  }
});