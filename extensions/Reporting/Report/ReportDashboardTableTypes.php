<?php

define('NI_REPORT_STRUCTURE', 100);
define('NI_REPORT_PRODUCTIVITY_STRUCTURE', 101);
define('HQP_REPORT_STRUCTURE', 102);

define('PROJECT_REPORT_PRODUCTIVITY_STRUCTURE', 103);
define('PROJECT_REPORT_TIME_STRUCTURE', 104);
define('PROJECT_ROSTER_STRUCTURE', 105);
define('PROJECT_CHAMP_ROSTER_STRUCTURE', 106);
define('PROJECT_NI_ROSTER_STRUCTURE', 107);

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
    array(array(STRUCT(HEAD, "Projects"),
                STRUCT(HEAD, "Hours/Week"), 
                STRUCT(HEAD, "HQP", "tooltip=\"Under HQP, the productivity table reports HQP who have been listed as being supervised by the NI during the current reporting period. The detailed listing of the HQP (accessible when you click on and of the HQP column links) documents more precisely why each individual HQP is listed.\""), 
                STRUCT(HEAD, "Publications", "PB: Published", "tooltip=\"Under Publications, the productivity table reports any publication co-authored by the NI, with a date within current reporting year and two years into the future (to account for 'to appear' publications). Publications are of different types but, in principle, are documents that appear in archival venues and can be cited.\""), 
                STRUCT(HEAD, "Artifacts", "PR: Peer Reviewed", "tooltip=\"Under Artifacts, the productivity table reports any artifact co-produced by the NI, with a date within the current reporting year and two years into the future (to account for things like scheduled software releases and planned artistic installations). This category of productivity evidence includes curated data repositories, open-source software, artistic works. They are annotated as \\\"peer reviewed\\\" or \\\"non peer-reviewed\\\".\""), 
                STRUCT(HEAD, "Activities", "tooltip=\"Under Activities, the productivity table reports any activity in which the NI was involved, with a date the current reporting year and two years beyond. This category is meant to include conference organization and similar activities.\""), 
                STRUCT(HEAD, "Presentations", "tooltip=\"Under Presentations, the productivity table reports any activity in which the NI was involved, with a date within the current reporting year and two years into the future. This category is meant to include conference organization and similar activities.\""), 
                STRUCT(HEAD, "Press"), 
                STRUCT(HEAD, "Awards", "tooltip=\"Under Awards, the productivity table reports awards bestowed to the NI within the current reporting year.\""), 
                STRUCT(HEAD, "Multimedia", "tooltip=\"Under Multimedia, the productivity table reports multimedia stories in which the NI was involved within the current reporting year.\""), 
                STRUCT(HEAD, "Contributions", "tooltip=\"Under Contributions, the productivity table reports cash and in-kind contributions to the NI's GRAND-related research activities by agencies outside GRAND during the current reporting year.\"")), 
    array(HEAD.'(Total:)',
          STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
          STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
          STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
          STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
          STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
          STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
          STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
          STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
          STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
          STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)), 
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                           STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                                                           STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                           STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                           STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                           STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                           STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                           STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                           STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                           STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)), 
      array(HEAD.'(Total:)',
            STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
            STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
            STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
            STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
            STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
            STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
            STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
            STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
            STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
            STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))
    );

$dashboardStructures[HQP_REPORT_STRUCTURE] =
    array(array(HEAD."(Projects)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)"),
          array(HEAD.'(Total:)', 
                STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END)),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                          STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                            STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                            STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                            STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                            STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                            STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                            STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END)),
          array(HEAD.'(Total:)', 
                STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END))
    );

$dashboardStructures[PROJECT_REPORT_PRODUCTIVITY_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)", HEAD."(Contributions)"),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE_ROLES,
                                                         STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                         STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                                                        STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                                                        STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END)),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_NCE_END), 
                STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_NCE_END),
                STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_NCE_END))
    );
    
$dashboardStructures[PROJECT_REPORT_TIME_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Roles, PNI: Principle Network Investigator, CNI: Collaborating Network Investigator, PL: Project Leader, COPL: Co Project Leader, PM: Project Manager, sPL: Sub-Project Leader)", HEAD."(University &amp; Department)", HEAD."(Hours/Week)", HEAD."(Allocated:<br />".(REPORTING_YEAR)." - ".(REPORTING_YEAR+1).")", HEAD."(Requested:<br />".(REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2).")"),
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
    array(array(HEAD."(People)", HEAD."(Roles, PNI: Principle Network Investigator, CNI: Collaborating Network Investigator, PL: Project Leader, COPL: Co Project Leader, PM: Project Manager, sPL: Sub-Project Leader)", HEAD."(Affiliation)"),
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
          STRUCT(GROUP_BY, PROJECT_PNI_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_CNI_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)),
          STRUCT(GROUP_BY, PROJECT_AR_NO_LEADERS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PROJECT_PEOPLE_ROLES,
                                                           STRUCT(PROJECT_UNIVERSITY, REPORTING_CYCLE_START, REPORTING_CYCLE_END)));

?>
