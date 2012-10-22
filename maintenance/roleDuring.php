<?php
	$options = array('help');
	require_once( 'commandLine.inc' );
	
	if( isset( $options['help'] ) ) {
		showHelp();
		exit(1);
	}
	
	$notBefore = false;
	$notAfter = false;
	
	if(isset($options['notBefore']) || isset($options['b'])){
	    $notBefore = true;
	}
	
	if(isset($options['notAfter']) || isset($options['a'])){
	    $notAfter = true;
	}
	
	$role = constant($options['role']);
	$start = $options['start'];
	$end = $options['end'];
	$format = "{id} :: {username}\n";
	if(isset($options['format'])){
	    $format = $options['format'];
	}
    
	if($role == NULL){
	    echo "Role '{$options['role']}' does not exist\n";
		exit(1);
	}
	echo "Role: {$options['role']}\nStart: $start\nEnd: $end\n";
    $people = Person::getAllPeople();
    foreach($people as $person){
        if($person->isRoleDuring($role, $start, $end)){
            if((($notBefore && !$person->isRoleDuring($role, '0000-01-01', $start)) || !$notBefore) &&
               (($notAfter && !$person->isRoleDuring($role, $end, '2030-01-01')) || !$notAfter)){
                $formatCopy = $format;
                $formatCopy = str_replace("{id}", $person->getId(), $formatCopy);
                $formatCopy = str_replace("{name}", $person->getNameForForms(), $formatCopy);
                $formatCopy = str_replace("{username}", $person->getName(), $formatCopy);
                $formatCopy = str_replace("{email}", $person->getEmail(), $formatCopy);
                echo $formatCopy;
            }
        }
    }
    echo "\n";
	
	function showHelp() {
		echo( <<<EOT
Displays the list of people who were listed as a part of <role> between <start> and <end>
<start> and <end> should be in the form of timestamps.  

The list is displayed using the specified format(optional).  
New lines are not assumed, and must be specified explicitly.  
If no format is defined, then it uses the default:
    "{id} :: {username}\n"
The following variables can be used for the display format
    -{id}
    -{username}
    -{name}
    -{email}

USAGE: php roleDuring [--help] [-b|--notBefore] [-a|--notAfter] [--role=<role>] [--start=<start>] [--end=<end>] [--format=<format>]

	--help
		Show this help information

EOT
	);
}
?>
