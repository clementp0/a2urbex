export default class ChatUser {
  constructor(element, parent, data, user) {
    this.element = element
    this.parent = parent
    this.data = data
    this.user = user

    this.default()
    this.triggers()
  }

  static initItem(parent, data) {
    const user = data.user ? data.user : data

    const line = parent.usersElement.find('.default').clone()

    line.removeClass('default').find('.item-left-username').text(user.username)
    if (user.image) line.find('.item-left-image').css('backgroundImage', `url(${user.image})`)

    const pseudoElement = line.find('.item-left-pseudo')
    if (pseudoElement.length) pseudoElement.text(data.pseudo ? data.pseudo : user.username)

    parent.usersElement.append(line)

    return new this(line, parent, data, user)
  }

  default() {
    this.removeElement = this.element.find('.item-right-remove')
    this.renameElement = this.element.find('.item-right-rename')
    this.renameBoxElement = this.element.find('.item-rename')
    this.renameBoxInputElement = this.element.find('.item-rename-input')
    this.closeBoxElement = this.element.find('.item-rename-close')
    this.confirmBoxElement = this.element.find('.item-rename-confirm')
    this.pseudoElement = this.element.find('.item-left-pseudo')
  }

  triggers() {
    this.removeElement.on('click', () => this.parent.removeUser(this))
    this.renameElement.on('click', (e) => this.renameBox(e))
    this.closeBoxElement.on('click', (e) => this.closeBox(e))
    this.confirmBoxElement.on('click', (e) => this.confirmBox(e))
  }

  renameBox(e) {
    e.preventDefault()

    this.renameBoxInputElement.val(this.data.pseudo ? this.data.pseudo : this.user.username)
    this.renameBoxElement.removeClass('hidden')
  }

  closeBox(e) {
    e.preventDefault()
    this.renameBoxElement.addClass('hidden')
  }

  confirmBox(e) {
    e.preventDefault()

    const url = this.confirmBoxElement.attr('href')
    const name = this.renameBoxInputElement.val()
    this.parent.renameUser(this, url, name)
  }

  updatePseudo(name) {
    this.pseudoElement.text(name)
    this.renameBoxElement.addClass('hidden')
  }
}
