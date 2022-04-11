<?php

/**
 * Image authorisation script
 *
 * To use this:
 *
 * Set $wgUploadDirectory to a non-public directory (not web accessible)
 * Set $wgUploadPath to point to this file
 *
 * Your server needs to support PATH_INFO; CGI-based configurations
 * usually don't.
 */

define( 'MW_NO_OUTPUT_COMPRESSION', 1 );
require_once( dirname( __FILE__ ) . '/includes/WebStart.php' );

require_once( dirname( __FILE__ ) . '/includes/StreamFile.php' );

use MediaWiki\MediaWikiServices;

$perms = User::getGroupPermissions( array( '*' ) );
if ( in_array( 'read', $perms, true ) ) {
	wfDebugLog( 'AnnokiUploadAuth', 'Public wiki' );
	wfPublicError();
}

// Extract path and image information
if( !isset( $_SERVER['PATH_INFO'] ) ) {
	wfDebugLog( 'AnnokiUploadAuth', 'Missing PATH_INFO' );
	wfForbidden();
}

$path = $_SERVER['PATH_INFO'];
$filename = realpath( $wgUploadDirectory . $_SERVER['PATH_INFO'] );
$realUpload = realpath( $wgUploadDirectory );
wfDebugLog( 'AnnokiUploadAuth', "\$path is {$path}" );
wfDebugLog( 'AnnokiUploadAuth', "\$filename is {$filename}" );

// Basic directory traversal check
if( substr( $filename, 0, strlen( $realUpload ) ) != $realUpload ) {
	wfDebugLog( 'AnnokiUploadAuth', 'Requested path not in upload directory' );
	wfForbidden();
}

// Extract the file name and chop off the size specifier
// (e.g. 120px-Foo.png => Foo.png)
$name = wfBaseName( $path );
if( preg_match( '!\d+px-(.*)!i', $name, $m ) )
	$name = $m[1];
wfDebugLog( 'AnnokiUploadAuth', "\$name is {$name}" );

$title = Title::makeTitleSafe( NS_FILE, $name );
if( !$title instanceof Title ) {
	wfDebugLog( 'AnnokiUploadAuth', "Unable to construct a valid Title from `{$name}`" );
	wfForbidden();
}

/** BT: Check to ensure the user has access to the file. */
if ($egAnProtectUploads){
  $unarchivedTitle = $title;
  
  //Check to see if the file is not current (from the archive).
  if (strpos($filename, "$realUpload/archive/") === 0) {
    $match = substr(strstr($filename, '!'),1);
    $unarchivedTitle =  Title::makeTitleSafe( NS_FILE, $match);
  }
  if (!MediaWikiServices::getInstance()->getPermissionManager()->userCan('read', $wgUser, $unarchivedTitle) || !$wgUser->isLoggedIn()){
    wfDebugLog( 'AnnokiUploadAuth', 'User does not have access to '.$unarchivedTitle->getPrefixedText());
    $errorFile = 'extensions/AccessControls/images/errorFile.gif';
    StreamFile::stream($errorFile, array( 'Cache-Control: private', 'Vary: Cookie' ));
    exit();
  }
 }
/** End BT Edit */

$title = $title->getPrefixedText();

// Check the whitelist if needed
if( !$wgUser->getId() && ( !is_array( $wgWhitelistRead ) || !in_array( $title, $wgWhitelistRead ) ) ) {
	echo $title;
	$title_tmp = str_replace("File:", "", str_replace(" ", "_", $title));
	wfDebugLog( 'AnnokiUploadAuth', "Not logged in and `{$title}` not in whitelist." );
	$upTable = getTableName("an_upload_permissions");
	$sql = "SELECT * 
		FROM $upTable
		WHERE upload_name = '$title_tmp'";
	$dbr = wfGetDB(DB_REPLICA);
	$result = $dbr->query($sql);
	$rows = array();
	while($row = $dbr->fetchRow($result)){
		$rows[] = $row;
	}
	if(count($rows) > 0){
		if($rows[0]['nsName'] != null){
			wfForbidden();
		}
	}
}

if( !file_exists( $filename ) ) {
	wfDebugLog( 'AnnokiUploadAuth', "`{$filename}` does not exist" );
	wfForbidden();
}
if( is_dir( $filename ) ) {
	wfDebugLog( 'AnnokiUploadAuth', "`{$filename}` is a directory" );
	wfForbidden();
}



// Stream the requested file
wfDebugLog( 'AnnokiUploadAuth', "Streaming `{$filename}`" );
StreamFile::stream($filename, array( 'Cache-Control: private', 'Vary: Cookie' ));

/**
 * Issue a standard HTTP 403 Forbidden header and a basic
 * error message, then end the script
 */
function wfForbidden($error = 'You need to log in to access files on this server.') { //BT edit added $error
	header( 'HTTP/1.0 403 Forbidden' );
	header( 'Vary: Cookie' );
	header( 'Content-Type: text/html; charset=utf-8' );
	echo <<<ENDS
<html>
<body>
<h1>Access Denied</h1>
<p>$error</p>
</body>
</html>
ENDS;
	exit();
}

/**
 * Show a 403 error for use when the wiki is public
 */
function wfPublicError() {
	header( 'HTTP/1.0 403 Forbidden' );
	header( 'Content-Type: text/html; charset=utf-8' );
	echo <<<ENDS
<html>
<body>
<h1>Access Denied</h1>
<p>The function of AnnokiUploadAuth.php is to output files from a private wiki. This wiki
is configured as a public wiki. For optimal security, AnnokiUploadAuth.php is disabled in 
this case.
</p>
</body>
</html>
ENDS;
	exit;
}

