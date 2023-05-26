$(() => {
  // Get the messenger and messenger_icon elements
  const messenger = document.querySelector('.messenger')
  const messengerIcon = document.querySelector('.messenger_icon')

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
})
