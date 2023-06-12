$(() => {
  $('.add_friend').on('click', function (e) {
    e.preventDefault()
    const url = $(this).attr('href')
    friendAction(url)
  })
  $('.remove_friend').on('click', function (e) {
    e.preventDefault()
    const url = $(this).attr('href')
    friendAction(url)
  })
  $('.pending_friend').on('click', function (e) {
    e.preventDefault()
    const url = $(this).attr('href')
    friendAction(url)
  })

  function friendAction(url) {
    $.ajax({
      url: url,
      method: 'POST',
      dataType: 'json',
      success: function (data) {
        if (!data || !data.state) alert('An error occured')

        $('.profile__container-infos-add-item').removeClass('show')
        if (data.state === 'friend') $('.remove_friend').addClass('show')
        if (data.state === 'not_friend') $('.add_friend').addClass('show')
        if (data.state === 'pending') $('.pending_friend').addClass('show')
      },
    })
  }
})
