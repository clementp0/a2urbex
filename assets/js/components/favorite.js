export default class FavoritePopup {
  static init(...args) {
    return new this(...args)
  }
  constructor(selector) {
    this.element = $(selector)
    this.button = this.element.find('.pin-fav')
    this.popup = this.element.find('.pin-fav-add')

    this.triggers()
  }

  triggers() {
    this.button.on('click', (e) => this.open(e))
    this.element.find('.pin-fav-add-close').on('click', (e) => this.close(e))
    this.element.on('click', '.pin-fav-item', (e) => this.toggleItem(e))
    this.element.find('.pin-fav-add-new').on('click', (e) => this.openAddNew(e))
    this.element.find('.pin-fav-add-new-confirm').on('click', (e) => this.addNew(e))
  }

  getId() {
    return this.element.attr('data-id')
  }

  open(e) {
    e.preventDefault()
    const id = this.getId()

    $.ajax({
      url: this.button.attr('href'),
      method: 'POST',
      dataType: 'json',
      data: { lid: id },
    })
      .done((json) => {
        if (json) {
          this.element.find('.pin-fav-list').empty()

          let fids =
            json.fids && json.fids.length
              ? json.fids.split(',').map((item) => parseInt(item))
              : null

          json.favs.forEach((item) => {
            let cid = 'fav_' + id + '_' + item.fav.id
            let line = $('<div>').addClass('form-check')
            let input = $(
              '<input type="checkbox" class="form-check-input pin-fav-item" value="' +
                item.fav.id +
                '" id="' +
                cid +
                '">'
            )
            let label = $(
              '<label class="form-check-label" for="' + cid + '">' + item.fav.name + '</label>'
            )
            if (fids !== null) input.prop('checked', fids.includes(item.fav.id) ? true : false)

            line.append(input).append(label)
            this.element.find('.pin-fav-list').append(line)
          })

          $('body')
            .find('.pin-fav-add')
            .removeClass('show')
            .find('.pin-fav-add-new-field')
            .removeClass('show')

          this.popup.addClass('show')
        }
      })
      .fail(() => {
        alert('Error')
      })
  }

  close(e) {
    e.preventDefault()
    this.popup.removeClass('show')
    this.popup.find('.pin-fav-add-new-field').removeClass('show')
  }

  toggleItem(e) {
    const current = $(e.currentTarget)

    $.ajax({
      url: this.element.data('url'),
      method: 'POST',
      dataType: 'json',
      data: {
        lid: this.getId(),
        fid: current.val(),
        checked: current.prop('checked') ? 1 : 0,
      },
    })
      .done((json) => {
        if (json.success) {
          this.element.attr('data-fids', json.fids ? json.fids : '')
          if (json.fids)
            this.element.find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
          else this.element.find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
        } else {
          alert('Error')
        }
      })
      .fail(() => {
        alert('Error')
      })
  }

  openAddNew(e) {
    e.preventDefault()
    this.popup.find('.pin-fav-add-new-field').addClass('show')
  }

  addNew(e) {
    e.preventDefault()

    const input = this.element.find('.pin-fav-add-new-input')
    let name = input.val()

    if (name.length && confirm("Confirmer l'ajout")) {
      $.ajax({
        url: this.element.data('url'),
        method: 'POST',
        dataType: 'json',
        data: { lid: this.getId(), name },
      })
        .done((json) => {
          if (json.success) {
            input.val('')
            input.parents('.pin-fav-add-new-field').removeClass('show')
            this.element.attr('data-fids', json.fids ? json.fids : '')
            if (json.fids)
              this.element.find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
            else this.element.find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
            this.element.find('.pin-fav').click()
          } else {
            alert('Error')
          }
        })
        .fail(() => {
          alert('Error')
        })
    }
  }
}
