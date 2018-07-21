<?php
  # ***************************************************************************************************
  # *** Open Pronto Soccorsi - Telegram Bot
  # *** Description: settings_t
  # ***        Note: Define variables / constants used in main.php
  # ***      Author: Cesare Gerbino
  # ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
  # ***     License: MIT (https://opensource.org/licenses/MIT)
  # ***************************************************************************************************

  define('TELEGRAM_BOT','PUT_HERE_YOUR_TELEGRAM_BOT_TOKEN'); // token Telegram Bot
  define('BOT_WEBHOOK', ''); // https url for start.php
  define('DATA_DB_PATH','/var/www/html/OpenProntoSoccorsi/Data');
  define('DATA_ACCESSES_DB_PATH','/var/www/html/OpenProntoSoccorsi/TelegramBot');
  define('DATA_SESSIONS_DB_PATH','/var/www/html/OpenProntoSoccorsi/TelegramBot');
  define('API','PUT_HERE_YOUR_GOOGLE_API_SHORTNER_TOKEN'); // api google shortner
  define('ERROR_MANAGER_TELEGRAM_BOT','PUT_HERE_YOUR_TELEGRAM_BOT_TOKEN_FOR_ERROR_MANAGER'); // token for the Error Manager Telegram Bot
  define('CHAT_ID_FOR_TO_SEND_ERROR_MESSAGES','PUT_HERE_YOUR_TELEGRAM_CHAT_ID_TO_SEND ERROR MESSAGES'); // ChatId to use tosend error messages to rror Manager Telegram Bot. NOTE: MUST be a personal chatId !!!!
  define('URL_API', 'http://localhost/OpenProntoSoccorsi/API/getProntoSoccorsoDetailsByMunicipality.php'); // API url reference for get Emergency Rooms details by municipality ...
  define('URL_API2', 'http://localhost/OpenProntoSoccorsi/API/getMunicipalityByLatLon.php'); // API url reference for get municipality by lat lon coordinates ...
  define('URL_API3', 'http://localhost/OpenProntoSoccorsi/TelegramBot/RenderRoute.php'); // API url reference for get municipality by lat lon coordinates ...
  define('MAPQUEST_KEY', 'PUT_HERE_YOUR_MAPQUEST_KEY'); // MapQuest key for calculate routes using MapQuest API ...
  define('MAPBOX_ACCESS_TOKEN', 'PUT_HERE_YOUR_MAPBOX_TOKEN'); // MabBox token per MapBox API
?>
