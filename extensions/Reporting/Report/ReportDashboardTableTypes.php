<?php

define('NI_REPORT_STRUCTURE', 100);
define('NI_REPORT_PRODUCTIVITY_STRUCTURE', 101);
define('HQP_REPORT_STRUCTURE', 102);

define('PROJECT_REPORT_PRODUCTIVITY_STRUCTURE', 103);
define('PROJECT_REPORT_TIME_STRUCTURE', 104);
define('PROJECT_ROSTER_STRUCTURE', 105);
define('PROJECT_CHAMP_ROSTER_STRUCTURE', 106);
define('PROJECT_NI_ROSTER_STRUCTURE', 107);
define('PROJECT_CONTRIBUTION_STRUCTURE', 108);

$productStructure = Product::structure();
$categories = array_keys($productStructure['categories']);

$head = array();
$persRow = array();
$projRow = array();
foreach($categories as $category){
    $head[] = HEAD."(".Inflect::pluralize($category).")";
    $persRow[] = STRUCT(PERSON_PRODUCTS, $category, REPORTING_CYCLE_START, REPORTING_NCE_END);
    $projRow[] = STRUCT(PROJECT_PRODUCTS, $category, REPORTING_CYCLE_START, REPORTING_NCE_END);
}

$dashboardStructures[NI_REPORT_STRUCTURE] =
    array(array(STRUCT(HEAD, "Projects"), 
                STRUCT(HEAD, "HQP", "tooltip=\"Under HQP, the network table reports HQP who have been listed as being supervised by the NI during the current reporting period. The detailed listing of the HQP (accessible when you click on and of the HQP column links) documents more precisely why each individual HQP is listed.\""), 
                STRUCT(HEAD, "Hours/Week"), 
                STRUCT(HEAD, "Allocated:<br />".(REPORTING_YEAR)." - ".(REPORTING_YEAR+1), "tooltip=\"The last two columns of the network table reports are the (NI) budget allocation for the current funding year (2013-14) and the budget request for the forthcoming funding year (2014-15). If the first budget amount is missing, then you must upload your budget reflecting your actual 2013-14 allocation (not including carryover), through the \\\"My Profile\\\" - \\\"Budget\\\" page. If the second budget amount is missing, then you have not yet uploaded a proper budget request for 2014-15 in the \\\"NI budget\\\" step of the reporting workflow; a corresponding error message will appear in your report-completion status.\""), 
                STRUCT(HEAD, "Requested:<br />".(REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2), "tooltip=\"The last two columns of the network table reports are the (NI) budget allocation for the current funding year (2013-14) and the budget request for the forthcoming funding year (2014-15). If the first budget amount is missing, then you must upload your budget reflecting your actual 2013-14 allocation (not including carryover), through the \\\"My Profile\\\" - \\\"Budget\\\" page. If the second budget amount is missing, then you have not yet uploaded a proper budget request for 2014-15 in the \\\"NI budget\\\" step of the reporting workflow; a corresponding error message will appear in your report-completion status.\"")),
    array(HEAD.'(Total:)',
          STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
          STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
          STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
          STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END)), 
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                           STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                                                           STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END)), 
      array(HEAD.'(Total:)', 
            STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
            STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
            STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
            STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END))
    );

$dashboardStructures[NI_REPORT_PRODUCTIVITY_STRUCTURE] =
    array(array_merge(array(STRUCT(HEAD, "Projects"),
                            STRUCT(HEAD, "Hours/Week"), 
                            STRUCT(HEAD, "HQP", "tooltip=\"Under HQP, the productivity table reports HQP who have been listed as being supervised by the NI during the current reporting period. The detailed listing of the HQP (accessible when you click on and of the HQP column links) documents more precisely why each individual HQP is listed.\"")), 
                      $head,
                      array(STRUCT(HEAD, "Multimedia", "tooltip=\"Under Multimedia, the productivity table reports multimedia stories in which the NI was involved within the current reporting year.\""), 
                            STRUCT(HEAD, "Contributions", "tooltip=\"Under Contributions, the productivity table reports cash and in-kind contributions to the NI's GRAND-related research activities by agencies outside GRAND during the current reporting year.\""))), 
    array_merge(array(HEAD.'(Total:)',
                      STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                      STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
                $persRow,
                array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                      STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))), 
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array_merge(
                                                           array(PERSON_PROJECTS,
                                                                 STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                                 STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
                                                           $persRow,
                                                           array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                                 STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))),
      array_merge(array(HEAD.'(Total:)',
                       STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                       STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
                 $persRow,
                 array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                       STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)))
    );

$dashboardStructures[HQP_REPORT_STRUCTURE] =
    array(array_merge(array(HEAD."(Projects)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array_merge(
                                                            array(PERSON_PROJECTS),
                                                            $persRow,
                                                            array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END)))
    );

$dashboardStructures[PROJECT_REPORT_PRODUCTIVITY_STRUCTURE] = 
    array(array_merge(array(HEAD."(People)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)'), $projRow, array(STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array_merge(array(PROJECT_PEOPLE_ROLES),
                                                                 $projRow,
                                                                 array(STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array_merge(
                                                                 array(PROJECT_PEOPLE_ROLES),
                                                                 $projRow,
                                                                 array(STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          array_merge(array(HEAD.'(Total:)'), $projRow, array(STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END)))
    );
    
$dashboardStructures[PROJECT_CONTRIBUTION_STRUCTURE] = 
    array(array_merge(array(HEAD."(People)"), array(HEAD."(Contributions)")),
          array_merge(array(HEAD.'(Total:)'), array(STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array_merge(array(PROJECT_PEOPLE),
                                                                 array(STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array_merge(
                                                                 array(PROJECT_PEOPLE),
                                                                 array(STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))),
          array_merge(array(HEAD.'(Total:)'), array(STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)))
    );
    
$dashboardStructures[PROJECT_REPORT_TIME_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Roles, NI: Network Investigator, PL: Project Leader, PM: Project Manager, sPL: Sub-Project Leader)", HEAD."(University &amp; Department)", HEAD."(Hours/Week)", HEAD."(Allocated:<br />".(REPORTING_YEAR)." - ".(REPORTING_YEAR+1).")", HEAD."(Requested:<br />".(REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2).")"),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE,
                                                         STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END),),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE,
                                                         STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                         STRUCT(PROJECT_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END),),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_ROLES, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                STRUCT(PROJECT_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END))
    );
    
$dashboardStructures[PROJECT_ROSTER_STRUCTURE] =
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
                                                           
$dashboardStructures[PROJECT_CHAMP_ROSTER_STRUCTURE] = 
    array(array(HEAD."(Champions)", HEAD."(Affiliation)"),
          array(PROJECT_HEAD),
          STRUCT(GROUP_BY, PROJECT_CHAMPIONS_ARRAY) => array(PROJECT_PEOPLE,
                                                             STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));
                                                             
$dashboardStructures[PROJECT_NI_ROSTER_STRUCTURE] = 
    array(array(HEAD."(NIs)", HEAD."(Affiliation)"),
          //array(),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_NI_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));

?>
