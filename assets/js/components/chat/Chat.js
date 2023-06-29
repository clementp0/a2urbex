import WebsocketConnector from '../WebsocketConnector'
import ChatList from './ChatList'
import ChatScreen from './ChatScreen'

export default class Chat extends ChatScreen {
  constructor(icon, wrapper) {
    super(wrapper)

    this.iconElement = icon
    this.currentItem = null
    this.isOpen = false

    this.default()
    this.triggers()
  }

  default() {
    this.loading()
    this.addUrl = this.screenElement.data('addurl')
    this.getUrl = this.screenElement.data('geturl')
    this.getAllUrl = this.screenElement.data('getallurl')
    this.channel = this.screenElement.data('channel')
    this.user = Number.parseInt(this.screenElement.data('user'))

    this.dotElement = this.iconElement.find('.chat-dot')

    this.websocket = new WebsocketConnector(websocketUrl, (socket) => this.openSocket(socket))
    this.screens.list = new ChatList(this.screenElement.find('.chat-list'), this)
  }

  triggers() {
    this.iconElement.on('click', () => this.open())
    $('body').on('click', '.send_user_message', (e) => this.screens.list.renderNewChat(e))
  }

  open() {
    this.isOpen = true
    this.screenElement.addClass('show')
    this.dot(false)
  }

  close() {
    this.isOpen = false
    this.screenElement.removeClass('show')
  }

  dot(bool = true) {
    if (bool) this.dotElement.addClass('new')
    else this.dotElement.removeClass('new')
  }

  openSocket(socket) {
    socket.subscribe(this.channel, (data) => this.screens.list.renderNewMessage(data))
    this.screens.list.getList()
  }
}
