<?php

class NewsPostingAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        $current = ($this->getParam('current') != "");
        $image = ($this->getParam('image') != "");
        if($id != ""){
            $news = NewsPosting::newFromId($id);
            if($image){
                header('Content-Type: image/png;base64');
                $exploded = explode("base64,", $news->getImage());
                echo base64_decode($exploded[1]);
                exit;
            }
            return $news->toJSON();
        }
        else{
            if($current){
                $newses = new Collection(NewsPosting::getCurrentNewsPostings());
            }
            else {
                $newses = new Collection(NewsPosting::getAllNewsPostings());
            }
            return $newses->toJSON();
        }
        return $page->toJSON();
    }
    
    function validate(){
        if(trim($this->POST('title')) == ""){
            $this->throwError("A news title must be provided");
        }
        if(strlen($this->POST('title')) > 70){
            $this->throwError("The news title must be no longer than 70 characters");
        }
        $this->checkFile();
    }
    
    function checkFile(){
        global $wgFileExtensions;
        $file = $this->POST('image');
        if(isset($file->data)){
            list($partname, $ext) = UploadBase::splitExtensions($file->filename);
            if(count($ext)){
                $finalExt = $ext[count($ext) - 1];
            }
            else{
                $finalExt = '';
            }
            if(strlen($file->data)*(3/4) > 1024*1024*5){ // Checks the approx size
                $this->throwError("File cannot be larger than 5MB");
            }
            else if(!UploadBase::checkFileExtension($finalExt, $wgFileExtensions)){
                $this->throwError("File type not allowed, must be one of the following: ".implode(", ", $wgFileExtensions));
            }
        }
    }
    
    function doPOST(){
        $me = Person::newFromWgUser();
        if(NewsPosting::isAllowedToCreate()){
            $this->validate();
            $image = "";
            if($this->POST('image') != "" && is_object($this->POST('image'))){
                $image = $this->POST('image')->data;
            }
            $news = new NewsPosting(array());
            $news->translatedId = $this->POST('translatedId');
            $news->userId = $me->getId();
            $news->visibility = $this->POST('visibility');
            $news->language = $this->POST('language');
            $news->title = $this->POST('title');
            $news->articleLink = $this->POST('articleLink');
            $news->postedDate = $this->POST('postedDate');
            $news->summary = $this->POST('summary');
            $news->author = $this->POST('author');
            $news->sourceName = $this->POST('sourceName');
            $news->sourceLink = $this->POST('sourceLink');
            $news->image = $image;
            $news->imageCaption = $this->POST('imageCaption');
            $news->create();
            return $news->toJSON();
        }
        $this->throwError("You need to be logged in to create a News Posting");
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        if($me->isLoggedIn()){
            $news = NewsPosting::newFromId($id);
            if($news->isAllowedToEdit()){
                $this->validate();
                $image = $news->getImage();
                if($this->POST('image') != "" && is_object($this->POST('image'))){
                    $image = $this->POST('image')->data;
                }
                $news->visibility = $this->POST('visibility');
                $news->language = $this->POST('language');
                $news->title = $this->POST('title');
                $news->articleLink = $this->POST('articleLink');
                $news->postedDate = $this->POST('postedDate');
                $news->summary = $this->POST('summary');
                $news->author = $this->POST('author');
                $news->sourceName = $this->POST('sourceName');
                $news->sourceLink = $this->POST('sourceLink');
                $news->image = $image;
                $news->imageCaption = $this->POST('imageCaption');
                $news->update();
                return $news->toJSON();
            }
            else{
                $this->throwError("You are not allowed to edit this News Posting");
            }
        }
        $this->throwError("You need to be logged in to create a News Posting");
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        if($me->isLoggedIn()){
            $news = NewsPosting::newFromId($id);
            if($news->isAllowedToEdit()){
                $news->delete();
                return $news->toJSON();
            }
            else{
                $this->throwError("You are not allowed to delete this News Posting");
            }
        }
        $this->throwError("You need to be logged in to delete a News Posting");
    }
	
}

?>
