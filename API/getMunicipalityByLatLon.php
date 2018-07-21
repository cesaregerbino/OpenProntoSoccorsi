<?php
   #***************************************************************************************************
   #*** Open Pronto Soccorsi - get the name of italian muncipality giving Lat Lon
   #*** Description: get the name of italian muncipality giving Lat Lon
   #***
   #***        Note:
   #***
   #***
   #***      Author: Cesare Gerbino
   #***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
   #***     License: MIT (https://opensource.org/licenses/MIT)
   #***************************************************************************************************

   # Get the lat ...
   $lat = $_GET['lat'];

   # Get the lon ...
   $lon = $_GET['lon'];

   $db_data_sessions = new SQLite3('../Data/OpenProntoSoccorsi.sqlite');

   # Loading SpatiaLite as an extension ...
   $db_data_sessions->loadExtension('mod_spatialite.so');

   $q="SELECT comune FROM comuni_From_ISTAT_32632 WHERE WITHIN(Transform(GeomFromText('POINT(".$lon." ".$lat.")',4326),32632),comuni_From_ISTAT_32632.Geometry)";

   $municipalityJson = "{";
   $municipalityJson .= "\"municipality\":\"";

   try {
        $stmt = $db_data_sessions->prepare($q);
        $results = $stmt->execute();

        $city  = '';
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
          $city .= $row['comune'];
        }
        if ($city != '' ) {
          $municipalityJson .= $city;
          $municipalityJson .= "\"";
          $municipalityJson .= "}";
          echo $municipalityJson;
        }
        else {
          $municipalityJson .= "NODATA";
          $municipalityJson .= "\"";
          $municipalityJson .= "}";
          echo $municipalityJson;
        }
   }
   catch(PDOException $e) {
           print "Something went wrong or Connection to database failed! ".$e->getMessage();
           $municipalityJson .= "ERROR";
           $municipalityJson .= "\"";
           $municipalityJson .= "}";
           echo $municipalityJson;
   }
   $db_data_sessions = null;
?>
