<?php

class ElitePostingAPI extends PostingAPI {
    
    static $className = "ElitePosting";
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        if(trim($this->POST('companyName')) == ""){
            $this->throwError("A company name must be provided");
        }
        return true;
    }
    
    function extraVars($posting){
        $posting->visibility = $this->POST('visibility');
        $posting->language = "English";
        $posting->companyName = $this->POST('companyName');
        $posting->companyProfile = $this->POST('companyProfile');
        $posting->reportsTo = $this->POST('reports_to');
        $posting->basedAt = $this->POST('based_at');
        $posting->responsibilities = $this->POST('responsibilities');
        $posting->qualifications = $this->POST('qualifications');
        $posting->skills = $this->POST('skills');
    }
	
}

?>
