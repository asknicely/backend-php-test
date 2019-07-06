
// Asynchronously completing a todo
function completeTodo(id) {
    const xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const completedTodoButton = document.getElementById('completebutton_' + id);
            completedTodoButton.innerText = "COMPLETED";
            completedTodoButton.setAttribute("class", "btn btn-xs btn-success");
        }
    };

    xhttp.open("GET", "/todo/" + id + "/complete", true);
    xhttp.send();
}

// Asynchronously deleting a todo
function deleteTodo(id) {
    const xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const deletedTodo = document.getElementById('todotr_' + id);
            deletedTodo.remove();
        }
    };

    xhttp.open("GET", "/todo/delete/" + id, true);
    xhttp.send();
}