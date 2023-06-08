import $ from 'jquery'
import '../notifications'
import '../registersw'

import ClearCache from '../components/cache'
import WebsocketConnector from '../components/websocket'

$(() => {
  ClearCache.init('#clear-cache-button', 'a2urbex')

  // global
  $(document).attr('title', '@a2urbex')

  const meta = $('<meta>').attr({
    name: 'apple-mobile-web-app-status-bar-style',
    content: 'default',
  })
  $('head').append(meta)

  // import / run / delete source file
  $('.delete-source').on('click', function () {
    const target_name = $('.output.uploads :selected').text()

    if (confirm('Delete source ' + target_name + ' source ?'))
      $('.delete-source').attr('href', 'delete/' + target_name)
  })

  $('.run-source').on('click', function () {
    const target_name = $('.output.uploads :selected').text()
    const target_id = $('.output.uploads :selected').val()

    if (confirm('Run source ' + target_name + ' ?'))
      $('.run-source').attr('href', 'import/' + target_id)
  })

  // websocket
  if (typeof websocketUrl !== 'undefined' && typeof websocketToken !== 'undefined') {
    const url = websocketUrl + '?' + websocketToken
    const websocket = WebsocketConnector.init(url, open)
  }

  function open(socket) {
    socket.subscribe('admin_progress', renderProgress)
  }

  function renderProgress(progression) {
    if (progression !== '100%') $('#fetch-pinterest').addClass('disabled')
    else $('#fetch-pinterest').removeClass('disabled')

    $('.progress-bar-thumb').css('width', progression)
    $('.progress-percent').text(progression)
  }

  // fetch pinterest
  $('#fetch-pinterest').on('click', function () {
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
