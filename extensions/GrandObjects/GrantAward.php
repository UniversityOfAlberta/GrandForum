<?php

class GrantAward extends BackboneModel {
    
    var $id;
    var $user_id;
    var $cle;
    var $department;
    var $organization;
    var $institution;
    var $province;
    var $country;
    var $fiscal_year;
    var $competition_year;
    var $amount;
    var $program_id;
    var $program_name;
    var $group;
    var $committee_code;
    var $committee_name;
    var $area_of_application_code;
    var $area_of_application_group;
    var $area_of_application;
    var $research_subject_code;
    var $research_subject_group;
    var $installment;
    var $partie;
    var $nb_partie;
    var $application_title;
    var $keyword;
    var $application_summary;
    var $coapplicants;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $grant = new GrantAward($data);
        return $grant;
    }
    
    static function newFromCle($cle){
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'),
                                    array('cle' => EQ($cle)));
        $grant = new GrantAward($data);
        return $grant;
    }
    
    static function getAllGrantAwards(){
        $grants = array();
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'));
        foreach($data as $row){
            $grant = new GrantAward(array($row));
            if($grant != null && $grant->getId() != 0){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    function GrantAward($data){
        $me = Person::newFromWgUser();
        if(count($data) > 0){
            $row = $data[0];
            if($me->getId() == $row['user_id'] || $me->isRoleAtLeast(ISAC)){
                $this->id = $row['id'];
                $this->user_id = $row['user_id'];
                $this->cle = $row['cle'];
                $this->department = $row['department'];
                $this->organization = $row['organization'];
                $this->institution = $row['institution'];
                $this->province = $row['province'];
                $this->country = $row['country'];
                $this->fiscal_year = $row['fiscal_year'];
                $this->competition_year = $row['competition_year'];
                $this->amount = $row['amount'];
                $this->program_id = $row['program_id'];
                $this->program_name = $row['program_name'];
                $this->group = $row['group'];
                $this->committee_code = $row['committee_code'];
                $this->committee_name = $row['committee_name'];
                $this->area_of_application_code = $row['area_of_application_code'];
                $this->area_of_application_group = $row['area_of_application_group'];
                $this->area_of_application = $row['area_of_application'];
                $this->research_subject_code = $row['research_subject_code'];
                $this->research_subject_group = $row['research_subject_group'];
                $this->installment = $row['installment'];
                $this->partie = $row['partie'];
                $this->nb_partie = $row['nb_partie'];
                $this->application_title = $row['application_title'];
                $this->keyword = $row['keyword'];
                $this->application_summary = $row['application_summary'];
                $this->coapplicants = $row['coapplicants'];
            }
        }
    }
   
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:GrantAwardPage#/{$this->getId()}";
    }
    
    function create(){
        
        DBFunctions::insert('grand_new_grants',
                            array('user_id' => $this->user_id,
                                  'cle' => $this->cle,
                                  'department' => $this->department,
                                  'organization' => $this->organization,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'fiscal_year' => $this->fiscal_year,
                                  'competition_year' => $this->competition_year,
                                  'amount' => $this->amount,
                                  'program_id' => $this->program_id,
                                  'program_name' => $this->program_name,
                                  'group' => $this->group,
                                  'committee_code' => $this->committee_code,
                                  'committee_name' => $this->committee_name,
                                  'area_of_application_code' => $this->area_of_application_code,
                                  'area_of_application_group' => $this->area_of_application_group,
                                  'area_of_application' => $this->area_of_application,
                                  'research_subject_code' => $this->research_subject_code,
                                  'research_subject_group' => $this->research_subject_group,
                                  'installment' => $this->installment,
                                  'partie' => $this->partie,
                                  'nb_partie' => $this->nb_partie,
                                  'application_title' => $this->application_title,
                                  'keyword' => $this->keyword,
                                  'application_summary' => $this->application_summary,
                                  'coapplicants' => $this->coapplicants));
        $this->id = DBFunctions::insertId();
        DBFunctions::commit();
        return $this;
    }
    
    function update(){
        DBFunctions::update('grand_new_grants',
                            array('user_id' => $this->user_id,
                                  'cle' => $this->cle,
                                  'department' => $this->department,
                                  'organization' => $this->organization,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'fiscal_year' => $this->fiscal_year,
                                  'competition_year' => $this->competition_year,
                                  'amount' => $this->amount,
                                  'program_id' => $this->program_id,
                                  'program_name' => $this->program_name,
                                  'group' => $this->group,
                                  'committee_code' => $this->committee_code,
                                  'committee_name' => $this->committee_name,
                                  'area_of_application_code' => $this->area_of_application_code,
                                  'area_of_application_group' => $this->area_of_application_group,
                                  'area_of_application' => $this->area_of_application,
                                  'research_subject_code' => $this->research_subject_code,
                                  'research_subject_group' => $this->research_subject_group,
                                  'installment' => $this->installment,
                                  'partie' => $this->partie,
                                  'nb_partie' => $this->nb_partie,
                                  'application_title' => $this->application_title,
                                  'keyword' => $this->keyword,
                                  'application_summary' => $this->application_summary,
                                  'coapplicants' => $this->coapplicants),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
        return $this;
    }
    
    function delete(){
        DBFunctions::delete('grand_new_grants',
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
        $this->id = null;
        return $this;
    }
    
    function toArray(){
        $json = array('id' => $this->id,
                      'user_id' => $this->user_id,
                      'cle' => $this->cle,
                      'department' => $this->department,
                      'organization' => $this->organization,
                      'institution' => $this->institution,
                      'province' => $this->province,
                      'country' => $this->country,
                      'fiscal_year' => $this->fiscal_year,
                      'competition_year' => $this->competition_year,
                      'amount' => $this->amount,
                      'program_id' => $this->program_id,
                      'program_name' => $this->program_name,
                      'group' => $this->group,
                      'committee_code' => $this->committee_code,
                      'committee_name' => $this->committee_name,
                      'area_of_application_code' => $this->area_of_application_code,
                      'area_of_application_group' => $this->area_of_application_group,
                      'area_of_application' => $this->area_of_application,
                      'research_subject_code' => $this->research_subject_code,
                      'research_subject_group' => $this->research_subject_group,
                      'installment' => $this->installment,
                      'partie' => $this->partie,
                      'nb_partie' => $this->nb_partie,
                      'application_title' => $this->application_title,
                      'keyword' => $this->keyword,
                      'application_summary' => $this->application_summary,
                      'coapplicants' => $this->coapplicants,
                      'url' => $this->getUrl()
        );
        return $json;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'grantaward'.$this->getId();
    }

}

?>
