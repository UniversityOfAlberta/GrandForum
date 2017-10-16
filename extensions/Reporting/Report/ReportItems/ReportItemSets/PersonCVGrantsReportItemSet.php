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
                if(strtolower($grant->getSponsor()) == "university of alberta" &&
                   array_search($grant->getSeqNo(), array(0, 35)) !== false){
                    // Rule 1: inside Fac of Science and start up funds
                    //echo "Rule 1 {$grant->getTitle()}<br />";
                    continue;
                }
                else if($grant->getTotal() == 0){
                    // Rule 2: blank PI names or 0 funding signal a reason to exclude
                    //echo "Rule 2 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'Z') === 0){
                    // Rule 101: Not interesting – internal facilities support/ centre support
                    //echo "Rule 101 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'Y224') === 0){
                    // Rule 102: AITF Centre support
                    //echo "Rule 102 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'Y000') === 0){
                    // Rule 103: Internal Centre Funding
                    //echo "Rule 103 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'B') === 0 ||
                        strpos($grant->getProjectId(), 'P') === 0 ||
                        strtolower($grant->getSponsor()) === "national research council of canada" ||
                        strstr(strtolower($grant->getDescription()), "billing") !== false ||
                        strstr(strtolower($grant->getDescription()), "charges") !== false ||
                        strstr(strtolower($grant->getDescription()), "service") !== false ||
                        strstr(strtolower($grant->getDescription()), "maintenance") !== false ||
                        strstr(strtolower($grant->getDescription()), "assistant") !== false ||
                        strstr(strtolower($grant->getDescription()), "lecture") !== false ||
                        strstr(strtolower($grant->getDescription()), "expenses") !== false){
                    // Rule 104: Project ID scan – Billing/Journal productions/services
                    //echo "Rule 104 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'N') === 0){
                    // Rule 105: Other internally transferred research funds
                    //echo "Rule 105 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'D') === 0 ||
                        strpos($grant->getProjectId(), 'W') === 0 ||
                        strstr(strtolower($grant->getDescription()), "donations") !== false ||
                        strstr(strtolower($grant->getDescription()), "donation") !== false ||
                        strstr(strtolower($grant->getTitle()), "rconf") !== false ||
                        strstr(strtolower($grant->getTitle()), "conf") !== false ||
                        strstr(strtolower($grant->getTitle()), "donat") !== false ||
                        strstr(strtolower($grant->getTitle()), "congress") !== false ||
                        strstr(strtolower($grant->getTitle()), "conference") !== false ||
                        strstr(strtolower($grant->getTitle()), "meeting") !== false ||
                        strstr(strtolower($grant->getTitle()), "workshop") !== false ||
                        strstr(strtolower($grant->getTitle()), "school") !== false ||
                        strstr(strtolower($grant->getTitle()), "symposium") !== false){
                    // Rule 106: donations
                    //echo "Rule 106 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strpos($grant->getProjectId(), 'G022') === 0 ||
                        strpos($grant->getProjectId(), 'G099') === 0 ||
                        strstr(strtolower($grant->getDescription()), "general research") !== false){
                    // Rule 107: “General Research Funds”
                    //echo "Rule 107 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strtolower($grant->getRole()) == "student" ||
                        strstr(strtolower($grant->getProgDescription()), "fellowship") !== false ||
                        strstr(strtolower($grant->getProgDescription()), "student") !== false ||
                        strstr(strtolower($grant->getProgDescription()), "scholarship") !== false){
                    // Rule 200: awards to students seems to capture all student cases
                    //echo "Rule 200 {$grant->getTitle()}<br />";
                    continue;
                }
                else if(strtolower($grant->getSponsor()) == "university of alberta" &&
                    (array_search($grant->getSeqNo(), array(33, 51, 53, 55, 65, 137)) !== false ||
                     strstr(strtolower($grant->getProjectId()), "triumf") !== false
                    )){
                    // Rule 300: Miscellaneous not interesting for different kinds of reasons
                    //echo "Rule 300 {$grant->getTitle()}<br />";
                    continue;
                }
                $tuple = self::createTuple();
                $tuple['project_id'] = $grant->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
