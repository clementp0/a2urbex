export default class UserModal {
  constructor(selector) {
    this.element = $(selector)
    this.prevString = ''

    this.triggers()
  }

  triggers() {
    this.element.on('click', (e) => this.open(e))

    $('body').on('click', '.cmodal-background', this.close)
    $('body').on('click', '.cmodal-close', this.close)
    $('body').on('click', '.cmodal', (e) => e.stopPropagation())

    $('body').on('keyup', '.cmodal-search', (e) => this.search(e))
    $('body').on('paste', '.cmodal-search', (e) => setTimeout(() => this.search(e), 100))

    $('body').on('click', '.cmodal-unselect', this.unselect)
  }

  open(e) {
    e.preventDefault()
    const current = $(e.currentTarget)

    const url = current.attr('href')

    current.addClass('disabled')

    $.ajax({
      type: 'POST',
      url,
      success: (data) => {
        current.removeClass('disabled')

        $('body').find('.cmodal-background').remove().end().append(data)

        setTimeout(() => {
          $('body').find('.cmodal-background').removeClass('hidden')
        }, 10)
      },
      error: () => {
        current.removeClass('disabled')
      },
    })
  }

  close() {
    const item = $('.cmodal-background')

    item.addClass('hidden')
    this.prevString = ''

    setTimeout(() => {
      item.remove()
    }, 500)
  }

  search(e) {
    const current = $(e.currentTarget)
    let string = current.val()
    const url = current.data('url')

    const container = $('body').find('.cmodal .cmodal-result')
    const noresult = $('body').find('.cmodal .cmodal-noresult')
    const selected = $('body').find('.cmodal .cmodal-selected')
    const validate = $('body').find('.cmodal .cmodal-footer a')
    const searchWrapper = $('body').find('.cmodal .cmodal-search-wrapper')

    string = string.trim()
    if (string === this.prevString) return
    this.prevString = string
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
        exclude: true,
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

  unselect() {
    const selected = $('body').find('.cmodal .cmodal-selected')
    const validate = $('body').find('.cmodal .cmodal-footer a')
    const searchWrapper = $('body').find('.cmodal .cmodal-search-wrapper')

    searchWrapper.removeClass('hidden')
    selected.addClass('hidden').find('.cmodal-item').remove()
    validate.addClass('disabled').text(validate.data('origin')).attr('href', '#')
  }
}
