import WebsocketConnector from './websocket'

export default class Chat {
  static init(...args) {
    return new this(...args)
  }

  constructor(icon, wrapper) {
    this.icon = icon
    this.wrapper = wrapper
    this.current = null
    this.chatOpen = false

    this.default()
    this.triggers()
  }

  default() {
    this.addUrl = this.wrapper.data('addurl')
    this.getUrl = this.wrapper.data('geturl')
    this.getAllUrl = this.wrapper.data('getallurl')
    this.channel = this.wrapper.data('channel')
    this.user = Number.parseInt(this.wrapper.data('user'))

    this.dot = this.icon.find('.chat-dot')
    this.list = this.wrapper.find('.chat-list')
    this.messages = this.wrapper.find('.chat-messages')
    this.closeEl = this.list.find('.chat-close')
    this.backEl = this.messages.find('.chat-back')

    this.websocket = WebsocketConnector.init(websocketUrl, (socket) => this.openSocket(socket))
  }

  triggers() {
    this.icon.on('click', () => this.open())
    this.closeEl.on('click', () => this.close())
    this.backEl.on('click', () => this.back())
    this.list.on('click', '.item', (e) => this.openChatTrigger(e))
    this.messages.find('#message').on('keydown', (e) => {
      if (e.key === 'Enter' || e.keyCode === 13) this.sendMessage(e)
    })
    this.messages.find('#sendBtn').on('click', (e) => this.sendMessage(e))

    $('body').on('click', '.send_user_message', (e) => this.renderNewChat(e))
  }

  open() {
    this.chatOpen = true
    this.wrapper.addClass('show')
    this.dot.removeClass('new')
  }
  close() {
    this.chatOpen = false
    this.wrapper.removeClass('show')
  }
  back() {
    this.messages.removeClass('open')
    this.current = null
  }

  openSocket(socket) {
    socket.subscribe(this.channel, (data) => this.newMessage(data))

    $.ajax({
      url: this.getAllUrl,
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) this.renderList(data)
      },
    })
  }

  openChatTrigger(e) {
    const current = $(e.currentTarget)
    this.openChat(current)
  }
  openChat(current) {
    this.list.find('.chat-loading').addClass('show')
    this.current = current.data('name')

    $.ajax({
      url: this.getUrl.replace('/0', '/' + this.current),
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) {
          this.renderChat(data)
          current.removeClass('new')
        }
      },
    })
  }

  renderList(data) {
    data.forEach((item) => this.renderItem(item, item.lastMessage))
  }

  renderItem(chat, message, prepend = false, notification = false) {
    const line = this.list.find('.default').clone()

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

    if (prepend === true) this.list.find('.chat-inner').prepend(line)
    else this.list.find('.chat-inner').append(line)

    return line
  }

  updateItem(chat, message, current = false) {
    let line = this.list.find('.item[data-name="' + chat.name + '"]')

    if (!line.length) return this.renderItem(chat, message, true, true)

    line
      .find('.item-right-message-text')
      .text(message.value)
      .end()
      .find('.item-right-message-date')
      .text(this.formatDate(message.datetime, true))

    if (!current) line.addClass('new')

    this.list.find('.chat-inner').prepend(line)
  }

  renderChat(data) {
    this.messages.find('.message:not(.default)').remove()

    data.forEach((item) => {
      this.renderChatRow(item)
    })

    this.scrollBottom()
    this.messages.addClass('open')
    this.list.find('.chat-loading').removeClass('show')
  }

  renderChatRow(item) {
    const line = this.messages.find('.default').clone()

    if (item.sender && item.sender.roles) item.sender.roles.forEach((role) => line.addClass(role))
    else line.addClass('SERVER')

    if (item.sender && item.sender.id === this.user) line.addClass('user-current')

    if (item.sender) line.find('.name-text').text(item.sender.firstname + '#' + item.sender.id)
    else line.find('.name-text').text('a2urbex')

    line
      .removeClass('default')
      .find('.message-content')
      .text(item.value)
      .end()
      .find('.message-date')
      .text(this.formatDate(item.datetime))

    this.messages.find('#chat').append(line)
  }

  sendMessage(e) {
    e.preventDefault()

    const messageValue = this.messages.find('#message').val().trim()
    if (messageValue === '') return

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: this.addUrl.replace('/0', '/' + this.current),
      data: messageValue,
      success: (data) => {
        if (!data.success) window.location.reload()
        else this.messages.find('#message').val('')
      },
    })
  }

  newMessage(data) {
    const current = data.chat.name === this.current

    if (current) {
      this.renderChatRow(data.message)
      this.scrollBottom()
    } else if (this.chatOpen === false) {
      this.dot.addClass('new')
    }

    this.updateItem(data.chat, data.message, current)
  }

  renderNewChat(e) {
    e.preventDefault()

    $.ajax({
      url: $(e.currentTarget).attr('href'),
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (!data) return

        if (this.current !== data.name) this.back()
        this.open()

        let line = this.list.find('.item[data-name="' + data.name + '"]')
        if (!line.length) line = this.renderItem(data, null, true)
        this.openChat(line)
      },
    })
  }

  scrollBottom() {
    const chat = this.messages.find('#chat')
    chat.scrollTop(chat[0].scrollHeight)
  }

  formatDate(datetime, small = false) {
    const timestamp = Date.parse(datetime)
    const date = new Date(timestamp)

    const params = {
      year: 'numeric',
      month: 'numeric',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }

    if (small === false) return date.toLocaleString('fr-FR', params)

    if (Date.now() > timestamp + 24 * 60 * 60 * 1000) {
      return date.toLocaleString('fr-FR', { month: params.month, day: params.day })
    } else {
      return date.toLocaleString('fr-FR', { hour: params.hour, minute: params.minute })
    }
  }
}
