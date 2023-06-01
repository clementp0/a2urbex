export default class Search {
  static init(...args) {
    return new this(...args)
  }
  constructor(selector) {
    this.element = $(selector)

    this.default()
    this.triggers()
  }

  default() {
    this.element.find('legend:eq( 0 )').prepend('<i class="fa-solid fa-earth-europe"></i>')
    this.element.find('legend:eq( 1 )').prepend('<i class="fa-solid fa-gear"></i>')
    this.element.find('legend:eq( 2 )').prepend('<i class="fa-solid fa-sliders"></i>')
  }

  triggers() {
    const me = this
    $('#map-filter').on('click', (e) => this.map(e))
    this.element.find('fieldset.form-group > legend').on('click', this.toggleList)
  }

  map(e) {
    e.preventDefault()
    const current = $(e.currentTarget)

    const href = current.attr('href')
    this.element.find('form').attr('action', href).find('#submit').click()
  }

  toggleList() {
    const div = $(this).siblings('div')

    const open = $(this).attr('data-open') && $(this).attr('data-open') == 'true' ? false : true
    $(this).attr('data-open', open)

    if (open) {
      const height = div.children().length * 22.8
      div.css('maxHeight', height + 'px')
    } else {
      div.css('maxHeight', '0px')
    }
  }
}
