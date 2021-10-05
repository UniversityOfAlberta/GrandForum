<?php

class ElitePostingAPI extends PostingAPI {
    
    static $className = "ElitePosting";
    
    function doGET(){
        $id = $this->getParam('id');
        $className = static::$className;
        if($id == "intern" || $id == "phd"){
            $postings = array();
            foreach($className::getAllPostings() as $posting){
                if(strtolower($posting->type) == $id){
                    $postings[] = $posting;
                }
            }
            $postings = new Collection($postings);
            return $postings->toJSON();
        }
        else{
            return parent::doGET();
        }
    }
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A title must be provided");
        }
        if($this->POST('type') == "PhD"){
            $extra = $this->POST('extra');
            if(@$extra->title == "" ||
               @$extra->name == "" ||
               @$extra->companyName == "" ||
               @$extra->email == "" ||
               @$extra->phone == "" ||
               $this->POST('articleLink') == "" ||
               $this->POST('summary') == "" ||
               @$extra->qualifications == "" ||
               @$extra->level == "" ||
               @$extra->ack1 == "" ||
               @$extra->ack2 == ""){
                $this->throwError("Not all required fields have been filled.");
            }
            else if($extra->ack1 == "No" ||
                    $extra->ack2 == "No"){
                $this->throwError("You are not eligible to support a PhD candidate through this fellowship. Please revisit your response to confirm your selection.");
            }
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
