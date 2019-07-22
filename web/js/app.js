app = {
    todo: {
        complete: {
            do: function (elem) {
                $.ajax({
                    method: 'PUT',
                    url: '/todo/complete/' + $(elem).data('task-id'),
                }).done(function (rsp) {
                    app.todo.complete.done(elem, rsp);
                }).error(function (xhr, ajaxOptions, thrownError) {

                });
            },

            done: function (elem, rsp) {
                $(elem).closest('.task-row').addClass('completed');
                $(elem).remove();
                $('#alert-container').html(rsp.html);

                app.alert.fadeAlerts();
            }
        }

    },

    alert: {
        timeout: null,

        fadeAlerts: function () {
            if (app.alert.timeout) clearTimeout(app.alert.timeout);

            app.alert.timeout = setTimeout(function () {
                $(".alert").fadeOut(1000);
            }, 2000);
        }
    },

    start: function () {
        app.alert.fadeAlerts();
    }
};