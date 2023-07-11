export default class ChatUser {
  constructor(element, parent, data) {
    this.element = element
    this.parent = parent
    this.data = data

    this.default()
    this.triggers()
  }

  static initItem(parent, user) {
    const line = parent.usersElement.find('.default').clone()

    line.removeClass('default').find('.item-left-username').text(user.username)
    if (user.image) line.find('.item-left-image').css('backgroundImage', `url(${user.image})`)

    parent.usersElement.append(line)

    return new this(line, parent, user)
  }

  default() {
    this.removeElement = this.element.find('.item-right-remove')
  }

  triggers() {
    this.removeElement.on('click', () => this.parent.removeUser(this))
  }
}
