<?php

use MediaWiki\MediaWikiServices;

class SpecialSideUserLogin extends SpecialUserLogin {

    public function __construct() {
        parent::__construct(MediaWikiServices::getInstance()->getAuthManager());
    }
    
    protected function mainLoginForm( array $requests, $msg = '', $msgtype = 'error' ) {
        global $wgTitle;
        if(!empty($_POST) && $wgTitle->getText() != "UserLogin"){
            $msg = "";
        }
        parent::mainLoginForm($requests, $msg, $msgtype);
    }
    
    protected function postProcessFormDescriptor( &$formDescriptor, $requests ) {
        parent::postProcessFormDescriptor($formDescriptor, $requests);
        unset($formDescriptor['linkcontainer']);
        $formDescriptor['username']['autofocus'] = false;
        $formDescriptor['password']['autofocus'] = false;
        unset($formDescriptor['username']['label-raw']);
        unset($formDescriptor['password']['label-message']);
        $formDescriptor['passwordReset']['cssclass'] .= " underlined highlights-text";
    }
    
    function render(){
        global $wgTitle;
        if($wgTitle->getText() == "UserLogin"){
            return;
        }
        $this->getOutput()->clearHTML();
        $this->beforeExecute("");
        $this->execute("");
        echo $this->getOutput()->getHTML();
        echo "<script type='text/javascript'>
            $('#side form[name=userlogin]').attr('action', $('#side form[name=userlogin]').attr('action').replace('index.php?title=Special:UserLogin', 'index.php/Special:UserLogin?title=Special:UserLogin'));
            $('#userloginForm .cdx-checkbox__icon').remove();
        </script>";
    }

}

?>
