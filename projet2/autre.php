<?php
    $link ="https://www.data.gouv.fr/fr/datasets/r/5c4e1452-3850-4b59-b11c-3dd51d7fb8b5";
    $data=file_get_contents($link) or die("Impossible d'acceder aux service fournissant les données");

    echo json_encode(file($link));
    //echo json_encode(file_get_contents($link));

?>