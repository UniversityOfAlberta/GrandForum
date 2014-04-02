<?php

class WIPAPI extends PaperAPI{
	function WIPAPI($update=false){
	    global $config;
	    parent::PaperAPI($update, "WIP", "Presentation");
	    $this->addPOST("description",false,"The description of the presentation","My Description");
	    $this->addPOST("date",false,"The date of the presentation","2011-06-12");
	    $this->addPOST("status",false,"Whether or not the presentation was invited.  Can be either Invited or Not Invited","Not Invited");
	    $this->addPOST("event_title",false,"The name of the event","{$config->getValue('networkName')} Conference 2011");
	    $this->addPOST("event_location",false,"The location of the event","Vancouver");
	    $this->addPOST("organizing_body", false, "The association organizing this event","{$config->getValue('networkName')}");
	    $this->addPOST("url", false, "URL to a web site describing the event","http://www.grand-nce.ca/");
	}
}

?>
