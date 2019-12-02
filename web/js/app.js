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
    
})(window, document)