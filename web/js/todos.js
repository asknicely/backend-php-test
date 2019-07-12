
// Load Todos list Start
$.ajax({
    type: "GET",
    url: '/todoslist',
}).done(function(response){
	// if user session is expire
	if(response == "login required"){
		location.href = '\login';
	}
	//load list
    $('#todoslist').html(response);                       
}).fail(function(){
 	var message="Something wrong. Try again.....";
	$('#todoslist').html(message);  
});
// Load Todos list End

// Delete Todo start
$(document).on('click', '.deletetodo', function() {

$(this).find("span").removeClass("glyphicon-remove");
$(this).find("span").addClass("glyphicon-refresh glyphicon-spin");
var todoid= $(this).attr("todoid");
	$.ajax({
		type: "GET",
		url: '/todos/ajaxdelete/'+todoid,
	}).done(function(response){
		// if user session is expire
		if(response == "login required"){
		   location.href = '\login';
		  }
		  //remove from list
	    $('tr.todolidTR'+todoid).hide(1000);   
		$(".successMessage").find("span").html("Todo deleted successfully.");
			$(".successMessage").show(1000);
			setInterval(function(){
			    $(".successMessage").hide(1000);
			}, 4000); 
			
	}).fail(function(){
		alert('Something wrong. Try again.....');
	});
});
// Delete Todo end

// Todo change status to complete start
$(document).on('click', '.completetodo', function() {

var cthis = $(this); 
$(this).find("span").removeClass("glyphicon-plus");
$(this).find("span").addClass("glyphicon-refresh glyphicon-spin");

var todoid= $(this).attr("todoid");
	$.ajax({
		type: "GET",
		url: '/todos/completetodo/'+todoid,
	}).done(function(response){
		// if user session is expire
		if(response == "login required"){
		   location.href = '\login';
		  }
             cthis.find("span").removeClass("glyphicon-refresh glyphicon-spin");
			 cthis.find("span").addClass("glyphicon-minus");
			 cthis.addClass("inprogresstodo");
			 cthis.addClass("btn-info");
			 cthis.removeClass("btn-success");
			 cthis.removeClass("completetodo");
			 
			$(".successMessage").find("span").html("Todo completed successfully.");
			$(".successMessage").show(1000);
			setInterval(function(){
			    $(".successMessage").hide(1000);
			}, 4000); 
			 
	}).fail(function(){
		alert('Something wrong. Try again.....');
		cthis.find("span").removeClass("glyphicon-refresh glyphicon-spin");
		cthis.find("span").addClass("glyphicon-plus");
	});
});
// Todo change status to complete End


$(document).on('click', '.inprogresstodo', function() {
	alert('Contact admin to re-schedule todo.');
});

// Add Todo and update list Start
$(document).on('click', '#addtodos', function() {
var cthis = $(this);
/*
	if($("#description").val().trim() == ""){
		alert("Please insert description");
		$("#description").val("");
		$("#description").focus();
		return;
	}
	
	*/
	$(this).find("span").show();
	$.ajax({
		type: "POST",
		url: '/todos/ajaxadd',
		data: { description: $("#description").val()} ,
	   }).done(function(response){
			// if user session is expire
			if(response == "login required"){
			   location.href = '\login';
			  }else if(response == "Please add description"){
				  cthis.find("span").hide(2000);
				$(".errorMesssage").find("span").html("Please add description.");
				$(".errorMesssage").show(1000);
				setInterval(function(){
					$(".errorMesssage").hide(1000);
					return;
				}, 4000);
				return;
			  }
			  
			var obj = JSON.parse(response);
			var trhtml = '<tr class="todolidTR'+ obj.id +'">'
					+'<td>'+obj.id+'</td>'
					+'<td>'+obj.user_id+'</td>'
					+'<td><a href="/todo/'+obj.id+'">'+obj.description+'</a></td>'
					+'<td>'
					 +'<button todoid="'+obj.id+'" type="button" class="btn btn-xs btn-danger deletetodo"><span class="glyphicon glyphicon-remove glyphicon-white"></span></button>'
					+' <button todoid="'+obj.id+'" type="button" class="btn btn-xs btn-success completetodo"><span class="glyphicon glyphicon-plus glyphicon-white"></span></button>'         
				   + '</td><tr>';
			$("#traddTodos").before(trhtml);
			cthis.find("span").hide(2000);
			$(".successMessage").find("span").html("Todo added successfully.");
			$(".successMessage").show(1000);
			setInterval(function(){
			    $(".successMessage").hide(1000);
			}, 4000);
			
		}).fail(function(){
			alert('Something wrong. Try again.....');
			cthis.find("span").hide(2000);
	    });
	$("#description").val("");

	});
// Add Todo and update list end


