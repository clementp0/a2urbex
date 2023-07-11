import ChatEdit from './ChatEdit'

export default class ChatNew extends ChatEdit {
  constructor(element, parent) {
    super(element, parent)

    this.default()
    this.triggers()
  }

  close() {
    this.clear()
    super.close()
  }

  default() {
    this.createElement = this.screenElement.find('.chat-edit-submit')
  }

  triggers() {
    this.createElement.on('click', (e) => this.create(e))
  }

  clear() {
    this.removeUsers()

    this.title = ''
    this.titleElement.val('')

    this.image = null
    this.imageElement.val('')
    this.imagePreviewElement.css('backgroundImage', 'unset').css('height', '0px')
    this.imageTypeElement.text('Add')
  }

  create(e) {
    e.preventDefault()

    if (!this.title.length) return alert('Choose a chat name')
    if (!this.users.length) return alert('Add a user')

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: this.createElement.attr('href'),
      data: {
        title: this.title,
        image: this.image,
        ids: this.getUserIds(),
      },
      success: (data) => {
        if (data && data.success) this.close()
      },
    })
  }
}
