<?php

class MyProductHandler extends AbstractDuplicatesHandler {
        
    var $type;
        
    function MyProductHandler($id, $type){
        $this->AbstractDuplicatesHandler($id);
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
        $papers = $me->getPapers($this->type, false, 'both');
        $paperArray = array();
        foreach($papers as $paper){
            $paperArray[$paper->getId()] = $paper;
        }
        return $paperArray;
    }
    
    function getArray2(){
        $papers = Paper::getAllPapers($this->type, 'both');
        $paperArray = array();
        foreach($papers as $paper){
            $paperArray[$paper->getId()] = $paper;
        }
        return $paperArray;
    }
    
    function showResult($paper1, $paper2){
        global $wgServer, $wgScriptPath;
        if(!$this->areIgnored($paper1->getId(), $paper2->getId())){
            similar_text($paper1->getTitle(), $paper2->getTitle(), $percent);
            $percent = round($percent);
            if($percent >= 85){
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
                foreach($data1 as $key => $data){
                    $datas1[] = "<b>$key</b>:&nbsp;".$data."\n";
                }
                foreach($data2 as $key => $data){
                    $datas2[] = "<b>$key</b>:&nbsp;".$data."\n";
                }
                
                $buffer = "";
                $buffer .= $this->beginTable($paper1->getId(), $paper2->getId(), $paper1->getTitle());
                $buffer .= $this->addDiffHeadRow("{$paper1->getCategory()}: {$paper1->getTitle()}", "{$paper2->getCategory()}: {$paper2->getTitle()}", "{$paper1->getUrl()}", "{$paper2->getUrl()}");
                $buffer .= $this->addDiffRow($paper1->getType(), $paper2->getType());
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
