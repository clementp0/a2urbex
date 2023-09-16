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

document.querySelector('.info-open').addEventListener('click', function () {
  const map__wrapper = document.querySelector('.map-info')
  map__wrapper.classList.add('open')

  document.querySelector('.info-close').addEventListener('click', () => {
    map__wrapper.classList.remove('open')
  })
})

document.querySelector('.category-open').addEventListener('click', function () {
  const source = document.querySelector('.source')
  source.classList.add('open')

  document.querySelector('.category-close').addEventListener('click', () => {
    source.classList.remove('open')
  })
})

// Charts
window.Apex = {
  chart: {
    foreColor: '#ccc',
    toolbar: {
      show: false,
    },
  },
  stroke: {
    width: 3,
  },
  dataLabels: {
    enabled: false,
  },
  tooltip: {
    theme: 'dark',
  },
  grid: {
    borderColor: '#535A6C',
    xaxis: {
      lines: {
        show: true,
      },
    },
  },
}

document.addEventListener('DOMContentLoaded', function () {
  const colors = ['#5B93FF', '#FF906B', '#FFC226', '#605BFF']

  // Chart1
  const options1 = {
    series: [
      {
        name: 'Count',
        data: [pinterest_count, globalmap_count, userl_count, kml_count],
      },
    ],
    chart: {
      height: 300,
      type: 'bar',
    },
    colors: colors,
    plotOptions: {
      bar: {
        columnWidth: '45%',
        distributed: true,
      },
    },
    dataLabels: {
      enabled: false,
    },
    legend: {
      show: false,
    },
    xaxis: {
      categories: [['Pinterest'], ['Globalmap'], ['Users'], ['KML/KMZ']],
      labels: {
        style: {
          colors: colors,
          fontSize: '12px',
        },
      },
    },
  }

  const chart1 = new ApexCharts(document.querySelector('#chart_source'), options1)
  chart1.render()

  // Chart2
  const options2 = {
    series: [
      {
        name: 'Urbex',
        data: [
          castle,
          hostel,
          cinema,
          train,
          hospital,
          house,
          factory,
          building,
          restaurant,
          military,
        ],
      },
    ],
    chart: {
      height: '300px',
      type: 'bar',
    },
    colors: ['#FF5733'],
    xaxis: {
      categories: [
        'Castle',
        'Hostel',
        'Cinema',
        'Train',
        'Hospital',
        'House',
        'Factory',
        'Building',
        'Restaurant',
        'Military',
      ],
    },
  }

  const chart2 = new ApexCharts(document.querySelector('#chart_category'), options2)
  chart2.render()
})
