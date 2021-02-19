<?php

class ProjectPeopleNoLeadersReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromHistoricId($this->projectId);
        $start = $this->getAttr("startDate", REPORTING_YEAR."-04-01 00:00:00");
        $end = $this->getAttr("endDate", (REPORTING_YEAR+1)."-03-31 23:59:59");
        $roles = explode(",", $this->getAttr("roles", NI));
        if($proj != null){
            $members = array();
            foreach($roles as $role){
                $members = array_merge($members, $proj->getAllPeopleDuring($role, $start, $end));
            }
            $alreadySeen = array();
            foreach($members as $m){
                if(isset($alreadySeen[$m->getId()])){
                    continue;
                }
                $alreadySeen[$m->getId()] = true;
                if($m->isRole(PL, $proj->getName())){
                    continue;
                }   
                $tuple = self::createTuple();
                $tuple['person_id'] = $m->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
