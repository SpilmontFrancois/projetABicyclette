<?php
stream_context_set_default(array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true), 'ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));

$urlIpAPI = "http://ip-api.com/xml/?lang=fr";
$ip = simplexml_load_string(file_get_contents($urlIpAPI))->query;

$urlCodePostal = "http://ip-api.com/json/" . $ip . "?fields=66842623";
$codePostal = json_decode(file_get_contents($urlCodePostal))->zip;

function convertCSVtoJSON($file, $delimiter, $codePostal)
{
    $data = file($file);
    $json = array();

    foreach ($data as $row) {
        if ($row[0] . $row[1] === $codePostal[0] . $codePostal[1]) {
            $json[] = explode($delimiter, $row);
        }
    }

    return json_encode($json);
}

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
    if ($http_response_header[0] === 'HTTP/1.1 200 OK') {
        $urlApiCovid = 'https://www.data.gouv.fr/fr/datasets/r/5c4e1452-3850-4b59-b11c-3dd51d7fb8b5';
        $dataCovid = file($urlApiCovid);

        if ($http_response_header[0] === 'HTTP/1.1 302 FOUND') {
            $jsonConverted = convertCSVtoJSON($urlApiCovid, ",", $codePostal);
        } else {
            echo "Erreur lors de l'accès aux données COVID";
        }

        $html = <<<HTML
            <h1 class="ms-2">Circulations</h1>
            <h2 class="ms-4">Carte des difficultés de circulation dans le département de la Loire Atlantique</h2>
            <div id="map" style="height: 70vh" class="ms-5 w-75">
            </div>
            <div id="covidData"></div>
            <div id="apiCalls"></div>
            <link rel="stylesheet" href="./bootstrap.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
            <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
            <script>
                function initMap() {
                    const myMap = L.map('map', { tap : false }).setView([$lat, $lon], 10)
                    L.tileLayer('https://{s}.tile.osm.org/{z}/{x}/{y}.png', {
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
                }

                function initCourbeCovid(){
                    document.getElementById('covidData').innerHTML = `
                        <hr/>
                        <div class='ms-4'>
                            <h1>Données COVID</h1>
                            <h3>Courbe du taux d'incidence</h3>
                            <canvas id="chartIncidence" class="w-50 h-auto mb-2"></canvas>
                            <hr/>
                            <h3>Courbe des hospitalisations</h3>
                            <canvas id="chartHospitalisations" class="w-50 h-auto mb-2"></canvas>
                            <hr/>
                            <h3>Courbe des décès</h3>
                            <canvas id="chartDeads" class="w-50 h-auto"></canvas>
                        </div>
                    `

                    let json = $jsonConverted
                    let data = [[], [], []]
                    let labels = []
                    json.forEach((elem) => {
                        data[0].push(elem[6])
                        data[1].push(elem[9])
                        data[2].push(elem[12])
                        labels.push(elem[1])
                    })
                    Chart.defaults.font.size = 30;

                    const chart1 = document.getElementById('chartIncidence').getContext('2d');
                    let myChart = new Chart(chart1, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                              label: 'Taux d\'incidence',
                              data: data[0],
                              fill: true,
                              backgroundColor: 'rgba(0, 0, 255, 0.5)',
                              borderColor: 'rgb(0, 0, 255)',
                            }]
                        },
                        options: {
                            responsive: true,
                        },
                    });

                    const chart2 = document.getElementById('chartHospitalisations').getContext('2d');
                    myChart = new Chart(chart2, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                              label: 'Hospitalisations',
                              data: data[1],
                              fill: true,
                              backgroundColor: 'rgba(0, 160, 0, 0.5)',
                              borderColor: 'rgb(0, 160, 0)',
                            }]
                        },
                        options: {
                            responsive: true,
                        },
                    });

                    const chart3 = document.getElementById('chartDeads').getContext('2d');
                    myChart = new Chart(chart3, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                              label: 'Décès',
                              data: data[2],
                              fill: true,
                              backgroundColor: 'rgba(255, 0, 0, 0.5)',
                              borderColor: 'rgb(255, 0, 0)',
                            }]
                        },
                        options: {
                            responsive: true,
                        },
                    });
                }

                function initSources(){
                    const address = "<?php echo $address; ?>"
                        document.getElementById('apiCalls').innerHTML += `
                        <hr/>
                        <div class='ms-2'>
                            <h2>Appels API :</h2>
                            <ul>
                                <li>
                                    <p>Adresse de la Mairie : <a href="https://api-adresse.data.gouv.fr/search/?q=${address}">https://api-adresse.data.gouv.fr/search/?q=${address}</a></p>
                                </li>
                                <li>
                                    <p>Infos routière de la Loire Atlantique : <a href="https://data.loire-atlantique.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&q=&lang=fr&rows=50">https://data.loire-atlantique.fr/api/records/1.0/search/?dataset=224400028_info-route-departementale&q=&lang=fr&rows=50</a></p>
                                </li>
                                <li>
                                    <p><a href="">Données COVID</a></p>
                                </li>
                            </ul>
                        </div>`
                }

                window.onload = function () {
                    initMap()
                    initCourbeCovid()
                    initSources()
                }
            </script>
        HTML;
        echo $html;
    } else {
        echo "Erreur : " . $http_response_header[0];
    }
} else {
    echo "Erreur : " . $http_response_header[0];
}
