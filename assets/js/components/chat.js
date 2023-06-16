import WebsocketConnector from './websocket'

export default class Chat {
  static init(...args) {
    return new this(...args)
  }

  constructor(icon, wrapper) {
    this.icon = icon
    this.wrapper = wrapper
    this.current = null

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
    this.list.on('click', '.item', (e) => this.openChat(e))
  }

  open() {
    this.wrapper.addClass('show')
    this.dot.removeClass('new')
  }
  close() {
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

  openChat(e) {
    this.list.find('.chat-loading').addClass('show')

    const current = $(e.currentTarget)
    this.current = current.data('name')

    $.ajax({
      url: this.getUrl.replace('/0', '/' + this.current),
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) this.renderChat(data)
      },
    })
  }

  renderList(data) {
    data.forEach((item) => {
      const line = this.list.find('.default').clone()

      line
        .attr('data-name', item.name)
        .removeClass('default')
        .find('.item-right-title')
        .text(item.title)
        .end()
        .find('.item-right-message-text')
        .text(item.lastMessage.message)
        .end()
        .find('.item-right-message-date')
        .text(this.formatDate(item.lastMessage.datetime, true))

      if (item.user && item.user.image)
        line.find('.item-left-image').css('backgroundImage', `url(item.user.image)`)

      this.list.find('.chat-inner').append(line)
    })
  }

  renderChat(data) {
    this.messages.find('.message:not(.default)').remove()

    data.forEach((item) => {
      this.renderChatRow(item)
    })

    this.messages.find('#chat').scrollTop($('#chat')[0].scrollHeight)
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
      .text(item.message)
      .end()
      .find('.message-date')
      .text(this.formatDate(item.datetime))

    this.messages.find('#chat').append(line)
  }

  newMessage(data) {}

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

$(() => {
  return

  const chatWrapper = $('.chat-wrapper')

  if (chatWrapper.length) {
    // websocket

    function open(socket) {
      $('#message').on('keydown', function (event) {
        if (event.key === 'Enter' || event.keyCode === 13) send(event)
      })
      $('#sendBtn').on('click', function (event) {
        send(event)
      })
    }

    function newMessage(data) {
      addMessage(JSON.parse(data))
      $('.chat-dot').addClass('new')
      $('#chat').scrollTop($('#chat')[0].scrollHeight)
    }

    const send = (event) => {
      event.preventDefault()

      const messageValue = $('#message').val().trim()
      if (messageValue === '') return

      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: chatAddUrl.replace('/0', '/global'),
        data: messageValue,
        success: (data) => {
          if (!data.success) window.location.reload()
          else $('#message').val('')
        },
      })
    }

    function addMessage(data) {
      const messageHTML = renderRow(data)
      $('#chat').append(messageHTML)
    }
  }
})
