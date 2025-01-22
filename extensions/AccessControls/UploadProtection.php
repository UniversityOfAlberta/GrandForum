<?php

class UploadProtection {
  const no_option = '--None--';

  static function initUploadFiles(){
    global $wgUploadDirectory, $IP;
    
    // Set up .htaccess file is upload directory to make files inaccessible directly
    $htaccess = $wgUploadDirectory.'/.htaccess'; //This might have to be changed depending on apache config
    if (!file_exists($htaccess)){
      $contents = file_get_contents(dirname( __FILE__ ).'/.htaccess.annoki');
      if ($contents)
	file_put_contents($htaccess, $contents);
    }

    // Set up AnnokiUploadAuth.php to intercept image requests
    $uploadAuth = $IP.'/AnnokiUploadAuth.php';
    if (!file_exists($uploadAuth)){
    $localUploadAuth = dirname( __FILE__ ).'/AnnokiUploadAuth.php.annoki';
      $contents = file_get_contents($localUploadAuth);
      if ($contents && is_writable($uploadAuth))
	file_put_contents($uploadAuth, $contents);
      else{
	print "<b>Annoki Error</b>: The file AnnokiUploadAuth.php must exist at the base directory of the MediaWiki installation, but the web server doesn't have sufficient permissions to create this file.  
<br><br>Please copy $localUploadAuth to $uploadAuth manually.
<br><br>UNIX: cp $localUploadAuth $uploadAuth";
      exit;
      }
    }
  }
  
  static function buildUploadForm($uploadFormObj){
    $uploadFormObj->uploadFormTextAfterSummary .= self::createNamespaceList();
    return true;
  }

  static function createNamespaceList(){
    global $wgUser;
    $namespaces = AnnokiNamespaces::getNamespacesForUser($wgUser);
    //sort($namespaces);
    $nsList = '';
    foreach ($namespaces as $ns)
      $nsList .= "$ns\n";
    
    $form = Xml::closeElement('td')."\n".
      Xml::closeElement('tr')."\n".
      Xml::openElement('tr')."\n".
      Xml::openElement('td', array('class' => 'mw-label'))."\n".
      Xml::element('label', array('for' => 'wpUploadNamespace'), 'Namespace (optional):')."\n".
      Xml::closeElement('td')."\n".
      Xml::openElement('td', array('class' => 'mw-input'))."\n".
      self::makeNamespaceDropdown($nsList).' (If you choose '.self::no_option.", everyone will have access to your upload)\n";

    return $form;
  }

  //Dropdown not made using Xml::listDropDown because we don't want an empty item in the list.
  private static function makeNamespaceDropdown($nsList){
    $id = 'wpUploadNamespace';

    $out = Xml::openElement( 'select', array('id'=>$id, 'name'=>$id));
    $out .= Xml::option(self::no_option, self::no_option);
    
    $out .= Xml::openElement('optgroup', array('label'=>'Select Namespace'));
    
    foreach (explode("\n", $nsList) as $option){
      $option = trim($option);
      if ($option == '')
	continue;
      $out .= Xml::option($option, $option);
    }

    $out .= Xml::closeElement('optgroup');
    $out .= Xml::closeElement('select');

    return $out;
  }

  //Save temp name in table with namespac
  static function storeNamespace($uploadFormObj){
    global $wgRequest, $egAnnokiTablePrefix;
    $namespace = $wgRequest->getText('wpUploadNamespace');

    if ($namespace == '')
      return true;
    
    $dbw = wfGetDB( DB_PRIMARY );
    //$uploadName = self::sanitize($uploadFormObj->mDesiredDestName);
    $uploadName = $uploadFormObj->mDesiredDestName; //replace does sanitize
    $dbw->replace("${egAnnokiTablePrefix}upload_perm_temp", array('upload_name'), array('upload_name' => $uploadName, 'nsName' => $namespace));
    
    return true;
  }
  
  //Make sure users aren't overwriting a file that they can't access.
  // This shouldn't ever actually get called, since the upload should fail prior to this (no edit privileges), but just in case.
  static function preventUnauthorizedOverwrite($saveName, $tempName, &$error){
    global $wgUser;

    $nsName = self::getNsForImageName($saveName);
    
    if (!$nsName || AnnokiNamespaces::canUserAccessNamespace($wgUser, $nsName))
      return true;
    
    $error = "Tried over overwrite a file that you don't have permission to access.  This file can only be accessed by members of the namespace $nsName.";
    return false;
  }

  static function buildUploadDBEntry($image){
    global $egAnnokiTablePrefix, $wgRequest;

    $selectedNamespace = $wgRequest->getText('wpUploadNamespace');

    if ($selectedNamespace == ''){
      $dbr = wfGetDB ( DB_REPLICA );
      $uploadName = self::sanitize($image->getTitle()); //selectField does not sanitize
      $selectedNamespace = $dbr->selectField("${egAnnokiTablePrefix}upload_perm_temp", 'nsName', 'upload_name=\''.$uploadName."'");
    }

    if ($selectedNamespace == self::no_option || !$selectedNamespace)
      $selectedNamespace = null;
  
    $dbw = wfGetDB( DB_PRIMARY );
    //$uploadName = self::sanitize($image->mDestName);
    $uploadName = $image->getTitle(); //replace does sanitize
    $dbw->replace("${egAnnokiTablePrefix}upload_permissions", array('upload_name'), array('upload_name' => $uploadName, 'nsName' => $selectedNamespace));
    $uploadName = self::sanitize($image->getTitle()); //delete does not sanitize
    //$uploadName = $image->mDesiredDestName;
    $dbw->delete("${egAnnokiTablePrefix}upload_perm_temp", array('upload_name=\''.$uploadName."'"));

    return true;
  }

  //We don't actually want to do this; in case the deleted file gets restored we still want it to be protected.
  //Occurs on   $wgHooks['ArticleDelete'][] = 'UploadProtection::removeUploadDbInfo';
  /*  static function removeUploadDbInfo(&$article, &$user, &$reason, $error){
    global $egAnnokiTablePrefix;
    
    $title = $article->getTitle();
    if ($title->getNamespace() == NS_IMAGE){
      print "Deleting";
      $dbw = wfGetDB( DB_PRIMARY );
      $dbw->delete("${egAnnokiTablePrefix}upload_permissions", array('upload_name=\''.$title->getDBkey()."'"));
    }
    return true;
    } */

  static function addNsInfoToImagePage($article){
    global $wgOut;
    if($article != null){
        $title = $article->getTitle();
        $nsId = $title->getNamespace();
        if ($nsId != NS_IMAGE || MWNamespace::isTalk($nsId))
          return true;

        $pageNS = self::getNsForImageTitle($title);
        if (!$pageNS)
          return true;

        $header = '<span style="background-color: #ffcccc;">This upload is protected, and is only accessible by members of the namespace '.$pageNS.'.</span>  To change the namespace associated with an upload, reupload the file or contact your system administrator.';

        $wgOut->addHTML($header);
    }
    return true;
  }

  //returns false if there is no NS for the given title
  static function getNsForImageTitle($title){
    return self::getNsForImageName($title->getDBkey());
  }
  
  //returns false if there is no NS for the given name
  static function getNsForImageName($imageName){
    global $egAnnokiTablePrefix;
    $dbr = wfGetDB( DB_REPLICA );
    $imageName = self::sanitize($imageName); //selectField does not sanitize
    $imageName = str_replace("_", " ", $imageName);
    return $dbr->selectField("${egAnnokiTablePrefix}upload_permissions", 'nsName', 'upload_name=\''.$imageName.'\' OR upload_name=\'File:'.$imageName."'");
  }

  static function sanitize($input){
    return DBFunctions::escape($input);
  }

}

?>
