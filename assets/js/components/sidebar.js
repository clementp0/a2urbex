$(() => {
  $('legend:eq( 0 )').prepend('<i class="fa-solid fa-earth-europe"></i>')
  $('legend:eq( 1 )').prepend('<i class="fa-solid fa-gear"></i>')
  $('legend:eq( 2 )').prepend('<i class="fa-solid fa-sliders"></i>')

  $('#map-filter').on('click', function (e) {
    e.preventDefault()
    $('.pin-search form').attr('action', $(this).attr('href')).find('#submit').click()
  })

  $('.pin-search fieldset.form-group > legend').on('click', function () {
    let div = $(this).siblings('div')

    let open = $(this).attr('data-open') && $(this).attr('data-open') == 'true' ? false : true
    $(this).attr('data-open', open)

    if (open) {
      let height = div.children().length * 22.8
      div.css('maxHeight', height + 'px')
    } else {
      div.css('maxHeight', '0px')
    }
  })
})
