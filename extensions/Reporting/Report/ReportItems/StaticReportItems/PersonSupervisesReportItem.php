<?php

class PersonSupervisesReportItem extends StaticReportItem {

    function getHTML(){
        global $wgServer, $wgScriptPath;
        
        $person = Person::newFromId($this->personId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        
        $tab = new PersonGradStudentsTab($person, array());

        $callback = new ReportItemCallback($this);

        $item = "<h4>Graduate Students (Supervised or Co-supervised): {$callback->getUserGradCount()}</h4>";
        $item .= $tab->supervisesHTML(Person::$studentPositions['grad'], 
                                      $this->getReport()->startYear."-07-01", 
                                      $this->getReport()->year."-06-30");
        
        $item .= "<br /><h4>Post-doctoral Fellows and Research Associates (Supervised or Co-supervised): {$callback->getUserFellowCount()}</h4>";
        $item .= $tab->supervisesHTML(Person::$studentPositions['pdf'], 
                                      $this->getReport()->startYear."-07-01", 
                                      $this->getReport()->year."-06-30");
        
        $item .= "<br /><h4>Technicians: {$callback->getUserTechCount()}</h4>";
        $item .= $tab->supervisesHTML(Person::$studentPositions['tech'], 
                                      $this->getReport()->startYear."-07-01", 
                                      $this->getReport()->year."-06-30");
        
        $item .= "<br /><h4>Undergraduates: {$callback->getUserUgradCount()}</h4>";
        $item .= $tab->supervisesHTML(Person::$studentPositions['ugrad'], 
                                      $this->getReport()->startYear."-07-01", 
                                      $this->getReport()->year."-06-30");
        
        return $item;
    }

    function render(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getHTML();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
