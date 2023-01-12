$(() => {

    $( "legend:eq( 0 )" ).replaceWith( "<legend class='col-form-label required'><i class='fa-solid fa-earth-europe'></i>Country</legend>" );
    $( "legend:eq( 1 )" ).replaceWith( "<legend class='col-form-label required'><i class='fa-solid fa-gear'></i></i>Type</legend>" );

    $('.pin-open-search').on('click', () => {
        $('.pin-wrapper').toggleClass('menu-open')
    })
    $('.pin-search-wrapper').on('click', function(e) {
        if(e.target != this) return
        $('.pin-wrapper').removeClass('menu-open')
    })

    $('.pin-fav').on('click', function(e) {
        e.preventDefault()
        let item = $(this)
        let icon = $(this).children()

        $.ajax({
            url: item.attr('href'),
            method: 'POST',
            dataType: 'json',
            data: {id: item.data('id')}           
        }).done((json) => {
            if(json.success) {
                if(icon.hasClass('fa-regular')) icon.removeClass('fa-regular').addClass('fa-solid')
                else icon.addClass('fa-regular').removeClass('fa-solid')
            } else {
                alert('Error')
            }
        }).fail(() => {
            alert('Error')
        })
    })

    // below replace with ajax later
    $('.fav-item-delete').on('click', function(e) {
        if(!confirm('Confirmer la suppression')) e.preventDefault()
    })
    $('.fav-item-share-link').on('click', function(e) {
        if(!confirm('Confirmer le partage du lien')) e.preventDefault()
    })

    $('.fav-item-share-user').on('click', function(e) {
        $(this).siblings('.share-select-wrapper').toggleClass('show')
    })

    $('.share-select').on('change', function(e) {
        $(this).siblings('.share-select-link').attr('href', $(this).val())
    })
 })