<?php

class ProductAPI extends RESTAPI {
    
    function doGET(){
        if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) == 1){
            $paper = Paper::newFromId($this->getParam('id'));
            if($paper == null || $paper->getTitle() == ""){
                $this->throwError("This product does not exist");
            }
            if($this->getParam('citation') != ""){
                header('Content-Type: text/plain');
                return $paper->getCitation();
            }
            if($this->getParam('bibtex') != ""){
                header('Content-Type: text/plain');
                echo $paper->toBibTeX();
                exit;
            }
            if($this->getParam('file') != ""){
                $file = $paper->getData($this->getParam('file'));
                if($file != null && isset($file->data)){
                    header('Content-Type: '.$file->type);
                    header('Content-Disposition: attachment; filename="'.$file->filename.'"');
                    $exploded = explode(",", $file->data);
                    echo base64_decode($exploded[1]);
                }
                else{
                    $this->throwError("The product <i>{$paper->getTitle()}</i> does not have a file by the id of {$this->getParam('file')}");
                }
                exit;
            }
            return $paper->toJSON();
        }
        else if($this->getParam('id') != "" && count(explode(",", $this->getParam('id'))) > 1){
            $json = array();
            $papers = Product::newFromIds(explode(",", $this->getParam('id')), false);
            foreach($papers as $paper){
                $json[] = $paper->toArray();
            }
            return large_json_encode($json);
        }
        else{
            $json = array();
            $start = 0;
            $count = 999999999;
            if($this->getParam('start') != "" &&
               $this->getParam('count') != ""){
                $start = $this->getParam('start');
                $count = $this->getParam('count');
            }
            if($this->getParam('category') != "" && 
               $this->getParam('projectId') != "" &&
               $this->getParam('grand') != ""){
                $projectId = explode(",", $this->getParam('projectId'));
                $papers = array();
                foreach($projectId as $pId){
                    $ps = Paper::getAllPapers($pId, 
                                              $this->getParam('category'), 
                                              $this->getParam('grand'),
                                              true,
                                              'Public',
                                              $start,
                                              $count);
                    foreach($ps as $p){
                        $papers["{$p->getType()}_{$p->getTitle()}{$p->getId()}"] = $p;
                    }
                }
                ksort($papers);
                $papers = array_values($papers);
            }
            else{
                $papers = Paper::getAllPapers('all', 'all', 'both', true, 'Public', $start, $count);
            }
            
            $codes = array();
            foreach($papers as $paper){
                $type = explode(" - ", $paper->getType());
                @$codes[$type[0]]++;
                $json[] = array_merge($paper->toArray(), array('code' => $type[0]."-".str_pad($codes[$type[0]], 3, "0", STR_PAD_LEFT)));
            }
            return large_json_encode($json);
        }
    }
    
    function doPOST(){
        $paper = new Paper(array());
        $this->checkFile();
        header('Content-Type: application/json');
        $paper->title = $this->POST('title');
        $paper->category = $this->POST('category');
        $paper->type = $this->POST('type');
        $paper->description = $this->POST('description');
        $paper->tags = $this->POST('tags');
        $paper->date = $this->POST('date');
        $paper->status = $this->POST('status');
        $paper->authors = $this->POST('authors');
        $paper->projects = $this->POST('projects');
        $paper->data = (array)($this->POST('data'));
        $paper->access_id = $this->POST('access_id');
        $paper->access = $this->POST('access');
        $status = $paper->create();
        if(!$status){
            $this->throwError("The product <i>{$paper->getTitle()}</i> could not be created");
        }
        $paper = Product::newFromId($paper->getId());
        return $paper->toJSON();
    }
    
    function doPUT(){
        $paper = Product::newFromId($this->getParam('id'));
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        $this->checkFile();
        header('Content-Type: application/json');
        $paper->title = $this->POST('title');
        $paper->category = $this->POST('category');
        $paper->type = $this->POST('type');
        $paper->description = $this->POST('description');
        $paper->tags = $this->POST('tags');
        $paper->date = $this->POST('date');
        $paper->status = $this->POST('status');
        $paper->authors = $this->POST('authors');
        $paper->projects = $this->POST('projects');
        $paper->data = (array)($this->POST('data'));
        $paper->access_id = $this->POST('access_id');
        $paper->access = $this->POST('access');
        $status = $paper->update();
        if(!$status){
            $this->throwError("The product <i>{$paper->getTitle()}</i> could not be updated");
        }
        $paper = Product::newFromId($this->getParam('id'));
        return $paper->toJSON();
    }
    
    function doDELETE(){
        $paper = Paper::newFromId($this->getParam('id'));
        if($paper == null || $paper->getTitle() == ""){
            $this->throwError("This product does not exist");
        }
        header('Content-Type: application/json');
        $status = $paper->delete();
        if($paper->getAccessId() > 0 && $status){
            $paper->deleted = "1";
            return $paper->toJSON();
        }
        return $this->doGET();
    }
    
    function checkFile(){
        global $wgFileExtensions;
        $data = $this->POST('data');
        if(isset($data->file) && isset($data->file->data)){
            $file = $data->file;
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
	
}

?>
