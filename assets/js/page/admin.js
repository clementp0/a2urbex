import $ from 'jquery'
import '../notifications'
import '../registersw'

$(() => {
  // todo rework script

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

  $(document).attr('title', '@a2urbex')

  var meta = $('<meta>').attr({
    name: 'apple-mobile-web-app-status-bar-style',
    content: 'default',
  })

  $('.delete-target').click(function () {
    var target_name = $('.output-uploads :selected').text()
    var target_to_delete = 'delete/' + target_name
    if (confirm('Delete ' + target_name + ' source ?')) {
      $('.delete-target').attr('href', target_to_delete)
    }
  })
  $('.run-target').click(function () {
    var target_name = $('.output-uploads :selected').text()
    var target_id = $('.output-uploads :selected').val()
    var target_import_link = 'import/' + target_id
    if (confirm('Run for ' + target_name + ' ?')) {
      $('.run-target').attr('href', target_import_link)
    }
  })

  const clearCacheButton = document.getElementById('clear-cache-button')
  clearCacheButton.addEventListener('click', async () => {
    try {
      await caches.delete('a2urbex')
      console.log('Cache deleted successfully')
      location.reload(true)
    } catch (err) {
      console.error('Error deleting cache:', err)
    }
  })

  const socket = new WebSocket(websocketUrl)

  socket.addEventListener('open', function () {
    console.log('CONNECTED')
    document.querySelector('.websocket_online').style.display = 'inline-block'
    document.querySelector('.websocket_offline').style.display = 'none'
  })

  socket.addEventListener('close', function () {
    console.log('DISCONNECTED')
    document.querySelector('.websocket_online').style.display = 'none'
    document.querySelector('.websocket_offline').style.display = 'inline-block'
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

  $('#fetch-pinterest').click(function () {
    $.ajax({
      url: '{{ path("app_fetch_pinterest") }}',
      method: 'GET',
      dataType: 'json',
      success: function (data) {
        console.log('Foo: ' + data)
      },
    })
  })
})
