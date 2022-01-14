<?php
/**
 * @package GrandObjects
 */
class Diversity extends BackboneModel {

    var $id = null;
    var $userId = "";
    var $language = "en";
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
    var $indigenous = "";
    var $disability = "";
    var $disabilityVisibility = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $minority = "";
    var $race = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $immigration = "";
    var $affiliation = "";
    var $age = "";
    var $indigenousApply = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $trueSelf = "";
    var $valued = "";
    var $space = "";
    var $respected = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $leastRespected = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $principles = "";
    var $principlesDescribe = "";
    var $statement = "";
    var $improve = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $training = "";
    var $preventsTraining = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $trainingTaken = array(
        'values' => array(),
        'other' => "",
        'decline' => ""
    );
    var $implemented = "";
    var $stem = "";
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
            $this->indigenous = $data[0]['indigenous'];
            $this->disability = $data[0]['disability'];
            $this->disabilityVisibility = unserialize($data[0]['disability_visibility']);
            $this->minority = $data[0]['minority'];
            $this->race = unserialize($data[0]['race']);
            $this->immigration = $data[0]['immigration'];
            $this->affiliation = $data[0]['affiliation'];
            $this->age = $data[0]['age'];
            $this->indigenousApply = unserialize($data[0]['indigenous_apply']);
            $this->trueSelf = $data[0]['true_self'];
            $this->valued = $data[0]['valued'];
            $this->space = $data[0]['space'];
            $this->respected = unserialize($data[0]['respected']);
            $this->leastRespected = unserialize($data[0]['least_respected']);
            $this->principles = $data[0]['principles'];
            $this->principlesDescribe = $data[0]['principles_describe'];
            $this->statement = $data[0]['statement'];
            $this->improve = unserialize($data[0]['improve']);
            $this->training = $data[0]['training'];
            $this->preventsTraining = unserialize($data[0]['prevents_training']);
            $this->trainingTaken = unserialize($data[0]['training_taken']);
            $this->implemented = $data[0]['implemented'];
            $this->stem = $data[0]['stem'];
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
        if(count($data) > 0){
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
    
    function getRaces(){
        if(!is_array($this->race)){
            $raceArray = get_object_vars($this->race);
        }
        else{
            $raceArray = $this->race;
        }
        $race = $raceArray['values'];
        $race[] = @$raceArray['other'];
        $race[] = @$raceArray['decline'];
        $race = array_filter($race);
        return $race;
    }
    
    function getGenders(){
        if(!is_array($this->gender)){
            $genderArray = get_object_vars($this->gender);
        }
        else{
            $genderArray = $this->gender;
        }
        $gender = $genderArray['values'];
        $gender[] = @$genderArray['other'];
        $gender[] = @$genderArray['decline'];
        $gender = array_filter($gender);
        return $gender;
    }
    
    function getOrientations(){
        if(!is_array($this->orientation)){
            $orientationArray = get_object_vars($this->orientation);
        }
        else{
            $orientationArray = $this->orientation;
        }
        $orientation = $orientationArray['values'];
        $orientation[] = @$orientationArray['other'];
        $orientation[] = @$orientationArray['decline'];
        $orientation = array_filter($orientation);
        return $orientation;
    }
    
    function isComplete(){
        if($this->decline === 1){
            return true;
        }
        if(trim($this->indigenous) == "" ||
           trim($this->disability) == "" ||
           (trim($this->disability) == "Yes" && trim($this->disabilityVisibility) == "") ||
           trim($this->minority) == "" ||
           trim($this->immigration) == "" ||
           count($this->getRaces()) == 0 ||
           count($this->getGenders()) == 0 ||
           count($this->getOrientations()) == 0){
            return false;  
        }
        return true;
    }
    
    function create(){
        DBFunctions::insert('grand_diversity',
                            array('user_id' => $this->userId,
                                  'language' => $this->language,
                                  'decline' => $this->decline,
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'orientation' => serialize($this->orientation),
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => serialize($this->disabilityVisibility),
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'immigration' => $this->immigration,
                                  'affiliation' => $this->affiliation,
                                  'age' => $this->age,
                                  'indigenous_apply' => serialize($this->indigenousApply),
                                  'true_self' => $this->trueSelf,
                                  'valued' => $this->valued,
                                  'space' => $this->space,
                                  'respected' => serialize($this->respected),
                                  'least_respected' => serialize($this->leastRespected),
                                  'principles' => $this->principles,
                                  'principles_describe' => $this->principlesDescribe,
                                  'statement' => $this->statement,
                                  'improve' => serialize($this->improve),
                                  'training' => $this->training,
                                  'prevents_training' => serialize($this->preventsTraining),
                                  'training_taken' => serialize($this->trainingTaken),
                                  'implemented' => $this->implemented,
                                  'stem' => $this->stem,
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
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => serialize($this->disabilityVisibility),
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'immigration' => $this->immigration,
                                  'affiliation' => $this->affiliation,
                                  'age' => $this->age,
                                  'indigenous_apply' => serialize($this->indigenousApply),
                                  'true_self' => $this->trueSelf,
                                  'valued' => $this->valued,
                                  'space' => $this->space,
                                  'respected' => serialize($this->respected),
                                  'least_respected' => serialize($this->leastRespected),
                                  'principles' => $this->principles,
                                  'principles_describe' => $this->principlesDescribe,
                                  'statement' => $this->statement,
                                  'improve' => serialize($this->improve),
                                  'training' => $this->training,
                                  'prevents_training' => serialize($this->preventsTraining),
                                  'training_taken' => serialize($this->trainingTaken),
                                  'implemented' => $this->implemented,
                                  'stem' => $this->stem,
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
        return (!$wgImpersonating && !$wgDelegating && ($this->userId == "" || $this->getPerson()->isMe() || $me->isRole(ADMIN) || $me->isRole(EDI)));
    }

    function toArray(){
        global $wgUser;
        if(!$this->canView()){
            return array();
        }
        $json = array('id' => $this->id,
                      'userId' => $this->userId,
                      'language' => $this->language,
                      'decline' => $this->decline,
                      'reason' => $this->reason,
                      'gender' => $this->gender,
                      'orientation' => $this->orientation,
                      'indigenous' => $this->indigenous,
                      'disability' => $this->disability,
                      'disabilityVisibility' => $this->disabilityVisibility,
                      'minority' => $this->minority,
                      'race' => $this->race,
                      'immigration' => $this->immigration,
                      'affiliation' => $this->affiliation,
                      'age' => $this->age,
                      'indigenousApply' => $this->indigenousApply,
                      'true_self' => $this->trueSelf,
                      'valued' => $this->valued,
                      'space' => $this->space,
                      'respected' => $this->respected,
                      'leastRespected' => $this->leastRespected,
                      'principles' => $this->principles,
                      'principlesDescribe' => $this->principlesDescribe,
                      'statement' => $this->statement,
                      'improve' => $this->improve,
                      'training' => $this->training,
                      'preventsTraining' => $this->preventsTraining,
                      'trainingTaken' => $this->trainingTaken,
                      'implemented' => $this->implemented,
                      'stem' => $this->stem,
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
