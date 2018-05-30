<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    DBFunctions::execSQL("TRUNCATE grand_salary_scales", true);
    
    $salary_scales = DBFunctions::select(array('bddEfec2_production.salary_scales'),
                                         array('*'));
                                         
    foreach($salary_scales as $scale){
        DBFunctions::insert('grand_salary_scales',
                            array('year'              => $scale['year'],
                                  'min_salary_assoc'  => $scale['min_salary_associate'],
                                  'min_salary_assist' => $scale['min_salary_assistant'],
                                  'min_salary_prof'   => $scale['min_salary_prof'],
                                  'min_salary_fso2'   => $scale['min_salary_fso2'],
                                  'min_salary_fso3'   => $scale['min_salary_fso3'],
                                  'min_salary_fso4'   => $scale['min_salary_fso4'],
                                  'max_salary_assoc'  => $scale['max_salary_assoc'],
                                  'max_salary_assist' => $scale['max_salary_assistant'],
                                  'max_salary_prof'   => $scale['max_salary_prof'],
                                  'max_salary_fso2'   => $scale['max_salary_fso2'],
                                  'max_salary_fso3'   => $scale['max_salary_fso3'],
                                  'max_salary_fso4'   => $scale['max_salary_fso4'],
                                  'increment_assoc'   => $scale['increment_assoc'],
                                  //'increment_assist'  => $scale['increment_assistant'],
                                  'increment_prof'    => $scale['increment_prof'],
                                  'increment_fso2'    => $scale['increment_fso2'],
                                  'increment_fso3'    => $scale['increment_fso3'],
                                  'increment_fso4'    => $scale['increment_fso4']));
                                  
    }

?>
