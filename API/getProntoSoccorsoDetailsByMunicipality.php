<?php

   # Get the Municipality name ...
   $municipality = $_GET['municipality'];

   $db = new SQLite3('../Data/OpenProntoSoccorso.sqlite');

   $q="SELECT * FROM dist_com_ps_2 WHERE pg_COMUNE = '".$municipality."'";

   try {
        $stmt = $db->prepare($q);
        $results = $stmt->execute();

        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
          //echo "Nome Pronto Soccorso = ".$row['pt_NAME']." - ".$row['pt_osm_id'];
          //echo "<br>";
          //echo "<br>";

          $pt_osm_id = $row['pt_osm_id'];

          $q1="SELECT * FROM ps_details WHERE osm_id = '".$pt_osm_id."'";
          try {
               $stmt1 = $db->prepare($q1);
               $results1 = $stmt1->execute();

               while ($row = $results1->fetchArray(SQLITE3_ASSOC)) {
                 $num_white_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_bianco_attesa']);
                 echo "Numero pazienti in codice bianco in attesa = ".$num_white_waiting;
                 echo "<br>";
                 echo "<br>";
                 $num_white_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_bianco_visita']);
                 echo "Numero pazienti in codice bianco in visita = ".$num_white_in_visita;
                 echo "<br>";
                 echo "<br>";


                 $num_green_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_verde_attesa']);
                 echo "Numero pazienti in codice verde in attesa = ".$num_green_waiting;
                 echo "<br>";
                 echo "<br>";
                 $num_green_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_verde_visita']);
                 echo "Numero pazienti in codice verde in visita = ".$num_green_in_visita;
                 echo "<br>";
                 echo "<br>";

                 $num_giallo_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_giallo_attesa']);
                 echo "Numero pazienti in codice giallo in attesa = ".$num_giallo_waiting;
                 echo "<br>";
                 echo "<br>";
                 $num_giallo_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_giallo_visita']);
                 echo "Numero pazienti in codice giallo in visita = ".$num_giallo_in_visita;
                 echo "<br>";
                 echo "<br>";

                 $num_rosso_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_rosso_attesa']);
                 echo "Numero pazienti in codice rosso in attesa = ".$num_rosso_waiting;
                 echo "<br>";
                 echo "<br>";
                 $num_rosso_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_rosso_visita']);
                 echo "Numero pazienti in codice rosso in visita = ".$num_rosso_in_visita;
                 echo "<br>";
                 echo "<br>";

               }
          }
          catch(PDOException $e) {
                  print "Something went wrong or Connection to database failed! ".$e->getMessage();
          }
        }
   }
   catch(PDOException $e) {
           print "Something went wrong or Connection to database failed! ".$e->getMessage();
   }
   $db = null;

   function getDetailsWaiting($url, $xpath_for_parsing) {
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

     //echo "Fatta la chiamata ... ".$url;

     $dom = new DOMDocument();
     @$dom->loadHTML($data);

     $xpath = new DOMXPath($dom);

     $colorWaitingNumber = $xpath->query($xpath_for_parsing);

     $theValue =  '';
     foreach( $colorWaitingNumber as $node )
     {
       //echo "Valore = ".$node->nodeValue;
       $theValue = $node->nodeValue;
     }

     return  $theValue;
   }
?>
