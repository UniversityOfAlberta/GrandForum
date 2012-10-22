<?php
    require_once( "../commandLine.inc" );
    
    $year = 2012;
    $output = <<<EOF
Feature: 2MM
        
    Scenario: Preparing pages for 2MM insertion
        Given I am logged in as "Admin" using password "BigLasagna"
            And I go to "Conference:GRAND_Annual_Conference_{$year}?action=edit&editType=template"
            And I fill in "Conference0|presentationsMAIN" with "*2 Minute Madness"
            And I press "Save page"\n\n
EOF;
    
    function addScenario($file, $name, $pageName, $pageText, $project){
        global $output, $year;
        $pageName = str_replace(" ", "_", $pageName);
        $pageText = str_replace("\n", "\\n", $pageText);
        
        $output .= <<<EOF
    Scenario: Setting up 2MM:{$pageName}
        Given I am logged in as "Admin" using password "BigLasagna"
        When I go to "Special:Upload?wpForReUpload=1"
            And I attach the file "files/$file" to "wpUploadFile"
            And I press "Upload file"
            And I go to "Presentation:$pageName?action=edit"
            And I fill in "wpTextbox1" with multiline "$pageText"
            And I press "Save page"
            And I go to "Conference:GRAND_Annual_Conference_$year?action=edit&editType=template"
            And I append "\\n**[[Presentation:{$project}_2_Minute_Madness_{$year} | {$project}]]" to "Conference0|presentationsMAIN"
            And I press "Save page"\n\n
EOF;
    }
    
    $string = file_get_contents("csv/2mm.csv");
    foreach(explode("\n", $string) as $line){
        $line = str_replace("”", "'", str_replace("“", "'", str_replace("ʼ", "'", $line)));
        $split = str_getcsv($line, ",", "\"");
        if(!isset($split[1])){
            continue;
        }
        $file = $split[0];
        /*
        if(!file_exists('files/'.str_replace("pdf", "png", $file))){
            echo "Converting {$file} to ".str_replace("pdf", "png", $file)."\n";
            $out = "";
            exec("convert files/{$file} files/".str_replace(".pdf", "-%d.tiff", $file)." 2>&1", $out);
            exec("convert -background none -gravity Center files/ ".str_replace(".pdf", "-*.tiff", $file)." -append files/".str_replace("pdf", "tiff", $file)." 2>&1", $out);
            echo "convert -background none -gravity Center files/ ".str_replace(".pdf", "-*.tiff", $file)." -append files/".str_replace("pdf", "tiff", $file);
            if(strpos(implode("", $out), "ERROR") !== false){
                echo "\tERROR: Removing file ".str_replace("pdf", "png", $file)."\n";
                exec("rm -f \"files/".str_replace(".pdf", "-*.png", $file)."\"");
            }
        }
        if(file_exists('files/'.str_replace("pdf", "png", $file))){
            $file = str_replace("pdf", "png", $file);
        }
        */
        $project = $split[1];
        $p = Project::newFromName($project);
        addScenario("$file", str_replace("files/", "", "$file"), "{$project} 2 Minute Madness {$year}", 
"{{Presentation 
|title = 
|EventName = GRAND Annual Conference {$year}
|presenter = 
|people = 
|project = [[{$project}:Main | {$p->getFullName()}]] 
|paper = 
|slides = [[File:".str_replace("files/", "", "$file")."|thumb|left]]
 }}", $project);
    }
    
    file_put_contents("features/2mm.feature", $output);
            
?>
