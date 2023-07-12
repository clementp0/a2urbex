import ChatScreen from './ChatScreen'
import UserModal from '../UserModal'
import ChatUser from './ChatUser'

export default class ChatEdit extends ChatScreen {
  constructor(element, parent) {
    super(element, parent)

    this.title = ''
    this.image = null
    this.types = ['image/png', 'image/jpeg', 'image/jpg']
    this.reader = new FileReader()
    this.users = []

    this.editDefault()
    this.editTriggers()
  }

  editDefault() {
    this.titleElement = this.screenElement.find('.chat-edit-title')
    this.imageWrapperElement = this.screenElement.find('.chat-edit-image-wrapper')
    this.imageElement = this.screenElement.find('.chat-edit-image')
    this.imagePreviewElement = this.screenElement.find('.chat-edit-image-preview')
    this.imageTypeElement = this.screenElement.find('.chat-edit-image-type')
    this.searchElement = this.screenElement.find('.chat-edit-search')
    this.usersElement = this.screenElement.find('.chat-edit-users')

    this.modalElement = this.screenElement.find('.chat-edit-search.inmodal')
    this.modal = new UserModal(this.modalElement, () => this.addUserTrigger())
    this.url = this.searchElement.data('href')
    this.updateUrl()
  }

  editTriggers() {
    this.titleElement.on('keyup', () => this.updateTitle())
    this.titleElement.on('paste', () => setTimeout(() => this.updateTitle(), 100))
    this.imageElement.on('change', (e) => this.updateImage(e))
    this.reader.onload = (e) => this.updateImagePreview(e)
  }

  updateUrl() {
    const url = this.buildSearchUrl(this.url, { ids: this.getUserIds() })
    this.searchElement.attr('href', url)
  }

  updateTitle() {
    this.title = this.titleElement.val()
  }
  updateTitleElement() {
    this.titleElement.val(this.title)
  }

  updateImage(e) {
    const file = e.target.files[0]

    if (!this.types.includes(file.type)) return alert('Illegal image format selected')
    if (file.size > 2000000) return alert('Image too big')

    this.reader.readAsDataURL(file)
  }

  updateImagePreview(e) {
    this.image = e.target.result
    this.updateImagePreviewElement()
  }
  updateImagePreviewElement() {
    this.imagePreviewElement.css('backgroundImage', `url(${this.image})`).css('height', '150px')
    this.imageTypeElement.text('Edit')
  }

  addUserTrigger() {
    this.addUser(this.modal.current)
  }

  addUser(user) {
    this.users.push(ChatUser.initItem(this, user))
    this.updateUrl()
  }

  removeUser(user) {
    user.element.remove()
    this.users = this.users.filter((item) => item !== user)
    this.updateUrl()
  }

  removeUsers() {
    this.users = []
    this.usersElement.find('.item:not(.default)').remove()
  }

  getUserIds() {
    if (!this.users.length) return
    return this.users.map((item) => item.user.id)
  }
}
