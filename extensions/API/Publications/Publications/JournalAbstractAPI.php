<?php

class JournalAbstractAPI extends PaperAPI{
	
	function JournalAbstractAPI($update=false){
	    parent::PaperAPI($update, "Journal Abstract", "Publication");
	    $this->addPOST("abstract",false,"The abstract of the publication","My Abstract");
	    $this->addPOST("date",false,"The date this publication was published, in the form YYYY-MM-DD","2010-10-15");
	    $this->addPOST("status",false,"The status of the publication.  Can be either Submitted,Under Revision,Published,Rejected","Submitted");
	    $this->addPOST("published_in",false,"The title of the journal that this publication was published in","Scientific Journal");
	    $this->addPOST("pages",false,"The page numbers where this publication was located in the aforementioned journal","183-194");
	    $this->addPOST("volume",false,"The volume of the journal","3");
	    $this->addPOST("number",false,"The numbe of the journal","2");
	    $this->addPOST("publisher",false,"The name of the publisher","My Publishing Company");
	    $this->addPOST("isbn",false,"The ISBN of the publication","90-70002-34-5");
	    $this->addPOST("issn",false,"The ISSN of the publication","90-70002-34-5");
	    $this->addPOST("doi",false,"The doi of the publication","10.1000/182");
	}
}

?>
