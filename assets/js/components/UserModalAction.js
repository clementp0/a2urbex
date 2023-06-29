export default class UserModalAction {
  constructor(html, parent) {
    this.element = html
    this.parent = parent
    this.prevString = ''

    this.default()
    this.triggers()
  }

  default() {
    this.clearModals()
    $('body').append(this.element)

    setTimeout(() => {
      this.element.removeClass('hidden')
    }, 10)

    this.modalElement = this.element.find('.cmodal')
    this.closeElement = this.element.find('.cmodal-close')
    this.searchElement = this.element.find('.cmodal-search')
    this.searchWrapperElement = this.element.find('.cmodal-search-wrapper')
    this.resultElement = this.element.find('.cmodal-result')
    this.noresultElement = this.element.find('.cmodal-noresult')
    this.unselectElement = this.element.find('.cmodal-unselect')
    this.selectedElement = this.element.find('.cmodal-selected')
    this.validateElement = this.element.find('.cmodal-footer a')
  }

  clearModals() {
    $('body').find('.cmodal-background').remove()
  }

  triggers() {
    this.element.on('click', () => this.close())
    this.closeElement.on('click', () => this.close())
    this.modalElement.on('click', (e) => e.stopPropagation())
    this.searchElement.on('keyup', (e) => this.search())
    this.searchElement.on('paste', (e) => setTimeout(() => this.search(), 100))
    this.unselectElement.on('click', (e) => this.unselect())

    if (typeof this.parent.callback === 'function')
      this.validateElement.on('click', (e) => this.parent.callback(e))
  }

  close() {
    this.element.addClass('hidden')

    setTimeout(() => {
      this.element.remove()
      this.parent.deleteAction()
    }, 500)
  }

  search() {
    let string = this.searchElement.val()
    const url = this.searchElement.data('url')

    string = string.trim()
    if (string === this.prevString) return
    this.prevString = string
    if (string.length < 1) return this.resultElement.empty()

    this.noresultElement.find('.cmodal-string').text(string)

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url,
      data: {
        search: string,
      },
      success: (data) => {
        this.resultElement.empty()

        if (data.length) {
          this.noresultElement.addClass('hidden')
          this.resultElement.removeClass('hidden')

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

            item.on('click', (e) => {
              this.parent.current = element

              const clone = $(e.currentTarget).clone()
              const selectedId = clone.data('id')
              const selectedName = clone.find('.cmodal-item-name').text()

              this.searchWrapperElement.addClass('hidden')

              this.searchElement.val('')
              this.selectedElement.append(clone).removeClass('hidden')
              this.resultElement.addClass('hidden').empty()

              this.validateElement
                .attr('href', this.validateElement.data('href') + selectedId)
                .text(this.validateElement.data('alt').replace('%user%', selectedName))
                .removeClass('disabled')
            })

            this.resultElement.append(item)
          })
        } else {
          this.resultElement.addClass('hidden')
          this.noresultElement.removeClass('hidden')
        }
      },
      error: () => {},
    })
  }

  unselect() {
    this.parent.current = null

    this.searchWrapperElement.removeClass('hidden')
    this.selectedElement.addClass('hidden').find('.cmodal-item').remove()
    this.validateElement
      .addClass('disabled')
      .text(this.validateElement.data('origin'))
      .attr('href', '#')
  }
}
