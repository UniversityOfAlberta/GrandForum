<?php

class PersonSubLeadSubProjectReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $projects = array();
        $project = Project::newFromHistoricId($this->projectId);
        foreach($project->getSubProjects() as $sub){
            if($person->isRole(PL, $sub)){
                $projects[] = $sub;
            }
        }
        if(is_array($projects)){
            foreach($projects as $proj){
                $tuple = self::createTuple();
                $tuple['project_id'] = $proj->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
