function initMap() {
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 6,
        center: { lat: 46.71109, lng: 1.7191036 },
    });

    setMarkers(map);
}

function setMarkers(map) {
    // Adds markers to the map.
    // Marker sizes are expressed as a Size of X,Y where the origin of the image
    // (0,0) is located in the top left of the image.
    // Origins, anchor positions and coordinates of the marker increase in the X
    // direction to the right and in the Y direction down.
    const image = {
        url: 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
        // This marker is 20 pixels wide by 32 pixels high.
        size: new google.maps.Size(20, 32),
        // The origin for this image is (0, 0).
        origin: new google.maps.Point(0, 0),
        // The anchor for this image is the base of the flagpole at (0, 32).
        anchor: new google.maps.Point(0, 32),
    };
    // Shapes define the clickable region of the icon. The type defines an HTML
    // <area> element 'poly' which traces out a polygon as a series of X,Y points.
    // The final coordinate closes the poly by connecting to the first coordinate.
    const shape = {
        coords: [1, 1, 1, 20, 18, 20, 18, 1],
        type: "poly",
    };

    const items = JSON.parse(locations)
    for(let key in items) {
        const marker = new google.maps.Marker({
            position: { lat: Number.parseFloat(items[key].loc.lat), lng: Number.parseFloat(items[key].loc.lon) },
            map,
            icon: image,
            shape: shape,
            title: items[key].loc.name,
            type: items[key].loc.type,
        })

        marker.addListener('click', () => {
            //map.setZoom(8)
            //map.setCenter(marker.getPosition())

            $('.pin-fav-add').removeClass('show')
            $('#map-overlay').addClass('show')
            if (items[key].loc.image === null){
            $('#map-overlay .map-overlay-img').css('backgroundImage', 'url("/assets/default.png")')
            }else{
            $('#map-overlay .map-overlay-img').css('backgroundImage', 'url('+items[key].loc.image+')')
            }
            if(items[key].loc.name) $('#map-overlay .map-overlay-title').text(items[key].loc.name)
            $('#map-overlay .map-overlay-type .pin-type-text').text(items[key].loc.type !== null ? items[key].loc.type.name : 'other')
            $('#map-overlay .map-overlay-type .pin-type-icon').html(items[key].loc.type !== null ? '<i class="fa-solid ' + items[key].loc.type.icon + '"></i>' : '<i class="fa-solid fa-map-pin"></i>')

            $('.pin-fav-wrapper').attr('data-id', items[key].loc.id)
            $('.pin-fav-wrapper').attr('data-fids', items[key].fids)

            let mapsUrl = $('.map-overlay-action .pin-map').data('url') + items[key].loc.lat + ',' + items[key].loc.lon
            $('.map-overlay-action .pin-map').attr('href', mapsUrl)
            let editUrl = $('.map-overlay-action .pin-conf').data('url') + items[key].loc.id
            $('.map-overlay-action .pin-conf').attr('href', editUrl)

            if(items[key].fids) $('#map-overlay').find('.pin-fav i').addClass('fa-solid').removeClass('fa-regular')
            else $('#map-overlay').find('.pin-fav i').addClass('fa-regular').removeClass('fa-solid')
        })
    }
}

window.initMap = initMap;


$(() => {
    $('.map-overlay-close').on('click', e => {
        e.preventDefault()
        $('#map-overlay').removeClass('show')
    })
})