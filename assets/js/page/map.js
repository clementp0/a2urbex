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

  for (let key in items) {
    let current = pins['default']
    if (items[key].loc.type) {
      if (!pins[items[key].loc.type.icon]) {
        pins[items[key].loc.type.icon] = { ...icon }
        pins[items[key].loc.type.icon].url =
          pins[items[key].loc.type.icon].url + items[key].loc.type.icon + '.png'
      }

      current = pins[items[key].loc.type.icon]
    }

    const marker = new google.maps.Marker({
      position: {
        lat: Number.parseFloat(items[key].loc.lat),
        lng: Number.parseFloat(items[key].loc.lon),
      },
      map,
      icon: current,
      shape: shape,
      title: items[key].loc.name,
      type: items[key].loc.type,
      zIndex: current.zIndex,
    })

    marker.addListener('click', () => {
      if (items[key].loc.disabled == 1) {
        $('.map-overlay-img').attr('id', 'disabled')
      } else {
        $('.map-overlay-img').removeAttr('id')
      }

      $('.pin-fav-add').removeClass('show')
      $('#map-overlay').addClass('show')
      if (items[key].loc.image === null) {
        $('#map-overlay .map-overlay-img').css('backgroundImage', 'url("/assets/default.png")')
      } else {
        $('#map-overlay .map-overlay-img').css(
          'backgroundImage',
          'url(' + items[key].loc.image + ')'
        )
      }
      if (items[key].loc.name) $('#map-overlay .map-overlay-title').text(items[key].loc.name)
      $('#map-overlay .map-overlay-type .pin-type-text').text(
        items[key].loc.type !== null ? items[key].loc.type.name : 'other'
      )
      $('#map-overlay .map-overlay-type .pin-type-icon').html(
        items[key].loc.type !== null
          ? '<i class="fa-solid ' + items[key].loc.type.icon + '"></i>'
          : '<i class="fa-solid fa-map-pin"></i>'
      )

      $('.pin-fav-wrapper').attr('data-id', items[key].loc.lid)
      $('.pin-fav-wrapper').attr('data-fids', items[key].fids)

      let mapsUrl =
        $('.map-overlay-action .pin-map').data('url') +
        items[key].loc.lat +
        ',' +
        items[key].loc.lon
      $('.map-overlay-action .pin-map').attr('href', mapsUrl)
      let editUrl = $('.map-overlay-action .pin-conf')
        .data('url')
        .replace('-key-', items[key].loc.lid)
      $('.map-overlay-action .pin-conf').attr('href', editUrl)
      let wazeUrl =
        $('.map-overlay-action .pin-waze').data('url') +
        items[key].loc.lat +
        ',' +
        items[key].loc.lon +
        '&navigate=yes&zoom=17'
      $('.map-overlay-action .pin-waze').attr('href', wazeUrl)

      if (items[key].fids)
        $('#map-overlay').find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
      else $('#map-overlay').find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
    })
  }
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
    })
  })

  inputResult.removeClass('hidden')
}

function search2(map, searchBox, markers) {
  // const places = searchBox.getPlaces()
  // if (places.length == 0) return
  // console.log(markers)
  // markers.m.forEach((marker) => marker.setMap(null))
  // markers.m = []
  // const bounds = new google.maps.LatLngBounds()
  // places.forEach((place) => {
  //   if (!place.geometry || !place.geometry.location) {
  //     console.log('Returned place contains no geometry')
  //     return
  //   }
  //   const icon = {
  //     url: place.icon,
  //     size: new google.maps.Size(71, 71),
  //     origin: new google.maps.Point(0, 0),
  //     anchor: new google.maps.Point(17, 34),
  //     scaledSize: new google.maps.Size(25, 25),
  //   }
  //   markers.m.push(
  //     new google.maps.Marker({
  //       map,
  //       icon,
  //       title: place.name,
  //       position: place.geometry.location,
  //     })
  //   )
  //   if (place.geometry.viewport) bounds.union(place.geometry.viewport)
  //   else bounds.extend(place.geometry.location)
  // })
  // map.fitBounds(bounds)
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
