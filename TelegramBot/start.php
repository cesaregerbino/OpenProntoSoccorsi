#!/usr/bin/php
<?php

include('settings_t.php');
include('getUpdates.php');


	$bot_id = TELEGRAM_BOT;
	$bot = new Telegram($bot_id);

if (php_sapi_name() == 'cli') {
  if ($argv[1] == 'sethook') {
    $bot->setWebhook(BOT_WEBHOOK);
  } else if ($argv[1] == 'removehook') {
    $bot->removeWebhook();
  }else if ($argv[1] == 'getupdates') {
	getUpdates($bot);
   }
  exit;
}

$bot->init();
$update = $bot->getData();

$update_manager= new mainloop();
$update_manager->start($bot,$update);
