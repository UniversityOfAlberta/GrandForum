<?php

require_once('commandLine.inc');
global $wgUser;

$wgUser = User::newFromId(1);

// Staff
$people = Person::getAllPeopleDuring(HQP, '0000-00-00', '2100-00-00');

usort($people, function($a, $b){
    return ($a->getReversedName() >= $b->getReversedName());
});

$department = (isset($argv[0])) ? $argv[0] : "Computing Science";

echo "\"FORUM ID\",\"FIRST NAME\",\"MIDDLE NAME\",\"LAST NAME\",\"STUDENT ID\",\"CCID\",\"POSITION\",\"START\",\"END\"\n";

foreach($people as $person){
    $unis = $person->getUniversities();
    foreach($unis as $uni){
        if($uni['department'] == $department && in_array(strtolower($uni['position']), array_merge(Person::$studentPositions['grad'], Person::$studentPositions['pdf']))){
            $position = $uni['position'];
            $id = $person->getId();
            $firstName = $person->getFirstName();
            $middleName = $person->getMiddleName();
            $lastName = $person->getLastName();
            $eId = $person->getEmployeeId();
            $ccid = explode("@", $person->getEmail());
            $ccid = @$ccid[0];
            $start = $uni['start'];
            $end = $uni['end'];
            echo "\"{$id}\",\"{$firstName}\",\"{$middleName}\",\"{$lastName}\",\"{$eId}\",\"{$ccid}\",\"{$position}\",\"{$start}\",\"{$end}\"\n";
        }
    }
    
}
   
?>
