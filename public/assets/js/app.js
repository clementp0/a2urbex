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
        let parent = item.parents('.pin-fav-wrapper')

        let fids = parent.attr('data-fids').length ? parent.attr('data-fids').split(',').map(item => parseInt(item)) : null
        let id = parent.data('id')

        $.ajax({
            url: item.attr('href'),
            method: 'POST',
            dataType: 'json',
            // data: {id: item.data('id')}  
        }).done((json) => {
            if(json && json.length) {
                parent.find('.pin-fav-list').empty()

                json.forEach(item => {
                    let cid = 'fav_'+id+'_'+item.fav.id
                    let line = $('<div>').addClass('form-check')
                    let input = $('<input type="checkbox" class="form-check-input pin-fav-item" value="'+item.fav.id+'" id="'+cid+'">')
                    let label = $('<label class="form-check-label" for="'+cid+'">'+item.fav.name+'</label>')
                    if(fids!== null) input.prop('checked', fids.includes(item.fav.id) ? true : false)
                    
                    line.append(input).append(label)
                    parent.find('.pin-fav-list').append(line)
                })

                parent.find('.pin-fav-add').addClass('show')
            }
        }).fail(() => {
            alert('Error')
        })
    })

    $('.pin-fav-add-close').on('click', function(e) {
        e.preventDefault()
        $(this).parents('.pin-fav-add').removeClass('show')
    })

    $('.pin-fav-wrapper').on('click', '.pin-fav-item', function() {
        let parent = $(this).parents('.pin-fav-wrapper')
        let fid = $(this).val()
        let lid = parent.data('id')
        let checked = $(this).prop('checked') ? 1 : 0

        $.ajax({
            url: parent.data('url'),
            method: 'POST',
            dataType: 'json',
            data: {lid, fid, checked}  
        }).done(json => {
            if(json.success) {
                parent.attr('data-fids', json.fids)
            } else {
                alert('Error')
            }
        }) .fail(() => {
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