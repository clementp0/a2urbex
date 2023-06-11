$(()=>{
    $(".add_friend").on("click", function(e){
        e.preventDefault()
        const url = $(this).attr('href')
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            success: function (data) {
              changeBtnState(data.state)
            },
          })
    })
    $(".remove_friend").on("click", function(e){
        e.preventDefault()
        const url = $(this).attr('href')
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            success: function (data) {
              changeBtnState(data.state)
            },
          })
    })
    function changeBtnState(state){
        $(".profile__container-infos-add-item").removeClass("show")
        if (state === 'friend') $(".remove_friend").addClass("show")
        if (state === 'not_friend') $(".add_friend").addClass("show")
        if (state === 'pending') $(".pending_friend").addClass("show")
        console.log(state);
    }
})