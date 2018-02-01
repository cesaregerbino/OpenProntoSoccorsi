echo "Test Pronto Soccorso Genova ..."
curl http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality=Genova > /dev/null
sleep 30
echo "Test Pronto Soccorso Torino ..."
curl http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality=Torino > /dev/null
sleep 30
echo "Test Pronto Soccorso Alghero ..."
curl http://localhost/OpenProntoSoccorso/API/getProntoSoccorsoDetailsByMunicipality.php?municipality=Alghero > /dev/null
