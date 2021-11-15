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
            $this->throwError("<span class='en'>A title must be provided.</span>
                               <span class='fr'>Un titre doit être fourni.</span>");
        }
        if($this->POST('type') == "Intern"){
            $extra = $this->POST('extra');
            if(@$extra->region == "" ||
               @$extra->companyName == "" ||
               @$extra->reportsTo == "" ||
               @$extra->basedAt == "" ||
               @$extra->contact == "" ||
               @$extra->email == "" ||
               @$extra->phone == "" ||
               $this->POST('summary') == "" ||
               @$extra->training == "" ||
               @$extra->responsibilities == "" ||
               @$extra->qualifications == "" ||
               @$extra->level == "" ||
               @$extra->positions == ""){
                $this->throwError("<span class='en'>Not all required fields have been filled.</span>
                                   <span class='fr'>Tous les champs obligatoires n'ont pas été remplis.</span>");
            }
        }
        else if($this->POST('type') == "PhD"){
            $extra = $this->POST('extra');
            if(@$extra->region == "" ||
               @$extra->title == "" ||
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
                $this->throwError("<span class='en'>Not all required fields have been filled.</span>
                                   <span class='fr'>Tous les champs obligatoires n'ont pas été remplis.</span>");
            }
            else if($extra->ack1 == "No" ||
                    $extra->ack2 == "No"){
                $this->throwError("<span class='en'>You are not eligible to support a PhD candidate through this fellowship. Please revisit your response to confirm your selection.</span>
                                   <span class='fr'>Vous n'êtes pas admissible à soutenir un candidat au doctorat grâce à cette bourse. Veuillez revoir votre réponse pour confirmer votre sélection.</span>");
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
