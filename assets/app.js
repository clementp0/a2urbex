/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

$(() => {
    console.log('coucou 2 hugo')
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
 })
// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

