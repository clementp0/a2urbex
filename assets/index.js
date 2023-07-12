import $ from 'jquery'
import './js/notifications'
import './js/registersw'

import ClearCache from './js/components/ClearCache'
import Search from './js/components/Search'
import UserModal from './js/components/UserModal'
import FavoritePopup from './js/components/FavoritePopup'
import CustomInput from './js/components/CustomInput'
import Chat from './js/components/chat/Chat'

$(() => {
  new ClearCache('#clear-cache-button', 'a2urbex')
  CustomInput.auto()

  // Chat
  const chatIcon = $('.chat-icon')
  const chatWrapper = $('#chat-wrapper')
  if (chatIcon.length && chatWrapper.length) new Chat(chatIcon, chatWrapper)

  // Side menu
  new Search('.pin-search')

  $('.pin-open-search').on('click', () => {
    $('.has-sidebar').toggleClass('menu-open')
  })
  $('.pin-search-wrapper').on('click', function (e) {
    if (e.target != this) return
    $('.has-sidebar').removeClass('menu-open')
  })

  // Friend page
  const friendElement = $('.add-friend.inmodal')
  new UserModal(friendElement)

  $('.friend-accept').on('click', (e) => {
    if (!confirm('Accept user ?')) e.preventDefault()
  })
  $('.friend-decline').on('click', (e) => {
    if (!confirm('Decline user ?')) e.preventDefault()
  })
  $('.friend-remove').on('click', (e) => {
    if (!confirm('Remove friend ?')) e.preventDefault()
  })

  // Favorite page
  const favElement = $('.fav-item-share-user.inmodal')
  new UserModal(favElement)

  $('.pin-fav-wrapper').each(function () {
    new FavoritePopup(this)
  })

  $('.fav-item-delete').on('click', function (e) {
    if (!confirm('Delete list')) e.preventDefault()
  })
  $('.fav-item-share-link').on('click', function (e) {
    if (!confirm('Change list permission')) e.preventDefault()
  })

  $('.fav-item-copy-link').on('click', function (e) {
    e.preventDefault()
    if (confirm('Copy list link')) {
      const copyText = location.origin + $(this).attr('href')
      document.addEventListener(
        'copy',
        function (e) {
          e.clipboardData.setData('text/plain', copyText)
          e.preventDefault()
        },
        true
      )
      document.execCommand('copy')
    }
  })
})
