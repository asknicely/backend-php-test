// Update the status of checkbox
$(document).ready(function() {
	$("input[name='completed']").each(function(j, item) {
		var id = item.value;
		var status = $("#todo_desc_" + id).attr("class");
		item.checked = status == "done" ? true : false;
	});
	// Assign max rows value from localStorage to the select element
	if (localStorage.maxRows) {
		$("#maxRows")
			.val(localStorage.maxRows)
			.change();
	}
	// Run pagination function
	getPagination("#table-id", 0);
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

//function: implement paginating function
function getPagination(table, flag) {
	var lastPage = 1;
	$("#maxRows")
		.on("change", function(evt) {
			//$('.paginationprev').html(''); // reset pagination
			lastPage = 1;
			$(".pagination")
				.find("li")
				.slice(1, -1)
				.remove();
			var trnum = 0;
			var maxRows = parseInt($(this).val()); // get Max Rows from select option
			// store max rows
			localStorage.maxRows = maxRows;
			if (maxRows == 5000) {
				$(".pagination").hide();
			} else {
				$(".pagination").show();
			}
			var totalRows = $(table + " tbody tr").length - 1; // numbers of rows
			var currentPage = 1;
			if (flag > 0) {
				localStorage.currentPage = currentPage;
			} else {
				if (localStorage.currentPage) {
					currentPage = localStorage.currentPage;
				}
			}
			$(table + " tr:gt(0)").each(function() {
				// each TR in table and not the header
				trnum++; // Start Counter
				if (
					trnum > maxRows * currentPage ||
					trnum <= maxRows * currentPage - maxRows
				) {
					$(this).hide();
				} else {
					$(this).show();
				} //else fade in
				// if (trnum > maxRows) {
				// 	// if tr number gt maxRows
				// 	$(this).hide(); // fade it out
				// }
				// if (trnum <= maxRows) {
				// 	$(this).show();
				// } // else fade in Important in case if it ..
			}); // was fade out to fade it in
			if (totalRows > maxRows) {
				// if tr total rows gt max rows option
				var pageNums = Math.ceil(totalRows / maxRows);
				// numbers of pages
				for (var i = 1; i <= pageNums; ) {
					// for each page append pagination li
					$(".pagination #prev")
						.before(
							'<li data-page="' +
								i +
								'">\
<span>' +
								i++ +
								'<span class="sr-only">(current)</span></span>\
</li>'
						)
						.show();
				} // end for i
			} // end if row count > max rows
			$('.pagination [data-page="' + currentPage + '"]').addClass("active"); // add active class to the first li
			$(".pagination li").on("click", function(evt) {
				// on click each page
				evt.stopImmediatePropagation();
				evt.preventDefault();
				var pageNum = $(this).attr("data-page"); // get it's number
				localStorage.currentPage = pageNum;
				var maxRows = parseInt($("#maxRows").val()); // get Max Rows from select option
				if (pageNum == "prev") {
					if (lastPage == 1) {
						return;
					}
					pageNum = --lastPage;
				}
				if (pageNum == "next") {
					if (lastPage == $(".pagination li").length - 2) {
						return;
					}
					pageNum = ++lastPage;
				}
				lastPage = pageNum;
				var trIndex = 0; // reset tr counter
				$(".pagination li").removeClass("active"); // remove active class from all li
				$('.pagination [data-page="' + lastPage + '"]').addClass("active"); // add active class to the clicked
				// $(this).addClass('active'); // add active class to the clicked
				$(table + " tr:gt(0)").each(function() {
					// each tr in table not the header
					trIndex++; // tr index counter
					// if tr index gt maxRows*pageNum or lt maxRows*pageNum-maxRows fade if out
					if (
						trIndex > maxRows * pageNum ||
						trIndex <= maxRows * pageNum - maxRows
					) {
						$(this).hide();
					} else {
						$(this).show();
					} //else fade in
				}); // end of for each tr in table
			}); // end of on click pagination list
			flag++;
		})
		.change();
	// end of on select change
	// END OF PAGINATION
}
