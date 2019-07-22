app = {
    todo: {
        complete: {
            do: function (elem) {
                $.ajax({
                    method: 'PUT',
                    url: '/todo/complete/' + $(elem).data('task-id'),
                }).done(function () {
                    app.todo.complete.done(elem);
                }).error(function (xhr, ajaxOptions, thrownError) {

                });
            },

            done: function (elem) {
                $(elem).closest('.task-row').addClass('completed');
                $(elem).remove();
            }
        }

    }
};