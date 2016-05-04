$("input[name=expiration_date]").hide();
if ($("input[name=has_expiration]").is(":checked")) {
	$("input[name=expiration_date]").show();
}
$("input[name=has_expiration]").click(function() {
	$("input[name=expiration_date]").toggle();
});
$("#conditional-container").hide();
if ($("input[name=has_condition]").is(":checked")) {
	$("#conditional-container").show();
}
$("input[name=has_condition]").click(function() {
	$("#conditional-container").toggle();
});
var operationRequiredArray = [
	"Reduce by Percentage",
	"Free With Purchase",
	"Reduce by Fixed Amount",
	"Buy One Get One Free"
];
var variableArray = [
	"Percentage Off",
	"Name of Item",
	"Amount To Remove (D.CC)",
	"Name of Item"
];
var placeHolderValue;
if ($.inArray($("#operation").val(), operationRequiredArray) == -1) {
	$("#variable").hide();
} else {
	placeHolderValue = variableArray[operationRequiredArray.indexOf($("#operation").val())];
	$("#variable").attr("placeholder", placeHolderValue);
}
$("#operation").on("change", function() {
	if ($.inArray($("#operation").val(), operationRequiredArray) !== -1) {
		placeHolderValue = variableArray[operationRequiredArray.indexOf($("#operation").val())];
		$("#variable").attr("placeholder", placeHolderValue);
		$("#variable").show();
	} else {
		$("#variable").hide();
	}
});
var conditionsArray = [
	"Total Order Cost Must Be Over Amount",
    "Order Must Contain Item",
    "Order Must Contain More Than A Certain Number of Items",
    "Applies to Category"
];
var conditionVariableArray = [
	"Threshold Cost Amount (D.CC)",
	"Required Item",
	"Threshold Item Amount",
	"Required Category"
];
$("#condition_variable").attr("placeholder", conditionVariableArray[$.inArray($("#condition").val(), conditionsArray)]);
$("#condition").on("change", function() {
	$("#condition_variable").attr("placeholder", conditionVariableArray[$.inArray($(this).val(), conditionsArray)]);
});
