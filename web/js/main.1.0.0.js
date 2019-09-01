$(document).ready(function(){

    // - Declare Variables
        // - HTML Document
    var window_height       = $( document ).height();

        // - Nav Section
    var nav_home_icon       = $('.navbar-brand i');

        // - Forms
            // - Login Form
           var login_form   = $('#login-form');

            // - Todos Addistions
           var todo_add   = $('#todo_add');

    // - Set the height of the body
    $('html').css('min-height',window_height);

        // - Watch for window resize
        $('html').resize(function(){

            // Redclare new Window height value
            var window_height   = $( document ).height();

            // Reinitialise the Body height
            $('html').css('min-height',window_height);

        });

    // - Adding animation to the home icon
    nav_home_icon.hover(function(){
       $(this).toggleClass('animated rubberBand');
    });

    // Login Form validation
    login_form.validate({
        errorClass: 'error is-invalid',
        validClass: 'is-valid',
        errorPlacement: function(error, element) {}
    });

    // Login Form validation
    todo_add.validate({
        errorClass: 'error is-invalid',
        validClass: 'is-valid',
        errorPlacement: function(error, element) {}
    });
});