import $ from 'jquery'
import '../notifications'
import '../registersw'

import ClearCache from '../components/cache'

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

  // fetch pinterest
  $('#fetch-pinterest').on('click', function () {
    console.log('send')
    socket.send(JSON.stringify({ type: 'publish', channel: 'admin_progress', message: 'coucou' }))
    //progress()
    // $.ajax({
    //   url: pinterestUrl,
    //   method: 'GET',
    //   dataType: 'json',
    //   success: function (data) {},
    // })
  })

  const socket = new WebSocket(websocketUrl + '?' + session)

  socket.onopen = function (event) {
    const message = {
      type: 'subscribe',
      channel: 'admin_progress',
    }
    socket.send(JSON.stringify(message))
    console.log('subscribe')
  }

  socket.onmessage = function (event) {
    console.log('receive', JSON.parse(event.data))
  }

  socket.onerror = function (event) {
    console.log('error', event.currentTarget)
  }

  socket.onclose = function (event) {
    console.log('close', event)
  }

  // todo rework websocket
  /*
  const socket = new WebSocket(websocketUrl)

  socket.addEventListener('open', function () {
    console.log('CONNECTED')
    $('.websocket').addClass('online')
  })

  socket.addEventListener('close', function () {
    console.log('DISCONNECTED')
    $('.websocket').removeClass('online')
  })

  socket.addEventListener('message', function (e) {
    try {
      const message = JSON.parse(e.data)
      renderProgress(message.progression)
    } catch (e) {}
  })

  function renderProgress(progression) {
    $('.progress-bar-thumb').css('width', progression)
    $('.progress-percent').text(progression)
  }

  function progress() {
    $('.progress-percent').text('Starting...')
    let lastValue = null

    setInterval(() => {
      $.ajax({
        type: 'GET',
        url: 'admin/fetch-progress',
        success: (value) => {
          if (value !== lastValue) {
            lastValue = value
            socket.send(JSON.stringify(message))
            const message = { progression: value }
            renderProgress(value)
          }
        },
      })
    }, 1000)
  }

  $('#message_admin').on('click', function (e) {
    e.preventDefault()

    const messageValue = $('#message').val().trim()
    if (messageValue === '') return

    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: '/chat-admin-add',
      data: messageValue,
      success: (data) => {
        if (data.error) {
          alert("Erreur lors de l'envoi du message")
        } else {
          socket.send(JSON.stringify(data))
          alert('Message envoyé !')
        }
      },
    })
  })
  */
})
