<?php
stream_context_set_default(array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true)));

$urlApi = 'https://api-adresse.data.gouv.fr/search/?q=';
$address = "Mairie de Notre-Dame-des-Landes";
$urlApi = $urlApi . str_replace(' ', '+', $address);
$geocode = file_get_contents($urlApi) or die("Impossible d'acceder aux services de géolocalisation");

if ($http_response_header[0] === 'HTTP/1.1 200 OK') {
    $output = json_decode($geocode);

    $lon = $output->features[0]->geometry->coordinates[0];
    $lat = $output->features[0]->geometry->coordinates[1];

    $html = <<<HTML
            <div id="map" style="height: 70vh">
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
            <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
            <script>
                function initMap() {
                    myMap = L.map('map').setView([$lat, $lon], 15)
                    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                        // Lien vers la source des données
                        attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                    }).addTo(myMap)
                    
                    const townHallIcon = L.icon({
                        iconUrl: './assets/icons/landmark-solid.svg',
                        iconSize: [24, 24],
                    });
                    
                    L.marker([$lat, $lon], { icon: townHallIcon }).addTo(myMap)
                }
                window.onload = function () {
                    initMap()
                }
            </script>
        HTML;
    echo $html;
}
