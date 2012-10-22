<?php
require_once("SpecialPage.php");
require_once('specials/SpecialUpload.php');

$wgExtensionFunctions[] = 'CustomSpecialUploadForm::efSetupUploadForm';

class CustomSpecialUploadForm extends UploadForm {
  function mainUploadForm( $msg=''){
    
    parent::mainUploadForm($msg);
    
  }

  static function efSetupUploadForm() {
    SpecialPage::$mList['Upload'] = "CustomSpecialUploadForm";
  }
}

?>