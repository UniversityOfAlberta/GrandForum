<?php

class JournalAPI extends RESTAPI {
    
    function doGET(){
        $id = $this->getParam('id');
        $search = $this->getParam('search');
        if($id != ""){
            $journal = Journal::newFromId($id);
            if($journal == null || $journal->getId() == 0){
                $this->throwError("This Journal does not exist");
            }
            return $journal->toJSON();
        }
        else if ($search != ""){
            $journals = new Collection(Journal::getAllJournalsBySearch($search));
            return $journals->toJSON();           
        }
        else{
            $journals = new Collection(Journal::getAllJournals());
            return $journals->toJSON();
        }
    }
    
    function doPOST(){
        $journal = new Journal(array());
        $journal->id = $this->POST('id');
        $journal->year = $this->POST('year');
        $journal->short_title = $this->POST('short_title');
        $journal->iso_abbrev = $this->POST('iso_abbrev');
        $journal->title = $this->POST('title');
        $journal->issn = $this->POST('issn');
        $journal->description = $this->POST('description');
        $journal->ranking_numerator = $this->POST('ranking_numerator');
        $journal->ranking_denominator = $this->POST('ranking_denominator');
        $journal->impact_factor = $this->POST('impact_factor');
        $journal->cited_half_life = $this->POST('cited_half_life');
        $journal->eigenfactor = $this->POST('eigenfactor');
        $journal->create();
        return $journal->toJSON();
    }
    
    function doPUT(){
        $id = $this->getParam('id');
        if($id != ""){
            $journal = Journal::newFromId($id);
            if($journal == null || $journal->getId() == 0){
                $this->throwError("This Journal does not exist");
            }
            $journal = new Journal(array());
            $journal->id = $this->POST('id');
            $journal->year = $this->POST('year');
            $journal->short_title = $this->POST('short_title');
            $journal->iso_abbrev = $this->POST('iso_abbrev');
            $journal->title = $this->POST('title');
            $journal->issn = $this->POST('issn');
            $journal->description = $this->POST('description');
            $journal->ranking_numerator = $this->POST('ranking_numerator');
            $journal->ranking_denominator = $this->POST('ranking_denominator');
            $journal->impact_factor = $this->POST('impact_factor');
            $journal->cited_half_life = $this->POST('cited_half_life');
            $journal->eigenfactor = $this->POST('eigenfactor');
            $journal->update();
            return $journal->toJSON();
        }
        return $this->doGET();
    }
    
    function doDELETE(){
        $id = $this->getParam('id');
        if($id != ""){
            $journal = Journal::newFromId($id);
            if($journal == null || $journal->getId() == 0){
                $this->throwError("This Journal does not exist");
            }
            $journal->delete();
            return $journal->toJSON();
        }
        return $this->doGET();
    }
	
}

?>
