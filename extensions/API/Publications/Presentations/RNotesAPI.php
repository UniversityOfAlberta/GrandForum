<?php

class RNotesAPI extends PaperAPI{
	function RNotesAPI($update=false){
	    parent::PaperAPI($update, "RNotes", "Presentation");
	    $this->addPOST("description",false,"The description of the presentation","My Description");
	    $this->addPOST("date",false,"The date of the presentation","2011-06-12");
	    $this->addPOST("status",false,"The status of the presentation.  Can be either Invited,Not Invited","Not Invited");
	    $this->addPOST("conference",false,"The name of the conference","GRAND Conference 2011");
	    $this->addPOST("location",false,"The location of the presentation","Vancouver Conference Centre");
	    $this->addPOST("organizing_body", false, "The person/organization who organized this activity","GRAND");
	    $this->addPOST("url", false, "The url(s) about this activity","http://www.grand-nce.ca/");
	}
}

?>
