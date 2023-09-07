export default class MapProgress {
  constructor(parent, sizeX, sizeY) {
    this.parent = parent
    this.squares = []
    this.sizeX = sizeX
    this.sizeY = sizeY

    this.prevPos = 0

    this.createGrid()
  }

  createGrid() {
    this.grid = document.createElement('div')
    this.grid.classList.add('grid')
    this.parent.append(this.grid)

    for (let x = 0; x < this.sizeX; x++) {
      for (let y = 0; y < this.sizeY; y++) {
        const el = document.createElement('div')
        el.classList.add('square')

        el.style.width = 100 / this.sizeX + '%'
        el.style.left = (100 / this.sizeX) * x + '%'
        el.style.height = 100 / this.sizeY + '%'
        el.style.top = (100 / this.sizeY) * y + '%'

        this.grid.append(el)
        this.squares.push(el)
      }
    }
  }

  updateGrid(percent) {
    const pos = Math.ceil((percent / 100) * (this.sizeX * this.sizeY))
    if (pos === this.prevPos) return

    for (let c = 0; c < this.sizeX * this.sizeY; c++) {
      if (c === pos) this.squares[c].classList.add('highlight')
      else this.squares[c].classList.remove('highlight')

      if (c <= pos) this.squares[c].classList.add('done')
      else this.squares[c].classList.remove('done')
    }

    this.prevPos = pos
  }
}
