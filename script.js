let map, directionsService, directionsRenderer;

// Inicializa el mapa con la ubicación predeterminada
function iniciarMap() {
    const ubicacionInicial = { lat: 25.5598042, lng: -100.9592244 }; // Saltillo, Coahuila

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: ubicacionInicial
    });

    // Servicios para la dirección y ruta
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    // Agregar marcador en la ubicación inicial
    new google.maps.Marker({
        position: ubicacionInicial,
        map: map,
        title: "Ubicación inicial"
    });

    document.getElementById('find-location').addEventListener('click', function() {
        obtenerUbicacion(ubicacionInicial);
    });
}

// Obtener la ubicación actual del usuario
function obtenerUbicacion(ubicacionInicial) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const userLocation = { lat: lat, lng: lng };

            // Llamar a la función para trazar la ruta
            trazarRuta(ubicacionInicial, userLocation);
        }, manejarError, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        });
    } else {
        alert("Geolocalización no es soportada por este navegador.");
    }
}

// Trazar la ruta entre la ubicación inicial y la ubicación del usuario
function trazarRuta(origen, destino) {
    const request = {
        origin: origen,
        destination: destino,
        travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route(request, function(result, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(result);

            // Calcular la distancia en kilómetros
            const distancia = google.maps.geometry.spherical.computeDistanceBetween(
                new google.maps.LatLng(origen.lat, origen.lng),
                new google.maps.LatLng(destino.lat, destino.lng)
            ) / 1000; // Convertir de metros a kilómetros

            // Mostrar la distancia
            document.getElementById('distance').textContent = `Distancia entre las ubicaciones: ${distancia.toFixed(2)} km`;
        } else {
            alert('No se pudo trazar la ruta.');
        }
    });
}

// Manejar errores de geolocalización
function manejarError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("Se denegó el acceso a tu ubicación.");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("La ubicación no está disponible.");
            break;
        case error.TIMEOUT:
            alert("La solicitud de ubicación ha caducado.");
            break;
        case error.UNKNOWN_ERROR:
            alert("Se ha producido un error desconocido.");
            break;
    }
}

// Cargar el mapa cuando la página esté lista
window.onload = iniciarMap;