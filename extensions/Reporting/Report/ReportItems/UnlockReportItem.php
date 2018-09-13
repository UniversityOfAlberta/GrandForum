<?php

class UnlockReportItem extends CheckboxReportItem {

    function setBlobValue($value){
        global $config, $wgScriptPath;
        // Send email before saving
        $person = Person::newFromId($this->personId);
        $me = Person::newFromWgUser();
        $chairs = Person::getAllPeople(ISAC);
        $deans = Person::getAllPeople(DEAN);
        
        $title = "Annual Report Unlocked";
        $message = "The Annual Report belonging to {$person->getNameForForms()} has been unlocked by {$me->getNameForForms()}.";
        $headers = "From: {$config->getValue('supportEmail')}" . "\r\n" .
                   "Reply-To: {$config->getValue('supportEmail')}" . "\r\n";
        if($me->isRole(IAC)){
            foreach($chairs as $chair){
                if($chair->getDepartment() == $me->getDepartment() && $wgScriptPath == ""){
                    mail($chair->getEmail(), $title, $message, $headers);
                }
            }
        }
        else if($me->isRole(DEANEA)){
            foreach($deans as $dean){
                if($wgScriptPath == ""){
                    mail($dean->getEmail(), $title, $message." {$dean->getName()}", $headers);
                }
            }
        }
        parent::setBlobValue($value);
    }

}

?>
