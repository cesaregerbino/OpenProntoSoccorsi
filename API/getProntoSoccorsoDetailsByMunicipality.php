<?php
   //include "/var/www/html/OpenProntoSoccorso/API/SkyScannerJsonPath/vendor/autoload.php";
   include "../Utility/SkyScannerJsonPath/vendor/autoload.php";

   date_default_timezone_set('Europe/Rome');

   # To manage special cases ...
   $special_case = '';

   # Get the Municipality name ...
   $municipality = $_GET['municipality'];

   //$municipality = "Bologna";

   //echo "Municipality = ".$_GET['municipality'];
   //echo "\n";
   //echo "\n";

   # Set access to data base...
   $db = new SQLite3('../Data/OpenProntoSoccorso.sqlite');

   //echo ".... DB connection OK ...";

   # Set the query for the current Municipality ...
   $q="SELECT * FROM dist_com_ps_2 WHERE pg_COMUNE = '".$municipality."'";

   //echo "Query = ".$q;
   //echo "\n";
   //echo "\n";

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

          //echo "..... Query = ".$q1;
          //echo "\n";
          //echo "\n";

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

               //echo "..... query executed !";
               //echo "\n";
               //echo "\n";

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

                 //echo "..... JsonResult = ".$jsonResult;
                 //echo "\n";
                 //echo "\n";
                 //echo "..... url_data = ".$row['url_data'];
                 //echo "\n";
                 //echo "\n";


                 if ($row['data_type'] == "POST") {
                   switch ($row['specific_function']) {
                        # The Sardinia hospitals case ...
                        case "OspedaliSardegna":
                          # Get the data via POST request for Sardinia hospital ...
                          $data = getDataViaPostOspedaliSardegna($row);

                          $dom = new DOMDocument();
                          @$dom->loadHTML($data);
                          # The white code details ...
                          $num_white_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_bianco_attesa'], $special_case, "num_white_waiting");
                          $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                          $time_white_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_bianco_attesa'], $special_case, "time_white_waiting");
                          $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                          $num_white_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_bianco_visita'], $special_case, "num_white_in_visita");
                          $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                          $time_white_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_bianco_visita'], $special_case, "time_white_in_visita");
                          $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";
                          # The green code details ...
                          $num_green_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_verde_attesa'], $special_case, "num_green_waiting");
                          $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                          $time_green_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_verde_attesa'], $special_case, "time_green_waiting");
                          $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                          $num_green_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_verde_visita'], $special_case, "num_green_in_visita");
                          $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                          $time_green_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_verde_visita'], $special_case, "time_green_in_visita");
                          $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";
                          # The yellow details ...
                          $num_yellow_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_giallo_attesa'], $special_case, "num_yellow_waiting");
                          $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                          $time_yellow_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_giallo_attesa'], $special_case, "time_yellow_waiting");
                          $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                          $num_yellow_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_giallo_visita'], $special_case, "num_yellow_in_visita");
                          $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                          $time_yellow_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_giallo_visita'], $special_case, "time_yellow_in_visita");
                          $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";
                          # The red details ...
                          $num_red_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_rosso_attesa'], $special_case, "num_red_waiting");
                          $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                          $time_red_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_rosso_attesa'], $special_case, "time_red_waiting");
                          $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                          $num_red_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_rosso_visita'], $special_case, "num_red_in_visita");
                          $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                          $time_red_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_rosso_visita'], $special_case, "time_red_in_visita");
                          $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                          $jsonResult .= "}";

                          break;
                   }
                 } else {
                   # Set CURL parameters: pay attention to the PROXY config !!!!
                   $ch = curl_init();
                   curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
                   curl_setopt($ch, CURLOPT_HEADER, 0);
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                   curl_setopt($ch, CURLOPT_URL, $row['url_data']);
                   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                   curl_setopt($ch, CURLOPT_PROXY, '');
                   $data = curl_exec($ch);
                   curl_close($ch);

                   # Manage the special cases ...
                   switch ($row['specific_function']) {
                        case "Molinette":
                          $data = parsingMolinetteJSON($data); # Transform the data ...
                          break;
                        case "InfantileReginaMargherita":
                          $data = parsingMolinetteJSON($data); # Transform the data ...
                          break;
                        case "OspedaleImperia": # Need to postprocess the json output ...
                          $special_case = "OspedaleImperia";
                          break;
                        case "OspedaleMantova": # Need to postprocess the json output ...
                          $special_case = "OspedaleMantova";
                          break;
                        case "OspedalePieveCoriano": # Need to postprocess the json output ...
                          $special_case = "OspedalePieveCoriano";
                          break;
                        case "OspedaliBologna": # Need to postprocess the json output ...
                          $special_case = "OspedaliBologna";
                          break;
                        case "OspedalePrato": # Need to postprocess the json output ...
                          $data = getDataOspedalePrato($row['url_data']);
                          break;
                        case "OspedaleCaserta": # Need to manage the custuom response format ...
                          $special_case = "OspedaleCaserta";
                          break;
                   }

                   switch ($row['data_type']) {
                     # Manage the JSON data case ...
                     case "JSON":
                       //$parser = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                       //$o = $parser->decode($data);
                       $jsonObject = new JsonPath\JsonObject($data);
                       //print_r ($o);

                       # The white code details ...
                       $num_white_waiting = getDetailsWaitingJSON($row['xpath_numeri_bianco_attesa']);
                       $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                       $time_white_waiting = getDetailsWaitingJSON($row['xpath_tempi_bianco_attesa']);
                       $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                       $num_white_in_visita = getDetailsWaitingJSON($row['xpath_numeri_bianco_visita']);
                       $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                       $time_white_in_visita = getDetailsWaitingJSON($row['xpath_tempi_bianco_visita']);
                       $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";
                       # The yellow code details ...
                       $num_yellow_waiting = getDetailsWaitingJSON($row['xpath_numeri_giallo_attesa']);
                       $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                       $time_yellow_waiting = getDetailsWaitingJSON($row['xpath_tempi_giallo_attesa']);
                       $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                       $num_yellow_in_visita = getDetailsWaitingJSON($row['xpath_numeri_giallo_visita']);
                       $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                       $time_yellow_in_visita = getDetailsWaitingJSON($row['xpath_tempi_giallo_visita']);
                       $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";
                       # The green code details ...
                       $num_green_waiting = getDetailsWaitingJSON($row['xpath_numeri_verde_attesa']);
                       $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                       $time_green_waiting = getDetailsWaitingJSON($row['xpath_tempi_verde_attesa']);
                       $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                       $num_green_in_visita = getDetailsWaitingJSON($row['xpath_numeri_verde_visita']);
                       $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                       $time_green_in_visita = getDetailsWaitingJSON($row['xpath_tempi_verde_visita']);
                       $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";
                       # The red code details ...
                       $num_red_waiting = getDetailsWaitingJSON($row['xpath_numeri_rosso_attesa']);
                       $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                       $time_red_waiting = getDetailsWaitingJSON($row['xpath_tempi_rosso_attesa']);
                       $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                       $num_red_in_visita = getDetailsWaitingJSON($row['xpath_numeri_rosso_visita']);
                       $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                       $time_red_in_visita = getDetailsWaitingJSON($row['xpath_tempi_rosso_visita']);
                       $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                       $jsonResult .= "}";

                       break;
                     # Manage the XPATH data case ...
                     case "XPATH":
                       $dom = new DOMDocument();
                       @$dom->loadHTML($data);
                       # The white code details ...
                       $num_white_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_bianco_attesa'], $special_case, "num_white_waiting");
                       $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                       $time_white_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_bianco_attesa'], $special_case, "time_white_waiting");
                       $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                       $num_white_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_bianco_visita'], $special_case, "num_white_in_visita");
                       $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                       $time_white_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_bianco_visita'], $special_case, "time_white_in_visita");
                       $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";
                       # The green code details ...
                       $num_green_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_verde_attesa'], $special_case, "num_green_waiting");
                       $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                       $time_green_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_verde_attesa'], $special_case, "time_green_waiting");
                       $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                       $num_green_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_verde_visita'], $special_case, "num_green_in_visita");
                       $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                       $time_green_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_verde_visita'], $special_case, "time_green_in_visita");
                       $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";
                       # The yellow details ...
                       $num_yellow_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_giallo_attesa'], $special_case, "num_yellow_waiting");
                       $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                       $time_yellow_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_giallo_attesa'], $special_case, "time_yellow_waiting");
                       $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                       $num_yellow_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_giallo_visita'], $special_case, "num_yellow_in_visita");
                       $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                       $time_yellow_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_giallo_visita'], $special_case, "time_yellow_in_visita");
                       $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";
                       # The red details ...
                       $num_red_waiting = getDetailsWaitingXPATH($dom, $row['xpath_numeri_rosso_attesa'], $special_case, "num_red_waiting");
                       $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                       $time_red_waiting = getDetailsWaitingXPATH($dom, $row['xpath_tempi_rosso_attesa'], $special_case, "time_red_waiting");
                       $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                       $num_red_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_numeri_rosso_visita'], $special_case, "num_red_in_visita");
                       $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                       $time_red_in_visita = getDetailsWaitingXPATH($dom, $row['xpath_tempi_rosso_visita'], $special_case, "time_red_in_visita");
                       $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                       $jsonResult .= "}";

                       break;
                     # Manage the XML data case ...
                     case "XML":
                       # The white code details ...
                       $num_white_waiting = getDetailsWaitingXML($data, $row['xpath_numeri_bianco_attesa']);
                       $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                       $time_white_waiting = getDetailsWaitingXML($data, $row['xpath_tempi_bianco_attesa']);
                       $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                       $num_white_in_visita = getDetailsWaitingXML($data, $row['xpath_numeri_bianco_visita']);
                       $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                       $time_white_in_visita = getDetailsWaitingXML($data, $row['xpath_tempi_bianco_visita']);
                       $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";
                       # The green code details ...
                       $num_green_waiting = getDetailsWaitingXML($data, $row['xpath_numeri_verde_attesa']);
                       $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                       $time_green_waiting = getDetailsWaitingXML($data, $row['xpath_tempi_verde_attesa']);
                       $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                       $num_green_in_visita = getDetailsWaitingXML($data, $row['xpath_numeri_verde_visita']);
                       $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                       $time_green_in_visita = getDetailsWaitingXML($data, $row['xpath_tempi_verde_visita']);
                       $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";
                       # The yellow details ...
                       $num_yellow_waiting = getDetailsWaitingXML($data, $row['xpath_numeri_giallo_attesa']);
                       $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                       $time_yellow_waiting = getDetailsWaitingXML($data, $row['xpath_tempi_giallo_attesa']);
                       $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                       $num_yellow_in_visita = getDetailsWaitingXML($data, $row['xpath_numeri_giallo_visita']);
                       $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                       $time_yellow_in_visita = getDetailsWaitingXML($data, $row['xpath_tempi_giallo_visita']);
                       $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";
                       # The red details ...
                       $num_red_waiting = getDetailsWaitingXML($data, $row['xpath_numeri_rosso_attesa']);
                       $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                       $time_red_waiting = getDetailsWaitingXML($data, $row['xpath_tempi_rosso_attesa']);
                       $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                       $num_red_in_visita = getDetailsWaitingXML($data, $row['xpath_numeri_rosso_visita']);
                       $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                       $time_red_in_visita = getDetailsWaitingXML($data, $row['xpath_tempi_rosso_visita']);
                       $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                       $jsonResult .= "}";

                       break;

                     case "CUSTOM":
                         switch ($special_case) {
                           case "OspedaleCaserta":
                             $pieces = explode (",", $data);
                             # The white code details ...
                             $num_white_waiting = $pieces[4];
                             $jsonResult .= "\"numeri_bianco_attesa\": \"".$num_white_waiting."\"";
                             $time_white_waiting = "N.D.";
                             $jsonResult .= ",\"tempi_bianco_attesa\": \"".$time_white_waiting."\"";
                             $num_white_in_visita = "N.D.";
                             $jsonResult .= ",\"numeri_bianco_in_visita\": \"".$num_white_in_visita."\"";
                             $time_white_in_visita = "N.D.";
                             $jsonResult .= ",\"tempi_bianco_in_visita\": \"".$time_white_in_visita."\"";
                             # The green code details ...
                             $num_green_waiting = $pieces[3];
                             $jsonResult .= ",\"numeri_verde_attesa\": \"".$num_green_waiting."\"";
                             $time_green_waiting = "N.D.";
                             $jsonResult .= ",\"tempi_verde_attesa\": \"".$time_green_waiting."\"";
                             $num_green_in_visita = "N.D.";
                             $jsonResult .= ",\"numeri_verde_in_visita\": \"".$num_green_in_visita."\"";
                             $time_green_in_visita = "N.D.";
                             $jsonResult .= ",\"tempi_verde_in_visita\": \"".$time_green_in_visita."\"";
                             # The yellow details ...
                             $num_yellow_waiting = $pieces[2];
                             $jsonResult .= ",\"numeri_giallo_attesa\": \"".$num_yellow_waiting."\"";
                             $time_yellow_waiting = "N.D.";
                             $jsonResult .= ",\"tempi_giallo_attesa\": \"".$time_yellow_waiting."\"";
                             $num_yellow_in_visita = "N.D.";
                             $jsonResult .= ",\"numeri_giallo_in_visita\": \"".$num_yellow_in_visita."\"";
                             $time_yellow_in_visita = "N.D.";
                             $jsonResult .= ",\"tempi_giallo_in_visita\": \"".$time_yellow_in_visita."\"";
                             # The red details ...
                             $num_red_waiting = $pieces[1];
                             $jsonResult .= ",\"numeri_rosso_attesa\": \"".$num_red_waiting."\"";
                             $time_red_waiting = "N.D.";
                             $jsonResult .= ",\"tempi_rosso_attesa\": \"".$time_red_waiting."\"";
                             $num_red_in_visita = "N.D.";
                             $jsonResult .= ",\"numeri_rosso_in_visita\": \"".$num_red_in_visita."\"";
                             $time_red_in_visita = "N.D.";
                             $jsonResult .= ",\"tempi_rosso_in_visita\": \"".$time_red_in_visita."\"";
                             $jsonResult .= "}";

                             break;
                            }
                         break;
                   }
                 }
              }
          }
          catch(PDOException $e) {
                  print "Something went wrong or Connection to database failed! ".$e->getMessage();
          }
        }
        $jsonResult .= "]";
        $jsonResult .= "}";

        switch ($special_case) {
             case "OspedaleImperia": # Postprocess the jsonResult to eliminate strings ...
               $jsonResult = str_replace('Codice bianco:', '', $jsonResult);
               $jsonResult = str_replace('Codice verde:', '', $jsonResult);
               $jsonResult = str_replace('Codice giallo:', '', $jsonResult);
               $jsonResult = str_replace('Codice rosso:', '', $jsonResult);
               break;
            case "OspedaleMantova": # Postprocess the jsonResult to eliminate strings ...
               $jsonResult = str_replace('attesa": "[', 'attesa": "', $jsonResult);
               $jsonResult = str_replace('visita": "[', 'visita": "', $jsonResult);
               $jsonResult = str_replace(']",', '",', $jsonResult);
               break;
            case "OspedalePieveCoriano": # Postprocess the jsonResult to eliminate strings ...
               $jsonResult = str_replace('attesa": "[', 'attesa": "', $jsonResult);
               $jsonResult = str_replace('visita": "[', 'visita": "', $jsonResult);
               $jsonResult = str_replace(']",', '",', $jsonResult);
               break;
        }

        echo $jsonResult;
   }
   catch(PDOException $e) {
           print "Something went wrong or Connection to database failed! ".$e->getMessage();
   }
   $db = null;

   # Get the details in the case o  XPATH (data inside HTML pages) ..
   function getDetailsWaitingXPATH($dom, $xpath_for_parsing, $special_case, $type) {
     $xpath = new DOMXPath($dom);
     $colorWaitingNumber = $xpath->query($xpath_for_parsing);
     $theValue =  'N.D.';
     foreach( $colorWaitingNumber as $node )
     {
       $theValue = $node->nodeValue;
     }

     # Post elaboration for the special cases ...
     switch ($special_case) {
         case "OspedaliBologna": # Postprocess the jsonResult to eliminate strings ...
            if (($type == "num_white_waiting") or ($type == "num_green_waiting") or ($type == "num_yellow_waiting") or ($type == "num_red_waiting")) {
              $theValue = substr($theValue, strpos($theValue, 'Utenti in coda: ') + 16);
            }
            if (($type == "time_white_waiting") or ($type == "time_green_waiting") or ($type == "time_yellow_waiting") or ($type == "time_red_waiting")) {
              $theValue = strstr($theValue, ' - Utenti in coda', true);
            }
            break;
     }

     return  $theValue;
   }

   # Get the details in the case of JSON (data form open services) ...
   function getDetailsWaitingJSON($xpath_for_parsing) {
     //global $parser;
     //global $o;
     global $jsonObject;

     if ($xpath_for_parsing == "N.D.") {
        return "N.D.";
       }
     else {
        $match1 = $jsonObject->get($xpath_for_parsing);
        return  $match1[0];
     }
   }

   # Get the details in the case of XML ...
   function getDetailsWaitingXML($data, $xpath_for_parsing) {
     $xml = new SimpleXMLElement($data);

     $result = $xml->xpath($xpath_for_parsing);

     $theValue =  'N.D.';
     while(list( , $node) = each($result)) {
         $theValue = $node;
     }

     return $theValue;
   }

   # Manage the Molinette special case ...
   function parsingMolinetteJSON($data) {
     $json = json_decode($data, true);
     $json_new = "{\"colors\": [";
     $get_value = 0;
     $json_new .= "{";
     $json_new .= "\"colore\": \"bianco\",";
     foreach ($json['colors'] as $color) {
       if ($color['colore'] == "bianco") {
         $json_new .= "\"attesa\": \"".$color['attesa']."\",";
         $json_new .= "\"visita\": \"".$color['visita']."\"";
         $get_value = 1;
       }
     }
     if ($get_value == 0) {
       $json_new .= "\"attesa\": \"0\"".",";
       $json_new .= "\"visita\": \"0\"";
     }
     $json_new .= "},";
     $get_value = 0;
     $json_new .= "{";
     $json_new .= "\"colore\": \"verde\",";
     foreach ($json['colors'] as $color) {
       if ($color['colore'] == "verde") {
         $json_new .= "\"attesa\": \"".$color['attesa']."\",";
         $json_new .= "\"visita\": \"".$color['visita']."\"";
         $get_value = 1;
       }
     }
     if ($get_value == 0) {
       $json_new .= "\"attesa\": \"0\"".",";
       $json_new .= "\"visita\": \"0\"";
     }
     $json_new .= "},";
     $get_value = 0;
     $json_new .= "{";
     $json_new .= "\"colore\": \"giallo\",";
     foreach ($json['colors'] as $color) {
       if ($color['colore'] == "giallo") {
         $json_new .= "\"attesa\": \"".$color['attesa']."\",";
         $json_new .= "\"visita\": \"".$color['visita']."\"";
         $get_value = 1;
       }
     }
     if ($get_value == 0) {
       $json_new .= "\"attesa\": \"0\"".",";
       $json_new .= "\"visita\": \"0\"";
     }
     $json_new .= "},";
     $get_value = 0;
     $json_new .= "{";
     $json_new .= "\"colore\": \"rosso\",";
     foreach ($json['colors'] as $color) {
       if ($color['colore'] == "rosso") {
         $json_new .= "\"attesa\": \"".$color['attesa']."\",";
         $json_new .= "\"visita\": \"".$color['visita']."\"";
         $get_value = 1;
       }
     }
     if ($get_value == 0) {
       $json_new .= "\"attesa\": \"0\"".",";
       $json_new .= "\"visita\": \"0\"";
     }
     $json_new .= "}";
     $json_new .= "]}";
     return $json_new;
   }

   # Get the data for Sardinia Hospitals via POST request  ...
   function getDataViaPostOspedaliSardegna($row) {
     $ch = curl_init();

     curl_setopt_array($ch, array(
       CURLOPT_URL => $row['url_data'],
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => "",
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 30,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => "POST",
       CURLOPT_POSTFIELDS => "idMacroArea=null&codiceAziendaSanitaria=null&idAreaVasta=null&idPresidio=null&tipoProntoSoccorso=null&vicini=null&xhr=null",
       CURLOPT_HTTPHEADER => array(
         "cache-control: no-cache",
         "content-type: application/x-www-form-urlencoded"
       ),
     ));

     $server_output = curl_exec ($ch);

     curl_close ($ch);

     $jsonObject = new JsonPath\JsonObject($server_output);

     $jsonPathExpr = '$..view';

     $res = $jsonObject->get($jsonPathExpr);

     return $res[0];
   }

   # Get the data for Prato Hospital ...
   function getDataOspedalePrato($url) {
     # Set CURL parameters: pay attention to the PROXY config !!!!
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
     curl_setopt($ch, CURLOPT_HEADER, 0);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
     curl_setopt($ch, CURLOPT_PROXY, '');

     # In this case is needed manage cookies ..
     $cookies = "./cookie.txt";
     curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
     curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);

     # In this case is needed to get a double request to obtain the real data ...
     $data = curl_exec($ch);
     $data = curl_exec($ch);

     curl_close($ch);

     return $data;
   }







?>
