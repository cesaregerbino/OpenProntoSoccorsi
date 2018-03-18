<?php
  function getArrayMessages($language)
    {
      /*
      -----------------
      Language: Italian
      -----------------
      */
      $langIT = array();
      $langIT['LANG__CHOICE'] = 'Seleziona la lingua ';
      $langIT['MUNICIPALITY'] = 'Il comune identificato è: ';
      $langIT['NO_MUNICIPALITY'] = 'Non è stato possibile individuare un comune italiano in corrispondenza del punto indicato. Si prega di riprovare';
      $langIT['DB_ACCESS_ERROR'] = 'Si è riscontrato un problema sull\'accesso al nostro database. Riprovare più tardi: ci scusiamo per il disguido';
      $langIT['START_MESSAGE'] = 'Benvenuta/o! Quest\'applicazione Le permettera\' di visualizzare le liste di attesa dei primi 5 Pronto Soccorso più vicini nell\'intorno di un punto di Suo interesse o all\'interno dell\'area in un Comune italiano.\n
E\' necessario che mi indichi la Sua posizione corrente o di interesse (controlli che sia attiva la geolocalizzazione sul suo dispositivo ...); in caso contrario non saro\' in grado di individuare la Sua posizione e non potro\' calcolare il percorso per farLe raggiungere il Pronto Soccorso di Suo interesse.\n
Se non puo\', o non vuole, fornire la Sua attuale posizione o di interresse provero\' lo stesso a darLe una indicazione: mi fornisca il nome di un Comune italiano e Le indichero\' i primi 5 Pronto Soccorso più vicini e li potra\' comunque visualizzare su mappa.\n
Per indicare il Comune di interesse e\' sufficiente scriverne il nome.\n
E\' possibile visualizzare questo messaggio in qualsiasi momento scrivendo /start.\n
Le mappe utilizzate sono quelle derivate dai dati di OpenStreetMap (rif. http://www.openstreetmap.org/) e OSMBuildings (rif. http://www.osmbuildings.org/).\n
Il calcolo dei percorsi viene realizzato avvalendosi del servizio di routing di MapQuest (rif. https://developer.mapquest.com/products/directions)\n
L\'abbreviazione delle url viene realizzata avvalendosi del servizio Google URL Shortener (rif. https://goo.gl/)\n
Questo bot e\' stato realizzato a titolo sperimentale  da Cesare Gerbino (cesare.gerbino@gmail.com)\n
Il codice dell\'applicazione è disponibile al seguente url https://github.com/cesaregerbino/OpenProntoSoccorsoBot con licenza Licenza MIT Copyright (c) [2014] (rif. https://it.wikipedia.org/wiki/Licenza_MIT)\n
Per maggiori dettagli http://cesaregerbino.wordpress.com/xxxxxxxxxxxx\n';
      $langIT['SELECTED_LANGUAGE_IT'] = 'La lingua scelta è: ITALIANO. Tutti i prossimi messaggi saranno nella lingua italiana';
      $langIT['SELECTED_LANGUAGE_EN'] = 'La lingua scelta è: INGLESE. Tutti i prossimi messaggi saranno nella lingua inglese';
      $langIT['MUNICIPALITY_NAME'] = ' è CORRETTAMENTE un comune italiano!!';
      $langIT['NO_MUNICIPALITY_NAME'] = ' non è un comune italiano. Provare a digitare correttamente: Il nome del comune deve iniziare con una lettera maiuscola, se è composto da più parole ogni parola deve iniziare con una lettera maiuscola (es. La Loggia è corretto La loggia è errato ... ), se compaiono lettere accentate NON usare l\'apostrofo (es. Agliè è corretto, Aglie\' è errato) ...';
      $langIT['SEARCHING_PS_INFO'] = 'Stò cercando le informazioni sui numeri e tempi di attesa dei Pronto Soccorso per il comune di ';

      /*
      -----------------
      Language: English
      -----------------
      */
      $langEN = array();
      $langEN['LANG__CHOICE'] = 'Select the language ';
      $langEN['MUNICIPALITY'] = 'The identified municipality is: ';
      $langEN['NO_MUNICIPALITY'] = 'There is no Italian municipality at this point. Retry please';
      $langEN['DB_ACCESS_ERROR'] = 'Problems about access to the database. Retry later please';
      $langEN['START_MESSAGE'] = 'Welcome! Here there will be a message in English, I hope !!';
      $langEN['SELECTED_LANGUAGE_IT'] = 'The selected language is: ITALIAN. All the following messagges will be in italian language';
      $langEN['SELECTED_LANGUAGE_EN'] = 'The selected language is: ENGLISH All the following messagges will be in english language';
      $langEN['MUNICIPALITY_NAME'] = ' is a Italian municipality!!';
      $langEN['NO_MUNICIPALITY_NAME'] = ' is not an Italian municipality. Try to digit in right mode: th emunicipality name must to start with a capital character, if the name is composed by several words each one must to start with a capital character (es. La Loggia is right, La loggia is wrong ... ), if there are accented letters NOT to use apostrophe (es. Agliè is right, Aglie\' is wrong) ...';
      $langEN['SEARCHING_PS_INFO'] = 'I\'m searching information on waiting time about Pronto Soccorso for the municipality of ';

      $langCurrent = array();
      if ($language == "IT")
        {
          $langCurrent = $langIT;
        }
      elseif ($language == "EN")
        {
          $langCurrent = $langEN;
        }

      return $langCurrent;

   }
?>
