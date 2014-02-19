<?php

class ReviewProjectChampionsReportItemSet extends ReportItemSet {
    
    function getData(){
        $onlyShowStarted = (strtolower($this->getAttr("onlyShowStarted", "false")) == "true");
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $champions = array();
            foreach($proj->getChampionsDuring(($this->getReport()->year+1).REPORTING_PRODUCTION_MONTH, ($this->getReport()->year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                $report = new DummyReport(RP_CHAMP, $champ['user'], $proj, $this->getReport()->year);
                if(!$onlyShowStarted || 
                   ($report->hasStarted())){
                    $champions[$champ['user']->getId()] = $champ;
                }
            }
            if(!$proj->isSubProject()){
                foreach($proj->getSubProjects() as $sub){
                    foreach($sub->getChampionsDuring(($this->getReport()->year+1).REPORTING_PRODUCTION_MONTH, ($this->getReport()->year+1).REPORTING_RMC_MEETING_MONTH) as $champ){
                        $report = new DummyReport(RP_CHAMP, $champ['user'], $proj, $this->getReport()->year);
                        if(!$onlyShowStarted || 
                           ($report->hasStarted())){
                            $champions[$champ['user']->getId()] = $champ;
                        }
                    }
                }
            }
            $alreadySeen = array();
            foreach($champions as $c){
                if(isset($alreadySeen[$c['user']->getId()])){
                    continue;
                }
                $alreadySeen[$c['user']->getId()] = true; 
                $tuple = self::createTuple();
                $tuple['person_id'] = $c['user']->getId();
                $data[$c['user']->getReversedName()] = $tuple;
            }
            ksort($data);
        }
        return $data;
    }
}

?>
