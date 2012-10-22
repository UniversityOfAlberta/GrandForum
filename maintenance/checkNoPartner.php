<?php
require_once 'commandLine.inc';

$contributions = Contribution::getAllContributions();
echo "The Following contributions have no associated Partners:\n";
foreach($contributions as $contribution){
    if(count($contribution->getPartners()) == 0){
        if($contribution->getName() != ""){
            echo "  == {$contribution->getName()} <{$contribution->getUrl()}> ==\n";
            foreach($contribution->getPeople() as $person){
                if($person instanceof Person){
                    echo "    {$person->getName()} <{$person->getEmail()}>\n";
                }
            }
            echo "\n";
        }
    }
}

?>
