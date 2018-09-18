<?php

    require_once('commandLine.inc');
    global $wgUser;
  
    $wgUser = User::newFromId(1);
    
    if(count($argv) != 2){
        echo "Must provide two inputs: php mergeUsers.php <username> <filename>\n";
        exit;
    }
    
    $person = Person::newFromName($argv[0]);
    $ccv = file_get_contents($argv[1]);
    
    $publications = count($person->getPapersAuthored('Publication', '0000-00-00 00:00:00', '2100-01-01 00:00:00'));
    $presentations = count($person->getPapersAuthored('Presentation', '0000-00-00 00:00:00', '2100-01-01 00:00:00'));
    $grants = count($person->getGrants());
    $hqp = count($person->getHQP(true));
    
    preg_match_all('/9a34d6b273914f18b2273e8de7c48fd6/', $ccv, $nJournals);
    preg_match_all('/7cc778c33e64469987c55e2078be60d3/', $ccv, $nConferences);
    preg_match_all('/fd8f2ffe3f5c43db8b5c3f72d8ffd994/', $ccv, $nBooks);
    preg_match_all('/c7ce6f054e0941ea8b27127dbd4a26d0/', $ccv, $nPresentations);
    preg_match_all('/aaedc5454412483d9131f7619d10279e/', $ccv, $nGrants);
    preg_match_all('/4b36fa1eef2549f6ab3a3df7c1c81e0b/', $ccv, $nHQP);
    
    echo "\n";
    echo "{$person->getNameForForms()}\n";
    echo "============================\n";
    echo "              Forum |  CCV |\n";
    echo "============================\n";
    printf("Publications:  %4s | %4s |\n", $publications, count($nJournals[0]) + count($nConferences[0]) + count($nBooks[0]));
    printf("Presentations: %4s | %4s |\n", $presentations, count($nPresentations[0]));
    printf("Grants:        %4s | %4s |\n", $grants, count($nGrants[0]));
    printf("HQP:           %4s | %4s |\n", $hqp, count($nHQP[0]));
    echo "============================\n";
?>
