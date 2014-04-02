<?php

class GroupSeminarAPI extends PaperAPI{
	function GroupSeminarAPI($update=false){
	    global $config;
	    parent::PaperAPI($update, "Group Seminar", "Presentation");
	    $this->addPOST("description",false,"The description of the presentation","My Description");
	    $this->addPOST("date",false,"The date of the presentation","2011-06-12");
	    $this->addPOST("status",false,"Whether or not the presentation was invited.  Can be either Invited or Not Invited","Not Invited");
	    $this->addPOST("event_title",false,"The name of the event","{$config->getValue('networkName')} Conference 2011");
	    $this->addPOST("host_institution",false,"The uniiversity of company where presented","Vancouver");
	    $this->addPOST("host_department", false, "The department where presented","{$config->getValue('networkName')}");
	    $this->addPOST("host_research_group", false, "The research group to whom presented","{$config->getValue('networkName')}");
	    $this->addPOST("url", false, "URL to a web site describing the event","http://www.grand-nce.ca/");
	}
}

?>
