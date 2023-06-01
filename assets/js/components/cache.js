export default class ClearCache {
  static init(...args) {
    return new this(...args)
  }
  constructor(selector, name) {
    this.element = $(selector)
    this.name = name

    this.triggers()
  }

  triggers() {
    this.element.on('click', this.clear)
  }

  async clear() {
    try {
      await caches.delete(this.name)
      console.log('Cache deleted successfully')
      location.reload(true)
    } catch (err) {
      console.error('Error deleting cache:', err)
    }
  }
}
