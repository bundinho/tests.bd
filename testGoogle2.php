<?php
require_once 'invoice/lib/google-api-php-client/src/Google_Client.php';
require_once 'invoice/lib/google-api-php-client/src/contrib/Google_DriveService.php';
require_once 'invoice/lib/google-api-php-client/src/contrib/Google_Oauth2Service.php';

session_start();
// ...

$CLIENT_ID = '652321634979-ja6h6ljlqdj3tlid3i25hok0uvtipeck.apps.googleusercontent.com';
$CLIENT_SECRET = 'uQadEfy9iNKXgCZqrRwxljth';
$REDIRECT_URI = 'http://www.willowtree.no/testGoogle2.php';
$SCOPES = array(
    'https://www.googleapis.com/auth/drive.file',
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile');


/**
 * Exception thrown when an error occurred while retrieving credentials.
 */
class GetCredentialsException extends Exception {
  protected $authorizationUrl;

  /**
   * Construct a GetCredentialsException.
   *
   * @param authorizationUrl The authorization URL to redirect the user to.
   */
  public function __construct($authorizationUrl) {
    $this->authorizationUrl = $authorizationUrl;
  }

  /**
   * @return the authorizationUrl.
   */
  public function getAuthorizationUrl() {
    return $this->authorizationUrl;
  }

  /**
   * Set the authorization URL.
   */
  public function setAuthorizationurl($authorizationUrl) {
    $this->authorizationUrl = $authorizationUrl;
  }
}

/**
 * Exception thrown when no refresh token has been found.
 */
class NoRefreshTokenException extends GetCredentialsException {}

/**
 * Exception thrown when a code exchange has failed.
 */
class CodeExchangeException extends GetCredentialsException {}

/**
 * Exception thrown when no user ID could be retrieved.
 */
class NoUserIdException extends Exception {}

/**
 * Retrieved stored credentials for the provided user ID.
 *
 * @param String $userId User's ID.
 * @return String Json representation of the OAuth 2.0 credentials.
 */
function getStoredCredentials($userId) {
  // TODO: Implement this function to work with your database.
  throw new RuntimeException('Not implemented!');
}

/**
 * Store OAuth 2.0 credentials in the application's database.
 *
 * @param String $userId User's ID.
 * @param String $credentials Json representation of the OAuth 2.0 credentials to
                              store.
 */
function storeCredentials($userId, $credentials) {
  // TODO: Implement this function to work with your database.
  throw new RuntimeException('Not implemented!');
}

/**
 * Exchange an authorization code for OAuth 2.0 credentials.
 *
 * @param String $authorizationCode Authorization code to exchange for OAuth 2.0
 *                                  credentials.
 * @return String Json representation of the OAuth 2.0 credentials.
 * @throws CodeExchangeException An error occurred.
 */
function exchangeCode($authorizationCode) {
  try {
    global $CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URI;
    $client = new Google_Client();

    $client->setClientId($CLIENT_ID);
    $client->setClientSecret($CLIENT_SECRET);
    $client->setRedirectUri($REDIRECT_URI);
    $_GET['code'] = $authorizationCode;
    return $client->authenticate();
  } catch (Google_AuthException $e) {
    print 'An error occurred: ' . $e->getMessage();
    throw new CodeExchangeException(null);
  }
}

/**
 * Send a request to the UserInfo API to retrieve the user's information.
 *
 * @param String credentials OAuth 2.0 credentials to authorize the request.
 * @return Userinfo User's information.
 * @throws NoUserIdException An error occurred.
 */
function getUserInfo($credentials) {
  $apiClient = new Google_Client();
  $apiClient->setUseObjects(true);
  $apiClient->setAccessToken($credentials);
  $userInfoService = new Google_Oauth2Service($apiClient);
  $userInfo = null;
  try {
    $userInfo = $userInfoService->userinfo->get();
  } catch (Google_Exception $e) {
    print 'An error occurred: ' . $e->getMessage();
  }
  if ($userInfo != null && $userInfo->getId() != null) {
    return $userInfo;
  } else {
    throw new NoUserIdException();
  }
}

/**
 * Retrieve the authorization URL.
 *
 * @param String $emailAddress User's e-mail address.
 * @param String $state State for the authorization URL.
 * @return String Authorization URL to redirect the user to.
 */
function getAuthorizationUrl($emailAddress, $state) {
  global $CLIENT_ID, $REDIRECT_URI, $SCOPES;
  $client = new Google_Client();

  $client->setClientId($CLIENT_ID);
  $client->setRedirectUri($REDIRECT_URI);
  $client->setAccessType('offline');
  $client->setApprovalPrompt('force');
  $client->setState($state);
  $client->setScopes($SCOPES);
  $tmpUrl = parse_url($client->createAuthUrl());
  $query = explode('&', $tmpUrl['query']);
  $query[] = 'user_id=' . urlencode($emailAddress);
  return
      $tmpUrl['scheme'] . '://' . $tmpUrl['host'] . $tmpUrl['port'] .
      $tmpUrl['path'] . '?' . implode('&', $query);
}

/**
 * Retrieve credentials using the provided authorization code.
 *
 * This function exchanges the authorization code for an access token and
 * queries the UserInfo API to retrieve the user's e-mail address. If a
 * refresh token has been retrieved along with an access token, it is stored
 * in the application database using the user's e-mail address as key. If no
 * refresh token has been retrieved, the function checks in the application
 * database for one and returns it if found or throws a NoRefreshTokenException
 * with the authorization URL to redirect the user to.
 *
 * @param String authorizationCode Authorization code to use to retrieve an access
 *                                 token.
 * @param String state State to set to the authorization URL in case of error.
 * @return String Json representation of the OAuth 2.0 credentials.
 * @throws NoRefreshTokenException No refresh token could be retrieved from
 *         the available sources.
 */
function getCredentials($authorizationCode, $state) {
  $emailAddress = '';
  try {
    $credentials = exchangeCode($authorizationCode);
    $userInfo = getUserInfo($credentials);
    $emailAddress = $userInfo->getEmail();
    $userId = $userInfo->getId();
    $credentialsArray = json_decode($credentials, true);
    if (isset($credentialsArray['refresh_token'])) {
      storeCredentials($userId, $credentials);
      return $credentials;
    } else {
      $credentials = getStoredCredentials($userId);
      $credentialsArray = json_decode($credentials, true);
      if ($credentials != null &&
          isset($credentialsArray['refresh_token'])) {
        return $credentials;
      }
    }
  } catch (CodeExchangeException $e) {
    print 'An error occurred during code exchange.';
    // Drive apps should try to retrieve the user and credentials for the current
    // session.
    // If none is available, redirect the user to the authorization URL.
    $e->setAuthorizationUrl(getAuthorizationUrl($emailAddress, $state));
    throw $e;
  } catch (NoUserIdException $e) {
    print 'No e-mail address could be retrieved.';
  }
  // No refresh token has been retrieved.
  $authorizationUrl = getAuthorizationUrl($emailAddress, $state);
  throw new NoRefreshTokenException($authorizationUrl);
}

try{
    
    $credentials = getCredentials();
    echo '<pre>';
    print_r($credentials);
    echo '</pre>';
} catch (Exception $ex) {
    echo $ex->getMessage();
}

