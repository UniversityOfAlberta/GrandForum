<?php

class BSIPostingAPI extends PostingAPI {
    
    static $className = "BSIPosting";
    
    function validate(){
        return true;
    }
    
    function extraVars($posting){
        $posting->visibility = "Publish";
        $posting->language = "English";
        $posting->partnerName = $this->POST('partnerName');
        $posting->city = $this->POST('city');
        $posting->province = $this->POST('province');
        $posting->country = $this->POST('country');
        $posting->firstName = $this->POST('firstName');
        $posting->lastName = $this->POST('lastName');
        $posting->email = $this->POST('email');
        $posting->positions = $this->POST('positions');
        if(is_array($this->POST('discipline'))){
            $posting->discipline = implode(", ", $this->POST('discipline'));
        }
        else{
            $posting->discipline = $this->POST('discipline');
        }
        $posting->about = $this->POST('about');
        $posting->skills = $this->POST('skills');
    }
	
}

?>
