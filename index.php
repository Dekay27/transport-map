<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accra Car Tracking Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        #sidebar {
            height: 100vh;
            overflow-y: auto;
            width: 300px;
            padding: 15px;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
        }
        #map {
            height: 100vh;
            margin-left: 300px;
        }
        .car-item {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: white;
            cursor: pointer;
        }
        .car-item:hover {
            background-color: #e9ecef;
        }

        /* Pulse Effect */
        .pulse-icon {
            background: rgba(0, 136, 255, 0.4);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }
            50% {
                transform: scale(1.4);
                opacity: 0.6;
            }
            100% {
                transform: scale(0.8);
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<div id="sidebar" class="position-fixed">
    <h4>Available Cars</h4>
    <div id="carList"></div>
</div>

<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

<script>
    // Initialize map
    var map = L.map('map').setView([5.5600, -0.2050], 13);

    // Use Stadia Maps (Google-like)
    L.tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
        maxZoom: 20
    }).addTo(map);

    var currentRoute = null;
    var moveInterval = null;
    var carMarker = null;
    var markers = [];

    // Custom car icon
    var carIcon = L.icon({
        iconUrl: 'img.png', // Use your uploaded car icon
        iconSize: [32, 32],
        iconAnchor: [16, 16],
        popupAnchor: [0, -16]
    });

    // Fetch cars
    fetch('get_cars.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach((car, index) => {
                    addCarMarker(car);
                    addCarToSidebar(car, index);
                });

                // Automatically load first car's route
                showRoute(data[0]);
            }
        })
        .catch(error => console.error('Error fetching cars:', error));

    // Add car to sidebar
    function addCarToSidebar(car, index) {
        const carList = document.getElementById('carList');
        const carItem = document.createElement('div');
        carItem.className = 'car-item';
        carItem.innerHTML = `<strong>${car.name}</strong><br>From: ${car.source}<br>To: ${car.destination}`;
        carItem.onclick = () => {
            showRoute(car);
            map.setView([car.current_lat, car.current_lng], 16); // Zoom in to car
        };
        carList.appendChild(carItem);
    }

    // Add static car marker with pulse
    function addCarMarker(car) {
        var marker = L.marker([car.current_lat, car.current_lng], { icon: carIcon }).addTo(map);

        var pulseDiv = L.divIcon({ className: 'pulse-icon' });
        L.marker([car.current_lat, car.current_lng], { icon: pulseDiv }).addTo(map);

        marker.bindPopup(`<strong>${car.name}</strong><br>From: ${car.source}<br>To: ${car.destination}`);
        markers.push(marker);
    }

    // Show route and start moving car
    function showRoute(car) {
        if (currentRoute) {
            map.removeControl(currentRoute);
        }
        if (moveInterval) {
            clearInterval(moveInterval);
        }
        if (carMarker) {
            map.removeLayer(carMarker);
        }

        currentRoute = L.Routing.control({
            waypoints: [
                L.latLng(car.source_lat, car.source_lng),
                L.latLng(car.destination_lat, car.destination_lng)
            ],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            createMarker: function() { return null; }
        }).addTo(map);

        currentRoute.on('routesfound', function(e) {
            var coordinates = e.routes[0].coordinates;
            startMovingCar(coordinates, car);
        });
    }

    // Move the car along the route
    function startMovingCar(routeCoordinates, car) {
        var index = 0;
        carMarker = L.marker([routeCoordinates[0].lat, routeCoordinates[0].lng], { icon: carIcon }).addTo(map);

        moveInterval = setInterval(() => {
            if (index < routeCoordinates.length) {
                carMarker.setLatLng([routeCoordinates[index].lat, routeCoordinates[index].lng]);
                index++;
            } else {
                clearInterval(moveInterval); // stop moving at the end
            }
        }, 500); // move every 0.5 seconds
    }
</script>

</body>
</html>