$(() => {
  $('.coordinates').insertAfter('#new_location_lon')
  $('.coordinates').insertAfter('#location_lon')

  $('#new_location_lon, #new_location_lat').on('keyup', function () {
    const lon = $('#new_location_lon').val()
    const lat = $('#new_location_lat').val()
    const link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
    $('.coordinates').attr('href', link)
  })
  $('#location_lon, #location_lat').on('keyup', function () {
    const lon = $('#location_lon').val()
    const lat = $('#location_lat').val()
    const link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
    $('.coordinates').attr('href', link)
  })

  function previewImage(event) {
    const input = event.target
    if (input.files && input.files[0]) {
      const reader = new FileReader()
      reader.onload = function (e) {
        const preview = document.getElementById('image-preview')
        preview.style.backgroundImage = `url(${e.target.result})`
        preview.style.backgroundSize = 'cover'
      }
      reader.readAsDataURL(input.files[0])
    }
  }
  window.previewImage = previewImage

  $('#new_location_image').on('change', function () {
    if (this.files.length > 0) {
      const sizeInBytes = this.files[0].size
      const sizeInMB = (sizeInBytes / 1000000).toFixed(2)
      $('#fileSize').html(' (Selected : ' + sizeInMB + 'MB,<span class="max-size"> Max 8MB</span>)')
    }
  })

  window.onload = function () {
    $('.custom-file-label').append($('#image-preview'))
    $('.image-label-placeholder').append($('#fileSize'))
    $('#new_location').append($('#error'))
  }

  const form = $('form[name="new_location"]')
  form.on('submit', function (event) {
    const fileName = $('#new_location_image').val()
    const fileExtension = fileName.split('.').pop().toLowerCase()

    console.log(fileName, fileExtension)
    event.preventDefault()

    if (fileName.length && !['jpg', 'jpeg', 'png'].includes(fileExtension)) {
      const errorMessage =
        '<span class="alert alert-danger d-block">' +
        ' <span class="d-block">' +
        '   <span class="form-error-icon badge badge-danger text-uppercase">Error</span>' +
        '   <span class="form-error-message">Invalid File Type.</span>' +
        ' </span>' +
        '</span>'
      $('#error').html(errorMessage)
      $('#new_location').prepend($('#error'))
      event.preventDefault()
    }
  })
})
