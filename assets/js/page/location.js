import Coord from '../components/coord'

$(() => {
  Coord.init('#location', '#location_lat', '#location_lon')

  $('.coordinates').insertAfter('#location_lon')
  $('#location_lat, #location_lon').on('keyup', function () {
    const lat = $('#location_lat').val()
    const lon = $('#location_lon').val()
    const link = 'https://www.google.com/maps?t=k&q=' + lat + ',' + lon
    $('.coordinates').attr('href', link)
  })
})
