import WebsocketConnector from './websocket'

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

    // websocket
    const websocket = WebsocketConnector.init(websocketUrl, open)

    function open(socket) {
      socket.subscribe(chatChannel, newMessage)
      return
      socket.subscribe(chatChannel, newMessage)

      $.ajax({
        url: chatGetUrl,
        method: 'GET',
        dataType: 'json',
        success: function (data) {
          renderChatHistory(data)
        },
      })

      $('#message').on('keydown', function (event) {
        if (event.key === 'Enter' || event.keyCode === 13) send(event)
      })
      $('#sendBtn').on('click', function (event) {
        send(event)
      })
    }

    function newMessage(data) {
      addMessage(JSON.parse(data))
      $('#messenger_dot').addClass('messenger_dot')
      $('#chat').scrollTop($('#chat')[0].scrollHeight)
    }

    const send = (event) => {
      event.preventDefault()

      const messageValue = $('#message').val().trim()
      if (messageValue === '') return

      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: chatAddUrl,
        data: messageValue,
        success: (data) => {
          if (!data.success) window.location.reload()
          else $('#message').val('')
        },
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
            roles: ['SERVER'],
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
