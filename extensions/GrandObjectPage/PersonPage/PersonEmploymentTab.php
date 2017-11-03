<?php

class PersonEmploymentTab extends AbstractTab {

    var $person;
    var $visibility;
    var $category;
    var $startRange;
    var $endRange;

    function PersonEmploymentTab($person, $visibility){
        global $config;
        parent::AbstractTab("Education/Employment");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->tooltip = "Contains a table with a list of this Person's eduction/employment history.";
    }

    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return "";
        }

        $education = array();
        $employment = array();
        foreach($this->person->getUniversities() as $university){
            $startYear = substr($university['start'], 0, 4)." - ";
            $endYear = substr($university['end'], 0, 4);
            if($startYear == "0000 - "){
                $startYear = "";
            }
            if($endYear == "0000"){
                $endYear = "Present";
            }
            if(in_array($university['position'], array("Undergraduate", "Graduate Student - Master's", "Graduate Student - Doctoral", "Post-Doctoral Fellow"))){
                $education[$university['university']][] = "{$university['position']}, {$university['department']}<br />
                                                           {$startYear}{$endYear}<br />";
            }
            else{
                $employment[$university['university']][] = "{$university['position']}, {$university['department']}<br />
                                                            {$startYear}{$endYear}<br />";
            }
        }
        
        $this->html .= "<h2>Education History</h2>";
        foreach($education as $key => $item){
            $this->html .= "<h3>{$key}</h3>".implode("<br style='line-height:0.5em;' />", $item);
        }
        
        $this->html .= "<h2>Employment History</h2>";
        foreach($employment as $key => $item){
            $this->html .= "<h3>{$key}</h3>".implode("<br style='line-height:0.5em;' />", $item);
        }
        
    }

}    
?>
