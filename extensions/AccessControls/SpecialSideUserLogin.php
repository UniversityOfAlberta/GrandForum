<?php

class SpecialSideUserLogin extends SpecialUserLogin {

    function __construct(){
        parent::__construct();
        
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
        $this->getOutput()->clearHTML();
        $this->beforeExecute("");
        $this->execute("");
        echo $this->getOutput()->getHTML();
    }

}

?>
