<?php

class NewsPostingAPI extends PostingAPI {
    
    static $className = "NewsPosting";
    
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
        if(strlen($this->POST('author')) > 70){
            $this->throwError("The author must be no longer than 70 characters");
        }
        if(strlen($this->POST('sourceName')) > 70){
            $this->throwError("The source name must be no longer than 70 characters");
        }
        if(trim($this->POST('sourceName')) == "" && trim($this->POST('sourceLink')) != ""){
            $this->throwError("The source name must be not be empty when a source link is provided");
        }
        $this->checkFile();
    }
    
    function extraVars($posting){
        $posting->author = $this->POST('author');
        $posting->sourceName = $this->POST('sourceName');
        $posting->sourceLink = $this->POST('sourceLink');
    }
	
}

?>
