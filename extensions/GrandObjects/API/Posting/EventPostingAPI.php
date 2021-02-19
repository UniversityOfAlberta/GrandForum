<?php

class EventPostingAPI extends PostingAPI {
    
    static $className = "EventPosting";
    
    function validate(){
        global $config;
        $language = $this->POST('language');
        if($language == "en" || $language == "bi"){
            if(trim($this->POST('title')) == ""){
                $this->throwError("An english title must be provided");
            }
            if(strlen(trim($this->POST('summary'))) == 0){
                $this->throwError("The english summary must not be empty");
            }
        }
        if($language == "fr" || $language == "bi"){
            if(trim($this->POST('titleFr')) == ""){
                $this->throwError("A french title must be provided");
            }
            if(strlen(trim($this->POST('summaryFr'))) == 0){
                $this->throwError("The french summary must not be empty");
            }
        }
        $maxSummaryLength = ($config->getValue("networkName") == "AI4Society") ? 4000 : 2000;
        if(strlen($this->POST('title')) > 300){
            $this->throwError("The english title must be no longer than 300 characters");
        }
        if(strlen($this->POST('titleFr')) > 300){
            $this->throwError("The french title must be no longer than 300 characters");
        }
        if(strlen($this->POST('summary')) > $maxSummaryLength){
            $this->throwError("The english summary must be no longer than $maxSummaryLength characters");
        }
        if(strlen($this->POST('summaryFr')) > $maxSummaryLength){
            $this->throwError("The french summary must be no longer than $maxSummaryLength characters");
        }
        if(strlen($this->POST('imageCaption')) > 500){
            $this->throwError("The english image caption must be no longer than 500 characters");
        }
        if(strlen($this->POST('imageCaptionFr')) > 500){
            $this->throwError("The french image caption must be no longer than 500 characters");
        }
        if(strlen($this->POST('address')) > 70){
            $this->throwError("The address must be no longer than 70 characters");
        }
        if(strlen($this->POST('city')) > 70){
            $this->throwError("The city must be no longer than 70 characters");
        }
        if(strlen($this->POST('country')) > 70){
            $this->throwError("The country must be no longer than 70 characters");
        }
        if(trim($this->POST('address')) == ""){
            $this->throwError("An address must be provided");
        }
        if(trim($this->POST('city')) == ""){
            $this->throwError("A city must be provided");
        }
        if(trim($this->POST('country')) == ""){
            $this->throwError("A country must be provided");
        }
        $this->checkFile();
    }
    
    function extraVars($posting){
        $posting->address = $this->POST('address');
        $posting->city = $this->POST('city');
        $posting->province = $this->POST('province');
        $posting->country = $this->POST('country');
    }
	
}

?>
