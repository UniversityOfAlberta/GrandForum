<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    $wgUser = User::newFromName(""); // Name of Chair/EA
    $me = Person::newFromWgUser();
    
    $report = new DummyReport("RP_FEC", $me);
    $section = new EditableReportSection();
    $section->setParent($report);
    
    $itemSet = new DepartmentPeopleReportItemSet();
    $itemSet->setAttr("department", $me->getDepartment());
    $itemSet->setParent($section);
    
    $dummyReport = new DummyReport("FEC", $me);
    foreach($itemSet->getData() as $tuple){
        $faculty = Person::newFromId($tuple['person_id']);
        $wgUser = $faculty->getUser();
        
        Product::$cache = array();
        Product::$dataCache = array();
        Product::$exclusionCache = null;
        
        unset($_GET['preview']);
        unset($_GET['dpi']);
        initGlobals();
        
        $blob = new ReportBlob(BLOB_ARRAY, YEAR, $faculty->getId(), 0);
	    $blob_address = ReportBlob::create_address("RP_FEC", "FEC_SUBMIT", "LOCK", 0);
	    $blob->load($blob_address);
	    $blob_data = $blob->getData();
	    
	    if(isset($blob_data['lock'][1]) && $blob_data['lock'][1] == "Lock"){
	        continue;
	    }
        
        echo "{$faculty->getName()}: Generating...";
        $dummyReport->person = $faculty;
        $dummyReport->generatePDF($faculty, false, true);
        echo "Done\n";
    }
    
?>
