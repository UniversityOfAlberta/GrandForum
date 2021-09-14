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
        $posting->reportsTo = $this->POST('reportsTo');
        $posting->basedAt = $this->POST('basedAt');
        $posting->training = $this->POST('training');
        $posting->responsibilities = $this->POST('responsibilities');
        $posting->qualifications = $this->POST('qualifications');
        $posting->skills = $this->POST('skills');
        $posting->level = $this->POST('level');
        $posting->positions = $this->POST('positions');
        if($posting->isAllowedToEdit()){
            $posting->comments = $this->POST('comments');
        }
    }
	
}

?>
