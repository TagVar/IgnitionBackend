var previouslySelected;
if ($("select[name='region_selector']").val() != "") {
  $("select[name='timezone_selector']").show();
  var tempCurrentTimezoneArray = timezoneArray[ $("select[name='region_selector']").val() ];
  var tempCurrentOffsetArray = timezoneOffsetArray[ $("select[name='region_selector']").val() ];
  var groupedTimezones = {};
  var groupedOffsets = {};
  for (counter = 0; counter < tempCurrentOffsetArray.length; counter++) { 
    significantOffset = tempCurrentOffsetArray[counter].replace(/[^\d.-]/g, '');
    if (!(significantOffset in groupedTimezones)) {
      groupedTimezones[significantOffset] = [];
      groupedOffsets[significantOffset] = [];
    }
    groupedTimezones[significantOffset].push(tempCurrentTimezoneArray[counter]);
    groupedOffsets[significantOffset].push(tempCurrentOffsetArray[counter]);    
  }
  var offsetArray = [];
  for (var property in groupedTimezones) {
    if (!groupedTimezones.hasOwnProperty(property)) {
        continue;
    }
    groupedTimezones[property].sort();
    groupedOffsets[property].sort();
    offsetArray.push(parseFloat(property));
  }
  offsetArray.sort(function(a, b){return a-b});
  var currentTimezoneArray = [];
  var currentOffsetArray = [];
  for (counter = 0; counter < offsetArray.length; counter++) {
    currentTimezoneArray = currentTimezoneArray.concat(groupedTimezones[offsetArray[counter]]);
    currentOffsetArray = currentOffsetArray.concat(groupedOffsets[offsetArray[counter]]);
  }
  for (counter = 0; counter < currentTimezoneArray.length; counter++) {
    if (timezoneSelectorPost == currentTimezoneArray[counter]) {
      previouslySelected = "selected";
    } else {
      previouslySelected = "";
    }
    $("select[name='timezone_selector']").append("<option value='" + currentTimezoneArray[counter] + "'" + previouslySelected + ">" + currentTimezoneArray[counter] + " (" + currentOffsetArray[counter] + ")</option>");
  }
}
$("select[name='region_selector']").change(function() {
  if ($("select[name='region_selector']").val() != "") {
    $("select[name='timezone_selector']").show();
    var tempCurrentTimezoneArray = timezoneArray[ $("select[name='region_selector']").val() ];
    var tempCurrentOffsetArray = timezoneOffsetArray[ $("select[name='region_selector']").val() ];
    var groupedTimezones = {};
    var groupedOffsets = {};
    for (counter = 0; counter < tempCurrentOffsetArray.length; counter++) { 
      significantOffset = tempCurrentOffsetArray[counter].replace(/[^\d.-]/g, '');
      if (!(significantOffset in groupedTimezones)) {
	groupedTimezones[significantOffset] = [];
	groupedOffsets[significantOffset] = [];
      }
      groupedTimezones[significantOffset].push(tempCurrentTimezoneArray[counter]);
      groupedOffsets[significantOffset].push(tempCurrentOffsetArray[counter]);    
    }
    var offsetArray = [];
    for (var property in groupedTimezones) {
      if (!groupedTimezones.hasOwnProperty(property)) {
	  continue;
      }
      groupedTimezones[property].sort();
      groupedOffsets[property].sort();
      offsetArray.push(parseFloat(property));
    }
    offsetArray.sort(function(a, b){return a-b});
    var currentTimezoneArray = [];
    var currentOffsetArray = [];
    for (counter = 0; counter < offsetArray.length; counter++) {
      currentTimezoneArray = currentTimezoneArray.concat(groupedTimezones[offsetArray[counter]]);
      currentOffsetArray = currentOffsetArray.concat(groupedOffsets[offsetArray[counter]]);
    }
    $("select[name='timezone_selector']").empty();
    $("select[name='timezone_selector']").append("<option value='' default>Please select a timezone...</option>");
    for (counter = 1; counter < currentTimezoneArray.length; counter++) {
      if (timezoneSelectorPost == currentTimezoneArray[counter]) {
	previouslySelected = "selected";
      } else {
	previouslySelected = "";
      }
      $("select[name='timezone_selector']").append("<option value='" + currentTimezoneArray[counter] + "' " + previouslySelected + ">" + currentTimezoneArray[counter] + " (" + currentOffsetArray[counter] + ")</option>");
    }
  } else {
    $("select[name='timezone_selector']").hide();
  }
});