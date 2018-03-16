<!--
 ***************************************************************************************************
 *** Open Pronto Soccorso - Web Mapping
 *** Description: Simple web app HTML / PHP: Given a municipality name search the Italian First Aids waiting list numbers
 ***              for each code (white, green, yellow and red ones)
 ***        Note: This web app manages NOT all Italian First Aids locations but the only ones for which
 ***              the waiting list numbers for each code white, green, yellow and red ones) are available
 ***              in open data (services) or in some HTML web portal pages
 ***      Author: Cesare Gerbino
 ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorso
 ***     License: MIT (https://opensource.org/licenses/MIT)
 ***************************************************************************************************
-->

<html>
 <head>
  <meta charset='utf-8' />
  <title>
    Open Pronto Soccorsi
  </title>
  <style>
    #infodiv{
             left:2px;
             bottom:20px;
             font-size: 11px;
             z-index:9999;
             border-radius: 10px;
             -moz-border-radius: 10px;
             -webkit-border-radius: 10px;
             border: 2px solid #808080;
             background-color:#f2eeee;
             padding:5px;
             box-shadow: 0 3px 14px rgba(0,0,0,0.4)
    }
  </style>
 </head>
 <body>
   <!-- *** The info div  -->
   <div id="infodiv" style="leaflet-popup-content-wrapper">
       Questa applicazione permette di fornire le informazioni sulle attese (numeri e tempi) dei Pronto Soccorsi italiani a partire dall'indicazione
       del nome del comune e, al più, di un raggio di interesse nell'intorno del comune.<br>
       Non tutti i pronto soccorsi italiani sono individuati, ma solo quelli per cui risultino essere disponibili, in open data o come sito web, le
       informazioni sulle attese (numeri e tempi).<br>
       Questa applicazione e'' stata realizzata a titolo sperimentale  da Cesare Gerbino (cesare.gerbino@gmail.com)
       &nbsp;-&nbsp;
       Il codice sorgente  <a href="https://github.com/cesaregerbino/OpenProntoSoccorso" target="github">è disponibile su GitHub</a>
       &nbsp;-&nbsp;
       Licenza: <a href="https://opensource.org/licenses/MIT" target="licenza">MIT</a>
       &nbsp;-&nbsp;
       Maggiori <a href="https://cesaregerbino.wordpress.com/2015/02/04/numeri-civici-open-data-in-italia-disponibile-la-release-2-0-della-raccolta/" target="link">dettagli</a>
   </div>

  <br>

  <!-- *** The form for user input -->
  <form name="test" id="infodiv" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
   Nome del comune: <input type="text" name="comune"><br>
   <br>
   Raggio di ricerca intorno al comune:<br>
   <input type="radio" name="dist" value="0" checked> 0
   <input type="radio" name="dist" value="20"> 20 Km
   <input type="radio" name="dist" value="50"> 50 Km
   <input type="radio" name="dist" value="100"> 100 Km<br>
   <br>
   <input type="submit" name="submit" value="Ricerca"><br>
   <br>
  </form>

  <!-- *** TElaborate the user input -->
  <?php
   include("settings.php");

   //header("Location: http://localhost/OpenProntoSoccorso/WebApp/OpenProntoSoccorso.php");
   //exit;

   if(isset($_POST['submit']))
    {
     $comune = $_POST['comune'];
     $dist = $_POST['dist'];

     //*** Check for the typed name ...
     $isComune = checkComune($comune);

     //*** The name is an Italian Municipality ...
     if ($isComune == 1)
      {
         //echo "OK, ".$comune." e' un comune Italiano !!";

         ## To convert user entry in distance ...
         if ($dist == "0") $distance = 0;
         if ($dist == "20") $distance = 20000;
         if ($dist == "50") $distance = 50000;
         if ($dist == "100") $distance = 100000;


         $dataPS = getDataForProntoSoccorso($comune, $distance);
         printDataPS($dataPS);
      }
     //*** The name is NOT an Italian Municipality ...
     elseif ($isComune == 0)
      {
        echo "<div id='infodiv'>";
        echo "KO, ".$comune." NON e' un comune Italiano !!";
        echo "<br>";
        echo "<br>";
        echo "Provare a digitare correttamente: il nome del comune deve iniziare con una lettera maiuscola, se è composto da più parole ogni parola deve iniziare con una lettera maiuscola (es. La Loggia è corretto, La loggia è errato ... ), se compaiono lettere accentate NON usare l'apostrofo (es. Agliè è corretto, Aglie' è errato) ...";
        echo "</div>";
        echo "<br>";
      }
     //*** Error ...
     elseif ($isComune == -1)
      {
        echo "<div id='infodiv'>";
        echo "KO. Si è riscontrato un problema sull'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido";
        echo "</div>";
        echo "<br>";
      }

     }


    //*** The function to check if the municipality is in Italy ...
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

    //*** Get the data  ...
    function getDataForProntoSoccorso($comune, $dist)
     {
       $url = 'http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality='.$comune.'&distance='.$dist;

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

       //echo "JSON = ".$data;
 			 //echo "<br>";
 			 //echo "<br>";

       return $data;
     }


     //*** Print the data  ...
     function printDataPS($data)
      {
        //#Convert to string (json) ...
  			$json = json_decode($data, true);

  			$emptyData = TRUE;
        echo "<div id='infodiv'>";
  			foreach ($json['prontoSoccorsi'] as $ps) {
              $emptyData = FALSE;
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
              $url = str_replace('&', '&amp;', $ps['url_website']);
              echo "Sito web: <a href=\"".$url."\" target=\"newpage\">".$url."</a>";
  						echo "<br>";
  						echo "<br>";

              echo "<span STYLE='height:15px;width:110px;display:block;overflow:auto;background:#FFFFFF'><b>CODICE BIANCO</b></span>";
  						echo "In attesa = <b>".$ps['numeri_bianco_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_bianco_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_bianco_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_bianco_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

              echo "<span STYLE='height:15px;width:110px;display:block;overflow:auto;background:#008000'><b>CODICE VERDE</b></span>";
  						echo "In attesa = <b>".$ps['numeri_verde_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_verde_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_verde_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_verde_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

              echo "<span STYLE='height:15px;width:110px;display:block;overflow:auto;background:#FFFF00'><b>CODICE GIALLO</b></span>";
  						echo "In attesa = <b>".$ps['numeri_giallo_attesa']."</b>";
  						echo "<br>";
  						echo "Tempo stimato attesa = <b>".$ps['tempi_giallo_attesa']."</b>";
  						echo "<br>";
  						echo "In visita = <b>".$ps['numeri_giallo_in_visita']."</b>";
  						echo "<br>";
  						echo "Tempo stimato visita = <b>".$ps['tempi_giallo_in_visita']."</b>";
  						echo "<br>";
  						echo "<br>";

              echo "<span STYLE='height:15px;width:110px;display:block;overflow:auto;background:#FF0000'><b>CODICE ROSSO</b></span>";
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

        if ($emptyData == TRUE) {
              echo "Per il comune di interesse non è stato possibile individuare alcun Pronto Soccorso che fornisca i dati di attesa";
        }
        echo "</div>";
        echo "<br>";

        $comune = '';
        $dist = '';

      }

  ?>
 </body>
</html>
