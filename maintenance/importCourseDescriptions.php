<?php
    
require_once( "commandLine.inc" );
    
$lines = explode("\n", file_get_contents("csv/allCoursesDescription.csv"));

foreach($lines as $line){
    $csv = str_getcsv($line);
    
    if(count($csv) > 1){
        $subject = $csv[0];
        $catalog = $csv[1];
        $title = $csv[2];
        $desc = $csv[3];
        
        echo "$subject $catalog\n";
        DBFunctions::update('grand_courses',
                            array('`Descr`' => $title,
                                  '`Course Descr`' => $desc),
                            array('Subject' => $subject,
                                  'Catalog' => $catalog));
    }
}

?>

