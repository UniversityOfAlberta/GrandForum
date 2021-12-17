<?php

abstract class ProjectPublicationCell extends PublicationCell {
    
    function __construct($cellType, $params, $cellValue, $rowN, $colN, $table){
        $start = "0000-00-00";
        $end = "2100-00-00";
        if(count($params) == 1){
            $params[2] = $params[0];
        }
        else{
            if(isset($params[0])){
                // Start
                $start = $params[0];
            }
            if(isset($params[1])){
                // End
                $end = $params[1];
            }
        }
        if(isset($params[2])){
            $person = Person::newFromName($params[2]);
            if($person != null && $person->getName() != null){
                if(!$person->isRole(HQP)){
                    $hqps = $person->getHQP(true);
                }
                $this->obj = $person;
                $this->table = $table;
                $project = $table->obj;
                $papers = $project->getPapers($this->category, $start, $end);

                $values = array();
                foreach($papers as $paper){
                    if($person->isAuthorOf($paper) ){
                        $type = str_replace("Misc: ", "", $paper->getType());
                        if($type == ""){
                            $type = " ";
                        }
                        if($paper->getCategory() == "Publication"){
                            $status = $paper->getStatus();
                            if($status == "Published"){
                                $type .= "(PB)";
                            }
                            else{
                                $type .= "(Not PB)";
                            }
                        }
                        else if($paper->getCategory() == "Artifact"){
                            $status = $paper->getStatus();
                            if($status == "Peer Reviewed"){
                                $type .= "(PR)";
                            }
                            else{
                                $type .= "(Not PR)";
                            }
                        }
                        $values[$type][$paper->getId()] = $paper->getId();
                    }
                    else if(!$person->isRole(HQP)){
                        foreach($hqps as $hqp){
                            if($hqp->isAuthorOf($paper)){
                                $type = $paper->getType();
                                if($paper->getCategory() == "Publication"){
                                    $status = $paper->getStatus();
                                    if($status == "Published"){
                                        $type .= "(PB)";
                                    }
                                    else{
                                        $type .= "(Not PB)";
                                    }
                                }
                                else if($paper->getCategory() == "Artifact"){
                                    $status = $paper->getStatus();
                                    if($status == "Peer Reviewed"){
                                        $type .= "(PR)";
                                    }
                                    else{
                                        $type .= "(Not PR)";
                                    }
                                }
                                $values[$type][$paper->getId()] = $paper->getId();
                                break;
                            }
                        }  
                    }
                }
                $this->setValues($values);
            }
        }
        else{
            $project = $table->obj;
            $this->table = $table;
            $papers = $project->getPapers($this->category, $start, $end);
            $values = array();
            foreach($papers as $paper){
                $values['All'][$paper->getId()] = $paper->getId();
            }
            $this->setValues($values);
        }
        $this->sortByStatus();
    }
}
?>
