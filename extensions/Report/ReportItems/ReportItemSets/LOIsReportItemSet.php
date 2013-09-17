<?php

class LOIsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('subType', 'ALL_LOI');
        
        $subs = array();
        if($type == "LOI"){
            $subs = $person->getEvaluates($type);
            $tosort = array();
            foreach ($subs as $s){
                $tosort[$s->getName()] = $s;
            }
            ksort($tosort);
            $subs = array_values($tosort);
        }
        else if($type == "OPT_LOI"){
            $subs = $person->getEvaluates('OPT_LOI');
            $tosort = array();
            foreach ($subs as $s){
                $tosort[$s->getName()] = $s;
            }
            ksort($tosort);
            $subs = array_values($tosort);
            //print_r($subs);
        }
        else if($type == "LOI_REV2"){
            $subs = $person->getEvaluates('LOI_REV2');
            $tosort = array();
            foreach ($subs as $s){
                $tosort[$s->getName()] = $s;
            }
            ksort($tosort);
            $subs = array_values($tosort);
            //print_r($subs);
        }
        else if($type == "ALL_LOI"){
            $subs = LOI::getAllLOIs();
        }
        else if($type == "ALL_LOI2"){
            //Revision 2
            $subs = LOI::getAllLOIs(REPORTING_YEAR, 2);
        }
        else if($type == "POTENTIAL_LOI"){
            $assigned = $person->getEvaluates('LOI');
            $assigned_ids = array();
            foreach($assigned as $a){
                $assigned_ids[] = $a->getId();
            }

            $nonconf = LOI::getNonConflictingLOIs($person->getId());
            $count = 0;
            foreach($nonconf as $loi){
                if(!in_array($loi->getId(), $assigned_ids)){
                    $subs[] = $loi; 
                    $count++;
                }
            }

            //Sort
            $tosort = array();
            foreach ($subs as $s){
                $tosort[$s->getName()] = $s;
            }
            ksort($tosort);
            $subs = array_values($tosort);
        }
        else if($type == "LOI_EVALS"){
            $loi = LOI::newFromId($this->projectId);
            $evals = $loi->getEvaluators();
            $subs = array();

            foreach($evals as $e){
                $empty = true;

                for($q=1; $q<=15; $q++){
                    $answer = $this->getAnswer(BLOB_TEXT, $e->getId(), $loi->getId(), $q, EVL_LOI_C);
                    if(!empty($answer)){
                        $empty = false;
                        break;
                    }

                    $answer = $this->getAnswer(BLOB_ARRAY, $e->getId(), $loi->getId(), $q, EVL_LOI_YN);
                    if(!empty($answer)){
                        $empty = false;
                        break;
                    }
                }
                
                if(!$empty){
                    $subs[] = $e;
                }
            }

        }
        

        if(is_array($subs)){
            foreach($subs as $sub){
                $id = $sub->getId();
                $tuple = self::createTuple();
                if($type == "LOI_EVALS"){
                    $tuple['person_id'] = $id;
                }else{
                    $tuple['project_id'] = $id;
                }
                $data[] = $tuple;
            }
        }
        return $data;
    }

    function getAnswer($blobType, $personId, $projectId, $question, $subItem){
        $report = $this->getReport();
        //$section = $this->getSection();

        $blob = new ReportBlob($blobType, $report->year, $personId, $projectId);
        $blob_address = ReportBlob::create_address(RP_EVAL_LOI, SEC_NONE, $question, $subItem);
        $blob->load($blob_address);
        $blob_data = $blob->getData();
        $blb = "";
        if($blobType == BLOB_TEXT){
            $blb = str_replace("\00", "", $blob_data);
            $blb = str_replace("", "", $blob_data);
            $blb = str_replace("", "", $blob_data);
            $blb = str_replace("", "", $blob_data);
        }
        else if($blobType == BLOB_ARRAY){
            if(is_array($blob_data) && !empty($blob_data)){
                $blb = reset($blob_data);
            }
            else{
                $blb = "";
            }
        }

        return $blb;
    }   

    function getNComplete(){
        $type = $this->getAttr('subType', 'LOI');

        if($type == 'OPT_LOI'){
            return 0;
        }
        else{
            return parent::getNComplete();
        }
    }

    // function getNFields(){
    //     return ;
    // }

}

?>
