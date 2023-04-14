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

  $('body').on('click', '.cmodal-unselect', function () {
    const selected = $('body').find('.cmodal .cmodal-selected')
    const validate = $('body').find('.cmodal .cmodal-footer a')
    const searchWrapper = $('body').find('.cmodal .cmodal-search-wrapper')

    searchWrapper.removeClass('hidden')
    selected.addClass('hidden').find('.cmodal-item').remove()
    validate.addClass('disabled').text(validate.data('origin')).attr('href', '#')
  })

  let prevString = ''
  function searchUser(string, url) {
    const container = $('body').find('.cmodal .cmodal-result')
    const noresult = $('body').find('.cmodal .cmodal-noresult')
    const selected = $('body').find('.cmodal .cmodal-selected')
    const validate = $('body').find('.cmodal .cmodal-footer a')
    const searchWrapper = $('body').find('.cmodal .cmodal-search-wrapper')

    string = string.trim()

    if (string === prevString) return
    prevString = string
    if (string.length < 1) {
      container.empty()
      return
    }

    noresult.find('.cmodal-string').text(string)

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url,
      data: {
        search: string,
        exclude_friends: true,
      },
      success: (data) => {
        container.empty()

        if (data.length) {
          noresult.addClass('hidden')
          container.removeClass('hidden')

          data.forEach((element) => {
            const item = $(
              `<div class="cmodal-item" data-id="${element.id}">
                <div class="cmodal-item-left"><i class="fa-solid fa-user"></i></div>
                <div class="cmodal-item-right">
                  <p class="cmodal-item-name">${element.firstname} ${element.lastname}</p>
                  <p class="cmodal-item-username">${element.username}</p>
                </div>
              </div>`
            )

            item.on('click', function () {
              const clone = $(this).clone()
              const selectedId = clone.data('id')
              const selectedName = clone.find('.cmodal-item-name').text()

              searchWrapper.addClass('hidden').find('.cmodal-search').val('')
              selected.append(clone).removeClass('hidden')
              container.addClass('hidden').empty()
              validate
                .attr('href', validate.data('href') + selectedId)
                .text(validate.data('alt').replace('%user%', selectedName))
                .removeClass('disabled')
            })

            container.append(item)
          })
        } else {
          container.addClass('hidden')
          noresult.removeClass('hidden')
        }
      },
      error: () => {},
    })
  }
})
