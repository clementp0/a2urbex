import ChatItem from './ChatItem'
import ChatScreen from './ChatScreen'

export default class ChatList extends ChatScreen {
  constructor(element, parent) {
    super(element, parent)

    this.items = {}
    this.triggers()
  }

  triggers() {
    this.innerElement.on('click', '.item', (e) => this.openItemTrigger(e))
  }

  close() {
    this.main.isOpen = false
    this.main.screenElement.removeClass('show')
  }

  getList() {
    $.ajax({
      url: this.main.getAllUrl,
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) this.renderList(data)
      },
    })
  }

  renderList(data) {
    data.forEach((item) => this.renderItem(item, item.lastMessage))
    this.loading(false)
  }

  renderItem(chat, message, prepend = false, notification = false) {
    const line = this.innerElement.find('.default').clone()

    line
      .attr('data-name', chat.name)
      .removeClass('default')
      .find('.item-right-title')
      .text(chat.title)
      .end()
      .find('.item-right-message-text')
      .text(message?.value)
      .end()
      .find('.item-right-message-date')
      .text(message ? this.formatDate(message.datetime, true) : '')

    if (chat.image) line.find('.item-left-image').css('backgroundImage', `url(${chat.image})`)

    if (notification) line.addClass('new')

    if (prepend === true) this.innerElement.prepend(line)
    else this.innerElement.append(line)

    this.items[chat.name] = line
  }

  updateItem(chat, message) {
    if (!this.items[chat.name]) return this.renderItem(chat, message, true, true)

    this.items[chat.name]
      .find('.item-right-message-text')
      .text(message.value)
      .end()
      .find('.item-right-message-date')
      .text(this.formatDate(message.datetime, true))

    if (!this.isCurrentItem(chat.name) && !this.isSelf(message.sender?.id)) {
      this.items[chat.name].addClass('new')
    }

    this.innerElement.prepend(this.items[chat.name])
  }

  openItemTrigger(e) {
    const current = $(e.currentTarget)
    this.openItem(current)
  }

  openItem(current) {
    this.loading()

    const name = current.data('name')
    if (!this.screens[name]) this.screens[name] = ChatItem.initItem(current, this)

    this.screens[name].getChat()
  }

  closeItems() {
    Object.values(this.screens).forEach((screen) => {
      screen.close()
    })
  }

  isCurrentItem(name, bool = true) {
    if (bool === true) return this.main.currentItem && this.main.currentItem.name === name
    else return this.main.currentItem && this.main.currentItem.name !== name
  }

  isSelf(id) {
    return this.main.user === id
  }

  renderNewMessage(data) {
    if (this.screens[data.chat.name]) {
      this.screens[data.chat.name].renderChatRow(data.message)
      this.screens[data.chat.name].scrollBottom()
    } else if (this.main.isOpen === false && !this.isSelf(data.message.sender?.id)) {
      this.main.dot()
    }

    this.updateItem(data.chat, data.message)
  }

  renderNewChat(e) {
    e.preventDefault()

    $.ajax({
      url: $(e.currentTarget).attr('href'),
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (!data) return

        if (this.isCurrentItem(data.name, false)) this.main.currentItem.close()
        if (this.main.isOpen === false) this.main.open()
        if (!this.items[data.name]) this.renderItem(data, null, true)

        this.openItem(this.items[data.name])
      },
    })
  }
}
