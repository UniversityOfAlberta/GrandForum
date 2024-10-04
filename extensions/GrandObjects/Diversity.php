<?php
/**
 * @package GrandObjects
 */
class Diversity extends BackboneModel {

    var $id = null;
    var $userId = "";
    var $language = "en";
    var $submitted = false;
    var $decline = "";
    var $reason = "";
    var $gender = array(
        'value' => "",
        'woman' => "",
        'man' => "",
        'other' => ""
    );
    var $orientation = array(
        'value' => array(),
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
        'value' => "",
        'values' => array(),
        'other' => "",
        'decline' => "",
        'decline2' => ""
    );
    var $languageMinority = array(
        'value' => '',
        'yes' => "",
        'decline' => ""
    );
    var $immigration = array(
        'value' => array(),
        'other' => "",
        'decline' => ""
    );
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
            $this->submitted = $data[0]['submitted'];
            $this->decline = $data[0]['decline'];
            $this->reason = $data[0]['reason'];
            $this->gender = unserialize($data[0]['gender']);
            $this->orientation = unserialize($data[0]['orientation']);
            $this->indigenous = $data[0]['indigenous'];
            $this->disability = $data[0]['disability'];
            $this->disabilityVisibility = unserialize($data[0]['disability_visibility']);
            $this->minority = $data[0]['minority'];
            $this->race = unserialize($data[0]['race']);
            $this->languageMinority = unserialize($data[0]['language_minority']);
            $this->immigration = unserialize($data[0]['immigration']);
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
    
    function getSubmitted(){
        return $this->submitted;
    }
    
    function getRaces(){
        $array = (!is_array($this->race)) ? @get_object_vars($this->race) :  $this->race;
        $values = array($array['value']);
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getPopulation(){
        $array = (!is_array($this->race)) ? @get_object_vars($this->race) :  $this->race;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline2'];
        $values = array_filter($values);
        return $values;
    }
    
    function getImmigration(){
        $array = (!is_array($this->immigration)) ? @get_object_vars($this->immigration) :  $this->immigration;
        $values = array($array['value']);
        $values[] = @$array['decline'];
        $values[] = @$array['other'];
        $values = array_filter($values);
        return $values;
    }
    
    function getGenders(){
        $array = (!is_array($this->gender)) ? @get_object_vars($this->gender) :  $this->gender;
        $values = array($array['value']);
        if($array['man'] != ""){ $values[] = $array['man']; };
        if($array['woman'] != ""){ $values[] = $array['woman']; };
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getOrientations(){
        $array = (!is_array($this->orientation)) ? @get_object_vars($this->orientation) :  $this->orientation;
        $values = array($array['value']);
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getIndigenousApply(){
        $array = (!is_array($this->indigenousApply)) ? @get_object_vars($this->indigenousApply) : $this->indigenousApply;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getDisabilityVisibility(){
        $array = (!is_array($this->disabilityVisibility)) ? @get_object_vars($this->disabilityVisibility) : $this->disabilityVisibility;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getLanguage(){
        $array = (!is_array($this->languageMinority)) ? @get_object_vars($this->languageMinority) :  $this->languageMinority;
        $values = array($array['value']);
        if($array['yes'] != ""){ $values[] = $array['yes']; };
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getRespected(){
        $array = (!is_array($this->respected)) ? @get_object_vars($this->respected) : $this->respected;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getLeastRespected(){
        $array = (!is_array($this->leastRespected)) ? @get_object_vars($this->leastRespected) : $this->leastRespected;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getImprove(){
        $array = (!is_array($this->improve)) ? @get_object_vars($this->improve) : $this->improve;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getPreventsTraining(){
        $array = (!is_array($this->preventsTraining)) ? @get_object_vars($this->preventsTraining) : $this->preventsTraining;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function getTrainingTaken(){
        $array = (!is_array($this->trainingTaken)) ? @get_object_vars($this->trainingTaken) : $this->trainingTaken;
        $values = $array['values'];
        $values[] = @$array['other'];
        $values[] = @$array['decline'];
        $values = array_filter($values);
        return $values;
    }
    
    function create(){
        DBFunctions::insert('grand_diversity',
                            array('user_id' => $this->userId,
                                  'language' => $this->language,
                                  'submitted' => $this->submitted,
                                  'decline' => $this->decline,
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'orientation' => serialize($this->orientation),
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => serialize($this->disabilityVisibility),
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'language_minority' => serialize($this->languageMinority),
                                  'immigration' => serialize($this->immigration),
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
                                  'submitted' => $this->submitted,
                                  'decline' => $this->decline,
                                  'reason' => $this->reason,
                                  'gender' => serialize($this->gender),
                                  'orientation' => serialize($this->orientation),
                                  'indigenous' => $this->indigenous,
                                  'disability' => $this->disability,
                                  'disability_visibility' => serialize($this->disabilityVisibility),
                                  'minority' => $this->minority,
                                  'race' => serialize($this->race),
                                  'language_minority' => serialize($this->languageMinority),
                                  'immigration' => serialize($this->immigration),
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
                      'submitted' => $this->submitted,
                      'decline' => $this->decline,
                      'reason' => $this->reason,
                      'gender' => $this->gender,
                      'orientation' => $this->orientation,
                      'indigenous' => $this->indigenous,
                      'disability' => $this->disability,
                      'disabilityVisibility' => $this->disabilityVisibility,
                      'minority' => $this->minority,
                      'race' => $this->race,
                      'languageMinority' => $this->languageMinority,
                      'immigration' => $this->immigration,
                      'affiliation' => $this->affiliation,
                      'age' => $this->age,
                      'indigenousApply' => $this->indigenousApply,
                      'trueSelf' => $this->trueSelf,
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
