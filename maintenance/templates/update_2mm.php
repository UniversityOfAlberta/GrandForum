<?php
require_once( "../commandLine.inc" );
global $wgServer, $wgScriptPath;

$string = file_get_contents("csv/2mm.csv");
foreach(explode("\n", $string) as $line){
    $line = str_replace("”", "'", str_replace("“", "'", str_replace("ʼ", "'", $line)));
    $split = str_getcsv($line, ",", "\"");
    if(!isset($split[1])){
        continue;
    }
    $file = $split[0];
    $project = $split[1];


    //echo "\nFile=$file\nProject=$project\n";


    $papers = Paper::getAllPapers($project, 'Presentation', 'both');

    foreach ($papers as $paper){
    	$id = $paper->getId();
    	$year = substr($paper->getDate(),0,4);
    	$type = $paper->getType();
    	if($year == "2012" && $type == "2MM"){
    		//echo "===".$paper->getTitle() ."\n";
    		$data = $paper->getData();
    		$data['url'] = "{$wgServer}{$wgScriptPath}/index.php/File:{$file}";
    		//echo "===".$data['url'] ."\n";
    		
    		$new_data = serialize($data);

    		$sql = "UPDATE grand_products
		            SET data = '{$new_data}'
		            WHERE id = '{$id}'";
		    
		    DBFunctions::execSQL($sql, true);

		    //echo "===".$paper->getTitle() ."\n";
		    echo "**[[Presentation:{$id} | {$project}]]\n";
    	}
    }

/*
    $sql = "UPDATE grand_products
            SET status = '{$status}',
            data = '{$new_data}'
            WHERE category='Presentation' 
            AND type='2MM' 
            AND YEAR(date) = '2012'";
        
    DBFunctions::execSQL($sql, true);
*/
}
?>