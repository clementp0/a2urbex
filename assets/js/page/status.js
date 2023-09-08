import WebsocketConnector from '../components/WebsocketConnector'
import MapProgress from '../components/MapProgress'

$(() => {
  const wikimapiaMap = document.getElementById('map')
  const mapProgress = new MapProgress(wikimapiaMap, 24, 12)

  // websocket
  if (typeof websocketUrl !== 'undefined') {
    const websocket = new WebsocketConnector(websocketUrl, open)
  }

  function open(socket) {
    socket.subscribe('admin_progress', renderProgress)
  }

  function renderProgress(data) {
    if (data.type === 'wikimapia') {
      mapProgress.updateGrid(data.percent)

      if (data.sub_type === 'fetch') {
        wikimapiaMap.classList.remove('process')

        $('.map-percentage-text').text(`${data.percent}%`)
        $('.percentage-bar').width(data.percent + '%')
        $('.map-coordinates-text').text(`X:${data.x} Y:${data.y}`)
      } else if (data.sub_type === 'process') {
        wikimapiaMap.classList.add('process')

        $('.map-processing-text').text(data.percent + '%')
        $('.processing-bar').width(data.percent + '%')
        $('.map-processed-text').text(`${data.pinCount} / ${data.pinTotal}`)
      }
    }
  }
})

document.querySelector('.fa-circle-info').addEventListener('click', function () {
  const map__wrapper = document.querySelector('.map-info')

  document.querySelector('.info-open').addEventListener('click', () => {
    map__wrapper.classList.add('open')
  })
  document.querySelector('.info-close').addEventListener('click', () => {
    map__wrapper.classList.remove('open')
  })
})
