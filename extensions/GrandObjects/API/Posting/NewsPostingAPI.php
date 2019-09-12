<?php

class NewsPostingAPI extends PostingAPI {
    
    static $className = "NewsPosting";
    
    function validate(){
        $language = $this->POST('language');
        if($language == "English" || $language == "Bilingual"){
            if(trim($this->POST('title')) == ""){
                $this->throwError("An english title must be provided");
            }
            if(strlen(trim($this->POST('summary'))) == 0){
                $this->throwError("The english summary must not be empty");
            }
        }
        if($language == "French" || $language == "Bilingual"){
            if(trim($this->POST('titleFr')) == ""){
                $this->throwError("A french title must be provided");
            }
            if(strlen(trim($this->POST('summaryFr'))) == 0){
                $this->throwError("The french summary must not be empty");
            }
        }
        if(strlen($this->POST('title')) > 300){
            $this->throwError("The english title must be no longer than 300 characters");
        }
        if(strlen($this->POST('titleFr')) > 300){
            $this->throwError("The french title must be no longer than 300 characters");
        }
        if(strlen($this->POST('summary')) > 2000){
            $this->throwError("The english summary must be no longer than 2000 characters");
        }
        if(strlen($this->POST('summaryFr')) > 2000){
            $this->throwError("The french summary must be no longer than 2000 characters");
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
