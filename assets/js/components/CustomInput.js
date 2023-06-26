export default class CustomInput {
  static auto() {
    let items = []
    $('.custom-file').each(function () {
      const item = $(this)
      const parent = item.parents('form')

      items.push(new CustomInput(item, parent))
    })
  }

  constructor(item, parent) {
    this.item = item
    this.parent = parent

    this.fileSize = null
    this.error = null
    this.preview = null

    this.reader = new FileReader()
    this.label = item.siblings('label')
    this.input = item.find('input')
    this.name = this.label.text()
    this.isImage = item.parents('.custom-file-image-preview').length

    this.default()
    this.triggers()
  }

  default() {
    this.fileSize = $('<div>').addClass('file-size')
    this.label.append(this.fileSize)

    if (!this.isImage) return

    this.error = $('<div>').addClass('error')
    this.parent.find('> div').prepend(this.error)

    const inputName = this.input
      .attr('name')
      .slice(0, -1)
      .replace(this.parent.find('> div').attr('id') + '[', '')
    const defaultImage = this.parent.find('.custom-file-' + inputName).val()

    this.preview = $('<div>').addClass('image-preview')
    if (defaultImage) this.preview.css('backgroundImage', `url("${defaultImage}")`)
    this.item.find('.custom-file-label').append(this.preview)
  }

  triggers() {
    this.item.on('change', (e) => this.change(e))

    if (!this.isImage) return

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

    if (fileName.length && !['jpg', 'jpeg', 'png'].includes(fileExtension)) {
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
