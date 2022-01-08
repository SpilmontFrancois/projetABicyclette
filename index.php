<?php 
    $lienMeteoAPI = "https://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2";
    $data = file_get_contents($lienMeteoAPI);

    $myfile = fopen("data.xml", "w");
    fwrite($myfile, $data);
    fclose($myfile);

    $xml = new DOMDocument;
    $xml->load('data.xml');

    $xsl = new DOMDocument;
    $xsl->load('meteo.xsl');

    // Configuration du transformateur
    $proc = new XSLTProcessor;
    $proc->importStyleSheet($xsl); // attachement des règles xsl

    echo $proc->transformToXML($xml);
?>