<?php

class ProceedingsPaperAPI extends PaperAPI{
	
	function ProceedingsPaperAPI($update=false){
	    parent::PaperAPI($update, "Proceedings Paper", "Publication");
	    $this->addPOST("abstract",false,"The abstract of the publication","My Abstract");
	    $this->addPOST("date",false,"The date this publication was published, in the form YYYY-MM-DD","2010-10-15");
	    $this->addPOST("event_title",false,"The title of the event where this publication was published","SIGMOD");
	    $this->addPOST("event_location",false,"The location of the event where this publication was published","Vancouver");
	    $this->addPOST("book_title",false,"The title of the book, or proceedings that this publication was in","2009 SIGMOD Proceedings");
	    $this->addPOST("status",false,"The status of the publication.  Can be either Submitted,Accepted,Under Revision,Published,Rejected","Submitted");
	    $this->addPOST("pages",false,"The page numbers where this publication was located.","183-194");
	    $this->addPOST("publisher",false,"The name of the publisher","My Publishing Company");
	    $this->addPOST("isbn",false,"The ISBN of the publication","90-70002-34-5");
	    $this->addPOST("issn",false,"The ISSN of the publication","90-70002-34-5");
	    $this->addPOST("doi",false,"The doi of the publication","10.1000/182");
	    $this->addPOST("url",false,"Link to a copy of the publication","http://mySite.org/myPaper.pdf");
	    $this->addPOST("peer_reviewed",false,"Whether or not the publication is peer reviewed","Yes");
	}
}
?>
