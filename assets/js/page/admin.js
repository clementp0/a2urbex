import $ from 'jquery'
import '../notifications'
import '../registersw'

import ClearCache from '../components/ClearCache'
import WebsocketConnector from '../components/WebsocketConnector'

$(() => {
  new ClearCache('#clear-cache-button', 'a2urbex')

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

  function renderProgress(progression) {
    if (progression !== '100%') $('#fetch-pinterest').addClass('disabled')
    else $('#fetch-pinterest').removeClass('disabled')

    $('.progress-bar-thumb').css('width', progression)
    $('.progress-percent').text(progression)
  }

  // fetch pinterest
  $('#fetch-pinterest').on('click', function () {
    if ($(this).hasClass('disabled')) return
    $('.progress-percent').text('Starting...')

    $.ajax({
      url: pinterestUrl,
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        if (data.lock === true) alert('Script already running')
        $('#fetch-pinterest').addClass('disabled')
      },
    })
  })

  // admin chat
  $('#message-admin').on('click', function (e) {
    e.preventDefault()

    const messageValue = $('#message').val().trim()
    if (messageValue === '') return

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: chatAddAdminUrl,
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
