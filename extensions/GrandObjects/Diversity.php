<?php
/**
 * @package GrandObjects
 */
class Diversity extends BackboneModel {

    var $id = null;
    var $userId = "";
    var $language = "";
    var $decline = "";
    var $reason = "";
    var $gender = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $orientation = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $birth = "";
    var $indigenous = "";
    var $disability = "";
    var $disabilityVisibility = "";
    var $minority = "";
    var $race = array(
        'values' => array(),
        'other' => "",
        'indigenousOther' => "",
        'decline' => ""
    );
    var $racialized = "";
    var $immigration = "";
    var $comments = "";

    function Diversity($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->language = $data[0]['language'];
            $this->userId = $data[0]['user_id'];
            $this->decline = $data[0]['decline'];
            $this->reason = $data[0]['reason'];
            $this->gender = unserialize($data[0]['gender']);
            $this->orientation = unserialize($data[0]['orientation']);
            $this->birth = $data[0]['birth'];
            $this->indigenous = $data[0]['indigenous'];
            $this->disability = $data[0]['disability'];
            $this->disabilityVisibility = $data[0]['disability_visibility'];
            $this->minority = $data[0]['minority'];
            $this->race = unserialize($data[0]['race']);
            $this->racialized = $data[0]['racialized'];
            $this->immigration = $data[0]['immigration'];
            $this->comments = $data[0]['comments'];
        }
    }

    /**
     * Returns a new Diversity from the given id
     * @param id $id The id of the Diversity
     * @return Diversity The Diversity with the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_diversity'),
                                    array('*'),
                                    array('id' => $id));
        $diversity = new Diversity($data);
        return $diversity;
    }
    
    /**
     * Returns a new Diversity from the given user id
     * @param id $id The id of the Diversity
     * @return Diversity The Diversity with the given id
     */
    static function newFromUserId($userId){
        $data = DBFunctions::select(array('grand_diversity'),
                                    array('*'),
                                    array('user_id' => $userId));
        $diversity = new Diversity($data);
        return $diversity;
    }

    /**
     * Returns all Diversities available to a user
     * @return Array An Array of Diversities
     */
    static function getAllDiversity(){
        global $wgRoleValues;
        $diversities = array();

        $data = DBFunctions::select(array('grand_diversity'),
                                    array('id'));
        if(count($data) >0){
            foreach($data as $diversityId){
                $diversity = Diversity::newFromId($diversityId['id']);
                $diversities[] = $diversity;
            }
        }
        return $diversities;
    }

    function getId(){
        return $this->id;
    }
    
    function getPerson(){
        return Person::newFromId($this->userId);
    }
    
    function create(){
        DBFunctions::insert('grand_diversity',
                            array('user_id' => $this->userId,
                                  'language' => $this->language,
                                  'decline' => $this->decline,
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'orientation' => serialize($this->orientation),
                                  'birth' => $this->birth,
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => $this->disabilityVisibility,
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'racialized' => $this->racialized,
                                  'immigration' => $this->immigration,
                                  'comments' => $this->comments));
        $this->id = DBFunctions::insertId();
        return $this;
    }

    function update(){
        DBFunctions::update('grand_diversity',
                            array('user_id' => $this->userId,
                                  'language' => $this->language,
                                  'decline' => $this->decline,
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'orientation' => serialize($this->orientation),
                                  'birth' => $this->birth,
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => $this->disabilityVisibility,
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'racialized' => $this->racialized,
                                  'immigration' => $this->immigration,
                                  'comments' => $this->comments),
                            array('id' => $this->id));
        return $this;
    }

    function delete(){
        return false;
    }

    function canView(){
        global $wgImpersonating, $wgDelegating;
        $me = Person::newFromWgUser();
        return (!$wgImpersonating && !$wgDelegating && ($this->userId == "" || $this->getPerson()->isMe() || $me->isRoleAtLeast(MANAGER)));
    }

    function toArray(){
        global $wgUser;
        if(!$this->canView()){
            return array();
        }
        $json = array('id' => $this->id,
                      'user_id' => $this->userId,
                      'language' => $this->language,
                      'decline' => $this->decline,
                      'reason' => $this->reason,
                      'gender' => $this->gender,
                      'orientation' => $this->orientation,
                      'birth' => $this->birth,
                      'indigenous' => $this->indigenous,
                      'disability' => $this->disability,
                      'disabilityVisibility' => $this->disabilityVisibility,
                      'minority' => $this->minority,
                      'race' => $this->race,
                      'racialized' => $this->racialized,
                      'immigration' => $this->immigration,
                      'comments' => $this->comments);
        return $json;
    }

    function exists(){
        $diversity = Diversity::newFromId($this->getId());
        return ($diversity != null && $diversity->getId() != "");
    }

    function getCacheId(){
        global $wgSitename;
    }

}

?>
