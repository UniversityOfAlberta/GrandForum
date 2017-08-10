<?php

class GrantPartnerAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        if($id != ""){
            $grant = GrantPartner::newFromId($id);
            if($grant == null || $grant->getId() == 0){
                $this->throwError("This Grant Partner does not exist");
            }
            return $grant->toJSON();
        }
    }
    
    function doPOST(){
        $partner = new GrantPartner(array());
        $partner->award_id = $this->POST('award_id');
        $partner->part_institution = $this->POST('part_institution');
        $partner->province = $this->POST('province');
        $partner->country = $this->POST('country');
        $partner->committee_name = $this->POST('committee_name');
        $partner->fiscal_year = $this->POST('fiscal_year');
        $partner->org_type = $this->POST('org_type');
        $partner->create();
        return $partner->toJSON();
    }
    
    function doPUT(){
        $id = $this->getParam('id');
        if($id != ""){
            $partner = GrantPartner::newFromId($id);
            if($partner == null || $partner->getId() == 0){
                $this->throwError("This Grant Partner does not exist");
            }
            $partner->award_id = $this->POST('award_id');
            $partner->part_institution = $this->POST('part_institution');
            $partner->province = $this->POST('province');
            $partner->country = $this->POST('country');
            $partner->committee_name = $this->POST('committee_name');
            $partner->fiscal_year = $this->POST('fiscal_year');
            $partner->org_type = $this->POST('org_type');
            $partner->update();
            return $partner->toJSON();
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        $id = $this->getParam('id');
        if($id != ""){
            $partner = GrantPartner::newFromId($id);
            if($partner == null || $partner->getId() == 0){
                $this->throwError("This Grant Partner does not exist");
            }
            $partner->delete();
            return $partner->toJSON();
        }
        return $this->doGET();
    }
	
}

?>
