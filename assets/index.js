import $ from 'jquery'
import './js/notifications'
import './js/registersw'

import ClearCache from './js/components/cache'
import Search from './js/components/search'

import './js/components/favorite'
import './js/components/user'
import './js/components/chat'

$(() => {
  ClearCache.init('#clear-cache-button', 'a2urbex')
  Search.init('.pin-search')
})
