<?php
/*
 * Entry point of the test result
 * 
 * Code tested under wampserver 2.5
 * 
 * External libraries:
 * - podio-php-4.0.1
 * - html2pdf_v4.03_php5
 * - google-api-php-client
 * 
 * Important files/directories:
 * - lib/podio/config.php : Configuration file
 * - lib/utils/functions.php : contains the functions used in test.php
 * - data/podioRespone.txt : file containing data;
 * - pdfs/ : directory where the generated invoice pdfs are located
 * 
 * Tips : 
 * - var $podio_way : Toggle between using podio or podioResponse.txt data. Set to true if you want to retrieve data from Podio and no if you want to retrieve data from podioResponse.txt
 * - var $google_upload : activate/deactivate google upload. Set to true if you want to activate upload to Google Drive
 * - var $podio_attach : activate/deactivate upload to Podio. Set to true if you want to attach pdf file to Podio item. 
 */


require_once('lib/podio/config.php');
require_once('lib/podio/PodioAPI.php');
require_once 'lib/google-api-php-client/src/Google_Client.php';
require_once 'lib/google-api-php-client/src/contrib/Google_DriveService.php';
require_once('lib/utils/functions.php');
require_once('lib/utils/View.class.php');
require_once('lib/html2pdf/html2pdf.class.php');

/*
 * Features activation/deactivation variables
 */
$podio_way = true; // Set to true if you want to retrieve data from Podio and no if you want to retrieve data from podioResponse.txt
$google_upload = true; // Set to true if you want to activate upload to Google Drive .
$podio_attach = true; // Set to true if you want to attach pdf file to Podio item

$client = null;
$item = null;
try {
    //Podio::set_debug(true);
    Podio::setup(CLIENT_ID, CLIENT_SECRET);
    Podio::authenticate('app', array('app_id' =>APP_ID, 'app_token' =>APP_TOKEN)); // APP_ID and APP_TOKEN from config.php    
} catch (Exception $ex) {
    $podio_way = false;
}

if($google_upload){
    $client = new Google_Client();
    // Get your credentials from the console
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
    $client->setScopes(array('https://www.googleapis.com/auth/drive'));

    $service = new Google_DriveService($client);
}

// simulate POST Request;
$postdata = getPostData($_POST);

if(isset($postdata["action"]) && $postdata["action"] == "google_drive_upload")
{
    //upload of pdf into google drive
    $pdf = $postdata["pdf"];
    $_GET['code'] = $postdata["code"]; // insert the verification code here
    file_put_contents('token.json', $client->authenticate());

    $client->setAccessToken(file_get_contents('token.json'));

    $fullpaths = explode("/", $pdf);
    $filename = $fullpaths[count($fullpaths) - 1];  

    $file = new Google_DriveFile;
    $file->setMimeType('application/pdf');
    $file->setTitle($filename); 
    //$service = new apiDriveService($client);
    $insertedFile = $service->files->insert($file, array('data' => file_get_contents($pdf), 'mimeType' => 'application/pdf'));
    echo '<p>'.$filename.' successfully uploaded to Google Drive !</p>';
    exit;
}

//print '<pre>';
//print_r($postdata);
//print '</pre>';

switch ($postdata['type']) {
	case 'hook.verify':
		// Validate the webhook
		PodioHook::validate($postdata['hook_id'], array('code' => $postdata['code']));
            break;
        
	case 'item.create':
		// Do something. item_id is available in $postdata['item_id']
            break;
        
	case 'item.update':
            if(Podio::is_authenticated() && $podio_way)
            {
                // Get the item data and build an array of data
                $item = PodioItem::get($postdata['item_id']);
                $data = buildPodioResponse($item);
            }
            else 
            {
                //get the content of podioResponse.txt and turn it into an array
                $textData = file_get_contents(DATAFILE);
                $data = print_r_reverse($textData);
            }             
            
            try {
                //generate PDF
                $html = generateInvoiceHtml($data);
                $pdf = generateInvoicePDF($html);
                $fullpaths = explode("/", $pdf);
                $filename = $fullpaths[count($fullpaths) - 1];  
                echo '<p><a target="_blank" href="pdfs/'.$filename.'">'.$filename.'</a> successfully generated <br /></p>';
                
                //attach file to the Item 
                if(Podio::is_authenticated() && !empty($item) && $podio_attach)
                {
                    podioAttachFile($item, $pdf);
                }
                
                //upload into google drive
                if($google_upload)
                {
                    $authUrl = $client->createAuthUrl(array('https://www.googleapis.com/auth/drive.file'));
                    echo '<h3>Upload of '.$pdf.' to google drive</h3>'
                        . '<p>Go to the url below to generate google access code and paste generated code into the field dedicated to it</p>'
                        . '<quote style="background:#efe;border:1px solid #ccc;">'.$authUrl.'</quote>'
                        . '<form method="POST">'
                            . '<input type="hidden" name="action" value="google_drive_upload" />'
                            . '<input type="hidden" name="pdf" value="'.$pdf.'" />'
                            . '<label>Access code:</label><input type="text" length="150" name="code" value="" />'
                            . '<input type="submit"  name="submit_bt" value="SEND" />'
                        . '</form>';

                    //print $client->createAuthUrl(array('https://www.googleapis.com/auth/drive.file')); exit;

                    
                }
            } 
            catch (HTML2PDF_exception $ex) {
                 echo $ex;
            }
            catch (Exception $e) {
                echo $e;
            }
            
            
            break;
        
	case 'item.delete':
		// Do something. item_id is available in $postdata['item_id'] 
            break;
}

?>