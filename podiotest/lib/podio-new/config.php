<?php
/*
 * APP Settings
 */
define("ROOT_DIR",str_replace("\\", "/",__DIR__)."/../../") ;
define("DATAFILE_DIR", ROOT_DIR."data/");
define("DATAFILE", DATAFILE_DIR."podioRespone.txt");
define("TEMPLATE_DIR", ROOT_DIR."templates/");
define("TEMPLATEFILE", TEMPLATE_DIR."pdf_template.phtml");
define("PDF_DIR", ROOT_DIR."pdfs/");
/* 
 * PODIO Settings
 */
define("CLIENT_ID", "willowtreeapp");
define("CLIENT_SECRET", "55aX2eqKO4aOQ5dhAJb3L0uohTh0LHt2sebNl1JWSHgyP7KQvWQ94AGpqngzYi48");
//define('APP_ID', 6900467);
define('APP_ID', 8784902);
//define('APP_TOKEN', '248c00fd25324c13ba84955ba1ac4098');
define('APP_TOKEN', '4a5ff80b3ef94ce69735dfef1da9353f');
//define('PODIO_POST_REQUEST','item_id=118413739&item_revision_id=26&type=item.update&hook_id=224688');
define('PODIO_POST_REQUEST','item_id=177635048&item_revision_id=26&type=item.update&hook_id=224688');
define('PODIO_FILE_FIELD','attached-pdf');
define("INCOMING_APP_ID", 6899526);//Incoming APP ID in WillowTree APP
define("INCOMING_APP_TOKEN", "298c85c59e094f2794a6efd074ae2482");//Incoming Token
define("INCOMING_TYPE_FIELD_ID", 53571505); //Type field ID in Incoming APP 
define("INCOMING_TYPE_FIELD_ORDER_VALUE", 2);//Order integer value for Type field ID 


/*
 * Google API Settings
 */
define("GOOGLE_CLIENT_ID", "443335602758-955n646a3e6ftomburjjc4fgrc3093bf.apps.googleusercontent.com");
define("GOOGLE_CLIENT_SECRET", "sOXcMElxVApefFimPOGFXMrU");


