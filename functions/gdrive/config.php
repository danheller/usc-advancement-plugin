<?php
// Define Google Client Credentials, Scopes, and URIs
define('GCLIENT_ID', "994884789905-5pm91ppngna9hbtc33dulel2pvi02osb.apps.googleusercontent.com");
// client secret removed from configuration
define('GCLIENT_REDIRECT', "https://giving.usc.edu/signed/"  );
define('GCLIENT_SCOPE', "https://www.googleapis.com/auth/drive");
 
define('OAUTH2_TOKEN_URI',"https://oauth2.googleapis.com/token");
 
define('DRIVE_FILE_UPLOAD_URI',"https://www.googleapis.com/upload/drive/v3/files");
define('DRIVE_FILE_META_URI',"https://www.googleapis.com/drive/v3/files/");
 
if(!session_id()) session_start();
 
// Authentication URL
$gOauthURL = "https://accounts.google.com/o/oauth2/auth?scope=".(urldecode(GCLIENT_SCOPE))."&redirect_uri=".(urlencode(GCLIENT_REDIRECT))."&client_id=".(urlencode(GCLIENT_ID))."&access_type=online&response_type=code";
 
if(isset($_GET['code'])){
    $_SESSION['code'] = $_GET['code'];
    require_once("GoogleDriveUploadAPI.php");
    $gdriveAPI = new GoogleDriveUploadAPI();
    // Save Access Token
    $_SESSION['access_token'] = $gdriveAPI->GetAccessToken()['access_token'];
    header('location:./');
}
// if(!isset($_SESSION['code']))
// header("location:{$gOauthURL}");