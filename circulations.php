<?php
stream_context_set_default(array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true), 'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));

$urlApi = 'https://api-adresse.data.gouv.fr/search/?q=';
$address = "Mairie de Notre-Dame-des-Landes";
$urlApi = $urlApi . str_replace(' ', '+', $address);
$geocode = file_get_contents($urlApi);

if ($http_response_header[0] === 'HTTP/1.1 200 OK') {
    $output = json_decode($geocode);

    $lon = $output->features[0]->geometry->coordinates[0];
    $lat = $output->features[0]->geometry->coordinates[1];

    $urlApiCirculation = 'https://data.loire-atlantique.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&q=&lang=fr&rows=50';
    $dataCirculation = file_get_contents($urlApiCirculation);
    // $dataCirculation = json_decode($dataCirculation);

    $html = <<<HTML
            <h1 class="ms-2">Circulations</h1>
            <h2 class="ms-4">Carte des difficultés de circulation dans le département de la Loire Atlantique</h2>
            <div id="map" style="height: 70vh" class="ms-5 w-75">
            </div>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
            <link rel="stylesheet" href="./bootstrap.css" />
            <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
            <script>
                function initMap() {
                    myMap = L.map('map').setView([$lat, $lon], 10)
                    L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                        // Lien vers la source des données
                        attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                    }).addTo(myMap)

                    const deviationIcon = L.icon({
                        iconUrl: './assets/icons/map-signs-solid.svg',
                        iconSize: [24, 24],
                    });

                    const chantierIcon = L.icon({
                        iconUrl: './assets/icons/snowplow-solid.svg',
                        iconSize: [24, 24],
                    });

                    const otherDangerIcon = L.icon({
                        iconUrl: './assets/icons/exclamation-triangle-solid.svg',
                        iconSize: [24, 24],
                    });

                    const bateauIcon = L.icon({
                        iconUrl: './assets/icons/ship-solid.svg',
                        iconSize: [24, 24],
                    });
                    
                    let json = $dataCirculation
                    json = json.records
                    json.forEach((el)=>{
                        const iconToUse = el.fields.nature === 'Déviation' ? deviationIcon : el.fields.nature === 'Chantier' ? chantierIcon : el.fields.nature === "Bacs de Loire" ? bateauIcon : otherDangerIcon
                        L.marker([el.fields.latitude, el.fields.longitude], { icon: iconToUse }).addTo(myMap)
                        .bindPopup(
                              "<b>" + el.fields.nature + "</b><br>" + "Publié le : " + el.fields.datepublication + "<br/>" + el.fields.ligne1 + "<br>" + el.fields.ligne2 + "<br>" + el.fields.ligne3 + "<br>" + el.fields.ligne4
                        )
                    })

                    const townHallIcon = L.icon({
                        iconUrl: './assets/icons/landmark-solid.svg',
                        iconSize: [24, 24],
                    });
                    
                    L.marker([$lat, $lon], { icon: townHallIcon }).addTo(myMap).bindPopup("<b>Mairie de Notre-Dame-des-Landes</b>")

                    const address = "<?php echo $address; ?>"
                        document.body.innerHTML += `
                        <hr/>
                        <div class='ms-2'>
                            <h2>Appels API :</h2>
                            <ul>
                                <li>
                                    <p>https://api-adresse.data.gouv.fr/search/?q=${address}</p>
                                </li>
                                <li>
                                    <p>https://data.loire-atlantique.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&q=&lang=fr&rows=50</p>
                                </li>
                            </ul>
                        </div>`
                }
                window.onload = function () {
                    initMap()
                }
            </script>
        HTML;
    echo $html;
} else {
    echo "Erreur : " . $http_response_header[0];
}
