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
      if (data.sub_type === 'fetch'){
        mapProgress.updateGrid(data.percent)
        $('.map-percentage-text').text(`${data.percent}%`)
        $('.percentage-bar').width(data.percent + "%")
        $('.map-coordinates-text').text(`X:${data.x} Y:${data.y}`)
        console.log(data.percent)
      } else if(data.sub_type === 'process'){
        $('.map-processing-text').text(data.percent + "%")
        $('.processing-bar').width(data.percent + "%")
      }
      
     
    }
  }
})

document.querySelector('.fa-circle-info').addEventListener('click', function() {
  const mapInfo = document.querySelector('.map-info');
  const openButton = document.querySelector('.fa-circle-info');
  const closeButton = document.querySelector('.fa-circle-xmark');
  
  function openMapInfo() {
    mapInfo.style.right = '0px';
  }
  
  function closeMapInfo() {
    mapInfo.style.right = '-130%';
  }
  
  openButton.addEventListener('click', openMapInfo);
  closeButton.addEventListener('click', closeMapInfo);
});

