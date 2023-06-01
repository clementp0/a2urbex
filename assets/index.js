import $ from 'jquery'
import './js/notifications'
import './js/registersw'

import ClearCache from './js/components/cache'
import Search from './js/components/search'
import UserModal from './js/components/user'

import './js/components/favorite'
import './js/components/chat'

$(() => {
  ClearCache.init('#clear-cache-button', 'a2urbex')
  Search.init('.pin-search')
  UserModal.init('.inmodal')

  $('.friend-accept').on('click', (e) => {
    if (!confirm('Accept user ?')) e.preventDefault()
  })
  $('.friend-decline').on('click', (e) => {
    if (!confirm('Decline user ?')) e.preventDefault()
  })
  $('.friend-remove').on('click', (e) => {
    if (!confirm('Remove friend ?')) e.preventDefault()
  })
})
