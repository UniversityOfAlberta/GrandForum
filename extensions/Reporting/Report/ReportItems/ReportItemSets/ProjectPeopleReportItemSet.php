<?php

class ProjectPeopleReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromHistoricId($this->projectId);
        $proj_id = $this->projectId;
        $role = $this->getAttr("role", NI);
        $roles = explode(",", $role);
        $start = $this->getAttr("startDate", REPORTING_YEAR."-04-01 00:00:00");
        $end = $this->getAttr("endDate", (REPORTING_YEAR+1)."-03-31 23:59:59");
        if($start == ""){
            $start = REPORTING_YEAR."-04-01 00:00:00";
        }
        if($end == ""){
            $end = (REPORTING_YEAR+1)."-03-31 23:59:59";
        }
        if($proj != null){
            $members = array();
            foreach($roles as $role){
                foreach($proj->getAllPeopleDuring($role, $start, $end) as $person){
                    $members[$person->getReversedName()] = $person;
                }
            }
            ksort($members);
            $alreadySeen = array();
            foreach($members as $m){
                if(isset($alreadySeen[$m->getId()])){
                    continue;
                }
                $alreadySeen[$m->getId()] = true;
                $tuple = self::createTuple();
                $tuple['person_id'] = $m->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
