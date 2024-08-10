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

  // User menu

  $('.dropbtn').on('click', () => {
    $('.dropdown-content').toggleClass('dropdown-content-open')
  })
  $('.dropdown-content-tray').on('click', function (e) {
    if (e.target != this) return
    $('.dropdown-content').removeClass('dropdown-content-open')
  })

  // Favorite menu
  $('.dropbtnfav').on('click', function() {
    $('.dropdown-favorite-content').removeClass('dropdown-favorite-content-open');
    $(this).siblings('.dropdown-favorite-content').toggleClass('dropdown-favorite-content-open');
  });

  $('.dropdown-favorite-tray').on('click', function (e) {
    if (e.target != this) return
    $('.dropdown-favorite-content').removeClass('dropdown-favorite-content-open')
  })


  // Copy link
  $("#copyButton").click(function(){
      var $tempInput = $("<input>");
      $("body").append($tempInput);
      $tempInput.val(window.location.href).select();
      document.execCommand("copy");
      $tempInput.remove();

      $(this).html("<i class='fa-solid fa-check'></i><span></span> Copied to clipboard").addClass("copied");
      setTimeout(() => {
        $(this).html("<i class='fa-solid fa-share'></i><span></span> Share " + $(this).attr('data-text')).removeClass("copied");
      }, 2000);
  });
  
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
