<?php

$geoJson = <<<EOF
{
	"Comune": "Genova",
	"prontoSoccorsi": [{
		"osm_id": "4782964157",
		"x": "495265.25515142",
		"y": "4916447.1386341",
		"ps_name": "Pronto Soccorso Ente Ospedaliero Ospedali Galliera",
		"city": "Genova",
		"address": "Via Mura del Prato 6",
		"tel": "N.D",
		"email": "N.D",
		"url_website": "https://www.galliera.it/118",
		"numeri_bianco_attesa": "1",
		"tempi_bianco_attesa": "",
		"numeri_bianco_in_visita": "0",
		"tempi_bianco_in_visita": "",
		"numeri_verde_attesa": "13",
		"tempi_verde_attesa": "",
		"numeri_verde_in_visita": "1",
		"tempi_verde_in_visita": "",
		"numeri_giallo_attesa": "1",
		"tempi_giallo_attesa": "",
		"numeri_giallo_in_visita": "9",
		"tempi_giallo_in_visita": "",
		"numeri_rosso_attesa": "0",
		"tempi_rosso_attesa": "",
		"numeri_rosso_in_visita": "0",
		"tempi_rosso_in_visita": ""
	}, {
		"osm_id": "4788900397",
		"x": "498038.65415965",
		"y": "4917266.9196532",
		"ps_name": "Pronto Soccorso Ospedale San Martino",
		"city": "Genova",
		"address": "Via Francesco Saverio Mosso",
		"tel": "N.D",
		"email": "N.D",
		"url_website": "https://www.galliera.it/118",
		"numeri_bianco_attesa": "0",
		"tempi_bianco_attesa": "",
		"numeri_bianco_in_visita": "1",
		"tempi_bianco_in_visita": "",
		"numeri_verde_attesa": "7",
		"tempi_verde_attesa": "",
		"numeri_verde_in_visita": "8",
		"tempi_verde_in_visita": "",
		"numeri_giallo_attesa": "6",
		"tempi_giallo_attesa": "",
		"numeri_giallo_in_visita": "18",
		"tempi_giallo_in_visita": "",
		"numeri_rosso_attesa": "0",
		"tempi_rosso_attesa": "",
		"numeri_rosso_in_visita": "1",
		"tempi_rosso_in_visita": ""
	}]
}
EOF;

$geoArray = json_decode($geoJson, true);

foreach ($geoArray['prontoSoccorsi'] as $geoFeature) {
        echo $geoFeature['ps_name'];
        echo "\n";
        echo "\n";
}

/*
$key = 'D6B1-IcUpWc6rEq2v-a4AQ';

function findKey($geoJson, $key) {
    $geoArray = json_decode($geoJson, true);
    foreach ($geoArray['features'] as $geoFeature) {
        if (in_array($key, $geoFeature['properties']['keys'])) {
            return $geoFeature['properties']['key'];
            }
    }
}

var_dump(findKey($geoJson,$key));
*/
