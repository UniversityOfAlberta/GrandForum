<?php

class ThemeProjectsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $all = (strtolower($this->getAttr("all", "false")) == "true");
        $theme = Theme::newFromId($this->projectId);
        $projects = $theme->getProjects($all);
        foreach($projects as $project){
            $tuple = self::createTuple();
            $tuple['project_id'] = $project->getId();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
