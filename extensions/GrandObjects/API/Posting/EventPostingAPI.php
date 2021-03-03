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
        /*if(strlen($this->POST('summary')) > $maxSummaryLength){
            $this->throwError("The english summary must be no longer than $maxSummaryLength characters");
        }
        if(strlen($this->POST('summaryFr')) > $maxSummaryLength){
            $this->throwError("The french summary must be no longer than $maxSummaryLength characters");
        }*/
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
        $posting->website = $this->POST('website');
        $posting->image1 = $this->uploadFile(1, $posting);
        $posting->image2 = $this->uploadFile(2, $posting);
        $posting->image3 = $this->uploadFile(3, $posting);
    }
    
    function uploadFile($n, $posting){
        $image = $posting->getImage($n);
        
        // Do Deleting First
        if($this->POST("image_delete{$n}") != ""){
            $image = "";
        }
        
        // Then Try Upload
        if($this->POST("image{$n}") != "" && is_object($this->POST("image{$n}"))){
            $this->checkFile($n);
            $image = $this->POST("image{$n}")->data;
        }
        else if($this->POST("image_url{$n}") != ""){
            $type = "";
            if(strstr(@$this->POST("image_url{$n}"), ".gif") !== false){
                $type = "image/gif";
            }
            else if(strstr(@$this->POST("image_url{$n}"), ".png") !== false){
                $type = "image/png";
            }
            else if(strstr(@$this->POST("image_url{$n}"), ".jpg") !== false ||
                    strstr(@$this->POST("image_url{$n}"), ".jpeg") !== false){
                $type = "image/jpeg";
            }
            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $this->POST("image_url{$n}"));

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

            // $output contains the output string
            $file = curl_exec($ch);
            $size = strlen($file);

            // close curl resource to free up system resources
            curl_close($ch);
            if($type == ""){
                $this->throwError("The file you uploaded is not of the right type.  It should be either gif, png or jpeg");
            }
            if($size > 5*1024*1024){
                $this->throwError("The file you uploaded is too large.  It should be smaller than 5MB.");
            }
            if($file === false){
                $this->throwError("There was a problem retrieving the image.");
            }
            $image = "data:{$type};base64,".base64_encode($file);
        }
        return $image;
    }
	
}

?>
