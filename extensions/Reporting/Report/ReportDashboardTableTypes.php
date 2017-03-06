<?php

define('HQP_REPORT_STRUCTURE', 102);
define('PROJECT_REPORT_PRODUCTIVITY_STRUCTURE', 103);
define('PROJECT_ROSTER_STRUCTURE', 105);
define('PROJECT_CHAMP_ROSTER_STRUCTURE', 106);
define('PROJECT_NI_ROSTER_STRUCTURE', 107);
define('PROJECT_CONTRIBUTION_STRUCTURE', 108);
   
$dashboardStructures[HQP_REPORT_STRUCTURE] = function($start=REPORTING_CYCLE_START, $end=REPORTING_NCE_END){
    $productStructure = Product::structure();
    $categories = array_keys($productStructure['categories']);

    $head = array();
    $persRow = array();
    $projRow = array();
    foreach($categories as $category){
        $head[] = HEAD."(".Inflect::pluralize($category).")";
        $persRow[] = STRUCT(PERSON_PRODUCTS, $category, $start, $end);
        $projRow[] = STRUCT(PROJECT_PRODUCTS, $category, $start, $end);
    }
    return array(array_merge(array(HEAD."(Projects)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(STRUCT(PERSON_MULTIMEDIA, $start, $end))),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, $start, $end) => array_merge(
                                                            array(PERSON_PROJECTS),
                                                            $persRow,
                                                            array(STRUCT(PERSON_MULTIMEDIA, $start, $end))),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(STRUCT(PERSON_MULTIMEDIA, $start, $end)))
    );  
};

$dashboardStructures[PROJECT_REPORT_PRODUCTIVITY_STRUCTURE] = function($start=REPORTING_CYCLE_START, $end=REPORTING_NCE_END){
    $productStructure = Product::structure();
    $categories = array_keys($productStructure['categories']);

    $head = array();
    $persRow = array();
    $projRow = array();
    foreach($categories as $category){
        $head[] = HEAD."(".Inflect::pluralize($category).")";
        $persRow[] = STRUCT(PERSON_PRODUCTS, $category, $start, $end);
        $projRow[] = STRUCT(PROJECT_PRODUCTS, $category, $start, $end);
    }
    return array(array_merge(array(HEAD."(People)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)'), $projRow, array(STRUCT(PROJECT_MULTIMEDIA, $start, $end))),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array_merge(array(PROJECT_PEOPLE_ROLES),
                                                                 $projRow,
                                                                 array(STRUCT(PROJECT_MULTIMEDIA, $start, $end))),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, $start, $end) => array_merge(
                                                                 array(PROJECT_PEOPLE_ROLES),
                                                                 $projRow,
                                                                 array(STRUCT(PROJECT_MULTIMEDIA, $start, $end))),
          array_merge(array(HEAD.'(Total:)'), $projRow, array(STRUCT(PROJECT_MULTIMEDIA, $start, $end)))
    );
};
    
$dashboardStructures[PROJECT_CONTRIBUTION_STRUCTURE] = function($start=REPORTING_CYCLE_START, $end=REPORTING_NCE_END){
    return
    array(array_merge(array(HEAD."(People)"), array(HEAD."(Contributions)")),
          array_merge(array(HEAD.'(Total:)'), array(STRUCT(PROJECT_CONTRIBUTIONS, $start, $end))),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array_merge(array(PROJECT_PEOPLE),
                                                                 array(STRUCT(PROJECT_CONTRIBUTIONS, $start, $end))),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, $start, $end) => array_merge(
                                                                 array(PROJECT_PEOPLE),
                                                                 array(STRUCT(PROJECT_CONTRIBUTIONS, $start, $end))),
          array_merge(array(HEAD.'(Total:)'), array(STRUCT(PROJECT_CONTRIBUTIONS, $start, $end)))
    );
};
    
$dashboardStructures[PROJECT_ROSTER_STRUCTURE] = function(){
    return
    array(array(HEAD."(People)", HEAD."(Roles, NI: Network Investigator, PL: Project Leader, PM: Project Manager, sPL: Sub-Project Leader)", HEAD."(Affiliation)"),
          array(PROJECT_HEAD),
          STRUCT(GROUP_BY, PROJECT_CHAMPIONS_ARRAY) => array(PROJECT_PEOPLE,
                                                           STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE,
                                                           STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE,
                                                           STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));
};
                                                           
$dashboardStructures[PROJECT_CHAMP_ROSTER_STRUCTURE] = function(){
    return
    array(array(HEAD."(Champions)", HEAD."(Affiliation)"),
          array(PROJECT_HEAD),
          STRUCT(GROUP_BY, PROJECT_CHAMPIONS_ARRAY) => array(PROJECT_PEOPLE,
                                                             STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));
};
                                                             
$dashboardStructures[PROJECT_NI_ROSTER_STRUCTURE] = function(){
    return
    array(array(HEAD."(NIs)", HEAD."(Affiliation)"),
          //array(),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_NI_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));
};

?>
