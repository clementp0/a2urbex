export default class ChatUser {
  constructor(element, parent, data, user) {
    this.element = element
    this.parent = parent
    this.data = data
    this.user = user

    this.default()
    this.triggers()
    console.log(this)
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
  }

  triggers() {
    this.removeElement.on('click', () => this.parent.removeUser(this))
  }
}
