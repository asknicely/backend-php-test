$(function () {
    $(".delete-form").submit(function (event) {
        event.preventDefault();

        var action = $(this).attr('action');
        var formData = $(this).serialize();
        var form = $(this);

        $.ajax({
            url: action,
            type: 'post',
            dataType: 'json',
            data: formData,
            beforeSend: function (xhr) {
                if (confirm('Are you sure you want to delete this task?')) {
                    return true;
                } else {
                    return false;
                }
            }
        }).done(function (data, textStatus, xhr) {
            if (200 == xhr.status) {
                $(".success-message").html(data.message);
                form.parent().parent().animate({
                    opacity: 0
                }, 'slow', function () {
                    form.parent().parent().remove();
                });
            } else {
                $(".error-message").html(data.message);
            }

        });

    });

    $(".complete-form").submit(function (event) {
        event.preventDefault();

        var action = $(this).attr('action');
        var formData = $(this).serialize();
        var form = $(this);
        $.ajax({
            url: action,
            type: 'post',
            dataType: 'json',
            data: formData

        }).done(function (data, textStatus, xhr) {
            if (200 == xhr.status) {
                $(".success-message").html(data.message);
            } else {
                $(".error-message").html(data.message);
            }

        });

    });
}) 