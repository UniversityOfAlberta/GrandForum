<?php

class PersonCVGrantsReportItemSet extends ReportItemSet {
    
    function getData(){
        $phase = $this->getAttr("phase");
        $data = array();
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $grants = $person->getGrantsBetween($start, $end);
        if(is_array($grants)){
            foreach($grants as $grant){
                if(strpos($grant->getProjectId(), 'B') !== 0 &&
                   strpos($grant->getProjectId(), 'N') !== 0 &&
                   strpos($grant->getProjectId(), 'G126') !== 0 &&
                   strpos($grant->getProjectId(), 'G099') !== 0 &&
                   strpos($grant->getProjectId(), 'G022') !== 0 &&
                   $grant->getTotal() >= 5000 &&
                   $grant->getRole() != "Student" &&
                   strstr(strtolower($grant->getTitle()), "facsci") === false &&
                   strstr(strtolower($grant->getTitle()), "fos") === false &&
                   strstr(strtolower($grant->getTitle()), "fac sci") === false &&
                   strstr(strtolower($grant->getTitle()), "start up") === false &&
                   strstr(strtolower($grant->getTitle()), "startup") === false &&
                   strstr(strtolower($grant->getTitle()), "gen res") === false &&
                   strstr(strtolower($grant->getTitle()), "general research") === false &&
                   strstr(strtolower($grant->getTitle()), "ind cost") === false &&
                   strstr(strtolower($grant->getTitle()), "res allow") === false &&
                   strstr(strtolower($grant->getTitle()), "allowance") === false &&
                   strstr(strtolower($grant->getTitle()), "phd") === false &&
                   strstr(strtolower($grant->getTitle()), "pdf") === false &&
                   strstr(strtolower($grant->getTitle()), "crc") === false &&
                   strstr(strtolower($grant->getTitle()), "research allowance") === false &&
                   strstr(strtolower($grant->getTitle()), "allw") === false &&
                   strstr(strtolower($grant->getTitle()), "pot account") === false &&
                   strstr(strtolower($grant->getTitle()), "revenue") === false &&
                   strstr(strtolower($grant->getTitle()), "staff") === false &&
                   strstr(strtolower($grant->getDescription()), "allowance") === false &&
                   strstr(strtolower($grant->getDescription()), "start up") === false &&
                   strstr(strtolower($grant->getDescription()), "startup") === false &&
                   strstr(strtolower($grant->getDescription()), "student") === false &&
                   strstr(strtolower($grant->getDescription()), "pdf") === false &&
                   strstr(strtolower($grant->getDescription()), "postdoc") === false &&
                   strstr(strtolower($grant->getDescription()), "general research") === false &&
                   array_search($grant->getSeqNo(), array(33, 8, 9, 34,35, 51, 53, 65, 109)) === false &&
                   strstr(strtolower($grant->getProgDescription()), "phd") === false &&
                   strstr(strtolower($grant->getProgDescription()), "student") === false &&
                   strstr(strtolower($grant->getProgDescription()), "studentship") === false &&
                   strstr(strtolower($grant->getProgDescription()), "stdshp") === false &&
                   strstr(strtolower($grant->getProgDescription()), "canada research chair") === false &&
                   strstr(strtolower($grant->getProgDescription()), "postdoc") === false &&
                   strstr(strtolower($grant->getProgDescription()), "pdf") === false &&
                   strstr(strtolower($grant->getProgDescription()), "crc") === false){
                    $tuple = self::createTuple();
                    $tuple['project_id'] = $grant->id;
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
