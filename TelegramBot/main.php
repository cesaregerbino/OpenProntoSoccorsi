<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");
include("textMessages.php");

class mainloop{
	const MAX_LENGTH = 4096;

	function start($telegram,$update)
		{
			date_default_timezone_set('Europe/Rome');
			$today = date("Y-m-d H:i:s");
			//$data=new getdata();
			// Instances the class

			/* If you need to manually take some parameters
			*  $result = $telegram->getData();
			*  $text = $result["message"] ["text"];
			*  $chat_id = $result["message"] ["chat"]["id"];
			*/

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

			//## Update accesses counter ...
      $this->updateAccessesNumber($today_str);

			$my_lat = 0;
			$my_lon = 0;

			if(!empty($location)) {
				$my_lat = $location['latitude'];
				$my_lon = $location['longitude'];
			}

			$currentLanguage = $this->getSelectedLanguageFromSession($chat_id);

			if (($currentLanguage != '') AND ($currentLanguage != 'SET')) {
				//echo "Lingua corrente = ".$currentLanguage;
				//echo "\n";
				//echo "\n";
				$arrayMessages = array();
				$arrayMessages = getArrayMessages($currentLanguage);
			}

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
				//$text = "/start";
				//$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
			}

      elseif (($my_lat != 0) AND ($my_lon != 0) AND ($currentLanguage != '')) {
				 //echo "Coordinate: " .$my_lat." - " .$my_lon;
				 //echo "\n\n";

         //http://localhost/OpenProntoSoccorso/API/getMunicipalityByLatLon.php?lat=44.351698&lon=10.753116
         $url = 'http://localhost/OpenProntoSoccorso/API/getMunicipalityByLatLon.php?lat='.$my_lat.'&lon='.$my_lon;

		     echo "URL = ".$url;
				 echo "\n";
		     echo "\n";

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

				 if (($my_Comune != "NODATA") AND ($my_Comune != "ERROR") AND ($currentLanguage != ''))
					 {
						 $theReply = "Il comune identificato è: ".$my_Comune;
						 $theReply = $arrayMessages['MUNICIPALITY']." ".$my_Comune;
						 $arrayMessages['LANG__CHOICE'];
						 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
						 $telegram->sendMessage($content);

/*
						 $url = 'http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoByMunicipality.php?municipality='.$my_Comune;

				     echo "URL = ".$url;
						 echo "\n";
				     echo "\n";

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

				     echo "JSON = ".$data;
				     echo "\n";
				     echo "\n";

						 //#Convert to string (json) ...
				     $json = json_decode($data);

		         $myComune = '';
			       foreach ($json as $ps) {
		             echo "Nome = ".$ps[0][ps_name];
								 echo "\n";
						     echo "\n";
								 //$my_Comune = $municipality;
			       }
*/







					 }
				 elseif ($my_Comune == "NODATA" AND ($currentLanguage != ''))
					 {
						 //$theReply = "Non è stato possibile individuare un comune italiano in corrispondenza del punto indicato. Si prega di riprovare";
						 $theReply = $arrayMessages['NO_MUNICIPALITY'];
						 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
						 $telegram->sendMessage($content);
					 }
				 elseif ($my_Comune == "ERROR" AND ($currentLanguage != ''))
					 {
						 //$theReply = "Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
						 $theReply = $arrayMessages['DB_ACCESS_ERROR'];
						 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
						 $telegram->sendMessage($content);
					 }
	    }
			//Check if test = "/start"a --> User has typed /start ...
			elseif ($text == "/start" AND ($currentLanguage != '')) {
        /*$reply = "Benvenuta/o! Quest'applicazione Le permettera' di visualizzare le liste di attesa dei primi 5 Pronto Soccorso più vicini nell'intorno di un punto di Suo interesse o all'interno dell'area in un Comune italiano.\n
E' necessario che mi indichi la Sua posizione corrente o di interesse (controlli che sia attiva la geolocalizzazione sul suo dispositivo ...); in caso contrario non saro' in grado di individuare la Sua posizione e non potro' calcolare il percorso per farLe raggiungere il Pronto Soccorso di Suo interesse.\n
Se non puo', o non vuole, fornire la Sua attuale posizione o di interresse provero' lo stesso a darLe una indicazione: mi fornisca il nome di un Comune italiano e Le indichero' i primi 5 Pronto Soccorso più vicini e li potra' comunque visualizzare su mappa.\n
Per indicare il Comune di interesse e' sufficiente scriverne il nome.\n
E' possibile visualizzare questo messaggio in qualsiasi momento scrivendo /start.\n
Le mappe utilizzate sono quelle derivate dai dati di OpenStreetMap (rif. http://www.openstreetmap.org/) e OSMBuildings (rif. http://www.osmbuildings.org/).\n
Il calcolo dei percorsi viene realizzato avvalendosi del servizio di routing di MapQuest (rif. https://developer.mapquest.com/products/directions)\n
L'abbreviazione delle url viene realizzata avvalendosi del servizio Google URL Shortener (rif. https://goo.gl/)\n
Questo bot e' stato realizzato a titolo sperimentale  da Cesare Gerbino (cesare.gerbino@gmail.com)\n
Il codice dell'applicazione è disponibile al seguente url https://github.com/cesaregerbino/OpenProntoSoccorsoBot con licenza Licenza MIT Copyright (c) [2014] (rif. https://it.wikipedia.org/wiki/Licenza_MIT)\n
Per maggiori dettagli http://cesaregerbino.wordpress.com/xxxxxxxxxxxx\n";*/
        $reply = $arrayMessages['START_MESSAGE'];
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
				}
			elseif ((($text == "/lingua") OR ($text == "/language")) AND ($currentLanguage != '')) {
					//## Prepare the keyboard with languages choices options ...
	        if ($text == "/lingua" AND ($currentLanguage != '')) {
					  $option = array(["ITALIANO","INGLESE"]);
						//$selectionText = "Seleziona la lingua ";
						$selectionText = $arrayMessages['LANG__CHOICE'];
					}
					if ($text == "/language" AND ($currentLanguage != '')) {
					  $option = array(["ITALIAN","ENGLISH"]);
						//$selectionText = "Select the language ";
						$selectionText = $arrayMessages['LANG__CHOICE'];
					}
					$keyb = $telegram->buildKeyBoard($option, $onetime=true);
		      $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => $selectionText);
		      $telegram->sendMessage($content);
				}
			elseif (((strtoupper($text) == "ITALIANO") OR (strtoupper($text) == "ITALIAN")) AND ($currentLanguage != '')) {
					//## Set for the Italian language  ...
					//$theReply="La lingua scelta è: ITALIANO";
					$theReply = $arrayMessages['SELECTED_LANGUAGE_IT'];
					$content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					sleep (1);

	        $currentLanguage = "IT";
					$this->setSelectedLanguageInSession($chat_id, $currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages($currentLanguage);
				}
			elseif (((strtoupper($text) == "INGLESE") OR (strtoupper($text) == "ENGLISH")) AND ($currentLanguage != '')) {
					//## Set for the English language  ...
					//$theReply="The selected language is: ENGLISH";
					$theReply = $arrayMessages['SELECTED_LANGUAGE_EN'];
					$content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					sleep (1);

					$currentLanguage = "EN";
					//echo "currentLanguage prima di chiamare la funzione = ".$currentLanguage;
					//echo "\n";
					//echo "\n";
					$this->setSelectedLanguageInSession($chat_id, $currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages($currentLanguage);

					$arrayMessages = array();
					$arrayMessages = getArrayMessages("EN");
				}
		 elseif ($currentLanguage != '') //Check ithe text typed by the user --> Probably the user has typed the Comune name ...
				 {
					 /* $theReply1="The text you've typed is: ".$text;
					 $content = array('chat_id' => $chat_id, 'text' => $theReply1,'disable_web_page_preview'=>true);
					 $telegram->sendMessage($content);
					 sleep (1); */

		       $isComune = $this->checkComune($text);

					 if ($isComune == 1)
					   {
							 $theReply = "Stò cercando le informazioni sui numeri e tempi di attesa dei Pronto Soccorso di ".$text;
							 //$theReply = $text.$arrayMessages['MUNICIPALITY_NAME'];
							 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
							 $telegram->sendMessage($content);


							 $url = 'http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality='.$text;

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

					     echo "JSON = ".$data;
					     echo "\n";
					     echo "\n";

							 //#Convert to string (json) ...
					     $json = json_decode($data, true);

			         $psWaitTime = '';
				       foreach ($json['prontoSoccorsi'] as $ps) {
			             //echo "Nome = ".$ps['ps_name'];
									 //echo "\n";
							     //echo "\n";
									 //$my_Comune = $municipality;
									 $psWaitTime .= "Nome PS :".$ps['ps_name'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Città: ".$ps['city'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Indirizzo: ".$ps['address'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Telefono: ".$ps['tel'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Email: ".$ps['email'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Sito web: ".$ps['url_website'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "N° codici bianco in attesa: ".$ps['numeri_bianco_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici bianco: ".$ps['tempi_bianco_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "N° codici bianco in visita: ".$ps['numeri_bianco_in_visita'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici bianco in visita: ".$ps['tempi_bianco_in_visita'];
									 $psWaitTime .= "\n";

									 $psWaitTime .= "N° codici verdi in attesa: ".$ps['numeri_verde_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici verdi: ".$ps['tempi_verde_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "N° codici verdi in visita: ".$ps['numeri_verde_in_visita'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici verdi in visita: ".$ps['tempi_verde_in_visita'];
									 $psWaitTime .= "\n";

									 $psWaitTime .= "N° codici giallo in attesa: ".$ps['numeri_giallo_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici giallo: ".$ps['tempi_giallo_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "N° codici giallo in visita: ".$ps['numeri_giallo_in_visita'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici giallo in visita: ".$ps['tempi_giallo_in_visita'];
									 $psWaitTime .= "\n";

									 $psWaitTime .= "N° codici rosso in attesa: ".$ps['numeri_rosso_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici rosso: ".$ps['tempi_rosso_attesa'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "N° codici rosso in visita: ".$ps['numeri_rosso_in_visita'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "Tempo attesa per codici rosso in visita: ".$ps['tempi_rosso_in_visita'];
									 $psWaitTime .= "\n";
									 $psWaitTime .= "\n";
									 $psWaitTime .= "*************";
									 $psWaitTime .= "\n";
									 $psWaitTime .= "\n";


			             $content = array('chat_id' => $chat_id, 'text' => $psWaitTime,'disable_web_page_preview'=>true);
			             $telegram->sendMessage($content);

				       }







						 }
					 elseif ($isComune == 0)
					   {
							 //$theReply = $text." non è un comune italiano. Provare a digitare correttamente: Il nome del comune deve iniziare con una lettera maiuscola, se è composto da più parole ogni parola deve iniziare con una lettera maiuscola (es. La Loggia è corretto La loggia è errato ... ), se compaiono lettere accentate NON usare l'apostrofo (es. Agliè è corretto, Aglie' è errato) ...";
							 $theReply = $text.$arrayMessages['NO_MUNICIPALITY_NAME'];
							 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
							 $telegram->sendMessage($content);
					   }
					 elseif ($isComune == -1)
					   {
							 //$theReply = " Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
							 $theReply = $text.$arrayMessages['DB_ACCESS_ERROR'];
							 $content = array('chat_id' => $chat_id, 'text' => $theReply,'disable_web_page_preview'=>true);
							 $telegram->sendMessage($content);
					   }
			   }
		}

		function setSelectedLanguageInSession($chat_id, $currentLanguage)
 		{
			//echo "currentLanguage nella funzione= ".$currentLanguage;
			//echo "\n";
			//echo "\n";

			$db = new SQLite3(DATA_ACCESSES_DB_PATH.'/DataSessionsDB/DataSessionsDB.sqlite');
 			$q="SELECT * FROM data_sessions WHERE chat_id = '".$chat_id."'";
 			try {
 					 $stmt = $db->prepare($q);
 					 $results = $stmt->execute();
 					 $savedLanguage = "";
 					 while ($row = $results->fetchArray(SQLITE3_ASSOC)){
 									$savedLanguage = $row['language'];
 					 }
           if ($savedLanguage == '') {
						//echo "Stò per inserire la lingua ...".$currentLanguage;
						//echo "\n";
						//echo "\n";
            $insert = "INSERT INTO data_sessions (chat_id, language) VALUES (:chat_id, :language)";
						//echo "La insert ...".$insert;
						//echo "\n";
						//echo "\n";
						$stmt = $db->prepare($insert);

						//## Bind parameters to statement variables
            $stmt->bindParam(':chat_id', $chat_id);
            $stmt->bindParam(':language', $currentLanguage);
					 }
					 else {
						 //echo "Stò per aggiornare la lingua ...".$currentLanguage." Quella salvata era : ".$savedLanguage;
						 //echo "\n";
						 //echo "\n";
					   $update = "UPDATE data_sessions SET language=? WHERE chat_id = '".$chat_id."'";
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
			$q="SELECT * FROM data_sessions WHERE chat_id = '".$chat_id."'";
			//echo "Query = ".$q;
			//echo"\n";
			//echo"\n";
			try {
					 $stmt = $db_data_sessions->prepare($q);
					 $results = $stmt->execute();
					 $currentLanguage = "";
					 while ($row = $results->fetchArray(SQLITE3_ASSOC)){
									$currentLanguage = $row['language'];
					 }
					 //echo "currentLanguage in get function = ".$currentLanguage;
		 			 //echo"\n";
		 			 //echo"\n";
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
 			$db_data_sessions = new SQLite3(DATA_DB_PATH.'/OpenProntoSoccorso.sqlite');
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
