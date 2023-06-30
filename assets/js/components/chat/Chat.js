import WebsocketConnector from '../WebsocketConnector'
import ChatList from './ChatList'
import ChatNew from './ChatNew'
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
    this.newElement = this.screenElement.find('.chat-new-button')

    this.websocket = new WebsocketConnector(websocketUrl, (socket) => this.openSocket(socket))
    this.screens.list = new ChatList(this.screenElement.find('.chat-list'), this)
    this.screens.new = new ChatNew(this.screenElement.find('.chat-new'), this)
  }

  triggers() {
    this.iconElement.on('click', () => this.open())
    this.newElement.on('click', () => this.screens.new.open())
    $('body').on('click', '.send_user_message', (e) => this.screens.list.renderNewChat(e))
  }

  open() {
    this.isOpen = true
    this.screenElement.addClass('show')
    this.dot(false)
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
