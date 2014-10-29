<?php
require_once('lib/podio/config.php');
require_once('lib/podio/config.php');
require_once('lib/podio/PodioAPI.php');

//Authentications
Podio::setup(CLIENT_ID, CLIENT_SECRET);
Podio::authenticate('app', array('app_id' =>'8784902', 'app_token' =>'4a5ff80b3ef94ce69735dfef1da9353f'));

$item = PodioItem::get( 177635048 );

$upload = PodioFile::upload('testpodio.php', 'testpodio.php');
PodioFile::attach($upload->file_id, array('ref_type' => 'item', 'ref_id' => 177635048));
?>