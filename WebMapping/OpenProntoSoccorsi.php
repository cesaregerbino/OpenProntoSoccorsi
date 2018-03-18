<!--
 ***************************************************************************************************
 *** Open Pronto Soccorso - Web Mapping based on MapBox GL JS
 *** Description: Create a web map that shows the Italian First Aids locations and the waiting list numbers
 ***              for each code (white, green, yellow and red ones)
 ***        Note: This map shows NOT all Italian First Aids locations but the only ones for which
 ***              the waiting list numbers for each code white, green, yellow and red ones) are available
 ***              in open data (services) or in some HTML web portal pages
 ***      Author: Cesare Gerbino
 ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
 ***     License: MIT (https://opensource.org/licenses/MIT)
 ***************************************************************************************************
-->
<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title>Open Pronto Soccorsi</title>
    <meta name='viewport' content='initial-scale=1,maximum-scale=1,user-scalable=no' />

    <!-- *** References for JQuery ... -->
    <script src="http://code.jquery.com/jquery-3.3.1.min.js"></script>

    <!-- *** References for MapBox  ... -->
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v0.44.0/mapbox-gl.js'></script>
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v0.44.0/mapbox-gl.css' rel='stylesheet' />

    <!-- *** References for MapBox GL Geocoder ... -->
    <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.2.0/mapbox-gl-geocoder.min.js'></script>
    <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v2.2.0/mapbox-gl-geocoder.css' type='text/css' />

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
        .mapboxgl-popup {
            max-width: 400px;
            font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
        }
        tr th{
        	font-weight:bold;
            }
        tr th, tr td{
        	padding:5px;
        }
        th{
            border: 5px solid #C1DAD7;
        }
        td{
        	border: 5px solid #C1DAD7;
        }
        .c1{
        	background:#ffffff;
        }
        .c2{
        	background:#008000;
        }
        .c3{
        	background:#ffff00;
        }
        .c4{
        	background:#ff0000;
        }
        #menu {
             background: #fff;
             position: absolute;
             z-index: 1;
             top: 160px;
             right: 10px;
             border-radius: 1px;
             width: 120px;
             border: 1px solid rgba(0,0,0,0.4);
             font-family: 'Open Sans', sans-serif;
         }
         #menu a {
             font-size: 13px;
             color: #404040;
             display: block;
             margin: 0;
             padding: 0;
             padding: 10px;
             text-decoration: none;
             border-bottom: 1px solid rgba(0,0,0,0.25);
             text-align: center;
         }
         #menu a:last-child {
             border: none;
         }
         #menu a:hover {
             background-color: #f8f8f8;
             color: #404040;
         }
         #menu a.active {
             background-color: #3887be;
             color: #ffffff;
         }
         #menu a.active:hover {
             background: #3074a4;
         }
    </style>
  </head>
  <body>
    <nav id="menu"></nav>
    <div id='map'></div>

    <!-- *** Get the MapBox API key ... -->
    <?php
      include("settings.php");
      $MapBoxApiKey = API_MAPBOX;
    ?>;

    <script>
      var MapBoxAccessKey = "";
      var osm_id;
      var coords;
      var detailsStringHTML;
      var ps_name;
      var ps_url;

      // *** !!!! NOTABLE !!!: not the best solution but it's working. Share the MapBpox API key with Javascript !!!!! ...
      // *** Set the MapBox access key ...
      MapBoxAccessKey = <?php echo json_encode($MapBoxApiKey); ?>; //Don't forget the extra semicolon!
      mapboxgl.accessToken = MapBoxAccessKey;

      // *** Create a new MapBox GL JS map ...
      var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/outdoors-v9',
          center: [13.469,41.812],
          zoom: 5
      });

      // *** Load the map and add the geojson Pronto Soccorsi layer ...
      map.on('load', function () {
              map.addSource("ps", {
                      "type": "geojson",
                      "data": "./Data/ps-geojson.geojson"
              });

              map.addLayer({
                      "id": "Pronto Soccorsi",
                      "type": "circle",
                      "source": "ps",
                      'layout': {
                          'visibility': 'visible'
                       },
                      "paint": {
                              "circle-radius": 5,
                              "circle-color": "#ff0000"
                      }
              });

              // Insert the layer beneath any symbol layer.
              var layers = map.getStyle().layers;

              var labelLayerId;
              for (var i = 0; i < layers.length; i++) {
                  if (layers[i].type === 'symbol' && layers[i].layout['text-field']) {
                      labelLayerId = layers[i].id;
                      break;
                  }
              }

              map.addLayer({
                  'id': 'Edifici 3D',
                  'source': 'composite',
                  'source-layer': 'building',
                  'filter': ['==', 'extrude', 'true'],
                  'type': 'fill-extrusion',
                  'minzoom': 15,
                  'layout': {
                      'visibility': 'visible'
                   },
                  'paint': {
                      'fill-extrusion-color': '#aaa',

                      // use an 'interpolate' expression to add a smooth transition effect to the
                      // buildings as the user zooms in
                      'fill-extrusion-height': [
                          "interpolate", ["linear"], ["zoom"],
                          15, 0,
                          15.05, ["get", "height"]
                      ],
                      'fill-extrusion-base': [
                          "interpolate", ["linear"], ["zoom"],
                          15, 0,
                          15.05, ["get", "min_height"]
                      ],
                      'fill-extrusion-opacity': .6
                  }
              }, labelLayerId);

      });

      // *** Add the geocoder control ...
      map.addControl(new MapboxGeocoder({
          accessToken: mapboxgl.accessToken,
          position: "top-right"
      }));

      // *** Add the navigation control ...
      map.addControl(new mapboxgl.NavigationControl());


      // *** The layers menu ...
      var toggleableLayerIds = [ 'Pronto Soccorsi', 'Edifici 3D' ];
      for (var i = 0; i < toggleableLayerIds.length; i++) {
          var id = toggleableLayerIds[i];

          var link = document.createElement('a');
          link.href = '#';
          link.className = 'active';
          link.textContent = id;

          link.onclick = function (e) {
              var clickedLayer = this.textContent;
              e.preventDefault();
              e.stopPropagation();

              var visibility = map.getLayoutProperty(clickedLayer, 'visibility');

              if (visibility === 'visible') {
                  map.setLayoutProperty(clickedLayer, 'visibility', 'none');
                  this.className = '';
              } else {
                  this.className = 'active';
                  map.setLayoutProperty(clickedLayer, 'visibility', 'visible');
              }
          };

          var layers = document.getElementById('menu');
          layers.appendChild(link);
      }

      // When a click event occurs on a feature in the places layer, open a popup at the
      // location of the feature, with description HTML from its properties.
      map.on('click', 'Pronto Soccorsi', function (e) {
          // Get the osm_id and coords information about the point ...
          osm_id = e.features[0].properties.osm_id;
          coords = e.features[0].geometry.coordinates;
          ps_name = e.features[0].properties.Nome;
          ps_url = e.features[0].properties.Url;

          // Get the first aid details ...
          getPsDetails(e.features[0].properties.Citta);
      });

      // Change the cursor to a pointer when the mouse is over the places layer.
      map.on('mouseenter', 'Pronto Soccorsi', function () {
          map.getCanvas().style.cursor = 'pointer';
      });

      // Change it back to a pointer when it leaves.
      map.on('mouseleave', 'Pronto Soccorsi', function () {
          map.getCanvas().style.cursor = '';
      });

      // Get the First Aid details ...
      function getPsDetails(Citta) {
        var responseStringHTML;

        $.ajax({
          url: "http://localhost/OpenProntoSoccorsi/API/getProntoSoccorsoDetailsByMunicipality.php",
          method: "GET",
          data: {municipality: Citta, distance:0}
        })
        .done(function(output) {
          try {
            // handle success response
            var outputJSON = JSON.parse(output);
            var responseStringHTML = "";

            for(var i = 0; i < outputJSON.prontoSoccorsi.length; ++i) {
                if (outputJSON.prontoSoccorsi[i].osm_id == osm_id){
                  responseStringHTML = "<b>" + outputJSON.prontoSoccorsi[i].ps_name + "</b> <br>";
                  responseStringHTML = responseStringHTML +  "<b>Comune: " + outputJSON.Comune + "</b> <br>";
                  responseStringHTML = responseStringHTML +  "<center>";
                  responseStringHTML = responseStringHTML +  " <table>";

                  responseStringHTML = responseStringHTML +  " <tr>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c1\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  "    In attesa";
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c1\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_bianco_attesa;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c2\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_verde_attesa;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c3\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_giallo_attesa;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c4\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_rosso_attesa;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  " </tr>";

                  responseStringHTML = responseStringHTML +  " <tr>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c1\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  "    In visita";
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c1\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_bianco_in_visita;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c2\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_verde_in_visita;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c3\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_giallo_in_visita;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  "  <td class=\"c4\">";
                  responseStringHTML = responseStringHTML +  "   <center>";
                  responseStringHTML = responseStringHTML +  outputJSON.prontoSoccorsi[i].numeri_rosso_in_visita;
                  responseStringHTML = responseStringHTML +  "   </center>";
                  responseStringHTML = responseStringHTML +  "  </td>";
                  responseStringHTML = responseStringHTML +  " </tr>";

                  responseStringHTML = responseStringHTML +  "</table>";
                  responseStringHTML = responseStringHTML +  "</center>";

                  responseStringHTML = responseStringHTML +  "Verificare rispetto alla <a href=\"" + ps_url + "\" target=\"new\">fonte dati</a> di riferimento";

                  // Create a new pop-up  the first aid details ...
                  new mapboxgl.Popup()
                      .setLngLat(coords)
                      .setHTML(responseStringHTML)
                      .addTo(map);
                }
            }
            if (responseStringHTML == "") {
              responseStringHTML = "Non ci sono dati disponibili per: " + ps_name + "</br>";
              responseStringHTML = responseStringHTML + "Verificare rispetto alla <a href=\"" + ps_url + "\" target=\"new\">fonte dati</a> di riferimento";

              // Create a new pop-up  the first aid details ...
              new mapboxgl.Popup()
                  .setLngLat(coords)
                  .setHTML(responseStringHTML)
                  .addTo(map);
            }
          }
          catch(err) {
            responseStringHTML = "Mi spiace ma non e' stato possibile recuperare i dati.<br> Dettagli: probabile errore nella parsificazione del JSON ...<br>";
            responseStringHTML = responseStringHTML + "Verificare rispetto alla <a href=\"" + ps_url + "\" target=\"new\">fonte dati</a> di riferimento";

            new mapboxgl.Popup()
                .setLngLat(coords)
                .setHTML(responseStringHTML)
                .addTo(map);
          }
        })
        .fail(function() {
          // handle error response
          responseStringHTML = "Mi spiace ma non e' stato possibile recuperare i dati.\n Dettagli: probabile errore nella richiesta alla fonte dati ...<br>";
          responseStringHTML = responseStringHTML + "Verificare rispetto alla <a href=\"" + ps_url + "\" target=\"new\">fonte dati</a> di riferimento";

          new mapboxgl.Popup()
              .setLngLat(coords)
              .setHTML(responseStringHTML)
              .addTo(map);

        })
      }
    </script>

    <div id="infodiv" style="leaflet-popup-content-wrapper">
      Questa applicazione permette di fornire le informazioni sulle attese (numeri e tempi) dei Pronto Soccorsi italiani.<br>
      Non tutti i pronto soccorsi italiani sono individuati ma solo quelli per cui risultino essere disponibili, in open data o come sito web, le
      informazioni sulle attese (numeri e tempi)<br>
      Questa applicazione è stata realizzata a titolo sperimentale  da Cesare Gerbino (cesare.gerbino@gmail.com)
      &nbsp;-&nbsp;
      Il codice sorgente  <a href="https://github.com/cesaregerbino/OpenProntoSoccorsi" target="github">è disponibile su GitHub</a>
      &nbsp;-&nbsp;
      Licenza: <a href="https://opensource.org/licenses/MIT" target="licenza">MIT</a>
      &nbsp;-&nbsp;
      Maggiori <a href="https://cesaregerbino.wordpress.com/2015/02/04/numeri-civici-open-data-in-italia-disponibile-la-release-2-0-della-raccolta/" target="link">dettagli</a>
    </div>

  </body>
</html>
