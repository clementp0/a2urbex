import UserModalAction from './UserModalAction'

export default class UserModal {
  constructor(selector, callback = null) {
    this.element = $(selector)
    this.callback = callback
    this.action = null
    this.current = null

    this.triggers()
  }

  triggers() {
    this.element.on('click', (e) => this.open(e))
  }

  open(e) {
    e.preventDefault()
    const current = $(e.currentTarget)
    current.addClass('disabled')

    $.ajax({
      type: 'POST',
      url: current.attr('href'),
      success: (data) => {
        current.removeClass('disabled')
        this.action = new UserModalAction($(data), this)
      },
      error: () => {
        current.removeClass('disabled')
      },
    })
  }

  deleteAction() {
    this.action = null
    this.current = null
  }
}
