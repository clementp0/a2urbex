export default class Search {
  constructor(selector) {
    this.element = $(selector);

    this.default();
    this.triggers();
  }

  default() {
    const fieldsets = this.element.find('fieldset.form-group');
    this.deleteFilters = this.element.find('.pin-search-reset');
    fieldsets.wrapAll('<div class="filters-container"></div>');
    this.element.find('legend:eq(0)').prepend('<i class="fa-solid fa-earth-europe"></i>');
    this.element.find('legend:eq(1)').prepend('<i class="fa-solid fa-gear"></i>');
    this.element.find('legend:eq(2)').prepend('<i class="fa-solid fa-sliders"></i>');
    this.element.find('.pin-filter-btn').html('<i class="fa-solid fa-magnifying-glass"></i>');

    const formGroups = this.element.find('.form-group');
    const lastFormGroupContent = formGroups.last().children().not('legend').detach();
    formGroups.first().append(lastFormGroupContent);
    formGroups.first().append(this.deleteFilters);
  }

  triggers() {
    const me = this;
    $('#map-filter').on('click', (e) => this.map(e));
    this.element.find('fieldset.form-group > legend').on('click', this.toggleList);
  }

  map(e) {
    e.preventDefault();
    const current = $(e.currentTarget);

    const href = current.attr('href');
    this.element.find('form').attr('action', href).find('#submit').click();
  }

  toggleList() {
    const div = $(this).siblings('div');

    const open = $(this).attr('data-open') && $(this).attr('data-open') == 'true' ? false : true;
    $(this).attr('data-open', open);

    if (open) {
      div.css('maxHeight', '200px');
    } else {
      div.css('maxHeight', '0px');
    }
  }
}

window.addEventListener('scroll', function () {
  var scrollPosition = window.scrollY;
  var blurValue = Math.min(scrollPosition / 30, 20);
  var borderRadius;
  if (blurValue > 10) {
    borderRadius = 0;
  } else {
    borderRadius = 30 / (blurValue / 2);
  }

  document.querySelector('.pin-search').style.backdropFilter = 'blur(' + blurValue + 'px)';

  if (scrollPosition > 180) {
    document.querySelector('.pin-search form').style.width = blurValue * 10 + '%';
    document.querySelector('.search-bar').style.borderRadius = borderRadius + 'px';
  } else {
    document.querySelector('.pin-search form').style.width = '400px';
    document.querySelector('.search-bar').style.borderRadius = '50px';
  }

  if (scrollPosition > 315) {
    document.querySelector('.pin-search').classList.add('active');
  } else {
    document.querySelector('.pin-search').classList.remove('active');
  }

});
