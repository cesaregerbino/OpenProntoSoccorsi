<?php
    ini_set('display_errors', 1);

    //$url = 'http://www.galliera.it/118';
    //$url = 'http://listeps.cittadellasalute.to.it/?id=01090101';
    //$url = 'http://www.asl1.liguria.it/templateProntoSoccorso.asp';
    //$url = 'https://www.mauriziano.it/flex/cm/pages/ServeBLOB.php/L/IT/IDPagina/669';
    $url = 'https://servizionline.sanita.fvg.it/tempiAttesaService/tempiAttesaPs|N';


    //print "The url ... ".$url;
    //echo '<br>';
    //echo '<br>';

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


    //$curl = curl_init('http://www.livescore.com/soccer/england/');
    //curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
    //$data = curl_exec($curl);
    //curl_close($curl);
    //print_r(curl_getinfo($ch));
    //print_r(curl_error($ch));
    print_r($data);

    //print "Data ... ".$data;
    echo '<br>';
    echo '<br>';

    $dom = new DOMDocument();
    @$dom->loadHTML($data);

    $xpath = new DOMXPath($dom);

    // This is the xpath for a number under a bar ....
    // /html/body/div[2]/div[1]/div/div/ul/li[6]/span
    // How may I get it?

    //$greenWaitingNumber = $xpath->query('//li[@class="verde"]/span');
    //foreach( $greenWaitingNumber as $node )
    //{
    //  echo $node->nodeValue;
    //  echo '<br>';
    //  echo '<br>';
    //}

    //$greenWaitingNumber = $xpath->query('/html/body/div[2]/div[1]/div/div/ul/li[6]/span');
    //$Number = $xpath->query('/html/body/table/tbody/tr/td[2]/table[2]/tbody/tr/td[3]/table/tbody/tr[2]/td[1]/table/tbody/tr/td/div[1]/div[3]/div[1]/text()');
    //$Number = $xpath->query('(//div[@class="datiOspedaleCodici"]/div[2]/text())[2]');
    $Number = $xpath->query('$..aziende[?(@.descrizione==\"A.S.U.I. - Trieste\")]..prontoSoccorsi[?(@.descrizione==\"Pronto Soccorso e Terapia Urgenza Trieste\")]..dipartimenti[?(@.descrizione==\"Pronto Soccorso Maggiore\")]..codiciColore[?(@.descrizione==\"Bianco\")]..situazionePazienti..numeroPazientiInAttesa');
    print_r($Number);

    //Try like that: find('//div[@id='field1_name']/div[2]').text



    foreach( $Number as $n )
    {
      echo "Text: " .$n->nodeValue;
      echo '<br>';
      echo '<br>';
    }

     //echo "New: ".$greenWaitingNumber[0]->nodeValue;



    //$greenWaitingNumber = $xpath->query('/html/body/div[2]/div[1]/div/div/ul/li[6]/span');
    //$theText = (string).$greenWaitingNumber;

    //print "Data ... ".$theText;
    echo '<br>';
    echo '<br>';

    //http://usualcarrot.com/parsing-html-pages-using-xpath
    //http://stackoverflow.com/questions/40790317/parsing-html-to-find-certain-elements-in-php

?>
