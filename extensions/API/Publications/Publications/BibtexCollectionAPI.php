<?php

class BibtexCollectionAPI extends PaperAPI{
	
	function BibtexCollectionAPI($update=false){
	    parent::PaperAPI($update, "BibTex Collection", "Publication");
	    $this->addPOST("abstract",false,"The abstract of the publication","My Abstract");
	    $this->addPOST("date",false,"The date this publication was published, in the form YYYY-MM-DD","2010-10-15");
	    $this->addPOST("status",false,"The status of the publication.  Can be either Submitted,Under Revision,Published,Rejected","Submitted");
	    $this->addPOST("book_title",false,"The title of the book that this chapter is in", "My Book");
	    $this->addPOST("editors",false,"The editors of the book","Editor1, Editor2");
	    $this->addPOST("pages",false,"The pages where this chapter appears", "28-40");
	    $this->addPOST("publisher",false,"The name of the publisher","My Publishing Company");
	    $this->addPOST("address",false,"The address of the publisher","City, State/Province/Country");
	    $this->addPOST("isbn",false,"The ISBN of the publication","90-70002-34-5");
	    $this->addPOST("issn",false,"The ISSN of the publication","90-70002-34-5");
	    $this->addPOST("doi",false,"The doi of the publication","10.1000/182");
      $this->addPOST("url",false,"Link to a copy of the publication","http://mySite.org/myPaper.pdf");
      $this->addPOST("peer_reviewed",false,"Whether or not the publication is peer reviewed","Yes");
	}
}

?>
