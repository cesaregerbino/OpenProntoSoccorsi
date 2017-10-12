<?php
    ini_set('display_errors', 'On');
    error_reporting(E_ALL);

    include "./tmp/vendor/autoload.php";

    $url = 'https://servizionline.sanita.fvg.it/tempiAttesaService/tempiAttesaPs';

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

    $jsonObject = new JsonPath\JsonObject($data);

    //$jsonPathExpr = "$..aziende[?(@.descrizione==\"A.S.U.I. - Trieste\")]..prontoSoccorsi[?(@.descrizione==\"Pronto Soccorso e Terapia Urgenza Trieste\")]..dipartimenti[?(@.descrizione==\"Pronto Soccorso Maggiore\")]..codiciColore[?(@.descrizione==\"Verde\")]..situazionePazienti..numeroPazientiInAttesa";
    $jsonPathExpr = '$..aziende[?(@.descrizione=="A.S.U.I. - Trieste")]..prontoSoccorsi[?(@.descrizione=="Pronto Soccorso e Terapia Urgenza Trieste")]..dipartimenti[?(@.descrizione=="Pronto Soccorso Maggiore")]..codiciColore[?(@.descrizione=="Bianco")]..situazionePazienti..numeroPazientiInAttesa';

    $r = $jsonObject->get($jsonPathExpr);

    //print json_encode($r);

    print json_encode($r[0]);
?>
