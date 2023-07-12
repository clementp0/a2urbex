import ChatEdit from './ChatEdit'
import UserModal from '../UserModal'

export default class ChatInfo extends ChatEdit {
  constructor(element, parent) {
    super(element, parent)

    this.op = false
    this.titleEdit = false
    this.imageEdit = false
    this.defaultTitle = ''

    this.default()
    this.triggers()
  }

  default() {
    this.titleWrapperElement = this.screenElement.find('.chat-edit-title-wrapper')
    this.titleButtonElement = this.screenElement.find('.chat-edit-title-button')
    this.imageWrapperElement = this.screenElement.find('.chat-edit-image-wrapper')
    this.imageButtonElement = this.screenElement.find('.chat-edit-image-button')
    this.leaveButtonElement = this.screenElement.find('.chat-edit-leave')

    this.titleUrl = this.formatUrl(this.titleButtonElement.attr('href'), this.parent.name)
    this.imageUrl = this.formatUrl(this.imageButtonElement.attr('href'), this.parent.name)
    this.addUserUrl = this.searchElement.data('adduserurl')
  }

  triggers() {
    this.titleButtonElement.on('click', (e) => this.titleButton(e))
    this.imageButtonElement.on('click', (e) => this.imageButton(e))
    this.leaveButtonElement.on('click', (e) => this.leaveButton(e))
  }

  close() {
    super.close()
    this.resetTitle()
    this.resetTitle()
    this.removeUsers()
  }

  resetTitle() {
    this.titleEdit = false
    this.titleWrapperElement.removeClass('modified').addClass('default')
  }
  resetImage() {
    this.imageEdit = false
    this.imageWrapperElement.removeClass('modified').addClass('default')
  }

  getInfo(url) {
    $.ajax({
      url,
      method: 'GET',
      dataType: 'json',
      success: (data) => {
        if (data) this.renderInfo(data)
      },
    })
  }

  renderInfo(data) {
    const currentUser = data.chatUsers.find((item) => item.user.id === this.main.user)
    this.op = currentUser.op
    this.defaultTitle = data.title

    if (!data.multi || !this.op) {
      this.screenElement.find('.icon-edit').addClass('hidden')
      this.searchElement.addClass('hidden')
    }
    if (!data.multi) {
      this.titleWrapperElement.addClass('hidden')
      this.imageWrapperElement.addClass('hidden')
      this.leaveButtonElement.addClass('hidden')
    }

    if (data.image) {
      this.image = data.image
      this.updateImagePreviewElement()
    }

    const labelElement = this.imageWrapperElement.find('label')
    const id = labelElement.attr('for') + '-' + data.name
    labelElement.attr('for', id)
    this.imageElement.attr('id', id)

    this.title = data.title
    this.updateTitleElement()

    data.chatUsers.forEach((item) => this.addUser(item))

    this.parent.loading(false)
    this.open()
  }

  titleButton(e) {
    e.preventDefault()

    if (this.titleEdit && this.title?.length) {
      $.ajax({
        url: this.titleUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          title: this.title,
        },
        success: (data) => {
          if (data?.success) {
            alert('Title updated')
            this.resetTitle()
          }
        },
      })
    } else {
      this.titleWrapperElement.removeClass('default')
      this.titleElement.focus()
    }
  }

  updateTitle() {
    super.updateTitle()
    if (this.defaultTitle !== this.title) {
      this.titleWrapperElement.addClass('modified')
      this.titleEdit = true
    } else {
      this.titleWrapperElement.removeClass('modified')
      this.titleEdit = false
    }
  }

  imageButton(e) {
    e.preventDefault()

    if (this.imageEdit && this.image) {
      $.ajax({
        url: this.imageUrl,
        method: 'POST',
        dataType: 'json',
        data: {
          image: this.image,
        },
        success: (data) => {
          if (data?.success) {
            alert('Image updated')
            this.resetImage()
          }
        },
      })
    } else {
      this.imageWrapperElement.removeClass('default')
      this.imageElement.click()
    }
  }

  updateImage(e) {
    super.updateImage(e)
    if (this.image) {
      this.imageWrapperElement.addClass('modified')
      this.imageEdit = true
    } else {
      this.imageWrapperElement.removeClass('modified')
      this.imageEdit = false
    }
  }

  addUserTrigger() {
    $.ajax({
      url: this.formatUrl(this.addUserUrl, this.parent.name, this.modal.current.id),
      method: 'POST',
      dataType: 'json',
      success: (data) => {
        if (data.success === false) return alert('Unable to add user')
        super.addUserTrigger()
      },
    })
  }

  renameUser(user, url, name) {
    $.ajax({
      url: this.formatUrl(url, this.parent.name, user.user.id),
      method: 'POST',
      dataType: 'json',
      data: {
        name,
      },
      success: (data) => {
        if (!data?.success) return alert('Unable to rename user')
        else user.updatePseudo(name)
      },
    })
  }

  opUser(user, url) {
    $.ajax({
      url: this.formatUrl(url, this.parent.name, user.user.id),
      method: 'POST',
      dataType: 'json',
      success: (data) => {
        if (!data?.success) return alert('Unable to op user')
        else user.updateOp()
      },
    })
  }

  deleteUser(user, url) {
    $.ajax({
      url: this.formatUrl(url, this.parent.name, user.user.id),
      method: 'POST',
      dataType: 'json',
      success: (data) => {
        if (!data?.success) return alert('Unable to remove user')
        else this.removeUser(user)
      },
    })
  }

  leaveButton(e) {
    e.preventDefault()

    $.ajax({
      url: this.formatUrl(this.leaveButtonElement.attr('href'), this.parent.name),
      method: 'POST',
      dataType: 'json',
      success: (data) => {
        if (!data.success) {
          return alert('Unable to leave group')
        } else {
          this.close()
          this.parent.close()

          setTimeout(() => {
            const name = this.parent.name

            this.main.screens.list.screens[name].screenElement.remove()
            delete this.main.screens.list.screens[name]

            this.main.screens.list.items[name].remove()
            delete this.main.screens.list.items[name]
          }, 500)
        }
      },
    })
  }
}
