<?php

class PersonFutureProjectReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $proj = Project::newFromId($this->projectId);
        $projects = array();
        if($this->getReport()->topProjectOnly){
            $projects = array($this->getReport()->project);
        }
        else{
            if($proj != null){
                $tmpprojects = $proj->getSubProjectsDuring(($this->getReport()->year+1).REPORTING_CYCLE_START_MONTH, 
                                                           ($this->getReport()->year+1).REPORTING_CYCLE_END_MONTH);
                foreach($tmpprojects as $project){
                    if($person->isMemberOfDuring($project, ($this->getReport()->year+1).REPORTING_CYCLE_START_MONTH, 
                                                           ($this->getReport()->year+1).REPORTING_CYCLE_END_MONTH)){
                        $projects[] = $project;
                    }
                }
            }
            else{
                $tmpprojects = $person->getProjectsDuring(($this->getReport()->year+1).REPORTING_CYCLE_START_MONTH, 
                                                          ($this->getReport()->year+1).REPORTING_CYCLE_END_MONTH);
                
                foreach($tmpprojects as $project){
                    if(!$project->isSubProject()){
                        $projects[] = $project;
                    }
                }
            }
        }
        if(is_array($projects)){
            foreach($projects as $proj){
                if($proj->getPhase() == 2){
                    $tuple = self::createTuple();
                    $tuple['project_id'] = $proj->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
