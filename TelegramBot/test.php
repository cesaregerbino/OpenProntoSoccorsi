<?php

//SELECT comune FROM comuni32632 WHERE WITHIN(Transform(GeomFromText('POINT(11.611378 45.730675)',4326),32632),comuni32632.Geometry);

$lon = 11.611378;
$lat = 45.730675;

$db_data_sessions = new SQLite3('./DataBase/OpenProntoSoccorso.sqlite');

# Loading SpatiaLite as an extension ...
$db_data_sessions->loadExtension('mod_spatialite.so');

$q="SELECT comune FROM comuni32632 WHERE WITHIN(Transform(GeomFromText('POINT(".$lon." ".$lat.")',4326),32632),comuni32632.Geometry)";
//			$q="SELECT NAME FROM comuni32632 WHERE WITHIN(GeomFromText(MakePoint(".$lon.", ".$lat.", 32632)),comuni32632.Geometry)";

echo "Query = " .$q;
echo "\n\n";

try {
     $stmt = $db_data_sessions->prepare($q);
     $results = $stmt->execute();

     $count = 0;
     while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
       $data .= $row['comune'];
       $count = $count + 1;
     }
     echo "Result = OK - Comune = ".$data." Count = ".$count;
     echo "\n\n";
     
     return $data;
}
catch(PDOException $e) {
        print "Something went wrong or Connection to database failed! ".$e->getMessage();
        //return "ERROR";
        echo "Result = ERROR";
        echo "\n\n";

}
$db_data_sessions = null;

?>
