<?php

class WhitePaperAPI extends PaperAPI{
	
	function WhitePaperAPI($update=false){
	    parent::PaperAPI($update, "White Paper", "Publication");
	    $this->addPOST("abstract",false,"The abstract of the publication","My Abstract");
	    $this->addPOST("date",false,"The date this publication was published, in the form YYYY-MM-DD","2010-10-15");
	    $this->addPOST("status",false,"The status of the publication.  Can be either Submitted,Under Revision,Published,Rejected","Submitted");
	    $this->addPOST("published_in",false,"The title of the journal that this publication was published in","Technical Series Special Issue");
	    $this->addPOST("publisher",false,"The name of the publisher","My Publishing Company");
	    $this->addPOST("url",false,"The url of this white paper","http://www.dh.gov.uk/en/Publicationsandstatistics/Publications/PublicationsPolicyAndGuidance/DH_117353");
	    $this->addPOST("editor",false,"The editor of this white paper","Mr Editor");
      $this->addPOST("url",false,"Link to a copy of the publication","http://mySite.org/myPaper.pdf");
      $this->addPOST("peer_reviewed",false,"Whether or not the publication is peer reviewed","Yes");
	}
}

?>
