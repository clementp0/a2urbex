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
    $.ajax({
      url: pinterestUrl,
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        console.log('Foo: ' + data)
      },
    })
  })

  // todo rework websocket
  const socketStatus = new WebSocket(websocketUrl)

  socketStatus.addEventListener('open', function () {
    console.log('Connected to Status Websocket')
  })
  function addMessage(progression) {
    const messageHTML = renderRow(progression)
  }
  socketStatus.addEventListener('message', function (e) {
    try {
      const message = JSON.parse(e.data)
      addMessage(message.progression)
    } catch (e) {}
  })

  function renderRow(progression) {
    const progressBar = document.querySelector('.progress-bar')
    const progressText = document.querySelector('.progress-percent')
    progressBar.style.width = progression + '%'
    progressText.textContent = Math.floor(progression * 100) / 100 + '%'
  }

  document.addEventListener('DOMContentLoaded', () => {
    const startButton = document.getElementById('fetch-pinterest')

    startButton.addEventListener('click', () => {
      const progressText = document.querySelector('.progress-percent')
      progressText.textContent = 'Starting...'
      let lastValue = null

      function handleNewLines(value) {
        const message = {
          progression: value,
        }
        socketStatus.send(JSON.stringify(message))
        addMessage(message.progression)
      }

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
              handleNewLines(value)
            }
          }
        }
        xhr.send()
      }, 1000)
    })
  })

  const socket = new WebSocket(websocketUrl)

  socket.addEventListener('open', function () {
    console.log('CONNECTED')
    $('.websocket').addClass('online')
  })

  socket.addEventListener('close', function () {
    console.log('DISCONNECTED')
    $('.websocket').removeClass('online')
  })

  document.getElementById('message_admin').addEventListener('click', function (event) {
    event.preventDefault()
    const messageInput = document.getElementById('message')
    const messageValue = messageInput.value.trim()

    if (messageValue === '') return

    fetch('/chat-admin-add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: messageValue,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
          alert("Erreur lors de l'envoi du message")
        } else {
          socket.send(JSON.stringify(data))
          alert('Message envoyé !')
        }
      })
  })
})
