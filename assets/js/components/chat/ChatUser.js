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

    this.opElement = this.element.find('.item-right-op')
    if (this.parent.op && !this.data.op) this.opElement.removeClass('hidden')

    this.deleteElement = this.element.find('.item-right-delete')
    if (this.parent.op && this.user.id !== this.parent.main.user)
      this.deleteElement.removeClass('hidden')
  }

  triggers() {
    this.removeElement.on('click', () => this.parent.removeUser(this))
    this.renameElement.on('click', (e) => this.renameBox(e))
    this.closeBoxElement.on('click', (e) => this.closeBox(e))
    this.confirmBoxElement.on('click', (e) => this.confirmBox(e))
    this.opElement.on('click', (e) => this.opTrigger(e))
    this.deleteElement.on('click', (e) => this.deleteTrigger(e))
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

  opTrigger(e) {
    e.preventDefault()
    const name = this.data.pseudo ? this.data.pseudo : this.user.username
    if (!confirm('Are you sure you want to promote ' + name)) return

    const url = this.opElement.attr('href')
    this.parent.opUser(this, url)
  }

  updateOp() {
    this.opElement.addClass('hidden')
    this.data.op = true
  }

  deleteTrigger(e) {
    e.preventDefault()
    const name = this.data.pseudo ? this.data.pseudo : this.user.username
    if (!confirm('Are you sure you want to remove ' + name)) return

    const url = this.deleteElement.attr('href')
    this.parent.deleteUser(this, url)
  }
}
