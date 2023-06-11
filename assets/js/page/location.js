import Coord from '../components/coord'
import ImageInput from '../components/custominput'

$(() => {
  if ($('#location').length) {
    Coord.init('#location', '#location_lat', '#location_lon')
    ImageInput.auto()
  }

  $('.coordinates').insertAfter('#location_lon')
  $('#location_lat, #location_lon').on('keyup', function () {
    const lat = $('#location_lat').val()
    const lon = $('#location_lon').val()
    const link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
    $('.coordinates').attr('href', link)
  })
})
