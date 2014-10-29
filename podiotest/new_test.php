<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('lib/podio/config.php');
require_once('lib/podio/PodioAPI.php');
require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_DriveService.php';
require_once('lib/utils/functions.php');
require_once('lib/utils/View.class.php');
require_once('lib/html2pdf/html2pdf.class.php');

Podio::setup(CLIENT_ID, CLIENT_SECRET);
Podio::authenticate('app', array('app_id' =>APP_ID, 'app_token' =>APP_TOKEN)); // APP_ID and APP_TOKEN from config.php   

$item = PodioItem::get(177635048);

$upload = PodioFile::upload('new_test.php', 'new_test.php');
var_dump($upload);