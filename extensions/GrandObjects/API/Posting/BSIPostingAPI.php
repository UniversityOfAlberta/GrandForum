<?php

class BSIPostingAPI extends PostingAPI {
    
    static $className = "BSIPosting";
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        return true;
    }
    
    function extraVars($posting){
        $posting->visibility = "Publish";
        $posting->language = "English";
        $posting->type = $this->POST('type');
        $posting->partnerName = $this->POST('partnerName');
        $posting->city = $this->POST('city');
        $posting->province = $this->POST('province');
        $posting->country = $this->POST('country');
        $posting->firstName = $this->POST('firstName');
        $posting->lastName = $this->POST('lastName');
        $posting->email = $this->POST('email');
        $posting->positions = $this->POST('positions');
        $posting->positionsText = $this->POST('positionsText');
        if(is_array($this->POST('discipline'))){
            $posting->discipline = implode("; ", $this->POST('discipline'));
        }
        else{
            $posting->discipline = $this->POST('discipline');
        }
        $posting->about = $this->POST('about');
        $posting->skills = $this->POST('skills');
        $posting->deletedText = str_replace(">", "&gt;", str_replace("<", "&lt;", $this->POST('deletedText')));
    }
	
}

?>
