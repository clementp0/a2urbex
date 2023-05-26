function displayNotification(title, message) {
  if (Notification.permission === 'granted') {
    // If the user has granted permission to display notifications
    var notification = new Notification(title, {
      body: message,
      icon: '/a2urbex192x192.png',
    })
  } else if (Notification.permission !== 'denied') {
    // If the user has not yet granted or denied permission to display notifications
    Notification.requestPermission().then(function (permission) {
      // If permission is granted, display the notification
      if (permission === 'granted') {
        var notification = new Notification(title, {
          body: message,
          icon: '/a2urbex192x192.png',
        })
      }
    })
  }
}
