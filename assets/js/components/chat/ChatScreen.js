export default class ChatScreen {
  constructor(screen, parent = null, main = null) {
    this.screenElement = screen
    this.parent = parent ? parent : this
    this.main = main ? main : this.parent.main ? this.parent.main : this.parent

    this.screens = {}
    this.active = false

    this.screenDefault()
    this.screenTriggers()
  }

  screenDefault() {
    this.headerElement = this.screenElement.find('.chat-header')
    this.innerElement = this.screenElement.find('.chat-inner')
    this.closeElement = this.screenElement.find('.chat-close')
  }

  screenTriggers() {
    this.closeElement.on('click', () => this.close())
  }

  // implement depth later
  close(depth = 1) {
    this.screenElement.removeClass('open')
  }
  open() {
    this.screenElement.addClass('open')
  }

  loading(bool = true) {
    const chatLoading = this.screenElement.find('.chat-loading')
    if (bool) chatLoading.addClass('show')
    else chatLoading.removeClass('show')
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

  getGetUrl(name) {
    return this.main.getUrl.replace('/0', '/' + name)
  }
  getAddUrl(name) {
    return this.main.addUrl.replace('/0', '/' + name)
  }

  scrollBottom() {
    this.innerElement.scrollTop(this.innerElement[0].scrollHeight)
  }
}
