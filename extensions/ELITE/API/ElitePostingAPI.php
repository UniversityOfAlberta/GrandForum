<?php

class ElitePostingAPI extends PostingAPI {
    
    static $className = "ElitePosting";
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        return true;
    }
    
    function extraVars($posting){
        $posting->visibility = $this->POST('visibility');
        $posting->language = "English";
        $posting->type = $this->POST('type');
        $posting->extra = $this->POST('extra');
        if($posting->isAllowedToEdit()){
            $posting->comments = $this->POST('comments');
        }
    }
	
}

?>
