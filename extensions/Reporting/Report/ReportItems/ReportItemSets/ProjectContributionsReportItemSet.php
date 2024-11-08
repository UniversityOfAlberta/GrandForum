<?php

class ProjectContributionsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $project = Project::newFromHistoricId($this->projectId);
        $start = $this->getAttr('start', REPORTING_CYCLE_START);
        $end = $this->getAttr('end', REPORTING_CYCLE_END);
        $contributions = $project->getContributionsDuring($start, $end);
        if(is_array($contributions)){
            foreach($contributions as $contribution){
                $tuple = self::createTuple();
                $tuple['product_id'] = $contribution->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
