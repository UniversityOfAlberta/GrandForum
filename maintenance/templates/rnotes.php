<?php
    require_once( "../commandLine.inc" );
    $year = 2012;
    $output = <<<EOF
Feature: RNotes

    Scenario: Preparing pages for RNotes insertion
        Given I am logged in as "Admin" using password "BigLasagna"
            And I go to "Conference:GRAND_Annual_Conference_{$year}?action=edit&editType=template"
            And I append "\\n*Research Notes" to "Conference0|presentationsMAIN"
            And I press "Save page"\n\n
EOF;
    
    function addScenario($paper, $paperName, $pageName, $pageText){
        global $output, $year;
        $pageName = str_replace(" ", "_", $pageName);
        $pageText = str_replace("\n", "\\n", $pageText);
        $urlName = str_replace("_", " ", $pageName);

        $output .= <<<EOF
    Scenario: Setting up RNote:{$pageName}
        Given I am logged in as "Admin" using password "BigLasagna"
        When I go to "Special:Upload?wpForReUpload=1"
            And I attach the file "files/$paper" to "wpUploadFile"
            And I press "Upload file"
            And I go to "Presentation:RNotes{$year}_{$pageName}?action=edit"
            And I fill in "wpTextbox1" with multiline "$pageText"
            And I press "Save page"
            And I go to "Conference:GRAND_Annual_Conference_$year?action=edit&editType=template"
            And I append "\\n**[[Presentation:RNotes{$year}_{$pageName} | {$urlName}]]" to "Conference0|presentationsMAIN"
            And I append "" to "Conference0|postersMAIN"
            And I press "Save page"\n\n
EOF;
    }
    
    $string = file_get_contents("csv/rnotes.csv");
    foreach(explode("\n", $string) as $line){
        $line = str_replace("”", "'", str_replace("“", "'", str_replace("ʼ", "'", $line)));
        $split = str_getcsv($line, ",", "\"");
        if(!isset($split[3])){
            continue;
        }
        $title = $split[0];
        $paper = $split[1];
        //$slides = $split[1];
        /*
        if(!file_exists('files/'.str_replace("pdf", "png", $slides))){
            echo "Converting {$slides} to ".str_replace("pdf", "png", $slides)."\n";
            $out = "";
            exec("convert files/{$slides} files/".str_replace("pdf", "png", $slides).' 2>&1', $out);
            if(strpos(implode("", $out), "ERROR") !== false){
                echo "\tERROR: Removing file ".str_replace("pdf", "png", $slides)."\n";
                exec("rm -f files/".str_replace("pdf", "png", $slides));
            }
        }
        if(file_exists('files/'.str_replace("pdf", "png", $slides))){
            $slides = str_replace("pdf", "png", $slides);
        }
        */
        $people = $split[2];

        $peopleArray = explode(",", $people);
        $names = array();
        foreach($peopleArray as $name){
            $person = Person::newFromNameLike(trim($name));
            if($person == null || $person->getName() == ""){
                $names[] = trim($name);
            }
            else{
                $names[] = "[{$person->getUrl()} {$person->getNameForForms()}]";
            }
        }
        
        $project = explode(",", str_replace(" ", "", $split[3]));
        $projectLine = "";
        foreach($project as $pName){
            $p = Project::newFromName($pName);
            if($p != null){
                $projectLine .= "[{$wgServer}{$wgScriptPath}/index.php/{$p->getName()}:Main {$p->getFullName()}]<br />";
            }
        }
        
        addScenario($paper, 
                   str_replace("files/", "", $paper), 
                   "{$title}",
"{{Presentation 
|title = {$title}
|EventName = GRAND Annual Conference {$year}
|presenter = 
|people = ".implode(", ", $names)."
|project = $projectLine
|paper = [[File:".str_replace("files/", "", "$paper")."]]
|slides = 
 }}");
    }
    
    file_put_contents("features/rnotes.feature", $output);
            
?>
