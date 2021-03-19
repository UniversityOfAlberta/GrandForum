<?php

class UnlockReportItem extends CheckboxReportItem {

    function setBlobValue($value){
        global $config, $wgScriptPath;
        // Send email before saving
        $person = Person::newFromId($this->personId);
        $me = Person::newFromWgUser();
        $chairs = Person::getAllPeople(CHAIR);
        $deans = Person::getAllPeople(DEAN);
        
        $title = "Annual Report Unlocked";
        $message = "The Annual Report belonging to {$person->getNameForForms()} has been unlocked by {$me->getNameForForms()}.";
        $headers = "From: {$config->getValue('supportEmail')}" . "\r\n" .
                   "Reply-To: {$config->getValue('supportEmail')}" . "\r\n";
        if(isset($value[0]) && !isset($value[1])){
            if($me->isRole(EA)){
                foreach($chairs as $chair){
                    if(!isset($alreadySent[$chair->getEmail()]) && $chair->getDepartment() == $me->getDepartment() && $me->getId() != $chair->getId() && $wgScriptPath == ""){
                        mail($chair->getEmail(), $title, $message, $headers);
                        $alreadySent[$chair->getEmail()] = true;
                    }
                }
            }
            else if($me->isRole(DEANEA)){
                foreach($deans as $dean){
                    if(!isset($alreadySent[$dean->getEmail()]) && $me->getId() != $dean->getId() && $wgScriptPath == ""){
                        mail($dean->getEmail(), $title, $message." {$dean->getName()}", $headers);
                        $alreadySent[$dean->getEmail()] = true;
                    }
                }
            }
        }
        parent::setBlobValue($value);
    }

}

?>
