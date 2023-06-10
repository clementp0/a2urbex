function initMap() {
  // map instance
  const map = new google.maps.Map(document.getElementById('map'), {
    zoom: 6,
    center: { lat: 46.71109, lng: 1.7191036 },
  })

  // place searchbar
  const inputWrapper = $('#map-input-wrapper')
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(inputWrapper[0])

  let url = asyncMapUrl
  if (mapType === 'key') {
    const split = window.location.pathname.split('/')
    url += split[split.length - 1] + '/'
  }
  url += window.location.search

  $.ajax({
    url: url,
    method: 'GET',
    dataType: 'json',
  }).done((json) => {
    setMarkers(map, json)
    setUserPosition(map)
    initSearch(map, json, inputWrapper)
  })
}

function setMarkers(map, items) {
  const icon = {
    url: pinLocationPath + 'pin-',
    scaledSize: new google.maps.Size(20, 27),
    origin: new google.maps.Point(0, 0),
    anchor: new google.maps.Point(10, 27),
    zIndex: 2,
  }
  const shape = {
    coords: [10, 0, 17, 3, 20, 9, 10, 27, 0, 9, 3, 3],
    type: 'poly',
  }

  const pins = {}
  pins['default'] = { ...icon }
  pins['default'].url = pins['default'].url + 'default.png'
  pins['default'].zIndex = 1

  items.forEach((item) => {
    let current = pins['default']
    if (item.loc.type) {
      if (!pins[item.loc.type.icon]) {
        pins[item.loc.type.icon] = { ...icon }
        pins[item.loc.type.icon].url = pins[item.loc.type.icon].url + item.loc.type.icon + '.png'
      }

      current = pins[item.loc.type.icon]
    }

    const marker = new google.maps.Marker({
      position: {
        lat: Number.parseFloat(item.loc.lat),
        lng: Number.parseFloat(item.loc.lon),
      },
      map,
      icon: current,
      shape: shape,
      title: item.loc.name,
      type: item.loc.type,
      zIndex: current.zIndex,
    })

    marker.addListener('click', () => {
      popup(item)
    })
  })
}

function popup(item) {
  if (item.loc.disabled == 1) {
    $('.map-overlay-img').attr('id', 'disabled')
  } else {
    $('.map-overlay-img').removeAttr('id')
  }

  $('.pin-fav-add').removeClass('show')
  $('#map-overlay').addClass('show')
  if (item.loc.image === null) {
    $('#map-overlay .map-overlay-img').css('backgroundImage', 'url("/assets/default.png")')
  } else {
    $('#map-overlay .map-overlay-img').css('backgroundImage', 'url(' + item.loc.image + ')')
  }
  if (item.loc.name) $('#map-overlay .map-overlay-title').text(item.loc.name)
  $('#map-overlay .map-overlay-type .pin-type-text').text(
    item.loc.type !== null ? item.loc.type.name : 'other'
  )
  $('#map-overlay .map-overlay-type .pin-type-icon').html(
    item.loc.type !== null
      ? '<i class="fa-solid ' + item.loc.type.icon + '"></i>'
      : '<i class="fa-solid fa-map-pin"></i>'
  )

  $('.pin-fav-wrapper').attr('data-id', item.loc.lid)
  $('.pin-fav-wrapper').attr('data-fids', item.fids)

  let mapsUrl = $('.map-overlay-action .pin-map').data('url') + item.loc.lat + ',' + item.loc.lon
  $('.map-overlay-action .pin-map').attr('href', mapsUrl)
  let editUrl = $('.map-overlay-action .pin-conf').data('url').replace('-key-', item.loc.lid)
  $('.map-overlay-action .pin-conf').attr('href', editUrl)
  let wazeUrl =
    $('.map-overlay-action .pin-waze').data('url') +
    item.loc.lat +
    ',' +
    item.loc.lon +
    '&navigate=yes&zoom=17'
  $('.map-overlay-action .pin-waze').attr('href', wazeUrl)

  if (item.fids) $('#map-overlay').find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
  else $('#map-overlay').find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
}

function setUserPosition(map) {
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const marker = new google.maps.Marker({
        position: {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        },
        map,
      })
    },
    (error) => {
      alert(error.message)
    }
  )
}

function initSearch(map, json, inputWrapper) {
  inputWrapper.removeClass('disabled')
  const input = inputWrapper.find('#map-input')
  const inputResult = inputWrapper.find('#map-input-result')

  input.on('input', function () {
    const value = $(this).val().toLowerCase()
    search(map, json, inputWrapper, value)
  })

  input.on('blur', () => {
    setTimeout(() => {
      inputResult.addClass('hidden')
    }, 200)
  })
  input.on('focus', () => {
    inputResult.removeClass('hidden')
  })
}

function search(map, json, inputWrapper, value) {
  const result = json
    .filter((item) => item.loc.name && item.loc.name.toLowerCase().indexOf(value) !== -1)
    .splice(0, 10)

  const inputResult = inputWrapper.find('#map-input-result')
  inputResult.empty()

  result.forEach((item) => {
    const type = item.loc.type
    const row = $('<div>')

    row.addClass('item').html(`
      <span class="icon"><i class="fa-solid ${type ? type.icon : 'fa-map-pin'}" ></i></span>
      <span class="name">${item.loc.name}</span>
    `)
    inputResult.append(row)

    row.on('click', () => {
      map.setCenter(
        new google.maps.LatLng(Number.parseFloat(item.loc.lat), Number.parseFloat(item.loc.lon))
      )
      map.setZoom(10)

      popup(item)
    })
  })

  inputResult.removeClass('hidden')
}

window.initMap = initMap

$(() => {
  $('.map-overlay-close').on('click', (e) => {
    e.preventDefault()
    $('#map-overlay').removeClass('show')
  })

  $('#map-wrapper').on('click', '.gm-fullscreen-control', function () {
    $('#map-overlay').appendTo($('#map').find('div')[0])
  })
})
