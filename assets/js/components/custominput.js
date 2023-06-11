export default class ImageInput {
  static init(...args) {
    return new this(...args)
  }

  static auto() {
    let items = []
    $('.custom-file').each(function () {
      const item = $(this)
      const parent = item.parents('form')
      const label = item.siblings('label')

      items.push(ImageInput.init(item, parent, label))
    })
  }

  constructor(item, parent, label) {
    this.item = item
    this.parent = parent
    this.label = label

    this.fileSize = null
    this.error = null
    this.preview = null
    this.reader = new FileReader()
    this.input = item.find('input')
    this.name = this.label.text()

    this.default()
    this.triggers()
  }

  default() {
    this.fileSize = $('<div>').addClass('file-size')
    this.label.append(this.fileSize)

    this.error = $('<div>').addClass('error')
    this.parent.find('> div').prepend(this.error)

    this.preview = $('<div>').addClass('image-preview')
    this.item.find('.custom-file-label').append(this.preview)
  }

  triggers() {
    this.item.on('change', (e) => this.change(e))
    this.reader.onload = (e) => this.image(e)
    this.parent.on('submit', (e) => this.submit(e))
  }

  change(e) {
    if (e.target.files.length > 0) {
      const sizeInBytes = e.target.files[0].size
      const sizeInMB = (sizeInBytes / 1000000).toFixed(2)

      this.label
        .find('.file-size')
        .html(' (Selected : ' + sizeInMB + 'MB,<span class="max-size"> Max 8MB</span>)')

      this.reader.readAsDataURL(e.target.files[0])
    }
  }

  image(e) {
    this.preview.css('backgroundImage', `url(${e.target.result})`)
  }

  submit(e) {
    const fileName = this.input.val()
    const fileExtension = fileName.split('.').pop().toLowerCase()

    console.log(this.error)

    if (fileName.length && !['jpg', 'jpeg', 'png'].includes(fileExtension)) {
      console.log('error')
      const errorMessage =
        '<span class="alert alert-danger d-block">' +
        ' <span class="d-block">' +
        '   <span class="form-error-icon badge badge-danger text-uppercase">Error</span>' +
        '   <span class="form-error-message">' +
        '     Invalid File Type for ' +
        this.name +
        ' input' +
        '   </span>' +
        ' </span>' +
        '</span>'

      this.error.html(errorMessage)
      e.preventDefault()
    }
  }
}
