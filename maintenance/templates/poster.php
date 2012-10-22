<?php
    require_once( "../commandLine.inc" );
    $year = 2012;
    $output = <<<EOF
Feature: Posters

    Scenario: Preparing pages for Posters insertion
        Given I am logged in as "Admin" using password "BigLasagna"
            And I go to "Conference:GRAND_Annual_Conference_{$year}?action=edit&editType=template"
            And I fill in "Conference0|postersMAIN" with ""
            And I press "Save page"\n\n
EOF;
    
    function addScenario($paper, $paperName, $slides, $slidesName, $pageName, $pageText){
        global $output, $year;
        $pageName = str_replace(" ", "_", $pageName);
        $pageText = str_replace("\n", "\\n", $pageText);
        $urlName = str_replace("_", " ", $pageName);

        $output .= <<<EOF
    Scenario: Setting up Poster:{$pageName}
        Given I am logged in as "Admin" using password "BigLasagna"
        When I go to "Special:Upload?wpForReUpload=1"
            And I attach the file "files/$paper" to "wpUploadFile"
            And I press "Upload file"
            And I go to "Special:Upload?wpForReUpload=1"
            And I attach the file "files/$slides" to "wpUploadFile"
            And I press "Upload file"
            And I go to "Poster:{$pageName}?action=edit"
            And I fill in "wpTextbox1" with multiline "$pageText"
            And I press "Save page"
            And I go to "Conference:GRAND_Annual_Conference_$year?action=edit&editType=template"
            And I append "\\n*[[Poster:{$pageName} | {$urlName}]]" to "Conference0|postersMAIN"
            And I press "Save page"\n\n
EOF;
    }
    
    $string = file_get_contents("csv/posters.csv");
    foreach(explode("\n", $string) as $line){
        $line = str_replace("”", "'", str_replace("“", "'", str_replace("ʼ", "'", $line)));
        $split = str_getcsv($line, ",", "\"");
        if(!isset($split[4])){
            continue;
        }
        $title = $split[0];
        $paper = $split[1];
        $poster = $split[2];
        if(!file_exists('files/'.str_replace("pdf", "png", $poster))){
            echo "Converting {$poster} to ".str_replace("pdf", "png", $poster)."\n";
            $out = "";
            exec("convert files/{$poster} files/".str_replace("pdf", "png", $poster).' 2>&1', $out);
            if(strpos(implode("", $out), "ERROR") !== false){
                echo "\tERROR: Removing file ".str_replace("pdf", "png", $poster)."\n";
                exec("rm -f files/".str_replace("pdf", "png", $poster));
            }
        }
        if(file_exists('files/'.str_replace("pdf", "png", $poster))){
            $poster = str_replace("pdf", "png", $poster);
        }
        $people = $split[3];
        $project = $split[4];
        $p = Project::newFromName($project);
        
        $peopleArray = explode(",", $people);
        $names = array();
        foreach($peopleArray as $name){
            $person = Person::newFromNameLike(trim($name));
            if($person == null || $person->getName() == ""){
                $splitted = explode(" ", trim($name));
                if(count($splitted) == 2){ 
                    $person = Person::newFromNameLike($splitted[1].' '.$splitted[0]);
                }
                if($person == null || $person->getName() == ""){
                    $names[] = trim($name);
                }
                else{
                    $names[] = "[{$person->getUrl()} {$person->getNameForForms()}]";
                }
            }
            else{
                $names[] = "[{$person->getUrl()} {$person->getNameForForms()}]";
            }
        }
        
        addScenario($paper, 
                   str_replace("files/", "", $paper), 
                   $poster,
                   str_replace("files/", "", $poster),
                   "{$title}",
"{{Poster
|EventName = GRAND Annual Conference {$year}
|paper = [[File:".str_replace("files/", "", "$paper")."]]
|people = ".implode(", ", $names)."
|poster= [[File:".str_replace("files/", "", "$poster")."|thumb|left]]
|project = [[{$project}:Main | {$project}]]
}}
");
    }
    
    file_put_contents("features/poster.feature", $output);
            
?>
