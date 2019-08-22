<?php

class NewsPostingAPI extends PostingAPI {
    
    static $className = "NewsPosting";
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A news title must be provided");
        }
        if(strlen($this->POST('title')) > 300){
            $this->throwError("The news title must be no longer than 300 characters");
        }
        if(strlen(trim($this->POST('summary'))) == 0){
            $this->throwError("The summary must not be empty");
        }
        if(strlen($this->POST('summary')) > 2000){
            $this->throwError("The summary must be no longer than 2000 characters");
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
        if(strlen($this->POST('imageCaption')) > 70){
            $this->throwError("The image caption must be no longer than 70 characters");
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
