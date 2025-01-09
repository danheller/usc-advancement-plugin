<?php 
require_once("config.php");
require_once("GoogleDriveUploadAPI.php");
$gdriveAPI = new GoogleDriveUploadAPI();
$release_id = intval( $_SESSION['release'] );
$signedby = $_SESSION['signedby'];
$advpath = $_SESSION['advpath'];
//if(isset($_FILES) && !empty($_FILES['file']['tmp_name'])){
	$fname = 'Image release for ' . preg_replace("/[^A-Za-z0-9 ]/", '', $signedby); // $_FILES['file']['name'];
	// temporarily save the file
//	$upload = move_uploaded_file($_FILES['file']['tmp_name'], 'assets/temp/'.$fname);
//	if($upload){
		$access_token = $_SESSION['access_token'];
		$error="";
		if(!empty($access_token)){
			// identify the file mime type
			$mimeType = 'application/pdf'; //mime_content_type("assets/temp/".$_FILES['file']['name']);
			// get the file contents
			$FileContents =  file_get_contents( "https://giving.usc.edu/pdf/?r=" . $release_id );
	
			// Upload File to Google Drive
			$gDriveFID = $gdriveAPI->toDrive($FileContents, $mimeType);
			if($gDriveFID){
				// Rename Uploaded file
				$meta = [ "name" => $fname ];
				// Update Meta Revision
				$gDriveMeta = $gdriveAPI->FileMeta($gDriveFID, $meta);
				if($gDriveMeta){
//					unlink('assets/temp/'.$fname);
//					echo "<script> alert('File has been uploaded.');location.replace('./'); </script>";
				}else{
					$error = "Fail to Update the File Meta in Google Drive.";
				}
			}else{
				$error = "File Uploading failed in Google Drive.";
			}
		}else{
			$error = "File Uploading failed in Google Drive due to invalid access token.";
		}
//		unlink('assets/temp/'.$fname);
//		echo "<script> alert('File has failed to upload in Google Drive. Error: '.$error);location.replace('./'); </script>";
//	}else{
//		throw new ErrorException("File has failed to upload due to unknown reason.");
//	}
	
 
//}else{
//    throw new ErrorException("No Files has been sent.");
//}
 
function filter_filename($filename, $beautify=true) {
	// sanitize filename
	$filename = preg_replace(
		'~
		[<>:"/\\\|?*]|           # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
		[\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
		[\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
		[#\[\]@!$&\'()+,;=]|     # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
		[{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
		~x',
		'-', $filename);
	// avoids ".", ".." or ".hiddenFiles"
	$filename = ltrim($filename, '.-');
	// optional beautification
	if ($beautify) $filename = beautify_filename($filename);
	// maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	$filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
	return $filename;
}

function beautify_filename($filename) {
	// reduce consecutive characters
	$filename = preg_replace(array(
		// "file   name.zip" becomes "file-name.zip"
		'/ +/',
		// "file___name.zip" becomes "file-name.zip"
		'/_+/',
		// "file---name.zip" becomes "file-name.zip"
		'/-+/'
	), '-', $filename);
	$filename = preg_replace(array(
		// "file--.--.-.--name.zip" becomes "file.name.zip"
		'/-*\.-*/',
		// "file...name..zip" becomes "file.name.zip"
		'/\.{2,}/'
	), '.', $filename);
	// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
//	$filename = mb_strtolower($filename, mb_detect_encoding($filename));
	// ".file-name.-" becomes "file-name"
	$filename = trim($filename, '.-');
	return $filename;
}