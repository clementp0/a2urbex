import UserModalAction from './UserModalAction'

export default class UserModal {
  constructor(element, callback = null) {
    this.element = element
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
    this.element.addClass('disabled')

    $.ajax({
      type: 'POST',
      url: this.element.attr('href'),
      success: (data) => {
        this.element.removeClass('disabled')
        this.action = new UserModalAction($(data), this)
      },
      error: () => {
        this.element.removeClass('disabled')
      },
    })
  }

  deleteAction() {
    this.action = null
    this.current = null
  }
}
