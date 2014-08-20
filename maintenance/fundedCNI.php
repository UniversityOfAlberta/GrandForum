<?php
    require('commandLine.inc');
    
    if(!isset($argv[0])){
        echo <<<EOF
ERROR: A year must be specified. ie:
    $ php fundedCNI.php 2014

EOF;
        exit;
    }
    $year = $argv[0];
    if(!file_exists("fundedCNI$year.txt")){
        echo <<<EOF
ERROR: The file 'fundedCNI$year.txt' must exist

EOF;
    exit;
    }
    $contents = file_get_contents("fundedCNI$year.txt");
    $names = explode("\n", $contents);
    foreach($names as $name){
        $person = Person::newFromReversedName($name);
        if($person->exists()){
            DBFunctions::insert('grand_funded_cni',
                                array('user_id' => $person->getId(),
                                      'year' => $year));
        }
    }

?>
