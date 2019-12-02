((window, document)=>{

    /* source: https://codepen.io/damianmuti/pen/GEZoeG */
    let descriptionValidation = (event) => {
        let description = document.querySelector('input[name="description"]').value; console.log(description)
        if(description == ''){
            event.preventDefault();
            var $ = window.jQuery;
            $('.notify')
                .removeClass()
                .attr('data-notification-status', 'error')
                .addClass('top-left notify')
                .addClass('do-show');
            setTimeout(() => {
                $('.notify')
                    .removeClass('do-show')
            }, 6000)
        }
    }
    const form = document.querySelector('.description-form');
    if(form){
        //form.addEventListener('submit', descriptionValidation);
    }
    

    /* flash fadeOut */
    $('.alert-success').hide();
    setTimeout(()=>{
        $('.alert-success').slideDown();
    }, 500)
    setTimeout(()=>{
        $('.alert-success').slideUp()
    }, 5000)

    /* Task Completed */
    let base = document.querySelector('base').getAttribute('href')
    let icons = document.querySelectorAll('.completed-field span');
    for (i = 0; i < icons.length; ++i) {
        icons[i].addEventListener('click', (a) => {

            if (a.target.classList.contains('glyphicon-time')) {
                updating(a.target, 'glyphicon-time')
            } else {
                updating(a.target, 'glyphicon-ok')
            }

        });
    }

    let updating = (element, value) => {
        if (value == 'glyphicon-time') {
            completed(element)
        } else if (value == 'glyphicon-ok') {
            uncompleted(element)
        }
    }

    let completed = (element) => {
        element.classList.remove("glyphicon-time")
        element.classList.add("glyphicon-hourglass")
        fetch(base + '/todo/' + element.getAttribute('todo-id'), {
                method: 'POST',
                body: JSON.stringify({
                    completed: '1'
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then((data) => {
                setTimeout(() => {
                    element.classList.add("glyphicon-ok")
                    element.classList.remove("glyphicon-hourglass")
                }, 1000)

            })
            .catch(console.log)
    }

    let uncompleted = (element) => {
        element.classList.remove("glyphicon-ok")
        element.classList.add("glyphicon-hourglass")
        fetch(base + '/todo/' + element.getAttribute('todo-id'), {
                method: 'POST',
                body: JSON.stringify({
                    completed: '0'
                }),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then((data) => {
                setTimeout(() => {
                    element.classList.add("glyphicon-time")
                    element.classList.remove("glyphicon-hourglass")
                }, 1000)
            })
            .catch(console.log)
    }
    
    
    /* Delete tasks */
    let formToDelete = null;

    let deleteValidation = (event) => {
        event.stopPropagation();
        $('#deleteModal').modal()
        formToDelete = $(event.target).parent()
        if (formToDelete.is("form")) {
            //ok - click on button
        } else {
            //ok - click on button  > span
            formToDelete = $(event.target).parent().parent()
        }
        let description = $.trim($(formToDelete).parent().parent().find('.description').text());
        console.log(description)
        $('#deleteModal .description').html(description)
        event.preventDefault();
    }

    window.deleteTask = () => {
        formToDelete.submit();
    }

    let deleteForms = document.querySelectorAll('.delete-form button');
    for (i = 0; i < deleteForms.length; ++i) {
        deleteForms[i].addEventListener('click', deleteValidation);
    }
    
})(window, document)