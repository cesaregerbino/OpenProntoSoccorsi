<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    $url = 'https://servizi.apss.tn.it/statops/xml/010-PS-PS';

    $xpath_for_parsing = '/risposta/pronto_soccorso/reparto/ambulatorio/verde/text()';

    //#Set CURL parameters: pay attention to the PROXY config !!!!
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_PROXY, '');

    $data = curl_exec($ch);
    curl_close($ch);

    //print $data;

    $xml = new SimpleXMLElement($data);

    $result = $xml->xpath($xpath_for_parsing);

    $theValue =  'N.D.';
    while(list( , $node) = each($result)) {
        $theValue = $node;
    }

    print $theValue;
?>
