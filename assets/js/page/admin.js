import $ from 'jquery'
import '../notifications'
import '../registersw'

import ClearCache from '../components/ClearCache'
import WebsocketConnector from '../components/WebsocketConnector'
import MapProgress from '../components/MapProgress'

$(() => {
  new ClearCache('#clear-cache-button', 'a2urbex')

  const wikimapiaMap = document.getElementById('wikimapia-map')
  const mapProgress = new MapProgress(wikimapiaMap, 24, 12)
  mapProgress.updateGrid(32)

  // global
  $(document).attr('title', '@a2urbex')

  const meta = $('<meta>').attr({
    name: 'apple-mobile-web-app-status-bar-style',
    content: 'default',
  })
  $('head').append(meta)

  // import / run / delete source file
  $('.source-run').on('click', function () {
    source($(this), 'Run')
  })
  $('.source-delete').on('click', function () {
    source($(this), 'Delete')
  })

  function source(item, type = 'Run') {
    const targetName = $('.output.sources :selected').text()
    const targetId = $('.output.sources :selected').val()
    const url = item.data('href').replace('/0/', '/' + targetId + '/')

    if (targetId === '0') return alert('Select a source')

    if (confirm(type + ' source ' + targetName + ' source ?')) item.attr('href', url)
  }

  // websocket
  if (typeof websocketUrl !== 'undefined') {
    const websocket = new WebsocketConnector(websocketUrl, open, close)
  }

  function open(socket) {
    socket.subscribe('admin_progress', renderProgress)
    $('.websocket').addClass('online')
  }
  function close(socket) {
    $('.websocket').removeClass('online')
  }

  function renderProgress(data) {
    if (data.percent !== 100) $('.btn-fetch').addClass('disabled')
    else $('.btn-fetch').removeClass('disabled')

    $(`#${data.type} .progress-bar-thumb`).css('width', `${data.percent}%`)
    $(`#${data.type} .progress-info`).text(data.text.length ? data.text : `${data.percent}%`)
  }

  // fetch
  $('#fetch-pinterest, #fetch-wikimapia, #fetch-pending').on('click', function (e) {
    e.preventDefault()
    fetch($(this))
  })

  function fetch(element) {
    const url = element.attr('href')
    const type = element.data('type')

    if (element.hasClass('disabled')) return
    $(`#${type} .progress-info`).text('Starting...')

    $.ajax({
      url,
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        if (data.lock === true) alert('Script already running')
        $('.btn-fetch').addClass('disabled')
      },
    })
  }

  // admin chat
  $('#message-admin').on('click', function (e) {
    e.preventDefault()

    const messageValue = $('#message').val().trim()
    if (messageValue === '') return

    const url = $(this).attr('href')

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url,
      data: messageValue,
      success: (data) => {
        if (data.success) {
          $('#message').val('')
          alert('Message envoyé !')
        } else {
          alert("Erreur lors de l'envoi du message")
        }
      },
    })
  })
})
