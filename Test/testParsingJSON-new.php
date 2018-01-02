<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    include "./tmp/vendor/autoload.php";
    //include "../Utility/SkyScannerJsonPath/vendor/autoload.php";

    $url = 'http://dati.lazio.it/catalog/api/action/datastore_search?resource_id=4a5a084c-b63c-4f4d-b3f0-b1163c0ad077';

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

    //print json_encode($data);

    $jsonObject = new JsonPath\JsonObject($data);

    //print json_encode($jsonObject);

    $jsonPathExpr = '$..result..records[?(@.ISTITUTO=="Santa Scolastica")]..BIANCHI_ATT';

    $r = $jsonObject->get($jsonPathExpr);

    //print json_encode($r);

    print json_encode($r[0]);
?>
