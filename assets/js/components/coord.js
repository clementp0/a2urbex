export default class Coord {
  static init(...args) {
    return new this(...args)
  }
  constructor(selector, selectorLat, selectorLon) {
    this.element = $(selector)
    this.latElement = $(selectorLat)
    this.lonElement = $(selectorLon)

    this.authorizedCoordKey = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', '-']

    this.triggers()

    this.coordInputValidity(this.latElement[0])
    this.coordInputValidity(this.lonElement[0])
  }

  triggers() {
    this.latElement.on('keyup', (e) => this.keyup(e))
    this.lonElement.on('keyup', (e) => this.keyup(e))
    this.latElement.on('paste', (e) => this.paste(e))
    this.lonElement.on('paste', (e) => this.paste(e))
  }

  removeUnauthorizedCoordChar(target) {
    let newString = ''
    let value = target.value

    if (!value.includes('.') && value.split(',').length === 2) value = value.replace(',', '.')

    for (let i = 0; i < value.length; i++) {
      const letter = value.charAt(i)
      if (letter === '-' && i !== 0) continue
      if (letter === '.' && newString.includes('.')) continue
      if (!this.authorizedCoordKey.includes(letter)) continue
      newString += letter
    }
    target.value = newString
  }

  coordInputValidity(target) {
    let n = parseFloat($(target).val())
    let min = parseFloat($(target).attr('min'))
    let max = parseFloat($(target).attr('max'))
    if (n >= min && n <= max) target.setCustomValidity('')
    else target.setCustomValidity(`The value must be between ${min} and ${max}`)
  }

  coordCustomPaste(target) {
    let [lat, lon] = target.value.split(',')

    this.latElement.val(lat)
    this.removeUnauthorizedCoordChar(this.latElement[0])
    this.coordInputValidity(this.latElement[0])

    this.lonElement.val(lon)
    this.removeUnauthorizedCoordChar(this.lonElement[0])
    this.coordInputValidity(this.lonElement[0])
  }

  keyup(e) {
    this.removeUnauthorizedCoordChar(e.target)
    this.coordInputValidity(e.target)
  }

  paste(e) {
    setTimeout(() => {
      if (
        e.target.value.includes('.') &&
        e.target.value.includes(',') &&
        e.target.value.split(',').length === 2
      ) {
        this.coordCustomPaste(e.target)
      } else {
        this.removeUnauthorizedCoordChar(e.target)
        this.coordInputValidity(e.target)
      }
    }, 100)
  }
}
