<?php

class AwardsAPI extends PaperAPI{
	
	function AwardsAPI($update=false){
	    parent::PaperAPI($update, "Award", "Award");
	    $this->addPOST("description",false,"The description of the Award","My Description");
	    $this->addPOST("date",false,"The date of when this award was presented","2010-10-10");
	    $this->addPOST("url",false,"The URL of the award description","http://www.ieee.org/about/awards/medals/vonneumann.html");
	}
}

?>
