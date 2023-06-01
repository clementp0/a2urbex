$(() => {
  $('#clear-cache-button').on('click', async () => {
    try {
      await caches.delete('a2urbex')
      console.log('Cache deleted successfully')
      location.reload(true)
    } catch (err) {
      console.error('Error deleting cache:', err)
    }
  })
})
