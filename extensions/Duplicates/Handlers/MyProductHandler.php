<?php

class MyProductHandler extends AbstractDuplicatesHandler {
        
    var $type;
        
    function __construct($id, $type){
        parent::__construct($id);
        $this->type = $type;
    }
    
    static function init(){
        $structure = Product::structure();
        foreach($structure['categories'] as $catkey => $cat){
            $publicationHandler = new MyProductHandler("my$catkey", $catkey);
        }
    }
    
    function getArray(){
        $me = Person::newFromWgUser();
        $papers = $me->getPapers($this->type, false, 'both', true, 'Public');
        $paperArray = array();
        foreach($papers as $paper){
            $paperArray[$paper->getId()] = $paper;
        }
        return $paperArray;
    }
    
    function getArray2(){
        $papers = Paper::getAllPapers('all', $this->type, 'both', true, 'Public');
        $paperArray = array();
        foreach($papers as $paper){
            $paperArray[$paper->getId()] = $paper;
        }
        return $paperArray;
    }
    
    function showResult($paper1, $paper2){
        global $wgServer, $wgScriptPath;
        if(!$this->areIgnored($paper1->getId(), $paper2->getId())){
            $title1 = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $paper1->getTitle()));
            $title2 = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $paper2->getTitle()));
            if($title1 == $title2){
                $percent = 100;
            }
            else if(substr($title1, 0, 1) != substr($title2, 0, 1) && 
                    substr($title1, -1) != substr($title2, -1)){
                // If both the first and last character are differen then the two papers are probably different
                $percent = 0;        
            }
            else{
                similar_text($title1, $title2, $percent);
                $percent = round($percent);
            }
            if($percent >= 85){
                $projs1 = $paper1->getProjects();
                $projs2 = $paper2->getProjects();
                $projects1 = array();
                $projects2 = array();
                foreach($projs1 as $proj){
                    $projects1[] = $proj->getName();
                }
                foreach($projs2 as $proj){
                    $projects2[] = $proj->getName();
                }
                
                $auths1 = $paper1->getAuthors();
                $auths2 = $paper2->getAuthors();
                $authors1 = array();
                $authors2 = array();
                foreach($auths1 as $auth){
                    $authors1[] = $auth->getName();
                }
                foreach($auths2 as $auth){
                    $authors2[] = $auth->getName();
                }
                
                $data1 = $paper1->getData();
                $data2 = $paper2->getData();
                $datas1 = array();
                $datas2 = array();
                if(is_array($data1)){
                    foreach($data1 as $key => $data){
                        if(is_string($data)){
                            $datas1[] = "<b>$key</b>:&nbsp;".$data."\n";
                        }
                    }
                }
                if(is_array($data2)){
                    foreach($data2 as $key => $data){
                        if(is_string($data)){
                            $datas2[] = "<b>$key</b>:&nbsp;".$data."\n";
                        }
                    }
                }
                
                $buffer = "";
                $buffer .= $this->beginTable($paper1->getId(), $paper2->getId(), $paper1->getTitle());
                $buffer .= $this->addDiffHeadRow("{$paper1->getCategory()}: {$paper1->getTitle()}", "{$paper2->getCategory()}: {$paper2->getTitle()}", "{$paper1->getUrl()}", "{$paper2->getUrl()}");
                $buffer .= $this->addDiffRow($paper1->getType(), $paper2->getType());
                $buffer .= $this->addDiffRow(implode(" ", $projects1), implode(" ", $projects2));
                $buffer .= $this->addDiffRow(implode(" ", $authors1), implode(" ", $authors2));
                $buffer .= $this->addDiffRow($paper1->getStatus(), $paper2->getStatus());
                $buffer .= $this->addDiffRow($paper1->getDate(), $paper2->getDate());
                $buffer .= $this->addDiffNLRow(implode("", $datas1), implode("", $datas2));
                $buffer .= $this->addDiffNLRow($paper1->getDescription(), $paper2->getDescription());
                $buffer .= $this->addControls($paper1->getId(), $paper2->getId());
                $buffer .= $this->endTable();
                return $buffer;
            }
        }
    }
    
    function handleDelete(){
        $product = Product::newFromId($_POST['id']);
        $product->delete();
    }
}

?>
