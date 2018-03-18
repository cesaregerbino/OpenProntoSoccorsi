
#***************************************************************************************************
#*** Open Pronto Soccorso - Test on avaliable Italian First Aids
#*** Description: Invoke by curl for each Italian municipality where there are First Aids locations
#***              The municipalities are in the ElencoComuni.txt file
#***        Note: The ElencoComuni.txt file does NOT contains the all Italian First Aids, but the only ones for which
#***              the waiting list numbers for each code white, green, yellow and red ones) are available
#***              in open data (services) or in some HTML web portal pages
#***      Author: Cesare Gerbino
#***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
#***     License: MIT (https://opensource.org/licenses/MIT)
#***************************************************************************************************

while read comune; do
  echo "Test Pronto Soccorso del comune: " $comune
  curl http://localhost/OpenProntoSoccorsi/API/getProntoSoccorsoDetailsByMunicipality.php?municipality=${comune// /%20} > /dev/null
  sleep 5
done <ElencoComuni.txt
