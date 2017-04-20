

<!DOCTYPE html>
<html>
<head>
  <title>RenderRoute</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <?php

  /**
   * Render the route to the fuel station on a map
   * Author: Cesare Gerbino code in https://github.com/cesaregerbino/?????
  */



    //# Get the lat_form ...
    $lat_from = $_GET['lat_from'];
    //#echo 'Lat_from = '.$lat_from;
    //#echo '<br>';

    //# Get the lon_from ...
    $lon_from = $_GET['lon_from'];
    //#echo 'Lon_from = '.$lon_from;
    //#echo '<br>';

    //# Get the lat_to ...
    $lat_to = $_GET['lat_to'];
    //#echo 'Lat_to = '.$lat_to;
    //#echo '<br>';

    //# Get the lon_to ...
    $lon_to = $_GET['lon_to'];
    //#echo 'Lon_to = '.$lon_to;
    //#echo '<br>';

    //# Get the map type ...
    $map_type = $_GET['map_type'];
    //#echo 'map_type = '.$map_type;
    //#echo '<br>';

    echo '<br>';

    $url = 'http://open.mapquestapi.com/directions/v2/route?key=sIGCXEck88pGXnH3ARGtdd4iBGGidSGw&outFormat=json&routeType=fastest&timeType=1&narrativeType=html&enhancedNarrative=true&shapeFormat=raw&generalize=0&locale=it_IT&unit=k&from='.$lat_from.','.$lon_from.'&to='.$lat_to.','.$lon_to.'&drivingStyle=2&highwayEfficiency=21.0';

    #print $url;
    #echo '<br>';
    #echo '<br>';

    //#Set CURL parameters: pay attention to the PROXY config !!!!
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    //curl_setopt($ch, CURLOPT_PROXY, 'proxy.csi.it:3128');
    curl_setopt($ch, CURLOPT_PROXY, '');
    $data = curl_exec($ch);
    curl_close($ch);

    //print $data;
    //echo '<br>';
    //echo '<br>';

    //#Convert to string (json) the route ...
    $json = json_decode($data);

    if ($map_type==0) {
      //#Prepare the path description ...
      print '<table><tr><th>Immagine</th><th>Descrizione</th><th>Distanza</th></tr>';

      $maneuvers = $json->route->legs[0]->maneuvers;

      foreach ($maneuvers as $maneuver) {
        print '<tr>';
        print '<td>';
        print '<img src="'.$maneuver->iconUrl.'">';
        print '</td>';
        print '<td>';
        print $maneuver->narrative;
        print '</td>';
        print '<td>';
        print $maneuver->distance;
        print '</td>';
      }
    } else if ($map_type==2) {
  ?>
    <!-- //# Set the styles for the map page ...-->
     <style>
       html, body {
         margin: 0;
         padding: 0;
         width: 100%;
         height: 100%;
       }

       #map {
         width: 100%;
         height: 100%;
       }
     </style>
     <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css" />

     <!-- //# Add the OSM Buildings library ...-->
     <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    </head>
    <body>
     <div id="map"></div>
      <script>

       //# !!!! NOTABLE !!!: not the best solution but it's working. Share the PHP json object with Javascript !!!!!
       var json_route = <?php echo json_encode($json); ?>;

       //# Calculate the new map center based on the route bounding box ...
       lng_ul = json_route.route.boundingBox.ul.lng;
       lat_ul = json_route.route.boundingBox.ul.lat;
       lng_lr = json_route.route.boundingBox.lr.lng;
       lat_lr = json_route.route.boundingBox.lr.lat;
       lng_center = (lng_lr - lng_ul)/2 + lng_ul;
       lat_center = (lat_ul - lat_lr)/2 + lat_lr;


       //var map = L.map('map').setView([39.74739, -105], 13);
       var map = L.map('map').setView([lat_center, lng_center], 15);


// http://localhost/OpenProntoSoccorso/TelegramBot/RenderRoute.php?lat_from=44.399411&lon_from=8.953746&lat_to=44.408688700006&lon_to=8.9753667&map_type=2

       //L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpandmbXliNDBjZWd2M2x6bDk3c2ZtOTkifQ._QA7i5Mpkd_m30IGElHziw', {
       L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiY2VzYXJlIiwiYSI6Im1LdmxtRU0ifQ.uoGK9BB9eywCPknCRlB9JA', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
				'Imagery © <a href="http://mapbox.com">Mapbox</a>',
			id: 'mapbox.emerald'
       }).addTo(map);

       the_coords = '';

       for (i = 0; i < json_route.route.shape.shapePoints.length; i++) {
         if (i == 0) {
           the_coords = the_coords + '[' + json_route.route.shape.shapePoints[i+1] + ',' + json_route.route.shape.shapePoints[i] + ']';
         }
         else {
           if ((i % 2) == 0) {
              the_coords = the_coords + ',[' + json_route.route.shape.shapePoints[i+1] + ',' + json_route.route.shape.shapePoints[i] + ']';
           }
         }
       }

       //# Create the lineString json (text) of the route ...
       var my_lineString = '{\"type\": \"Feature\", \"properties\": {}, \"geometry\": {\"type\": \"LineString\", \"coordinates\": [' + the_coords + ']}}';

       //# Create le lineString json (object) of the route ...
       var lineString = JSON.parse(my_lineString);

       //# add the lineString json (object) of the route to the map ...
       L.geoJson(lineString).addTo(map);

      </script>
  <?php
    } else if ($map_type==3) {
  ?>
    <!-- //# Set the styles for the map page ...-->
    <style>
      html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
      }

      #map {
        width: 100%;
        height: 100%;
      }

      .control {
        position: absolute;
        left: 0;
        z-index: 1000;
      }

      .control.tilt {
        top: 0;
      }

      .control.rotation {
        top: 45px;
      }

      .control.zoom {
        top: 90px;
      }

      .control.zoom button{
        font-weight: normal;
      }

      .control button {
        width: 30px;
        height: 30px;
        margin: 15px 0 0 15px;
        border: 1px solid #999999;
        background: #ffffff;
        opacity: 0.6;
        border-radius: 5px;
        box-shadow: 0 0 5px #666666;
        font-weight: bold;
        text-align: center;
      }

      .control button:hover {
        opacity: 1;
        cursor: pointer;
      }
    </style>
    <link rel="stylesheet" href="OSMBuildings/OSMBuildings.css">

    <!-- //# Add the OSM Buildings library ...-->
    <script src="OSMBuildings/OSMBuildings.js"></script>

    <!-- //# Add the Turf library ...-->
    <script src='https://api.tiles.mapbox.com/mapbox.js/plugins/turf/v2.0.2/turf.min.js'></script>
  </head>

  <body>
    <div id="map"></div>

    <div class="control tilt">
      <button class="dec">&#8601;</button>
      <button class="inc">&#8599;</button>
    </div>

    <div class="control rotation">
      <button class="inc">&#8630;</button>
      <button class="dec">&#8631;</button>
    </div>

    <div class="control zoom">
      <button class="dec">-</button>
      <button class="inc">+</button>
    </div>

    <div id="start" style="width:10px;height:10px;position:absolute;z-Index:10;border:0px solid red;"><img src="start.gif" alt="Start" ></div>

    <div id="stop" style="width:10px;height:10px;position:absolute;z-Index:10;border:0px solid red;"><img src="end.gif" alt="Stop" ></div>

    <script>
      //# !!!! NOTABLE !!!: not the best solution but it's working. Share the PHP json object with Javascript !!!!!
      var json_route = <?php echo json_encode($json); ?>;

      //# Calculate the new map center based on the route bounding box ...
      lng_ul = json_route.route.boundingBox.ul.lng;
      lat_ul = json_route.route.boundingBox.ul.lat;
      lng_lr = json_route.route.boundingBox.lr.lng;
      lat_lr = json_route.route.boundingBox.lr.lat;
      lng_center = (lng_lr - lng_ul)/2 + lng_ul;
      lat_center = (lat_ul - lat_lr)/2 + lat_lr;

      //# Create the map ...
      var map = new GLMap('map', {
        position: { latitude:lat_center, longitude:lng_center },
        zoom: 16,
        tilt: 30,
        minZoom: 12,
        maxZoom: 20,
        state: true // stores map position/rotation in url
      });

      //# Create the OSM Building layer ...
      var osmb = new OSMBuildings({
        baseURL: './OSMBuildings',
        minZoom: 15,
        maxZoom: 22,
        attribution: 'Ã‚Â© 3D <a href="http://osmbuildings.org/copyright/">OSM Buildings</a>'
      }).addTo(map);

      //# Add the OMS basemap (MapQuest)  ...
      osmb.addMapTiles(
        'http://{s}.tiles.mapbox.com/v3/osmbuildings.kbpalbpk/{z}/{x}/{y}.png',
        {
        attribution: 'Ã‚Â© Data <a href="http://openstreetmap.org/copyright/">OpenStreetMap</a> Ã‚Â· Ã‚Â© Map <a href="http://mapbox.com">MapBox</a>'
        }
      );

      osmb.addGeoJSONTiles('http://{s}.data.osmbuildings.org/0.2/anonymous/tile/{z}/{x}/{y}.json');

      //***************************************************************************

      map.on('pointermove', function(e) {
        var id = osmb.getTarget(e.x, e.y);
        if (id) {
          document.body.style.cursor = 'pointer';
          osmb.highlight(id, '#f08000');
        } else {
          document.body.style.cursor = 'default';
          osmb.highlight(null);
        }
      });

      //***************************************************************************

      var controlButtons = document.querySelectorAll('.control button');

      for (var i = 0, il = controlButtons.length; i < il; i++) {
        controlButtons[i].addEventListener('click', function(e) {
          var button = this;
          var parentClassList = button.parentNode.classList;
          var direction = button.classList.contains('inc') ? 1 : -1;
          var increment;
          var property;

          if (parentClassList.contains('tilt')) {
            property = 'Tilt';
            increment = direction*10;
          }
          if (parentClassList.contains('rotation')) {
            property = 'Rotation';
            increment = direction*10;
          }
          if (parentClassList.contains('zoom')) {
            property = 'Zoom';
            increment = direction*1;
          }
          if (property) {
            map['set'+ property](map['get'+ property]()+increment);
          }
        });
      }

  //***************************************************************************

      var start = document.getElementById('start');
      var start_lon = <?php echo json_encode($lon_from); ?>;
      var start_lat = <?php echo json_encode($lat_from); ?>;

      var stop = document.getElementById('stop');
      var stop_lon = <?php echo json_encode($lon_to); ?>;
      var stop_lat = <?php echo json_encode($lat_to); ?>;

      map.on('change', function() {
        var pos = osmb.project(start_lat, start_lon, 15);
        start.style.left = Math.round(pos.x) + 'px';
        start.style.top = Math.round(pos.y) + 'px';

        var pos = osmb.project(stop_lat, stop_lon, 15);
        stop.style.left = Math.round(pos.x) + 'px';
        stop.style.top = Math.round(pos.y) + 'px';
      });

    </script>

    <script language="javascript">

      //# Extract the route coordinates  ...
      the_coords = '';

      for (i = 0; i < json_route.route.shape.shapePoints.length; i++) {
        if (i == 0) {
           the_coords = the_coords + '[' + json_route.route.shape.shapePoints[i+1] + ',' + json_route.route.shape.shapePoints[i] + ']';
        }
        else {
           if ((i % 2) == 0) {
              the_coords = the_coords + ',[' + json_route.route.shape.shapePoints[i+1] + ',' + json_route.route.shape.shapePoints[i] + ']';
           }
        }
      }

      //# Create the lineString json (text) of the route ...
      var my_lineString = '{\"type\": \"Feature\", \"properties\": {}, \"geometry\": {\"type\": \"LineString\", \"coordinates\": [' + the_coords + ']}}';

      //# Create le lineString json (object) of the route ...
      var lineString = JSON.parse(my_lineString);

      //# Set the unit measure and calculate the buffer using Turf.js ....
      var unit = 'meters';
      var buffered = turf.buffer(lineString, 1, unit);
      var result = turf.featurecollection([buffered]);

      //# Convert to string the buffered json ....
      var my_json = JSON.parse(JSON.stringify(result));

      //# Create an empty Polygon json to put in OSM Building map ....
      var geojson_route = {
        type: 'FeatureCollection',
        features: [{
          type: 'Feature',
          properties: {
            color: '#FF0080',
            roofColor: '#ff99cc',
            height: 0,
            minHeight: 0
          },
          geometry: {
           type: 'Polygon',
           coordinates: [
             [
             ]
           ]
          }
        }]
      };

      //# Add the coordinates at the Polygon json bringing them from the buffered json ....
      for (var i = 0; i < my_json.features[0].features[0].geometry.coordinates[0].length; i++) {
         geojson_route.features[0].geometry.coordinates[0][i] = my_json.features[0].features[0].geometry.coordinates[0][i];
      }

      //# Add the Polygon json to the map ....
      osmb.addGeoJSON(geojson_route);

      //# Refresh of the map to show the labels ...
      map.setPosition(map.getPosition());

    </script>

  <?php
    }

  ?>

</body>
</html>
