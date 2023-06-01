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
    progress()
    // $.ajax({
    //   url: pinterestUrl,
    //   method: 'GET',
    //   dataType: 'json',
    //   success: function (data) {},
    // })
  })

  // todo rework websocket
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
    $('.progress-bar').css('width', progression)
    $('.progress-percent').val(Math.floor(progression * 100) / 100 + '%')
  }

  function progress() {
    $('.progress-percent').text('Starting...')
    let lastValue = null

    setInterval(() => {
      const xhr = new XMLHttpRequest()
      xhr.open('GET', 'admin/fetch-progress')
      xhr.responseType = 'text'
      xhr.setRequestHeader('Cache-Control', 'max-age=0')
      xhr.onload = () => {
        if (xhr.status === 200) {
          const value = xhr.responseText
          if (value !== lastValue) {
            lastValue = value

            const message = { progression: value }
            socket.send(JSON.stringify(message))
            renderProgress(message.progression)
          }
        }
      }
      xhr.send()
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
})
