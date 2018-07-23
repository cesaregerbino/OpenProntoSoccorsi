<?php
# ***************************************************************************************************
# *** Open Pronto Soccorsi - Telegram Bot
# *** Description: main.php procedure
# ***        Note: This Telegram Bot was derived from the Telegram Bot for Italian Museums of DBUnico Mibact Lic. CC-BY
# ***              @author Francesco Piero Paolicelli @piersoft
# ***      Author: Cesare Gerbino
# ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
# ***     License: MIT (https://opensource.org/licenses/MIT)
# ***************************************************************************************************

include("Telegram.php");
include("textMessages.php");

class mainloop{
	const MAX_LENGTH = 4096;

	function start($telegram,$update)
		{
			date_default_timezone_set('Europe/Rome');
			$today = date("Y-m-d H:i:s");

			$text = $update["message"] ["text"];
			$chat_id = $update["message"] ["chat"]["id"];
			$user_id=$update["message"]["from"]["id"];
			$location=$update["message"]["location"];
			$reply_to_msg=$update["message"]["reply_to_message"];

			$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
			$db = NULL;
		}

	 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
		{
		  $data_db_path = DATA_DB_PATH;
			$data_accesses_path = DATA_ACCESSES_DB_PATH;
			$data_sessions_path = DATA_SESSIONS_DB_PATH;

			date_default_timezone_set('Europe/Rome');
			$today = date("Y-m-d H:i:s");
			$today_str = (string)date("Y-m-d");

			//*** Update accesses counter ...
      $this->updateAccessesNumber($today_str);

      //*** my_lat and my_lon will be the user coordinates ...
			$my_lat = 0;
			$my_lon = 0;

			if(!empty($location)) {
				$my_lat = $location['latitude'];
				$my_lon = $location['longitude'];
			}

      //*** Set the user selected language as current language ...
			$currentLanguage = $this->getSelectedLanguageFromSession($chat_id);

			if (($currentLanguage != '') AND ($currentLanguage != 'SET')) {
				//echo "Lingua corrente = ".$currentLanguage;
				//echo "\n";
				//echo "\n";
				$arrayMessages = array();
				$arrayMessages = getArrayMessages($currentLanguage);
			}

      //*** No language is still selected ...
			if ($currentLanguage == '') {
				//echo "Lingua non definita ...";
				//echo "\n";
				//echo "\n";
				$option = array(["ITALIAN","ENGLISH"],["ITALIANO","INGLESE"]);
				$selectionText = "Selezionare la lingua e poi scrivere /start per avviare \n\nSelect the language and then digit /start to start ";
			  $keyb = $telegram->buildKeyBoard($option, $onetime=true);
			  $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $selectionText);
			  $telegram->sendMessage($content);
				sleep (1);
				$this->setSelectedLanguageInSession($chat_id, "SET");
			}
      //*** The user has selected a point on the map: call the API to obtail the municipality ...
      elseif (($my_lat != 0) AND ($my_lon != 0) AND ($currentLanguage != '')) {
				 //echo "Coordinate: " .$my_lat." - " .$my_lon;
				 //echo "\n\n";

				 $url = URL_API2.'?lat='.$my_lat.'&lon='.$my_lon;
				 //echo "URL = ".$url;
				 //echo "\n";
		     //echo "\n";

		     //#Set CURL parameters: pay attention to the PROXY config !!!!
		     $ch = curl_init();
		     curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		     curl_setopt($ch, CURLOPT_HEADER, 0);
		     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		     curl_setopt($ch, CURLOPT_URL, $url);
		     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		     //curl_setopt($ch, CURLOPT_PROXY, '<proxy.xxx.com:<port>');
		     curl_setopt($ch, CURLOPT_PROXY, '');
		     $data = curl_exec($ch);
		     curl_close($ch);

		     //echo "JSON = ".$data;
		     //echo "\n";
		     //echo "\n";

				 //#Convert to string (json) the route ...
		     $json = json_decode($data);

         $myComune = '';
	       foreach ($json as $municipality) {
             //echo "Comune = ".$municipality;
						 //echo "\n";
				     //echo "\n";
						 $my_Comune = $municipality;
	       }

         //*** Municipality founded ...
				 if (($my_Comune != "NODATA") AND ($my_Comune != "ERROR") AND ($currentLanguage != ''))
					 {
						 $db = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');

						 //## Accedo al db di sessione per eliminare i dati di sessione per di interesse per l'id di chat ...
						 $q="DELETE FROM municipality_sessions WHERE Chat_id = :Chat_id";
						 try {
							 $stmt = $db->prepare($q);
							 $stmt->bindvalue(':Chat_id', $chat_id, SQLITE3_TEXT);
							 $results = $stmt->execute();
						 }
						 catch(PDOException $e) {
								 print "Something went wrong or Connection to database failed! ".$e->getMessage();
							 }

						 $q = "INSERT INTO municipality_sessions (chat_id, municipality, my_lat, my_lon) VALUES (:chat_id, :municipality, :my_lat, :my_lon)";
						 try {
									 $stmt = $db->prepare($q);

									 //## Bind parameters to statement variables
									 $stmt->bindParam(':chat_id', $chat_id);
									 $stmt->bindParam(':municipality', $my_Comune);
									 $stmt->bindParam(':my_lat', $my_lat);
									 $stmt->bindParam(':my_lon', $my_lon);
									 //## Execute statement
									 $stmt->execute();
							 }
							catch(PDOException $e) {
											print "Something went wrong or Connection to database failed! ".$e->getMessage();
							}
						 $db_data_sessions = null;

						 //## Preparo la keyboard con le opzioni di scelta per il raggio di ricerca intorno al punto di interesse ...
						 $search_distances = array(["0","20 km"],["50 km","100 km"]);
						 $keyb = $telegram->buildKeyBoard($search_distances, $onetime=true);

						 //$theReply = "Seleziona il raggio di ricerca intorno al comune";
						 $theReply = $arrayMessages['BUFFER_AROUND_MUNICIPALITY'];
						 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $theReply);
						 $telegram->sendMessage($content);
					 }
				 //*** Municipality NOT founded ...
				 elseif ($my_Comune == "NODATA" AND ($currentLanguage != ''))
					 {
						 //$theReply = "Non è stato possibile individuare un comune italiano in corrispondenza del punto indicato. Si prega di riprovare";
						 $theReply = $arrayMessages['NO_MUNICIPALITY'];
						 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
						 $telegram->sendMessage($content);
					 }
         //*** Error ...
				 elseif ($my_Comune == "ERROR" AND ($currentLanguage != ''))
					 {
						 //$theReply = "Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
						 $theReply = $arrayMessages['DB_ACCESS_ERROR'];
						 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
						 $telegram->sendMessage($content);
					 }
	    }
			//*** User has typed /start ...
			elseif ($text == "/start" AND ($currentLanguage != '')) {
        $reply = $arrayMessages['START_MESSAGE'];
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
				}
	    //*** The user has typed "lingua" or "language"
			elseif ((($text == "/lingua") OR ($text == "/language")) AND ($currentLanguage != '')) {
					//## Prepare the keyboard with languages choices options ...
	        if ($text == "/lingua" AND ($currentLanguage != '')) {
					  $option = array(["ITALIANO","INGLESE"]);
						$selectionText = $arrayMessages['LANG__CHOICE'];
					}
					if ($text == "/language" AND ($currentLanguage != '')) {
					  $option = array(["ITALIAN","ENGLISH"]);
						$selectionText = $arrayMessages['LANG__CHOICE'];
					}
					$keyb = $telegram->buildKeyBoard($option, $onetime=true);
		      $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $selectionText);
		      $telegram->sendMessage($content);
				}
		  //*** Set ITALIAN as language ...
			elseif (((strtoupper($text) == "ITALIANO") OR (strtoupper($text) == "ITALIAN")) AND ($currentLanguage != '')) {
					//## Set for the Italian language  ...
					$theReply = $arrayMessages['SELECTED_LANGUAGE_IT'];
					$content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					sleep (1);

	        $currentLanguage = "IT";
					$this->setSelectedLanguageInSession($chat_id, $currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages($currentLanguage);
				}
			//*** Set ENGLISH as language ...
			elseif (((strtoupper($text) == "INGLESE") OR (strtoupper($text) == "ENGLISH")) AND ($currentLanguage != '')) {
					//## Set for the English language  ...
					$theReply = $arrayMessages['SELECTED_LANGUAGE_EN'];
					$content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					sleep (1);

					$currentLanguage = "EN";
					$this->setSelectedLanguageInSession($chat_id, $currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages($currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages("EN");
				}
		 //*** The user has typed a Municipality name ...
		 elseif ((strtoupper($text) == "0") OR (strtoupper($text) == "20 KM") OR (strtoupper($text) == "50 KM") OR (strtoupper($text) == "100 KM"))  {
							## Accedo al db di sessione per recuperare il dato del comune di interesse per l'id di chat ...
							$db_data_sessions = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');
							$q="SELECT ds.Municipality, ds.my_lat, ds.my_lon
									FROM municipality_sessions as ds
									WHERE ds.Chat_id = :Chat_id";
							try {
								$stmt = $db_data_sessions->prepare($q);
								$stmt->bindvalue(':Chat_id', $chat_id, SQLITE3_TEXT);
								$results = $stmt->execute();
								while ($row = $results->fetchArray(SQLITE3_ASSOC)){
									$municipality = $row['municipality'];
									$my_lat = $row['my_lat'];
									$my_lon = $row['my_lon'];
								}
							}
							catch(PDOException $e) {
									print "Something went wrong or Connection to database failed! ".$e->getMessage();
								}

							$db_data_sessions = null;

							## To convert user entry in distance ...
							if ($text == "0") $distance = 0;
							if ($text == "20 km") $distance = 20000;
							if ($text == "50 km") $distance = 50000;
							if ($text == "100 km") $distance = 100000;

							$this->sendResponse($chat_id,$telegram,$municipality,$distance,$my_lat,$my_lon,$currentLanguage);
	  }
		 elseif ($currentLanguage != '') //Check the text typed by the user --> Probably the user has typed the Comune name ...
				 {
           //*** Check for the typed name ...
					 $isComune = $this->checkComune($text);

           //*** The name is an Italian Municipality ...
					 if ($isComune == 1)
					   {
							 $db = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');

							 //## Accedo al db di sessione per eliminare i dati di sessione per di interesse per l'id di chat ...
							 $q="DELETE FROM municipality_sessions WHERE Chat_id = :Chat_id";
							 try {
								 $stmt = $db->prepare($q);
								 $stmt->bindvalue(':Chat_id', $chat_id, SQLITE3_TEXT);
								 $results = $stmt->execute();
							 }
							 catch(PDOException $e) {
									 print "Something went wrong or Connection to database failed! ".$e->getMessage();
								 }

							 $q = "INSERT INTO municipality_sessions (chat_id, municipality) VALUES (:chat_id, :municipality)";
				  		 try {
				  					 $stmt = $db->prepare($q);

					 					 //## Bind parameters to statement variables
					           $stmt->bindParam(':chat_id', $chat_id);
					           $stmt->bindParam(':municipality', $text);

										 //## Execute statement
					 					 $stmt->execute();
				 				 }
				  			catch(PDOException $e) {
				  							print "Something went wrong or Connection to database failed! ".$e->getMessage();
				  			}
				  		 $db_data_sessions = null;

							 //## Preparo la keyboard con le opzioni di scelta per il raggio di ricerca intorno al punto di interesse ...
							 $search_distances = array(["0","20 km"],["50 km","100 km"]);
							 $keyb = $telegram->buildKeyBoard($search_distances, $onetime=true);
				       $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Seleziona il raggio di ricerca intorno al comune ");
				       $telegram->sendMessage($content);
						 }
					 //*** The name is NOT an Italian Municipality ...
					 elseif ($isComune == 0)
					   {
							 $theReply = $arrayMessages['NO_MUNICIPALITY_NAME'];
							 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
							 $telegram->sendMessage($content);
					   }
					 //*** Error ...
					 elseif ($isComune == -1)
					   {
							 //$theReply = " Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
							 $theReply = $arrayMessages['DB_ACCESS_ERROR'];
							 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
							 $telegram->sendMessage($content);
					   }
			   }
		}


		function sendResponse($chat_id, $telegram, $municipality, $distance, $my_lat, $my_lon, $currentLanguage)
 		{
			//echo "Lingua = ".$currentLanguage;
			//echo "\n";
			//echo "\n";

			$arrayMessages = array();
			$arrayMessages = getArrayMessages($currentLanguage);

			//$theReply = "Stò cercando le informazioni sui numeri e tempi di attesa dei Pronto Soccorso di ".$municipality;
			$theReply = $arrayMessages['SEARCHING_PS_INFO']."<b>".$municipality."</b>";
			$content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true,'parse_mode'=> "HTML");
			$telegram->sendMessage($content);

      // Prepare the municipality name to use in url in curl request ....
			$municipality = str_replace(" ", "%20", $municipality);
			$municipality = str_replace("'", "%27", $municipality);

			$url = URL_API.'?municipality='.$municipality.'&distance='.$distance;
			//echo "URL = ".$url;
			//echo "\n";
			//echo "\n";

			//#Set CURL parameters: pay attention to the PROXY config !!!!
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			//curl_setopt($ch, CURLOPT_PROXY, '<proxy.xxx.com:<port>');
			curl_setopt($ch, CURLOPT_PROXY, '');
			$data = curl_exec($ch);
			curl_close($ch);

			//echo "JSON = ".$data;
			//echo "\n";
			//echo "\n";

			//#Convert to string (json) ...
			$json = json_decode($data, true);

      $emptyData = TRUE;
			$psWaitTime = '';
			foreach ($json['prontoSoccorsi'] as $ps) {
            $emptyData = FALSE;
						$psWaitTime .= "Nome: <b>".$ps['ps_name']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Città: <b>".$ps['city']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Indirizzo: <b>".$ps['address']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Telefono: <b>".$ps['tel']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Email: <b>".$ps['email']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Sito web: ".str_replace('&', '&amp;', $ps['url_website']);
						if (($my_lat != 0) AND ($my_lon != 0)) {
							$longUrl = URL_API3."?lat_from=".$my_lat."&lon_from=".$my_lon."&lat_to=".$ps['Lat']."&lon_to=".$ps['Lon']."&map_type=0";
	            $shortUrl = $this->CompactUrl($longUrl);
							$psWaitTime .= "\n";
							//echo "shortUrl = ".$shortUrl;
							//echo "\n";
							//echo "\n";
							$psWaitTime .= "Descrizione del percorso: ".$shortUrl;

							$longUrl = URL_API3."?lat_from=".$my_lat."&lon_from=".$my_lon."&lat_to=".$ps['Lat']."&lon_to=".$ps['Lon']."&map_type=2";
	            $shortUrl = $this->CompactUrl($longUrl);
							$psWaitTime .= "\n";
							//echo "shortUrl = ".$shortUrl;
							//echo "\n";
							//echo "\n";
							$psWaitTime .= "Percorso: ".$shortUrl;
						}

						$psWaitTime .= "\n";
						$psWaitTime .= "\n";

						$psWaitTime .= "<b>CODICE BIANCO</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In attesa = <b>".$ps['numeri_bianco_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato attesa = <b>".$ps['tempi_bianco_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In visita = <b>".$ps['numeri_bianco_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato visita = <b>".$ps['tempi_bianco_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "\n";

						$psWaitTime .= "<b>CODICE VERDE</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In attesa = <b>".$ps['numeri_verde_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato attesa = <b>".$ps['tempi_verde_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In visita = <b>".$ps['numeri_verde_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato visita = <b>".$ps['tempi_verde_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "\n";

						$psWaitTime .= "<b>CODICE GIALLO</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In attesa = <b>".$ps['numeri_giallo_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato attesa = <b>".$ps['tempi_giallo_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In visita = <b>".$ps['numeri_giallo_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato visita = <b>".$ps['tempi_giallo_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "\n";

						$psWaitTime .= "<b>CODICE ROSSO</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In attesa = <b>".$ps['numeri_rosso_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato attesa = <b>".$ps['tempi_rosso_attesa']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "In visita = <b>".$ps['numeri_rosso_in_visita']."</b>";
						$psWaitTime .= "\n";
						$psWaitTime .= "Tempo stimato visita = <b>".$ps['tempi_rosso_in_visita']."</b>";
						$psWaitTime .= "\n";

						$psWaitTime .= "\n";
						$psWaitTime .= "*************";
						$psWaitTime .= "\n";
						$psWaitTime .= "\n";

						$content = array('chat_id' => $chat_id,  'text' => $psWaitTime, 'parse_mode'=> "HTML", 'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);

						$psWaitTime = "";
				}

				if ($emptyData == TRUE) {
					  $theReply = "Per il comune di interesse non è stato possibile individuare alcun Pronto Soccorso che fornisca i dati di attesa";
					  $content = array('chat_id' => $chat_id,  'text' => $theReply, 'parse_mode'=> "HTML", 'disable_web_page_preview'=>true);
					  $telegram->sendMessage($content);
			  }

		}


		function CompactUrl($longUrl)
		  {
		   $apiKey = API;

		   $postData = array('longUrl' => $longUrl);
		   $jsonData = json_encode($postData);

		   $curlObj = curl_init();

		   curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey.'&fields=id');
		   curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
		   curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
		   curl_setopt($curlObj, CURLOPT_HEADER, 0);
		   curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
		   curl_setopt($curlObj, CURLOPT_POST, 1);
		   curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

		   $response = curl_exec($curlObj);

		   // Change the response json string to object
		   $json = json_decode($response);

		   curl_close($curlObj);
		   $shortLink = get_object_vars($json);

		   return $shortLink['id'];
		 }

		function setSelectedLanguageInSession($chat_id, $currentLanguage)
 		{
			//echo "currentLanguage nella funzione= ".$currentLanguage;
			//echo "\n";
			//echo "\n";

			$db = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');
 			$q="SELECT * FROM language_sessions WHERE chat_id = '".$chat_id."'";
 			try {
 					 $stmt = $db->prepare($q);
 					 $results = $stmt->execute();
 					 $savedLanguage = "";
 					 while ($row = $results->fetchArray(SQLITE3_ASSOC)){
 									$savedLanguage = $row['language'];
 					 }
           if ($savedLanguage == '') {
            $insert = "INSERT INTO language_sessions (chat_id, language) VALUES (:chat_id, :language)";

						$stmt = $db->prepare($insert);

						//## Bind parameters to statement variables
            $stmt->bindParam(':chat_id', $chat_id);
            $stmt->bindParam(':language', $currentLanguage);
					 }
					 else {
					   $update = "UPDATE language_sessions SET language=? WHERE chat_id = '".$chat_id."'";

						 $stmt = $db->prepare($update);

						 //## Bind parameters to statement variables
						 $stmt->bindValue(1,$currentLanguage);
					 }

					 //## Execute statement
					 $stmt->execute();
				 }
 			catch(PDOException $e) {
 							print "Something went wrong or Connection to database failed! ".$e->getMessage();
 			}
 			$db_data_sessions = null;
 	 }


	 function getSelectedLanguageFromSession($chat_id)
		{
			$db_data_sessions = new SQLite3(DATA_SESSIONS_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');
			$q="SELECT * FROM language_sessions WHERE chat_id = '".$chat_id."'";
			try {
					 $stmt = $db_data_sessions->prepare($q);
					 $results = $stmt->execute();
					 $currentLanguage = "";
					 while ($row = $results->fetchArray(SQLITE3_ASSOC)){
									$currentLanguage = $row['language'];
					 }

           return $currentLanguage;
			}
			catch(PDOException $e) {
							print "Something went wrong or Connection to database failed! ".$e->getMessage();
			}
			$db_data_sessions = null;
	 }

	 function updateAccessesNumber($today)
		{
			//## Update access counter ...
			$access_counter = 0;
			$db = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataAccessesDB/DataAccessesDB.sqlite');
			$q="SELECT * FROM access_numbers WHERE Date = '".$today."'";
			try {
					 $stmt = $db->prepare($q);
					 $results = $stmt->execute();
					 while ($row = $results->fetchArray(SQLITE3_ASSOC)){
									$access_counter = $row['Counter'];
					 }

					 $access_counter = $access_counter + 1;

					 if ($access_counter == 1) {
						 $insert = "INSERT INTO access_numbers (Date, Counter) VALUES (:date, :counter)";

					   $stmt = $db->prepare($insert);

						 $stmt->bindParam(':date', $today);
						 $stmt->bindParam(':counter', $access_counter);					 }
					 else {
					   $update = "UPDATE access_numbers SET Counter=? WHERE Date = '".$today."'";

						 $stmt = $db->prepare($update);
						 $stmt->bindValue(1,$access_counter);
					 }

					 //## Execute statement
					 $stmt->execute();
			}
			catch(PDOException $e) {
							print "Something went wrong or Connection to database failed! ".$e->getMessage();
			}
			$db_data_sessions = null;
	 }

	 function checkComune($nameComune)
 		{
 			$db_data_sessions = new SQLite3(DATA_DB_PATH.'/OpenProntoSoccorsi.sqlite');
 			$q="SELECT * FROM comuni_From_ISTAT_32632 WHERE comune = \"".$nameComune."\"";

 			try {

 					 $stmt = $db_data_sessions->prepare($q);
 					 $results = $stmt->execute();

					 if ($results->fetchArray(SQLITE3_ASSOC) > 0) {
		 							return 1;
		  			}
					 else {
		 							return 0;
		  			}
 			}
 			catch(PDOException $e) {
 							print "Something went wrong or Connection to database failed! ".$e->getMessage();
							return -1;
 			}
 			$db_data_sessions = null;
 	 }

}
?>
