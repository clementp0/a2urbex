import ChatScreen from './ChatScreen'
import UserModal from '../UserModal'

export default class ChatNew extends ChatScreen {
  constructor(element, parent) {
    super(element, parent)

    this.title = ''
    this.image = null
    this.ids = new Set()
    this.types = ['image/png', 'image/jpeg', 'image/jpg']
    this.reader = new FileReader()

    this.default()
    this.triggers()
  }

  close() {
    this.clear()
    super.close()
  }

  default() {
    this.searchElement = this.screenElement.find('.chat-new-search')
    this.createElement = this.screenElement.find('.chat-new-submit')
    this.usersElement = this.screenElement.find('.chat-new-users')
    this.titleElement = this.screenElement.find('.chat-new-title')
    this.imageElement = this.screenElement.find('.chat-new-image')
    this.imagePreviewElement = this.screenElement.find('.chat-new-image-preview')

    this.url = this.searchElement.data('href')
    this.modal = new UserModal('.chat-new-search.inmodal', (e) => this.addUser(e))
    this.updateUrl()
  }

  triggers() {
    this.createElement.on('click', (e) => this.create(e))
    this.titleElement.on('keyup', () => this.updateName())
    this.titleElement.on('paste', () => setTimeout(() => this.updateName(), 100))
    this.imageElement.on('change', (e) => this.updateImage(e))
    this.reader.onload = (e) => this.updateImagePreview(e)
  }

  updateName() {
    this.title = this.titleElement.val()
  }

  updateImage(e) {
    const file = e.target.files[0]

    if (!this.types.includes(file.type)) return alert('Illegal image format selected')
    if (file.size > 2000000) return alert('Image too big')

    this.reader.readAsDataURL(file)
  }

  updateImagePreview(e) {
    this.image = e.target.result
    this.imagePreviewElement.css('backgroundImage', `url(${this.image})`).css('height', '150px')
  }

  updateUrl() {
    const url = this.buildSearchUrl(this.url, { ids: [...this.ids] })
    this.searchElement.attr('href', url)
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
