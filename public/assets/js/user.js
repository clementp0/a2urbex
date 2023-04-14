$(() => {
  $('.inmodal').on('click', function (e) {
    e.preventDefault()
    const url = $(this).attr('href')

    $('.inmodal').addClass('disabled')

    $.ajax({
      type: 'POST',
      url,
      success: (data) => {
        $('.inmodal').removeClass('disabled')

        $('body').find('.cmodal-background').remove().end().append(data)

        setTimeout(() => {
          $('body').find('.cmodal-background').removeClass('hidden')
        }, 10)
      },
      error: () => {
        $('.inmodal').removeClass('disabled')
      },
    })
  })

  function closeModal(item) {
    item.addClass('hidden')
    setTimeout(() => {
      item.remove()
    }, 500)
  }
  $('body').on('click', '.cmodal-background', function () {
    closeModal($(this))
  })
  $('body').on('click', '.cmodal-close', function () {
    closeModal($(this).parents('.cmodal-background'))
  })

  $('body').on('click', '.cmodal', function (e) {
    e.stopPropagation()
  })
})
