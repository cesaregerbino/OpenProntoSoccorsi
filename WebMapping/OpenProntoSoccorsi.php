<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title>Open Pronto Soccorsi</title>
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.39.1/mapbox-gl.js'></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.39.1/mapbox-gl.css' rel='stylesheet' />
    <style>
        body { margin:0; padding:0; }
        #map { position:absolute; top:0; bottom:0; width:100%; }
        #infodiv{
                 position:fixed;
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
    <div id='map'></div>

    <!-- *** Get the MapBox API key ... -->
    <?php
      include("settings.php");
      $MapBoxApiKey = API_MAPBOX;
    ?>;

    <script>
      var MapBoxAccessKey = "";

      // *** !!!! NOTABLE !!!: not the best solution but it's working. Share the PHP GraphHopper API key with Javascript !!!!! ...
      // *** Set the MapBox access key ...
      MapBoxAccessKey = <?php echo json_encode($MapBoxApiKey); ?>; //Don't forget the extra semicolon!
      mapboxgl.accessToken = MapBoxAccessKey;

      // *** Create a new MapBox GL JS map ...
      var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/light-v9',
          center: [13.469,41.812],
          zoom: 5
      });

      // *** Load the map and add the geojson Pronto Soccorsi layer ...
      map.on('load', function () {
              map.addSource("route", {
                      "type": "geojson",
                      "data": "./Data/ps-geojson.geojson"
              });

              map.addLayer({
                      "id": "route",
                      "type": "circle",
                      "source": "route",
                      "paint": {
                              "circle-radius": 5,
                              "circle-color": "#ff0000"
                      }
              });
      });

      // *** Add the navigation control ...
      map.addControl(new mapboxgl.NavigationControl());
    </script>
    <div id="infodiv" style="leaflet-popup-content-wrapper">
        I dettagli relativi ai dati originali con le url di pubblicazione, di download, le licenze d''uso e di download dei dati elaborati usati per la creazione della mappa sono <a href="http://www.cesaregerbino.com/ItalyOpenAddresses/List/ItalyOpenAddressesList-V02.csv" target="link">scaricabili qui</a>.<br>
        Maggiori dettagli <a href="https://cesaregerbino.wordpress.com/2015/02/04/numeri-civici-open-data-in-italia-disponibile-la-release-2-0-della-raccolta/" target="link">qui</a>
    </div>
  </body>
</html>
