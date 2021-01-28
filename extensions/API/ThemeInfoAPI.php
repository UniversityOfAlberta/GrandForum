<?php

class ThemeInfoAPI extends API{

    function __construct(){
        $this->addGET("themeName", false, "The name of the Theme", "(Big) Data");
        $this->addGET("projectName", false, "The name of the project", "PROJ1");
        $this->addGET("subProjectName", false, "The name of the subProject", "SUB1");
    }

    function processParams($params){
        $i = 0;
        foreach($params as $param){
            if($i == 1){
                $_GET['themeName'] = $param;
            }
            else if($i == 2){
                $_GET['projectName'] = $param;
            }
            else if($i == 3){
                $_GET['subProjectName'] = $param;
            }
            $i++;
        }
    }

    function doAction(){
        $start = microtime(true);
        header("Content-type: application/json");
        echo $this->outputJSON();
        exit;
    }
    
    function outputJSON(){
        $json = array();
        $theme = null;
        $project = null;
        $subProject = null;
        
        if(isset($_GET['themeName'])){
            if($_GET['themeName'] == "Strategic"){
                $theme = new Theme(array());
                $theme->acronym = "Strategic";
                $theme->name = "Partner Projects";
                $theme->description = "GRAND will have a small number of Partner Projects that will be characterized by their high risk / high reward and cross-cutting nature and by their potential to have a very significant impact on the Canadian digital media sector. Partner Projects will be high visibility co-investments with external partners. The projects will be tightly coupled, they will have a higher degree of autonomy in allocating their resources, and they will be expected to have much greater engagement with and larger contributions from the receptor community than regular projects within GRAND.";
            }
            else{
                $theme = Theme::newFromName($_GET['themeName']);
            }
        }
        if(isset($_GET['projectName'])){
            $project = Project::newFromName($_GET['projectName']);
        }
        if(isset($_GET['subProjectName'])){
            $subProject = Project::newFromName($_GET['subProjectName']);
        }
        
        if($subProject != null){
            $json['acronym'] = $subProject->getName();
            $json['fullName'] = $subProject->getFullName();
            $json['description'] = $subProject->getDescription();
            $json['leaders'] = array();
            foreach($subProject->getLeaders() as $leader){
                $json['leaders'][] = array('name' => $leader->getNameForForms(),
                                           'university' => $leader->getUni(),
                                           'url' => $leader->getUrl());
            }
        }
        else if($project != null){
            $json['acronym'] = $project->getName();
            $json['fullName'] = $project->getFullName();
            $json['description'] = $project->getDescription();
            $json['theme'] = $project->getChallenge()->getAcronym();
            $json['leaders'] = array();
            $json['subprojects'] = array();
            foreach($project->getLeaders() as $leader){
                $json['leaders'][] = array('name' => $leader->getNameForForms(),
                                           'university' => $leader->getUni(),
                                           'url' => $leader->getUrl());
            }
            foreach($project->getSubProjects() as $sub){
                $json['subprojects'][] = array('name' => $sub->getName(),
                                               'fullName' => $sub->getFullName());
            }
        }
        else if($theme != null){
            $json['name'] = $theme->getAcronym();
            $json['fullName'] = $theme->getName();
            $json['description'] = $theme->getDescription();
            $json['leaders'] = array();
            $json['projects'] = array();
            $leaders = $theme->getLeaders();
            foreach($leaders as $leader){
                $json['leaders'][] = array('name' => $leader->getNameForForms(),
                                           'university' => $leader->getUni(),
                                           'url' => $leader->getUrl());
            }
            foreach(Project::getAllProjects() as $project){
                if($project->getChallenge()->getAcronym() == $theme->getAcronym()){
                    $json['projects'][] = array('name' => $project->getName(),
                                                'fullName' => $project->getFullName());
                }
                if($theme->getAcronym() == "Strategic" && $project->isBigBet()){
                    $json['projects'][] = array('name' => $project->getName(),
                                                'fullName' => $project->getFullName());
                }
            }
        }
        else {
            $themes = Theme::getAllThemes(PROJECT_PHASE);
            $strategic = new Theme(array());
            $theme->acronym = "Strategic";
            $strategic->name = "Partner Projects";
            $themes[] = $strategic;
            foreach($themes as $theme){
                $data['name'] = $theme->getAcronym();
                $data['fullName'] = $theme->getName();
                $data['leaders'] = array();
                $data['projects'] = array();
                $leaders = $theme->getLeaders();
                foreach($leaders as $leader){
                    $data['leaders'][] = array('name' => $leader->getNameForForms(),
                                               'university' => $leader->getUni(),
                                               'url' => $leader->getUrl());
                }
                foreach(Project::getAllProjects() as $project){
                    if($project->getChallenge()->getAcronym() == $theme->getAcronym()){
                        $data['projects'][] = array('name' => $project->getName(),
                                                    'fullName' => $project->getFullName());
                    }
                    if($theme->getAcronym() == "Strategic" && $project->isBigBet()){
                        $data['projects'][] = array('name' => $project->getName(),
                                                    'fullName' => $project->getFullName());
                    }
                }
                $json[] = $data;
            }
        }
        return json_encode($json);
    }
    
    function isLoginRequired(){
        return false;
    }
}

?>
