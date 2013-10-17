<?php

class SpinOffAPI extends PaperAPI{
	function SpinOffAPI($update=false){
	    parent::PaperAPI($update, "Spin-Off", "Activity");
	    $this->addPOST("description",false,"The description of the Spin-Off","My Description");
	    $this->addPOST("date",false,"The date of that the Spin-Off was created","2011-06-12");
	}
}

?>
