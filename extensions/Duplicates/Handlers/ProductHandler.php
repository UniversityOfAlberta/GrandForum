<?php

function paper_lengthSort($a, $b){
    return (strlen($a->getTitle()) < strlen($b->getTitle()));
}

class ProductHandler extends AbstractDuplicatesHandler {
        
    var $type;
    
    var $papers = null;
    
    static function init(){
        $structure = Product::structure();
        foreach($structure['categories'] as $catkey => $cat){
            $publicationHandler = new ProductHandler(strtolower($catkey), $catkey);
        }
    }
        
    function ProductHandler($id, $type){
        $this->AbstractDuplicatesHandler($id);
        $this->type = $type;
    }
    
    function getArray(){
        if($this->papers == null){
            $papers = Paper::getAllPapers($this->type, 'both');
            $this->papers = array();
            $paperLengths = array();
            foreach($papers as $paper){
                $this->papers[] = $paper;
            }
            usort($this->papers, 'paper_lengthSort');
        }
        return $this->papers;
    }
    
    function getArray2(){
        return $this->getArray();
    }
    
    function canShortCircuit($paper1, $paper2){
        $length1 = strlen($paper1->getTitle());
        $length2 = strlen($paper2->getTitle());
        $lengthDiff = abs($length1 - $length2)/max($length1, $length2);
        return ($lengthDiff > 0.15);
    }
    
    function showResult($paper1, $paper2){
        if(!$this->areIgnored($paper1->getId(), $paper2->getId())){
            if(strtolower($paper1->getTitle()) == strtolower($paper2->getTitle())){
                $percent = 100;
            }
            else{
                similar_text(preg_replace("/[^a-zA-Z0-9]+/", "", $paper1->getTitle()), preg_replace("/[^a-zA-Z0-9]+/", "", $paper2->getTitle()), $percent);
                $percent = round($percent);
            }
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
