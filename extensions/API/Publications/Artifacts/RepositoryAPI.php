<?php

class RepositoryAPI extends PaperAPI{
	
	function RepositoryAPI($update=false){
	    parent::PaperAPI($update, "Repository", "Artifact");
	    $this->addPOST("description",false,"The description of the artifact","My Description");
	    $this->addPOST("date",false,"The date this artifact was published","2010-10-15");
	    $this->addPOST("status",false,"The status of the artifact.  Can be either 'Peer Reviewed' or 'Not Peer Reviewed'","Peer Reviewed");
	    $this->addPOST("url",false,"The location of the repository","http://repositories.lib.utexas.edu/");
	}
}
?>
