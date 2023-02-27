$(() => {

    $('legend:eq( 0 )').prepend('<i class="fa-solid fa-earth-europe"></i>')
    $('legend:eq( 1 )').prepend('<i class="fa-solid fa-gear"></i>')
    $('legend:eq( 2 )').prepend('<i class="fa-solid fa-sliders"></i>')

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

        let id = parent.attr('data-id')

        $.ajax({
            url: item.attr('href'),
            method: 'POST',
            dataType: 'json',
            data: {lid: id}
        }).done((json) => {
            if(json) {
                parent.find('.pin-fav-list').empty()

                let fids = json.fids && json.fids.length ? json.fids.split(',').map(item => parseInt(item)) : null

                json.favs.forEach(item => {
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
        $(this).parents('.pin-fav-add').find('.pin-fav-add-new-field').removeClass('show')
    })

    $('.pin-fav-wrapper').on('click', '.pin-fav-item', function() {
        let parent = $(this).parents('.pin-fav-wrapper')
        let fid = $(this).val()
        let lid = parent.attr('data-id')
        let checked = $(this).prop('checked') ? 1 : 0

        $.ajax({
            url: parent.data('url'),
            method: 'POST',
            dataType: 'json',
            data: {lid, fid, checked}  
        }).done(json => {
            if(json.success) {
                parent.attr('data-fids', json.fids ? json.fids : '')
                if(json.fids) parent.find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
                else parent.find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
            } else {
                alert('Error')
            }
        }) .fail(() => {
            alert('Error')
        })
    })

    $('.pin-fav-add-new').on('click', function(e) {
        e.preventDefault()
        $(this).siblings().addClass('show')
    })
    $('.pin-fav-add-new-confirm').on('click', function(e) {
        e.preventDefault()

        let parent = $(this).parents('.pin-fav-wrapper')
        let input = $(this).siblings()
        let lid = parent.data('id')
        let name = input.val()
        
        if(name.length && confirm('Confirmer l\'ajout')) {
            $.ajax({
                url: parent.data('url'),
                method: 'POST',
                dataType: 'json',
                data: {lid, name}  
            }).done(json => {
                if(json.success) {
                    input.val('')
                    input.parents('.pin-fav-add-new-field').removeClass('show')
                    parent.attr('data-fids', json.fids ? json.fids : '')
                    if(json.fids) parent.find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
                    else parent.find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
                    parent.find('.pin-fav').click()
                } else {
                    alert('Error')
                }
            }) .fail(() => {
                alert('Error')
            })
        }
    })

    // below replace with ajax later
    $('.fav-item-delete').on('click', function(e) {
        if(!confirm('Delete list')) e.preventDefault()
    })
    $('.fav-item-share-link').on('click', function(e) {
        if(!confirm('Change list permission')) e.preventDefault()
    })
    $('.fav-item-copy-link').on('click', function(e) {
        if(!confirm('Copy list link')) e.preventDefault()
    })

    $('.fav-item-share-user').on('click', function(e) {
        $(this).siblings('.share-select-wrapper').toggleClass('show')
    })

    $('.share-select').on('change', function(e) {
        $(this).siblings('.share-select-link').attr('href', $(this).val())
    })


    $('#map-filter').on('click', function(e) {
        e.preventDefault()
        $('.pin-search form')
            .attr('action', $(this).attr('href'))
            .find('#submit').click()
    })

    $('.pin-search fieldset.form-group > legend').on('click', function() {
        let div = $(this).siblings('div')

        let open = $(this).attr('data-open') && $(this).attr('data-open') == 'true' ? false : true
        $(this).attr('data-open', open)

        if(open) {
            let height = div.children().length * 21
            div.css('maxHeight', height + 'px')
        } else {
            div.css('maxHeight', '0px')
        }
    })
 })