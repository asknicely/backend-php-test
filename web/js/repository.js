function getTodos() {
    return new Promise(function (resolve, reject) {
        fetch("/api/todos").then(function (data) {
            return data.json();
        }).then(function (data) {
            resolve(data);
        }).catch(function (err) {
            reject(err);
        });
    });
}

function deleteTodo(id) {
    return fetch("/api/todo/delete/" + id, {
        method: "POST"
    });
}

function changeTodoStatus(id, is_completed) {
    return fetch("/api/todo/changeCompleteStatus/" + id, {
        method: 'POST',
        body: JSON.stringify({
            is_completed: is_completed
        })
    });
}

function createNewTodo(text) {
    return new Promise(function (resolve, reject) {
        fetch("/api/todo/add", {
            method: 'POST',
            body: JSON.stringify({
                description: text
            })
        }).then(function (data) {
            return data.json();
        }).then(function (data) {
            resolve(data);
        }).catch(function (err) {
            reject(err);
        });
    });
}