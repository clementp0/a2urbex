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

  $('body').on('keyup', '.cmodal-search', function () {
    let el = $(this)
    searchUser(el.val(), el.data('url'))
  })
  $('body').on('paste', '.cmodal-search', function () {
    setTimeout(() => {
      let el = $(this)
      searchUser(el.val(), el.data('url'))
    }, 100)
  })

  function searchUser(string, url) {
    $.ajax({
      type: 'POST',
      url,
      data: {
        search: string,
        exclude_friends: true,
      },
      success: (data) => {},
      error: () => {},
    })
  }
})
