<?php
  # ***************************************************************************************************
  # *** Open Pronto Soccorsi - Web Mapping
  # *** Description: Simple web app HTML / PHP: Given a municipality name search the Italian Emergency Rooms waiting list numbers
  # ***              for each code (white, green, yellow and red ones)
  # ***        Note: This web app manages NOT all Italian Emergency Rooms locations but the only ones for which
  # ***              the waiting list numbers for each code (white, green, yellow and red ones) are available
  # ***              in open data (services) or in some HTML web portal pages
  # ***      Author: Cesare Gerbino
  # ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
  # ***     License: MIT (https://opensource.org/licenses/MIT)
  # ***************************************************************************************************

  session_start();
  $_SESSION['page1']=1;
?>

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
             font-size: 14px;
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
       del nome del comune e, al più, di un raggio di interesse nell'intorno del comune stesso.<br>
       Non tutti i pronto soccorsi italiani sono individuati, ma solo quelli per cui risultino essere disponibili, in open data o come sito web, le
       informazioni sulle attese (numeri e tempi).<br>
       Questa applicazione è stata realizzata a titolo sperimentale  da Cesare Gerbino (cesare.gerbino@gmail.com)
       &nbsp;-&nbsp;
       Il codice sorgente  <a href="https://github.com/cesaregerbino/OpenProntoSoccorsi" target="github">è disponibile su GitHub</a>
       &nbsp;-&nbsp;
       Licenza: <a href="https://opensource.org/licenses/MIT" target="licenza">MIT</a>
       &nbsp;-&nbsp;
       Maggiori <a href="https://cesaregerbino.wordpress.com/2018/07/24/open-pronto-soccorsi-rendere-disponibili-ed-accessibili-i-dati-di-numeri-e-tempi-di-attesa-per-tipologia-dei-pronto-soccorsi-italiani/" target="link">dettagli</a>
   </div>

  <br>

  <!-- *** The form for user input -->
  <form name="test" id="infodiv" action="OpenProntoSoccorsiCalculate.php" method="post">
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
</body>
</html>
