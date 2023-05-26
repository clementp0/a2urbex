$(() => {
  $('.coordinates').insertAfter('#new_location_lon')
  $('.coordinates').insertAfter('#location_lon')
  $(document).ready(function () {
    $('#new_location_lon, #new_location_lat').keyup(function () {
      var lon = $('#new_location_lon').val()
      var lat = $('#new_location_lat').val()
      var link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
      $('.coordinates').attr('href', link)
    })
  })
  $(document).ready(function () {
    $('#location_lon, #location_lat').keyup(function () {
      var lon = $('#location_lon').val()
      var lat = $('#location_lat').val()
      var link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
      $('.coordinates').attr('href', link)
    })
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
  var fileInput = document.getElementById('new_location_image')
  var fileSize = document.getElementById('fileSize')

  fileInput.addEventListener('change', function () {
    if (fileInput.files.length > 0) {
      var sizeInBytes = fileInput.files[0].size
      var sizeInMB = (sizeInBytes / 1000000).toFixed(2)
      fileSize.innerHTML = ' (Selected : ' + sizeInMB + 'MB,<span class="max-size"> Max 8MB</span>)'
    }
  })

  window.onload = function () {
    var imagepreview = document.getElementById('image-preview')
    var uploader = document.querySelector('.custom-file-label')
    uploader.appendChild(imagepreview)
    var size = document.getElementById('fileSize')
    var uploaderlabel = document.querySelector('.image-label-placeholder')
    uploaderlabel.appendChild(size)
    var error = document.getElementById('error')
    var formtype = document.querySelector('#new_location')
    formtype.appendChild(error)
  }

  const form = document.querySelector('form[name="new_location"]')
  form.addEventListener('submit', function (event) {
    const fileInput = document.querySelector('#new_location_image')
    const fileName = fileInput.value
    const fileExtension = fileName.split('.').pop().toLowerCase()

    if (fileName.length && !['jpg', 'jpeg', 'png'].includes(fileExtension)) {
      event.preventDefault()
      const errorMessage =
        '<span class="alert alert-danger d-block"><span class="d-block"><span class="form-error-icon badge badge-danger text-uppercase">Error</span> <span class="form-error-message">Invalid File Type.</span></span></span>'
      const errorElement = document.querySelector('#error')
      errorElement.innerHTML = errorMessage
      const newLocationElement = document.querySelector('#new_location')
      newLocationElement.insertBefore(errorElement, newLocationElement.firstChild)
    }
  })
})
