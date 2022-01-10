<?php
stream_context_set_default(array('http' => array('proxy' => 'tcp://www-cache:3128', 'request_fulluri' => true)));

// Récupération des données de géolocalisation
$ipURI = "http://ip-api.com/xml/?lang=fr";
$geolocData = simplexml_load_string(file_get_contents($ipURI));
$coo = $geolocData->lat . "," . $geolocData->lon;

if ($http_response_header[0] === 'HTTP/1.1 200 OK') {
    // Récupération des données météo
    $lienMeteoAPI = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=" . $coo . "&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
    $data = simplexml_load_string(file_get_contents($lienMeteoAPI));
    if ($http_response_header[0] === 'HTTP/1.1 200 OK') {
        // Chargement du fichier XSL
        $xsl = new DOMDocument;
        $xsl->load('meteo.xsl');

        // Configuration du transformateur
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl); // attachement des règles xsl

        echo $proc->transformToXML($data);
    } else {
        echo "Erreur lors de la récupération des données météo";
    }
} else {
    echo "Erreur de connexion au serveur d'IP-API.com";
}
