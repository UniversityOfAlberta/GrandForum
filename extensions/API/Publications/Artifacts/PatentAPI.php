<?php

class PatentAPI extends PaperAPI{
	
	function PatentAPI($update=false){
	    parent::PaperAPI($update, "Patent", "Artifact");
	    $this->addPOST("description",false,"The description of the artifact","My Description");
	    $this->addPOST("date",false,"The date this patent was accepted","2010-10-15");
	    $this->addPOST("status",false,"The status of the artifact.  Can be either 'Peer Reviewed' or 'Not Peer Reviewed'","Peer Reviewed");
	    $this->addPOST("number",false, "The patent number", "7478099");
	}
}

?>
