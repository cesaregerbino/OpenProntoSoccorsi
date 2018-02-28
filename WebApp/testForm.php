<html>
 <head>
  <title>
    Open Pronto Soccorsi
  </title>
 </head>
 <body>
  <form name="test" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
   Nome del comune: <input type="text" name="comune">
   <input type="submit" name="submit" value="Ricerca"><br>
  </form>

  <?php
   include("settings.php");

   if(isset($_POST['submit']))
    {
     $comune = $_POST['comune'];

     //*** Check for the typed name ...
     $isComune = checkComune($comune);

     //*** The name is an Italian Municipality ...
     if ($isComune == 1)
      {
         //echo "OK, ".$comune." e' un comune Italiano !!";
         $dataPS = getDataForProntoSoccorso($comune);
         printDataPS($dataPS);
      }
     //*** The name is NOT an Italian Municipality ...
     elseif ($isComune == 0)
      {
        echo "KO, ".$comune." NON e' un comune Italiano !!";
        echo "<br>";
        echo "<br>";
        echo "Provare a digitare correttamente: il nome del comune deve iniziare con una lettera maiuscola, se è composto da più parole ogni parola deve iniziare con una lettera maiuscola (es. La Loggia è corretto, La loggia è errato ... ), se compaiono lettere accentate NON usare l'apostrofo (es. Agliè è corretto, Aglie' è errato) ...";
      }
     //*** Error ...
     elseif ($isComune == -1)
      {
        echo "KO. Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
      }
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

    function getDataForProntoSoccorso($comune)
     {
       $url = 'http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality='.$comune.'&distance=0';

 			 //echo "URL = ".$url;
 			 //echo "<br>";
 			 //echo "<br>";

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

       return $data;


 			 //echo "JSON = ".$data;
 			 //echo "<br>";
 			 //echo "<br>";
     }


     function printDataPS($data)
      {
        //#Convert to string (json) ...
  			$json = json_decode($data, true);

  			//$psWaitTime = '';
  			foreach ($json['prontoSoccorsi'] as $ps) {
  						echo "Nome: <b>".$ps['ps_name']."</b>";
  						echo "<br>";
  						echo "Città: <b>".$ps['city']."</b>";
  						echo "<br>";
  						echo "Indirizzo: <b>".$ps['address']."</b>";
  						echo "<br>";
  						echo "Telefono: <b>".$ps['tel']."</b>";
  						echo "<br>";
  						echo "Email: <b>".$ps['email']."</b>";
  						echo "<br>";
  						echo "Sito web: ".str_replace('&', '&amp;', $ps['url_website']);
  						echo "<br>";
  						echo "<br>";

  						echo "<b>CODICE BIANCO</b>";
  						echo "<br>";
  						echo "In attesa = <b>".$ps['numeri_bianco_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_bianco_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_bianco_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_bianco_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

  						echo "<b>CODICE VERDE</b>";
  						echo "<br>";
  						echo "In attesa = <b>".$ps['numeri_verde_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_verde_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_verde_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_verde_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

  						echo "<b>CODICE GIALLO</b>";
  						echo "<br>";
  						echo "In attesa = <b>".$ps['numeri_giallo_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_giallo_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_giallo_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_giallo_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

  						echo "<b>CODICE ROSSO</b>";
  						echo "<br>";
  						echo "In attesa = <b>".$ps['numeri_rosso_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_rosso_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_rosso_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_rosso_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";
  						echo "*************";
  						echo "<br>";
  						echo "<br>";
      }
      }

  ?>
 </body>
</html>
