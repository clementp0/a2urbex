import ChatEdit from './ChatEdit'
import UserModal from '../UserModal'

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
    this.usersElement = this.screenElement.find('.chat-edit-users')

    this.modal = new UserModal('.chat-edit-search.inmodal', (e) => this.addUser(e))
  }

  triggers() {
    this.createElement.on('click', (e) => this.create(e))
  }

  addUser(e) {
    e.preventDefault()

    const user = this.modal.current
    this.modal.action.close()

    this.addNewUser(user)
    this.ids.add(user.id)
    this.updateUrl()
  }

  addNewUser(user) {
    const line = this.usersElement.find('.default').clone()

    line.removeClass('default').find('.item-left-username').text(user.username)
    if (user.image) line.find('.item-left-image').css('backgroundImage', `url(${user.image})`)

    this.usersElement.append(line)
    this.ids.add(user.id)

    line.find('.item-right-close').on('click', () => {
      line.remove()
      this.ids.remove(user.id)
    })
  }

  clear() {
    this.ids = new Set()
    this.usersElement.find('.item:not(.default)').remove()

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
    if (!this.ids.size) return alert('Add a user')

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: this.createElement.attr('href'),
      data: {
        title: this.title,
        image: this.image,
        ids: [...this.ids],
      },
      success: (data) => {
        if (data && data.success) this.close()
      },
    })
  }
}
