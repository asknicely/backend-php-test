function getTodos(): Promise {
    return new Promise(((resolve, reject) => {
        fetch("/api/todos").then((data) => data.json()).then((data) => {
            resolve(data);
        }).catch((err) => {
            reject(err);
        })
    }))
}

function deleteTodo(id): Promise {
    return fetch(`/api/todo/delete/${id}`, {
        method: "POST",
    });
}

function changeTodoStatus(id, is_completed): Promise {
    return fetch(`/api/todo/changeCompleteStatus/${id}`, {
        method: 'POST',
        body: JSON.stringify({
            is_completed: is_completed
        })
    });
}