$(document).ready( function(){

  $('.delete-task').hide();
  $('.status-message').hide();

  const queryString = window.location;

  // Delete ToDo Task By Id
  //-------------------------------------------------------------------//
  $('.del-task-id').click(function (e){
    e.preventDefault();
    $('.error-message').hide();

    if(confirm("Are you sure you want to delete this task?")){

          let task_id = $(this).val();

          $.ajax({
            method: "POST",
            url: "/todo/delete/"+task_id
          }).done(function( r ) {
            if(r == true){
              $('#'+task_id).remove();
              $('.delete-task').show();
            }
          });
    }
    else{
        return false;
    }
  });

  // Update ToDo Task To Completed By Id
  //-------------------------------------------------------------------//
  $('.status-task-id').click(function (e){
    e.preventDefault();
    $('.error-message').hide();

    if(confirm("Are you sure you want to delete this task?")){

          let task_id = $(this).attr("task_id");
          let task_status = $(this).attr("task_status");

          $.ajax({
            method: "POST",
            url: "/todo/status/"+task_id+"/"+task_status
          }).done(function( r ) {
            if(r == true){
              $('a[task_id="'+task_id+'"]').text('').unwrap().wrap('<td>Completed</td>');
              $('.status-message').show();
            }
          });
    }
    else{
        return false;
    }
  });


});
