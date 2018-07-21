#!/bin/bash

#***************************************************************************************************
#*** Open Pronto Soccorsi - Test on avaliable Italian Emergency Rooms
#*** Description: Invoke by curl for each Italian municipality where there are Emergency Rooms locations
#***              The municipalities are in the ElencoComuni.txt file
#***        Note: The ElencoComuni.txt file does NOT contains the all Italian Emergency Rooms, but the only ones for which
#***              the waiting list numbers for each code (white, green, yellow and red ones) are available
#***              in open data (services) or in some HTML web portal pages
#***      Author: Cesare Gerbino
#***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
#***     License: MIT (https://opensource.org/licenses/MIT)
#***************************************************************************************************

set -x
rm /var/www/html/OpenProntoSoccorsi/Tools/test.log
while read comune; do
  echo "Test Pronto Soccorso del comune: " $comune
  /usr/bin/curl  'http://localhost/OpenProntoSoccorsi/API/getProntoSoccorsoDetailsByMunicipality.php?municipality='${comune// /%20}'&distance=0' > /dev/null
  sleep 5
done </var/www/html/OpenProntoSoccorsi/Tools/ElencoComuni.txt
