<?php
class PersonContributionsReportItemSet extends ReportItemSet {
    function getData(){
        $phase = $this->getAttr("phase");
        $data = array();
        $person = Person::newFromId($this->personId);
	$contributions = $person->getContributionsDuring(2015);
	if(is_array($contributions)){
            foreach($contributions as $contribution){
                $tuple = self::createTuple();
                $tuple['project_id'] = $contribution->id;
                $data[] = $tuple;
            }
        }
        return $data;
    }
}
?>
