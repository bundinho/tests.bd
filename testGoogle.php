<?php
require_once 'invoice/lib/google-api-php-client/src/Google_Client.php';
require_once 'invoice/lib/google-api-php-client/src/contrib/Google_DriveService.php';
require_once 'invoice/lib/google-api-php-client/src/contrib/Google_Oauth2Service.php';
session_start();

define('DRIVE_SCOPE', 'https://www.googleapis.com/auth/drive');
define('SERVICE_ACCOUNT_EMAIL', '652321634979-7khbbk4a0gfq1pf29pke6ig5k3mpfedp@developer.gserviceaccount.com');
define('SERVICE_ACCOUNT_PKCS12_FILE_PATH', 'Willowtree-d286b82f525d.p12');

/**
 * Build and returns a Drive service object authorized with the service accounts.
 *
 * @return Google_DriveService service object.
 */
function buildService() {
  $key = file_get_contents(SERVICE_ACCOUNT_PKCS12_FILE_PATH);
  $auth = new Google_AssertionCredentials(
      SERVICE_ACCOUNT_EMAIL,
      array(DRIVE_SCOPE),
      $key);
  $client = new Google_Client();
  $client->setUseObjects(true);
  $client->setAssertionCredentials($auth);
  return new Google_DriveService($client);
}

try
{
//     $file = $service->files->get("appfolder");
//    
//    print "Id: " . $file->getId();
//    print "Title: " . $file->getTitle();
//exit();
    $filename = $pdf='brand_willow_tree_jan14.pdf';

    $service = buildService();
    $file = new Google_DriveFile();
    $file->setMimeType('application/pdf');
    $file->setTitle($filename); 
    
    $parent = new ParentReference();
    $parent->setId("appfolder");
    $file->setParents(array($parent));
    //$service = new apiDriveService($client);
    $insertedFile = $service->files->insert($file, array('data' => file_get_contents($pdf), 'mimeType' => 'application/pdf'));
    echo '<pre>';print_r($insertedFile);echo '</pre>';

} catch (Exception $ex) {
    echo $ex->getMessage();
}


