// Force refresh page
$(document).ready(function() {
	if (window.name != "bencalie") {
		location.reload();
		window.name = "bencalie";
	} else {
		window.name = "";
	}
});
// Checkbox listener
$("input[name=completed]").change(function() {
	var id = this.value;
	if ($(this).is(":checked")) {
		changeCompleted(id, true);
	} else {
		changeCompleted(id, false);
	}
});
// Change Completed status of checkbox
function changeCompleted(id, status) {
	var url = "/todo/" + id + "/check";
	$.ajax({
		type: "POST",
		url: url,
		data: { completed: status },
		success: function(res) {
			var completed = status ? "done" : "todo";
			$("#todo_desc_" + id).attr("class", completed);
			$("#todo_desc_" + id).attr("class", completed);
		}
	});
}
