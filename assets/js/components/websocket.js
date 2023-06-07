export default class WebsocketConnector {
  static init(...args) {
    return new this(...args)
  }

  constructor(url, open = null, close = null) {
    this.url = url
    this.openCallback = open
    this.closeCallback = close
    this.channels = {}
    this.debug = websocket_debug

    this.websocket = new WebSocket(this.url)
    this.triggers()
  }

  triggers() {
    this.websocket.onopen = (event) => this.onOpen(event)
    this.websocket.onclose = (event) => this.onClose(event)
    this.websocket.onerror = (event) => this.onError(event)
    this.websocket.onmessage = (event) => this.onMessage(event)
  }

  onOpen(event) {
    console.log('Connected to Websocket')

    if (this.openCallback !== null && typeof this.openCallback === 'function') {
      this.openCallback(this)
    }
  }

  onClose(event) {
    console.log('Disconnected from Websocket')

    if (this.closeCallback !== null && typeof this.closeCallback === 'function') {
      this.closeCallback(this)
    }
  }

  onMessage(event) {
    const data = JSON.parse(event.data)
    if (this.debug) console.log('receive', data)

    if (this.channels[data.channel] && typeof this.channels[data.channel] === 'function') {
      this.channels[data.channel](data.content)
    }
  }

  onError(event) {
    console.log('Websocket error : ' + event)
  }

  sendEvent(object) {
    const json = JSON.stringify(object)
    if (this.debug) console.log('send', object)
    if (this.websocket) this.websocket.send(json)
  }

  subscribe(channel, callback = null) {
    const event = { type: 'subscribe', channel: channel }
    this.channels[channel] = callback

    this.sendEvent(event)
  }

  unsubscribe(channel) {
    const event = { type: 'unsubscribe', channel: channel }
    delete this.channels[channel]
    this.sendEvent(event)
  }

  publish(channel, message) {
    const event = { type: 'publish', channel: channel, message: message }
    this.sendEvent(event)
  }
}
