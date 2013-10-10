<?php
include_once('../commandLine.inc');
$all_people = Person::getAllPeople('all');
foreach($all_people as $person){
    $uid = $person->getId();
    
    $sd = new SessionData($person->getId(), 'Special:Report', SD_BUDGET_CSV);
	$data = $sd->fetch(false);
	if(count($data) > 0){
	    $blob = new ReportBlob(BLOB_EXCEL, 2010, $uid);
        $blob->store($data, ReportBlob::create_address(RP_RESEARCHER, RES_BUDGET));
	}

	/**********************************************************************
	 * Supplemental Budget ************************************************
	 **********************************************************************/
	$sd = new SessionData($person->getId(), 'Special:SupplementalReport', SD_SUPPL_BUDGET);
	$data = $sd->fetch(false);
	if (strlen($data) > 0) {
		$blob = new ReportBlob(BLOB_EXCEL, 2010, $uid);
		$blob->store($data, ReportBlob::create_address(RP_RESEARCHER, RES_ALLOC_BUDGET));
	}
}
?>
