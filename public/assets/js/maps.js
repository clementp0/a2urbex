function initMap() {
    const map = new google.maps.Map(document.getElementById("maps"), {
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
        url: "https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png",
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
        new google.maps.Marker({
            position: { lat: Number.parseFloat(items[key].loc.lon), lng: Number.parseFloat(items[key].loc.lat) },
            map,
            icon: image,
            shape: shape,
            title: items[key].loc.name,
        })
    }
}

window.initMap = initMap;