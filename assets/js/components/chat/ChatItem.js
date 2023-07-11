import ChatInfo from './ChatInfo'
import ChatScreen from './ChatScreen'

export default class ChatItem extends ChatScreen {
  constructor(element, item, parent) {
    super(element, parent)

    this.itemElement = item
    this.name = item.data('name')
    this.rendered = false

    this.default()
    this.triggers()
  }

  static initItem(current, parent) {
    const element = parent.main.screenElement
      .find('.chat-item.default')
      .clone()
      .removeClass('default')

    parent.main.screenElement.find('.chat-items').append(element)

    return new this(element, current, parent)
  }

  close() {
    super.close()
    if (this.main.currentItem === this) this.main.currentItem = null
  }

  default() {
    this.inputElement = this.screenElement.find('.message-input')
    this.sendElement = this.screenElement.find('.message-send')
    this.infoElement = this.screenElement.find('.chat-info-button')
    this.infoUrl = this.formatUrl(this.infoElement.attr('href'), this.name)
    if (this.name !== 'global') this.infoElement.removeClass('hidden')

    this.screens.info = new ChatInfo(this.screenElement.find('.chat-info'), this)
  }

  triggers() {
    this.inputElement.on('keydown', (e) => {
      if (e.key === 'Enter' || e.keyCode === 13) this.sendMessage(e)
    })
    this.sendElement.on('click', (e) => this.sendMessage(e))

    this.infoElement.on('click', (e) => this.openInfo(e))
  }

  getChat() {
    if (this.rendered) return this.openChat()

    $.ajax({
      url: this.getGetUrl(this.name),
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) this.renderChat(data)
      },
    })
  }

  openChat() {
    this.parent.closeItems()
    this.main.currentItem = this
    this.open()
    this.itemElement.removeClass('new')
    this.parent.loading(false)
  }

  renderChat(data) {
    data.forEach((item) => this.renderChatRow(item))
    this.renderChatHeader()
    this.scrollBottom()
    this.openChat()
    this.rendered = true
  }

  renderChatHeader() {
    this.headerElement
      .find('.chat-title-text')
      .text(this.itemElement.find('.item-right-title').text())
      .end()
      .find('.chat-title-image')
      .css('backgroundImage', this.itemElement.find('.item-left-image').css('backgroundImage'))
  }

  renderChatRow(item) {
    const line = this.innerElement.find('.default').clone()

    if (item.sender && item.sender.roles) item.sender.roles.forEach((role) => line.addClass(role))
    else if (this.name === 'global') line.addClass('SERVER')
    else line.addClass('INFO')

    if (item.sender && item.sender.id === this.user) line.addClass('user-current')

    if (item.sender) line.find('.name-text').text(item.sender.username)
    else line.find('.name-text').text('a2urbex')

    line
      .removeClass('default')
      .find('.message-content')
      .text(item.value)
      .end()
      .find('.message-date')
      .text(this.formatDate(item.datetime))

    this.innerElement.append(line)
  }

  sendMessage(e) {
    e.preventDefault()

    const messageValue = this.inputElement.val().trim()
    if (messageValue === '') return

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: this.getAddUrl(this.name),
      data: messageValue,
      success: (data) => {
        if (!data.success) alert('An error occurred')
        else this.inputElement.val('')
      },
    })
  }

  openInfo(e) {
    e.preventDefault()
    this.loading()
    this.screens.info.getInfo(this.infoUrl)
  }
}
