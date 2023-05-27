$(() => {
  const messenger = $('.messenger')

  if (messenger.length) {
    const messengerIcon = $('.messenger_icon')
    const messengerClose = $('.messenger_close')

    messengerIcon.on('click', () => {
      $('#messenger_dot').removeClass('messenger_dot')
      messenger.addClass('show')
    })
    messengerClose.on('click', () => {
      messenger.removeClass('show')
    })

    // send socket message
    const socket = new WebSocket(websocketUrl)

    socket.addEventListener('open', function () {
      console.log('Connected to General Chat')
      fetch('/chat-get')
        .then((response) => response.json())
        .then((data) => {
          renderChatHistory(data)
        })
    })

    socket.addEventListener('message', function (e) {
      try {
        const data = JSON.parse(e.data)
        addMessage(data)
        $('#messenger_dot').addClass('messenger_dot')
        $('#chat').scrollTop($('#chat')[0].scrollHeight)
      } catch (e) {}
    })

    $('#message').on('keydown', function (event) {
      if (event.key === 'Enter' || event.keyCode === 13) {
        send(event)
      }
    })
    $('#sendBtn').on('click', function (event) {
      send(event)
    })

    const send = (event) => {
      event.preventDefault()

      const messageValue = $('#message').val().trim()
      if (messageValue === '') return

      fetch('/chat-add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: messageValue,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            if (data.error === 'reload') window.location.reload()
            else alert(date.error)
          } else {
            addMessage(data)
            $('#chat').scrollTop($('#chat')[0].scrollHeight)
            $('#message').val('')
            socket.send(JSON.stringify(data))
          }
        })
    }

    function addMessage(data) {
      const messageHTML = renderRow(data)
      $('#chat').append(messageHTML)
    }

    function renderChatHistory(chatHistory) {
      const chatHTML = chatHistory
        .map((item) => {
          return renderRow(item)
        })
        .join('')

      $('#chat').html(chatHTML)
      $('#chat').scrollTop($('#chat')[0].scrollHeight)
    }

    function renderRow(item) {
      const timestamp = Date.parse(item.datetime)
      const date = new Date(timestamp)
      const dateFormatted = date.toLocaleString('fr-FR', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })

      const sender = item.sender
        ? item.sender
        : {
            id: 0,
            firstname: 'a2urbex',
            roles: ['ROLE_SERVER'],
          }

      return (
        "<div class='message " +
        sender.roles.join(' ') +
        (sender.id === currentId ? ' user_current' : '') +
        "'>" +
        "<p class='name'>" +
        '<span>' +
        sender.firstname +
        '#' +
        sender.id +
        '</span>' +
        (sender.roles.includes('ROLE_ADMIN')
          ? '<span class="shield"><i class="fa-solid fa-shield-halved"></i></span>'
          : '') +
        '</p>' +
        "<p class='message_content'> " +
        item.message +
        '</p>' +
        "<p class='message_date'> " +
        dateFormatted +
        '</p>' +
        '</div>'
      )
    }
  }
})
