<?php

class AllMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $type = $this->getAttr('subType', 'NI');
        $class = $this->getAttr('class', 'Person');
        $year = $this->getReport()->year;
        if($type != 'SAB' && $type != 'Project' && $class != "Project"){
            $subs = Person::getAllEvaluates($type, $year, $class);
            $sorted = array();
            foreach ($subs as $s){
                $rev_name = $s[0]->getReversedName()."_".$s[0]->getId();
                if($s[1] != 0){
                    $rev_name .= "_".$s[1];
                }
                $sorted["{$rev_name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
        }
        else {
            $subs = Person::getAllEvaluates($type, $year, $class);
            $sorted = array();
            foreach ($subs as $s){
                $name = $s[0]->getName()."_".$s[0]->getId();
                if($s[1] != 0){
                    $name .= "_".$s[1];
                }
                $sorted["{$name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
        }
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                if($type == "Project" || $type == "SAB" || $class == "Project"){
                    $tuple['project_id'] = $sub[0]->getId();
                    $tuple['person_id'] = $sub[1];
                }
                else{
                    $tuple['person_id'] = $sub[0]->getId();
                    $tuple['project_id'] = $sub[1];
                }
                $data[] = $tuple;
            }
        }
        $sortBy = $this->getAttr('sortBy', '', false);
        if($sortBy != ""){
            $that = $this;
            usort($data, function($a, $b) use ($that, $sortBy) {
                $aItem = new StaticReportItem();
                $aItem->setProjectId($a['project_id']);
                $aItem->setPersonId($a['person_id']);
                $aItem->setParent($that->getSection());
                
                $bItem = new StaticReportItem();
                $bItem->setProjectId($b['project_id']);
                $bItem->setPersonId($b['person_id']);
                $bItem->setParent($that->getSection());
                
                return $aItem->varSubstitute($sortBy) > $bItem->varSubstitute($sortBy);
            });
        }
        return $data;
    }

}

?>
