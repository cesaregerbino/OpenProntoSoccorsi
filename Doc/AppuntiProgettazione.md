# ELENCO E STRUTTURA DELLE TABELLE NECESSARIE  #


## Layer spaziale dei comuni d'Italia: comuni-ISTAT-32632 ##

#### Fonte dati ####
La fonte dati è ISTAT: https://www.istat.it/it/archivio/124086 (e precisamente lo scarico dei dati in WGS84 UTM32N quindi http://www.istat.it/storage/cartografia/confini_amministrativi/non_generalizzati/2016/Limiti_2016_WGS84.zip ).


#### Modalità di scarico ####
Si può scaricare con una ``wget`` nel seguente modo

``wget   http://www.istat.it/storage/cartografia/confini_amministrativi/non_generalizzati/2016/Limiti_2016_WGS84.zip``

NOTA: ricordarsi che, se si è dietro ad un proxy occorre indicarlo, quindi l'istruzione diventa

``wget -e use_proxy=yes -e http_proxy=<proxy>:<porta> http://www.istat.it/storage/cartografia/confini_amministrativi/non_generalizzati/2016/Limiti_2016_WGS84.zip``

oppure con ``curl`` nel seguente modo

``curl -o Limiti_2016_WGS84.zip http://www.istat.it/storage/cartografia/confini_amministrativi/non_generalizzati/2016/Limiti_2016_WGS84.zip``

NOTA: ricordarsi che, se si è dietro ad un proxy occorre indicarlo, quindi l'istruzione diventa

``curl --proxy <proxy>:<porta> -o Limiti_2016_WGS84.zip http://www.istat.it/storage/cartografia/confini_amministrativi/non_generalizzati/2016/Limiti_2016_WGS84.zip``

#### Struttura ####
Si mantiene quella standard dell'ISTAT.

I campi che interessano principalmente sono:
* COMUNE: toponimo del Comune
* PRO_COM: codice ISTAT del Comune

#### Ulteriori passi ####
Occorre trasformare i dati da EPSG4326 in EPSG32632 e quindi:

* ``unzip Limiti_2016_WGS84.zip``
* ``ogr2ogr -t_srs EPSG:32632 comuni_From_ISTAT_32632.shp Limiti_2016_WGS84/Com2016_WGS84/Com2016_WGS84.shp``

E' quindi possibile eliminare files e directories temporanee:

* ``rm Limiti_2016_WGS84.zip``
* ``rm -R Limiti_2016_WGS84``




## Layer spaziale dei Pronto Soccorso d'Italia ##

#### Fonte dati ####
Questo dato NON esiste in modalità "open": alcuni dati sono su OSM ma non tutti quelli che servono.

Occorre recuperare i dati dai servizi / portali e poi aggiornare i dati su OSM: valutare se e quali info aggiuntive aggiungere
(indirizzo, telefono, ecc .. ). Attenzione al fatto che queste info possono diventare vecchie quindi forse è meglio di no

#### Modalità di scarico ####
I dati si recuperano con questa query Overpass Turbo (rif. https://overpass-turbo.eu/):

```
[out:json][timeout:250];
// fetch area “Italia” to search in
{{geocodeArea:Italia}}->.searchArea;
// gather results
(
  // query part for: “highway=motorway”
  //way["highway"="motorway"]["ref"="A10"](area.searchArea);
  //relation["highway"="motorway"]["ref"="A10"](area.searchArea);
  node["amenity"="hospital"]["emergency"="yes"](area.searchArea);
);
// print results
out body;
>;
out skel qt;
```

Per eseguire la query da linea di comando è necessario, sempre da Overpass Turbo (rif. https://overpass-turbo.eu/), esportare la query come Overpass XML.

Si ottiene questo:

```
<osm-script output="json" output-config="" timeout="250">
  <id-query into="searchArea" ref="3600365331" type="area"/>
  <union into="_">
    <query into="_" type="node">
      <has-kv k="amenity" modv="" v="hospital"/>
      <has-kv k="emergency" modv="" v="yes"/>
      <area-query from="searchArea" into="_" ref=""/>
    </query>
  </union>
  <print e="" from="_" geometry="skeleton" limit="" mode="body" n="" order="id" s="" w=""/>
  <recurse from="_" into="_" type="down"/>
  <print e="" from="_" geometry="skeleton" limit="" mode="skeleton" n="" order="quadtile" s="" w=""/>
</osm-script>
```
E' necessario modificare il formato di output da `json` a `xml` in quanto risulterà più facile la successiva conversione automatica in ESRI shapefile e quindi il file è da trasformare in

```
<osm-script output="xml" output-config="" timeout="250">
  <id-query into="searchArea" ref="3600365331" type="area"/>
  <union into="_">
    <query into="_" type="node">
      <has-kv k="amenity" modv="" v="hospital"/>
      <has-kv k="emergency" modv="" v="yes"/>
      <area-query from="searchArea" into="_" ref=""/>
    </query>
  </union>
  <print e="" from="_" geometry="skeleton" limit="" mode="body" n="" order="id" s="" w=""/>
  <recurse from="_" into="_" type="down"/>
  <print e="" from="_" geometry="skeleton" limit="" mode="skeleton" n="" order="quadtile" s="" w=""/>
</osm-script>
```

L'esecuzione della query da linea di comando è la seguente:

``wget --post-file=ps_From_OSM.txt http://overpass-api.de/api/interpreter --output-document=ps_From_OSM.osm``

NOTA: ricordarsi che, se si è dietro ad un proxy occorre indicarlo, quindi l'istruzione diventa

``wget -e use_proxy=yes -e http_proxy=<proxy>:porta> --post-file=ps_from_OSM.txt http://overpass-api.de/api/interpreter --output-document=ps_From_OSM.osm``

#### Struttura ####
Si mantiene quella standard di OSM.

#### Ulteriori passi ####
Per convertire il file .osm in ESRI shapefile

``ogr2ogr -nlt POINT -skipfailures TMP_DIR ps_From_OSM.osm``

Occorre trasformare i dati da EPSG4326 in EPSG32632 e quindi:

``ogr2ogr -t_srs EPSG:32632 ps_From_OSM_32632.shp TMP_DIR/points.shp``

E' quindi possibile eliminare files e directories temporanee:

* ``rm ps_From_OSM.osm``
* ``rm -R TMP_DIR``

A questo punto è possibile caricare i dati in Spatialite: il caricamento avviene manualmente usando Spatialite GUI (versione dev)

#### Calcolo delle coordinate X e Y per i Pronto Soccorsi ####
Una volta caricati i dati dei pronto soccorsi in Spatialite per calcolare le coordinate X e Y dei Pronto Soccorsi è possibile usare Spatialite nel seguente modo.

ALTER TABLE 'ps_From_OSM_32632' ADD COLUMN X REAL;
ALTER TABLE 'ps_From_OSM_32632' ADD COLUMN Y REAL;
UPDATE 'ps_From_OSM_32632' SET X=ST_X("Geometry") , Y=ST_Y("Geometry");

#### Calcolo delle coordinate Lat e Lon per i Pronto Soccorsi ####
Per calcolare le coordinate Lat e Lon dei Pronto Soccorsi è possibile usare Spatialite nel seguente modo.

ALTER TABLE 'ps_From_OSM_32632' ADD COLUMN LON double;
ALTER TABLE 'ps_From_OSM_32632' ADD COLUMN LAT double;
UPDATE 'ps_From_OSM_32632' SET LON=ST_X(ST_Transform(geometry,4326));
UPDATE 'ps_From_OSM_32632' SET LAT=ST_Y(ST_Transform(geometry,4326));



## Quali sono i pronto soccorso più "vicini" ad ogni comune d'Italia ##

Questo dato và calcolato preventivamente a livello nazionale perchè calcolarlo "dinamicamente" è troppo oneroso.

Per "vicini" si intende i PS che ricadono "dentro" il comune e anche quelli più "vicini" all'area del comune.

La query spaziale da applicare in Spatialite è stata fornita da Furieri sulla lista pubblica di Spatialite (rif. https://groups.google.com/forum/#!topic/spatialite-users/Hnpe_GdA6Pg), e poi in forma "privata" in quanto è necessario avere una nuova versione di Spatialite ancora in sviluppo che
mi è stata fornita.

Ovviamente occorre prima di tutto caricare in Spatialite i layer dei comuni (comuni-From-ISTAT-32632) e dei pronto soccorso (ps-From-OSM-32632) creati in precedenza: questa operazione si può effettuare manualmente usando Spatialite GUI di dev fornita.

ATTENZIONE: quando si importano i dati dagli shapefiles ricordarsi di impostare esplicitamente il sistema di riferimento EPSG 32632 (indicare "32632" come numero)

Successivamente occorre dare i seguenti comandi:

#### Step 1 ####
```
CREATE TABLE dist_com_ps_1 AS
 SELECT pg.ROWID AS pg_rowid,
        pg.COMUNE AS pg_COMUNE,
        pt.ROWID AS pt_rowid,
        pt.OSM_ID as pt_osm_id,
        pt.NAME AS pt_NAME,
        pt.X AS pt_X,
        pt.Y AS pt_Y,
        pt.LON AS pt_LON,
        pt.LAT AS pt_LAT,
        ST_Distance(pg.geometry, pt.geometry) AS dist
 FROM comuni_From_ISTAT_32632 AS pg, ps_From_OSM_32632 AS pt
 ORDER BY pg_rowid, dist;
```

#### Step 2 ####
```
CREATE TABLE dist_com_ps_2 AS
 SELECT pg_rowid,
        pg_COMUNE,
        sequence_nextval('seq' || pg_rowid) AS seqno,
        pt_rowid,
        pt_osm_id,
        pt_NAME,
        pt_X,
        pt_Y,
        pt_LON,
        pt_LAT,
        dist
 FROM dist_com_ps_1;
```

#### Step 3 ####
```
DELETE FROM dist_com_ps_2 WHERE seqno > 10;
```

#### Step 4 ####
Creazione dell'indice per velocizzare la query
```
create index idx_dist_com_ps_1_pg_COMUNE_dist on dist_com_ps_1(pg_COMUNE, dist);
```

#### Varie ####
Alternative sono state suggerite per POSTGRESQL (NON provate ... ), in seguito ad una richiesta su
StackExchange (lista GIS - http://gis.stackexchange.com/questions/229505/the-first-n-points-near-a-polygon-spatialite-or-postgis-query)

#### Struttura della tabella dist_com_ps_2 ####
La struttura della tabella dist_com_ps_2 è la seguente:

* pg_rowid: id del comune (come da ISTAT)
* pg_COMUNE: nome del comune (come da ISTAT)
* seqno: progressivo che misura la distanza del pronto soccorso dal comune
* pt_rowid: id del pronto soccorso (come da OSM)
* pt_NAME: nome del pronto soccorso (come da OSM)
* dist: distanza del pronto soccorso dal comune (vale 0 se il pronto soccorso ricade nel comune ... )


## Tabella di dettaglio dei Pronto Soccorsi per il reperimento dei dati di tempi e numeri pazienti in attese per colore ##
Una ipotesi di struttura dati della tabella delle info di dettaglio per i Pronto Soccorsi potrebbe essere la seguente:

* osm_id: id del Pronto Soccorso come in OSM
* ps_name: da mettere a mano (al più verificare corrispondenza con OSM ...)
* city: da mettere a mano
* address: da mettere a mano
* tel: da mettere a mano
* email: da mettere a mano
* url_website: url del sito web dove sell'Ospedale ( Pronto Soccorso)
* url_data: url del sito web dove ci sono i dati dei tempi e dei numeri dei pazienti in attesa
* specific_function: da usare se serve una funzione specifica di parsing (altrimenti il valore è NO)
* data_type: (HTML / JSON)
* xpath_numeri_bianco: xpath dell'elemento della pagina HTML che contiene il numero di pazienti in attesa con codice bianco
* xpath_tempi_bianco: xpath dell'elemento della pagina HTML che contiene il temp odi attesa per i pazienti in attesa con codice bianco
* xpath_numeri_verde: xpath dell'elemento della pagina HTML che contiene il numero di pazienti in attesa con codice verde
* xpath_tempi_verde: xpath dell'elemento della pagina HTML che contiene il temp odi attesa per i pazienti in attesa con codice verde
* xpath_numeri_giallo: xpath dell'elemento della pagina HTML che contiene il numero di pazienti in attesa con codice giallo
* xpath_tempi_giallo: xpath dell'elemento della pagina HTML che contiene il temp odi attesa per i pazienti in attesa con codice giallo
* xpath_numeri_rosso: xpath dell'elemento della pagina HTML che contiene il numero di pazienti in attesa con codice rosso
* xpath_tempi_rosso: xpath dell'elemento della pagina HTML che contiene il temp odi attesa per i pazienti in attesa con codice rosso
* proc_name: ????

In più sono da considerare:

* x_coord: coordinata in EPSG32632
* y_coord: coordinata in EPSG32632
* lat: latidudine in WGS84
* lon: longitidine in WGS84

che si potrebbero aggiungere allo shapefile ps_From_OSM_32632 facendole calcolare a Spatialite


              ADDRESS
              TEL
              MAIL
              X-COORD
              Y-COORD
              LAT
              LON
              WEBSITE-URL
              DATA-URL
              PROC-NAME
              DATA-TYPE (es. HTML, JSON)
              XPATH

## Creazione layer pronto soccorsi per web mapping ##
Per la parte di web mapping è necessario avere un layer dei pronto soccorsi da aggiungere sulla mappa.

Si può utilizzare un GeoJSON.

Da Spatialite è possibile creare una tabella che contiene le info che servono con questo comando:

      CREATE TABLE ps_layer
        AS SELECT
           t2.osm_id as osm_id,
           t2.ps_name as Nome,
           t2.city as Citta,
           t2.address as Indirizzo,
           t2.url_website as Url,
           t1.X as X,
           t1.Y as Y,
           t1.LON as LON,
           t1.LAT as LAT
        FROM ps_From_OSM_32632 AS t1, ps_details AS t2
        WHERE t1.osm_id = t2.osm_id;

Caricare questa tabella in QGIS.
Esportala come file CSV
Reimportarla come layer usando le coordinate X e Y (32632)
Salvare come GeoJSON, avendo cura di indicare come sistema di riferimento EPSG:4326


=========================================================


00) Nel file di avvio occorre indicare CHIARAMENTE che si tratta di una cosa prototipale e che i dati sono presi "on line" ed in "real time"
    dai servizi (ove disponibili), o dai portali e che quindi vale la validità delle informaizoni messe a disposizione delle fonti

    Ovviamente devono essere riportati anche:
       -) riferimenti delle fonti (pubblicare elenco delle url da cui si traggono i dati: mettere il file CSV o una paginetta HTML, magari
          generata dinamicamente da una tabella di db dove tenere queste info, così da non dover manutenere la pagina ma costruirla "al volo"
          ad ogni chiamata e quindi basta mantenere aggironata la tabella su db .... )
      -) licenza d'uso
      -) url repository
      -) riferimenti per contatti


01) Gli input possono esser di due tipologie:
        a) coordinate fornite interattivamente dall'utente che digita sulla mappa
        b) nome del comune digitato dall'utente

    01-a) Se l'input è un comune occorre verificare che quanto scritto dall'utente sia effettivamente
          il nome di un comune (verificare i comuni con nomi con spazi, apostrofi, lettere accentate, ecc ...)
          In caso contrario, gestire l'errore e ritornare un messaggio all'utente
    01-b) Se l'input è una coppia di coordinate da questa si ricava qual'è il comune in cui queste ricadono.
          La query spaziale da utilizzare è la seguente

          select * from comuni32632 where within(GeomFromText('POINT(837121 5087604)'),comuni32632.Geometry);

    Quindi l'elemento di partenza è SEMPRE il comune (approssimazione accettabile ...)

02) Fare una query su COM-PS per estrarre i record dei PS "più vicini" al comune in questione

03) Per ogni ID-PS cercare nella tabella dei Pronto Soccorsi il contenuto dei campi: DATA-URL, PROC-NAME, DATA-TYPE, XPATH

04) Richiamare la procedura contenuta nel campo PROC passandogli come parametro il contenuto del campo DATA-URL, DATA-TYPE e XPATH
    Vista l'eterogeneità dei dati le procedure che saranno il contenuto del campo PROC saranno diverse, potenzialmente UNA PER OGNI PRONTO SOCCORSO
    o comunque al più raggruppate per ASL o aggregazione varia.
    La logica di business quindi sarà eterogenea: il grosso dei casi è quello di parsing del contenuto delle pagine HTML riferite nel campo DATA-URL
    che deve andare a reperire il contenuto di un elemento della pagine il cui percorso xpath è contenuto nel campo XPATH

    La cosa importante è cercare di fare in modo che queste procedure restituiscano tutte la stessa struttura dati che dovrà essere la seguente:
     -) PS-NAME
     -) CITY
     -) ADDRESS
     -) TEL
     -) MAIL
     -) WEBSITE-URL
     -) Codice Rosso: numero pazienti in visita
     -) Codice Rosso: numero pazienti in attesa
     -) Codice Rosso: tempo medio di attesa
     -) Codice Giallo: numero pazienti in visita
     -) Codice Giallo: numero pazienti in attesa
     -) Codice Giallo: tempo medio di attesa
     -) Codice Verde: numero pazienti in visita
     -) Codice Verde: numero pazienti in attesa
     -) Codice Verde: tempo medio di attesa
     -) Codice Bianco: numero pazienti in visita
     -) Codice Bianco: numero pazienti in attesa
     -) Codice Bianco: tempo medio di attesa

05) Trattare il caso di Roma o similare in cui vi sono nel comune più di 10 Pronti soccorso e quindi si rischia che non si visualizzino tutti: aumentare il numero di PS considerati? fare delle 2eccezioni" e solo per questi aumentare? Da analizzare e progettare

06) Fare web application semplice con solo un campi di iinput in cui l'utente  inserisci il nome del comune. Da analizzare e progettare

07) Fare web mapping application con la visualizzazione dei soli PS dicui si hanno dati e la possibilità di fare "identify" sul PS e restituire le info dei numeri e tempi di attesa. La API NON funzionano per id PS ma solo per Comune. Da analizzare e progettare

08) Rivedere il tag dei PS inseriti in OSM coem suggerito da lista e poi capire come recuperare gli stessi dati sia Overpass Turbo

per l'installazione far ricreare le dipendenze di skyscanner a partire dal solo json e facendo rigirare composer update
