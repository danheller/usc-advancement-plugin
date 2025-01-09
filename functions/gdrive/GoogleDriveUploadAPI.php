<?php 
 
class GoogleDriveUploadAPI{
    function __construct(){
 
    }
    public function GetAccessToken() { 
        $curlPost = 'client_id='.GCLIENT_ID.'&redirect_uri=' .GCLIENT_REDIRECT. '&client_secret=' . GCLIENT_SECRET . '&code='. $_SESSION['code'] . '&grant_type=authorization_code'; 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, OAUTH2_TOKEN_URI);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
 
        if ($http_code != 200) { 
            $error_msg = 'Failed to receieve access token'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            print_r($data);
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
 
        return $data; 
    } 
    public function toDrive($FileContents, $MimeType) { 
        $API_URL = DRIVE_FILE_UPLOAD_URI . '?uploadType=media'; 
 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $API_URL);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: '.$MimeType, 'Authorization: Bearer '. $_SESSION['access_token'])); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $FileContents); 
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);         
 
        if ($http_code != 200) { 
            $error_msg = 'Failed to upload file to Google Drive'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
 
        return $data['id']; 
    } 
    public function FileMeta($FileID, $FileMetaData) { 
        $API_URL = DRIVE_FILE_META_URI . $FileID; 
 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $API_URL);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '. $_SESSION['access_token'])); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($FileMetaData)); 
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);         
 
        if ($http_code != 200) { 
            $error_msg = 'Failed to update file metadata'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            print_r($data);
            throw new Exception('Error '.$http_code.': '.$error_msg); 
        } 
 
        return $data; 
    } 
}