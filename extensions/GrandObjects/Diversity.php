<?php
/**
 * @package GrandObjects
 */
class Diversity extends BackboneModel {

    var $id;
    var $userId;
    var $reason;
    var $gender;
    var $sexuality;
    var $birth;
    var $indigenous;
    var $disability;
    var $disabilityVisibility;
    var $minority;
    var $race;
    var $racialized;
    var $immigration;
    var $comments;

    function Diversity($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->userId = $data[0]['user_id'];
            $this->reason = $data[0]['reason'];
            $this->gender = unserialize($data[0]['gender']);
            $this->sexuality = unserialize($data[0]['sexuality']);
            $this->birth = $data[0]['birth'];
            $this->indigenous = $data[0]['indigenous'];
            $this->disability = $data[0]['disability'];
            $this->disabilityVisibility = $data[0]['disability_visability'];
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
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'sexuality' => serialize($this->sexuality),
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
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'sexuality' => serialize($this->sexuality),
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
        $me = Person::newFromWgUser();
        return ($this->getPerson()->isMe() || $me->isRoleAtLeast(MANAGER))
    }

    function toArray(){
        global $wgUser;
        if($this->canView()){
            return array();
        }
        $json = array('user_id' => $this->userId,
                      'reason' => $this->reason,
                      'gender' => $this->gender,
                      'sexuality' => $this->sexuality,
                      'birth' => $this->birth,
                      'indigenous' => $this->indigenous,
                      'disability' => $this->disability,
                      'disability_visibility' => $this->disabilityVisibility,
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
