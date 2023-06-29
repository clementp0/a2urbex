export default class UserModal {
  constructor(selector) {
    this.element = $(selector)
    this.triggers()
  }

  triggers() {
    this.element.on('click', (e) => this.open(e))
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
}
