import $ from 'jquery'
import './js/notifications'
import './js/registersw'

import ClearCache from './js/components/cache'
import Search from './js/components/search'
import UserModal from './js/components/user'
import FavoritePopup from './js/components/favorite'
import ImageInput from './js/components/custominput'

import './js/components/chat'

$(() => {
  ClearCache.init('#clear-cache-button', 'a2urbex')
  Search.init('.pin-search')
  UserModal.init('.inmodal')
  ImageInput.auto()
  $('.pin-fav-wrapper').each(function () {
    FavoritePopup.init(this)
  })

  // open/close side menu
  $('.pin-open-search').on('click', () => {
    $('.has-sidebar').toggleClass('menu-open')
  })
  $('.pin-search-wrapper').on('click', function (e) {
    if (e.target != this) return
    $('.has-sidebar').removeClass('menu-open')
  })

  // Friend page
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
  $('.fav-item-delete').on('click', function (e) {
    if (!confirm('Delete list')) e.preventDefault()
  })
  $('.fav-item-share-link').on('click', function (e) {
    if (!confirm('Change list permission')) e.preventDefault()
  })

  $('.fav-item-copy-link').on('click', function (e) {
    e.preventDefault()
    if (confirm('Copy list link')) {
      const copyText = $(this).attr('href')
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
