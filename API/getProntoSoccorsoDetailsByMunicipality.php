<?php
   # Get the Municipality name ...
   $municipality = $_GET['municipality'];

   /*
   echo "Municipality = ".$municipality;
   echo "\n";
   echo "\n";
   */

   # Set access to data base...
   $db = new SQLite3('../Data/OpenProntoSoccorso.sqlite');

   # Set the query for the current Municipality ...
   $q="SELECT * FROM dist_com_ps_2 WHERE pg_COMUNE = '".$municipality."'";
   try {
        # Initialize the Json ...
        $jsonResult = "{\"Comune\": \"".$municipality."\", \"prontoSoccorsi\": [";

        # Prepare for the query ...
        $stmt = $db->prepare($q);

        # Execute the query ...
        $results = $stmt->execute();

        $firstIteration = TRUE;

        # Iterate on the Pronto Soccoro istances ...
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {

          # Set the query for the current osm_id ...
          $q1="SELECT * FROM ps_details WHERE osm_id = '".$row['pt_osm_id']."'";

          # Get the coordinates ...
          $pt_X = $row['pt_X'];
          $pt_Y = $row['pt_Y'];
          $pt_LON = $row['pt_LON'];
          $pt_LAT = $row['pt_LAT'];
          try {

               # Prepare for the query ...
               $stmt1 = $db->prepare($q1);

               # Execute the query ...
               $results1 = $stmt1->execute();

               # Get the Pronto Soccorso details ...
               while ($row = $results1->fetchArray(SQLITE3_ASSOC)) {
                 if ($firstIteration == FALSE) {
                   $jsonResult .= ",";
                 }
                 $firstIteration = FALSE;

                 # Get the generic details ...
                 $jsonResult .= "{";
                 $jsonResult .= "\"osm_id\": \"".$row['osm_id']."\",";
                 $jsonResult .= "\"x\": \"".$pt_X."\",";
                 $jsonResult .= "\"y\": \"".$pt_Y."\",";
                 $jsonResult .= "\"Lon\": \"".$pt_LON."\",";
                 $jsonResult .= "\"Lat\": \"".$pt_LAT."\",";
                 $jsonResult .= "\"ps_name\": \"".$row['ps_name']."\",";
                 $jsonResult .= "\"city\": \"".$row['city']."\",";
                 $jsonResult .= "\"address\": \"".$row['address']."\",";
                 $jsonResult .= "\"tel\": \"".$row['tel']."\",";
                 $jsonResult .= "\"email\": \"".$row['email']."\",";
                 $jsonResult .= "\"url_website\": \"".$row['url_website']."\",";

                 # The white code details ...
                 $num_white_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_bianco_attesa']);
                 $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                 $time_white_waiting = getDetailsWaiting($row['url_data'], $row['xpath_tempi_bianco_attesa']);
                 $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                 $num_white_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_bianco_visita']);
                 $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                 $time_white_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_tempi_bianco_visita']);
                 $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";

                 # The green code details ...
                 $num_green_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_verde_attesa']);
                 $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                 $time_green_waiting = getDetailsWaiting($row['url_data'], $row['xpath_tempi_verde_attesa']);
                 $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                 $num_green_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_verde_visita']);
                 $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                 $time_green_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_tempi_verde_visita']);
                 $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";

                 # The yellow details ...
                 $num_yellow_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_giallo_attesa']);
                 $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                 $time_yellow_waiting = getDetailsWaiting($row['url_data'], $row['xpath_tempi_giallo_attesa']);
                 $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                 $num_yellow_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_giallo_visita']);
                 $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                 $time_yellow_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_tempi_giallo_visita']);
                 $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";

                 # The red details ...
                 $num_red_waiting = getDetailsWaiting($row['url_data'], $row['xpath_numeri_rosso_attesa']);
                 $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                 $time_red_waiting = getDetailsWaiting($row['url_data'], $row['xpath_tempi_rosso_attesa']);
                 $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                 $num_red_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_numeri_rosso_visita']);
                 $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                 $time_red_in_visita = getDetailsWaiting($row['url_data'], $row['xpath_tempi_rosso_visita']);
                 $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                 $jsonResult .= "}";
               }
          }
          catch(PDOException $e) {
                  print "Something went wrong or Connection to database failed! ".$e->getMessage();
          }
        }
        $jsonResult .= "]";
        $jsonResult .= "}";
        echo $jsonResult;
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

     $dom = new DOMDocument();
     @$dom->loadHTML($data);
     $xpath = new DOMXPath($dom);
     $colorWaitingNumber = $xpath->query($xpath_for_parsing);
     $theValue =  '';
     foreach( $colorWaitingNumber as $node )
     {
       $theValue = $node->nodeValue;
     }
     return  $theValue;
   }
?>
