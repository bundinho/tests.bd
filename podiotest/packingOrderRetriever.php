<?php

/* 
 * This script serves to retrieve all orders in the App Incoming and push them to firebase;
 */
require_once('lib/podio/config.php');
require_once('lib/podio/PodioAPI.php');
Podio::setup(CLIENT_ID, CLIENT_SECRET);
Podio::authenticate('app', array('app_id' =>INCOMING_APP_ID, 'app_token' =>INCOMING_APP_TOKEN));
$items = PodioItem::filter(INCOMING_APP_ID);
//$items = PodioItem::filter(
//        INCOMING_APP_ID, 
//        array('filters' => array(INCOMING_TYPE_FIELD_ID =>array(INCOMING_TYPE_FIELD_ORDER_VALUE))));

print '<pre>';
print $items->total;
print $items->filtered;
print_r($items);
print '</pre>';


