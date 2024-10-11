<?php
    class VoteFreezeAPI extends API{
        var $courses = array();
        function processParams($params){
            //TODO
        }

        function doAction($noEcho=false){
            global $wgMessage, $config;
            $me = Person::newFromWgUser();

            if($me->isRole(VDEAN) || 
               $me->isRole(DEAN) || 
               $me->isRole(HR) || 
               $me->isRole(ADMIN) || 
               $me->isRole("FEC") ||
               $me->isRole("FEC ".getFaculty()) ||
               $me->isRole("ATSEC")){
                $freeze = DBFunctions::execSQL("SELECT rp_subitem, data, encrypted
                                                FROM grand_report_blobs
                                                WHERE year = ".YEAR."
                                                AND rp_type = 'RP_FEC_TABLE'
                                                AND rp_section = 'TABLE'
                                                AND rp_item = 'FREEZE'
                                                AND (data = 'Frozen' OR data = 'Unanimous')");

                $t_freeze = DBFunctions::execSQL("SELECT rp_subitem, data, encrypted
                                                  FROM grand_report_blobs
                                                  WHERE year = ".YEAR."
                                                  AND rp_type = 'RP_FEC_TABLE'
                                                  AND rp_section = 'TABLE'
                                                  AND rp_item = 'T_FREEZE'
                                                  AND (data = 'Frozen' OR data = 'Unanimous')");

                $p_freeze = DBFunctions::execSQL("SELECT rp_subitem, data, encrypted
                                                  FROM grand_report_blobs
                                                  WHERE year = ".YEAR."
                                                  AND rp_type = 'RP_FEC_TABLE'
                                                  AND rp_section = 'TABLE'
                                                  AND rp_item = 'P_FREEZE'
                                                  AND (data = 'Frozen' OR data = 'Unanimous')");

                $increments = DBFunctions::select(array('grand_report_blobs'),
                                                  array('rp_subitem', 'data', 'encrypted'),
                                                  array('year' => YEAR,
                                                        'rp_type' => "RP_FEC_TABLE",
                                                        'rp_section' => "TABLE",
                                                        'rp_item' => "INCREMENT",
                                                        'data' => NEQ("")));
                $this->data['increment'] = array();
                $this->data['freeze'] = array();
                $this->data['t_freeze'] = array();
                $this->data['p_freeze'] = array();
                foreach($increments as $case){
                    $data = ($case['encrypted'] == 1) ? decrypt($case['data']) : $case['data'];
                    if($data != ""){
                        $this->data['increment'][$case['rp_subitem']] = $data;
                    }
                }
                foreach($freeze as $case){
                    $this->data['freeze'][] = $case['rp_subitem'];
                }
                foreach($t_freeze as $case){
                    $this->data['t_freeze'][] = $case['rp_subitem'];
                }
                foreach($p_freeze as $case){
                    $this->data['p_freeze'][] = $case['rp_subitem'];
                }
            }
            else{
                $wgMessage->addError("You do not have access to this api");
                exit;
            }
        }

        function isLoginRequired(){
            return true;
        }
    }
?>
