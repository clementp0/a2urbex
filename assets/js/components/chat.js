$(() => {
  // Get the messenger and messenger_icon elements
  const messenger = document.querySelector('.messenger')
  const messengerIcon = document.querySelector('.messenger_icon')

  if (messenger && messenger.length) {
    // Add click event listener to messenger_icon
    messengerIcon.addEventListener('click', function () {
      // Show the messenger with animation
      messenger.style.display = 'block'
      var element = document.getElementById('messenger_dot')
      element.classList.remove('messenger_dot_notification')
      messenger.style.animation = 'slide-up 0.5s ease'
    })

    // Get the messenger_close element
    const messengerClose = document.querySelector('.messenger_close')

    // Add click event listener to messenger_close
    messengerClose.addEventListener('click', function () {
      // Hide the messenger with animation
      messenger.style.animation = 'slide-down 0.5s ease'
      setTimeout(function () {
        var element = document.getElementById('messenger_dot')
        element.classList.remove('messenger_dot_notification')
        messenger.style.display = 'none'
        messenger.style.animation = ''
      }, 500)
    })

    // Define the slide-up and slide-down animations
    const slideUpAnimation = `
    @keyframes slide-up {
      from {
        transform: translateX(130%);
      }
      to {
        transform: translateX(0%);
      }
    }
  `

    const slideDownAnimation = `
    @keyframes slide-down {
      from {
        transform: translateX(0%);
      }
      to {
        transform: translateX(130%);
      }
    }
  `

    // Add the slide-up and slide-down animations to the document
    const style = document.createElement('style')
    style.innerHTML = slideUpAnimation + slideDownAnimation
    document.head.appendChild(style)

    // send message via socket
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
        // NOTIFICATION
        const myDiv = document.getElementById('messenger_dot')
        myDiv.classList.add('messenger_dot_notification')
        // NOTIFICATION
        addMessage(data)
        $('#chat').scrollTop($('#chat')[0].scrollHeight)
      } catch (e) {}
    })

    document.getElementById('message').addEventListener('keydown', function (event) {
      if (event.keyCode === 13) {
        event.preventDefault()
        document.getElementById('sendBtn').click()
      }
    })

    document.getElementById('sendBtn').addEventListener('click', function (event) {
      event.preventDefault()
      const messageInput = document.getElementById('message')
      const messageValue = messageInput.value.trim()

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
            messageInput.value = ''
            socket.send(JSON.stringify(data))
          }
        })
    })

    function addMessage(data) {
      const messageHTML = renderRow(data)
      document.getElementById('chat').innerHTML += messageHTML
    }

    function renderChatHistory(chatHistory) {
      const chatHTML = chatHistory
        .map((item) => {
          return renderRow(item)
        })
        .join('')
      $('#chat').scrollTop($('#chat')[0].scrollHeight)
      document.getElementById('chat').innerHTML = chatHTML
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

    $('#chat').scrollTop($('#chat')[0].scrollHeight)
  }
})
