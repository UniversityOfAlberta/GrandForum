<?php

abstract class PostingAPI extends RESTAPI {
    
    static $className = "";
    
    function doGET(){
        $id = $this->getParam('id');
        $current = ($this->getParam('current') != "");
        $deleted = ($this->getParam('deleted') != "");
        $new = ($this->getParam('new') != "");
        $image = ($this->getParam('image') != "");
        $image_id = $this->getParam('image_id');
        $className = static::$className;
        if($id != ""){
            $previewCode = explode("-", $id);
            $previewCode = @$previewCode[1];
            $_GET['previewCode'] = $previewCode;
            $posting = $className::newFromId($id);
            if($posting->id != ""){
                if(($previewCode != "" && $previewCode == $posting->getPreviewCode()) || 
                   ($previewCode == "" && $previewCode == $posting->getPreviewCode())){
                    $posting->visibility = "Publish";
                    $posting->generatePreviewCode();
                }
            }
            if($image){
                header('Content-Type: '.$posting->getImageMime($image_id));
                header('Cache-Control: max-age=86400');
                header('Cache-Control: public');
                header('Last-Modified: '.gmdate(DATE_RFC1123, mktime(0, 0, 0, 1, 1, 2000)));
                if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $this->getParam('md5') == $posting->getImageMD5($image_id)){
                    header('HTTP/1.1 304 Not Modified');
                    exit;
                }
                $img = $posting->getImage($image_id);
                $exploded = explode("base64,", $img);
                echo base64_decode(@$exploded[1]);
                exit;
            }
            return $posting->toJSON();
        }
        else{
            $start = 0;
            $count = 999999999;
            if($this->getParam('start') != "" &&
               $this->getParam('count') != ""){
                $start = $this->getParam('start');
                $count = $this->getParam('count');
            }
            if($new && $this->getParam('date') != ""){
                $date = $this->getParam('date');
                $postings = new Collection($className::getNewPostings($date));
            }
            else if($current){
                $postings = new Collection($className::getCurrentPostings());
            }
            else if($deleted){
                $postings = new Collection($className::getDeletedPostings());
            }
            else {
                $postings = new Collection($className::getAllPostings());
            }
            $postings = $postings->paginate($start, $count);
            return $postings->toJSON();
        }
    }
    
    abstract function validate();
    
    function checkFile($n=""){
        global $wgFileExtensions;
        $file = $this->POST("image{$n}");
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
        $className = static::$className;
        if($className::isAllowedToCreate()){
            $this->validate();
            $image = "";
            if($this->POST('image_delete') != ""){
                $image = "";
            }
            if($this->POST('image') != "" && is_object($this->POST('image'))){
                $image = $this->POST('image')->data;
            }
            $posting = new $className(array());
            $posting->userId = $me->getId();
            $posting->visibility = $this->POST('visibility');
            $posting->language = $this->POST('language');
            $posting->title = $this->POST('title');
            $posting->titleFr = $this->POST('titleFr');
            $posting->articleLink = $this->POST('articleLink');
            $posting->startDate = $this->POST('startDate');
            $posting->endDate = $this->POST('endDate');
            $posting->summary = $this->POST('summary');
            $posting->summaryFr = $this->POST('summaryFr');
            $posting->image = $image;
            $posting->imageCaption = $this->POST('imageCaption');
            $posting->imageCaptionFr = $this->POST('imageCaptionFr');
            $this->extraVars($posting);
            $posting->create();
            return $posting->toJSON();
        }
        $this->throwError("You need to be logged in to create a Posting");
    }
    
    function doPUT(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        $className = static::$className;
        if($me->isLoggedIn()){
            $posting = $className::newFromId($id);
            if($posting->isAllowedToEdit()){
                $this->validate();
                $image = $posting->getImage();
                if($this->POST('image_delete') != ""){
                    $image = "";
                }
                if($this->POST('image') != "" && is_object($this->POST('image'))){
                    $image = $this->POST('image')->data;
                }
                $posting->visibility = $this->POST('visibility');
                $posting->language = $this->POST('language');
                $posting->title = $this->POST('title');
                $posting->titleFr = $this->POST('titleFr');
                $posting->articleLink = $this->POST('articleLink');
                $posting->startDate = $this->POST('startDate');
                $posting->endDate = $this->POST('endDate');
                $posting->summary = $this->POST('summary');
                $posting->summaryFr = $this->POST('summaryFr');
                $posting->image = $image;
                $posting->imageCaption = $this->POST('imageCaption');
                $posting->imageCaptionFr = $this->POST('imageCaptionFr');
                $this->extraVars($posting);
                
                $posting->update();
                return $posting->toJSON();
            }
            else{
                $this->throwError("You are not allowed to edit this Posting");
            }
        }
        $this->throwError("You need to be logged in to create a Posting");
    }
    
    function doDELETE(){
        $me = Person::newFromWgUser();
        $id = $this->getParam('id');
        $className = static::$className;
        if($me->isLoggedIn()){
            $posting = $className::newFromId($id);
            if($posting->isAllowedToEdit()){
                $posting->delete();
                return $posting->toJSON();
            }
            else{
                $this->throwError("You are not allowed to delete this Posting");
            }
        }
        $this->throwError("You need to be logged in to delete a Posting");
    }
    
    abstract function extraVars($posting);
	
}

?>
