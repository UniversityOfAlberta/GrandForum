<?php
autoload_register('QueryableTable/DashboardTable/Arrays');
autoload_register('QueryableTable/DashboardTable/Cells');

//CellTypes
//// Person Types
define('PERSON_NAME', 100);
define('PERSON_ROLES', 101);
define('PERSON_HQP', 102);
define('PERSON_PARTNERS', 103);
define('PERSON_UNIVERSITY', 104);
define('PERSON_HOURS', 105);
define('PERSON_PROJECTS', 106);
define('PERSON_PUBLICATIONS', 107);
define('PERSON_ARTIFACTS', 108);
define('PERSON_ACTIVITIES', 109);
define('PERSON_PRESS', 110);
define('PERSON_AWARDS', 111);
define('PERSON_CONTRIBUTIONS', 112);
define('PERSON_SUPERVISORS', 113);
define('PERSON_BUDGET', 114);
define('PERSON_ALLOCATED_BUDGET', 115);
define('PERSON_MULTIMEDIA', 116);
define('PERSON_PRESENTATIONS', 117);
//// Person Array Types
define('PERSON_PROJECTS_ARRAY', 125);

//// Project Types
define('PROJECT_NAME', 1001);
define('PROJECT_PEOPLE', 1002);
define('PROJECT_ROLES', 1003);
define('PROJECT_PARTNERS', 1004);
define('PROJECT_UNIVERSITY', 1005);
define('PROJECT_HOURS', 1006);
define('PROJECT_BUDGET', 1007);
define('PROJECT_ALLOCATED_BUDGET', 1008);
define('PROJECT_PUBLICATIONS', 1010);
define('PROJECT_ARTIFACTS', 1011);
define('PROJECT_ACTIVITIES', 1012);
define('PROJECT_AWARDS', 1013);
define('PROJECT_CONTRIBUTIONS', 1014);
define('PROJECT_PRESS', 1015);
define('PROJECT_MULTIMEDIA', 1016);
define('PROJECT_PEOPLE_ROLES', 1017);
define('PROJECT_PRESENTATIONS', 1018);
//// Project Array Types
define('PROJECT_PEOPLE_ARRAY', 1125);
define('PROJECT_LEADERS_ARRAY', 1126);
define('PROJECT_PEOPLE_NO_LEADERS_ARRAY', 1127);

$cellTypes[PERSON_NAME] = "PersonNameCell";
$cellTypes[PERSON_ROLES] = "PersonRolesCell";
$cellTypes[PERSON_HQP] = "PersonHQPCell";
$cellTypes[PERSON_SUPERVISORS] = "PersonSupervisorsCell";
$cellTypes[PERSON_BUDGET] = "PersonBudgetCell";
$cellTypes[PERSON_ALLOCATED_BUDGET] = "PersonAllocatedBudgetCell";
$cellTypes[PERSON_PARTNERS] = "PersonPartnersCell";
$cellTypes[PERSON_UNIVERSITY] = "PersonUniversityCell";
$cellTypes[PERSON_HOURS] = "PersonHoursCell";
$cellTypes[PERSON_PROJECTS] = "PersonProjectsCell";
$cellTypes[PERSON_PUBLICATIONS] = "PersonPublicationsCell";
$cellTypes[PERSON_ARTIFACTS] = "PersonArtifactsCell";
$cellTypes[PERSON_ACTIVITIES] = "PersonActivitiesCell";
$cellTypes[PERSON_PRESS] = "PersonPressCell";
$cellTypes[PERSON_AWARDS] = "PersonAwardsCell";
$cellTypes[PERSON_MULTIMEDIA] = "PersonMultimediaCell";
$cellTypes[PERSON_PRESENTATIONS] = "PersonPresentationsCell";
$cellTypes[PERSON_CONTRIBUTIONS] = "PersonContributionsCell";
$arrayTypes[PERSON_PROJECTS_ARRAY] = "PersonProjectsArray";

$cellTypes[PROJECT_NAME] = "ProjectNameCell";
$cellTypes[PROJECT_PEOPLE] = "ProjectPeopleCell";
$cellTypes[PROJECT_ROLES] = "ProjectRolesCell";
$cellTypes[PROJECT_PARTNERS] = "ProjectPartnersCell";
$cellTypes[PROJECT_UNIVERSITY] = "ProjectUniversityCell";
$cellTypes[PROJECT_HOURS] = "ProjectHoursCell";
$cellTypes[PROJECT_BUDGET] = "ProjectBudgetCell";
$cellTypes[PROJECT_ALLOCATED_BUDGET] = "ProjectAllocatedBudgetCell";
$cellTypes[PROJECT_PUBLICATIONS] = "ProjectPublicationsCell";
$cellTypes[PROJECT_ARTIFACTS] = "ProjectArtifactsCell";
$cellTypes[PROJECT_ACTIVITIES] = "ProjectActivitiesCell";
$cellTypes[PROJECT_AWARDS] = "ProjectAwardsCell";
$cellTypes[PROJECT_MULTIMEDIA] = "ProjectMultimediaCell";
$cellTypes[PROJECT_CONTRIBUTIONS] = "ProjectContributionsCell";
$cellTypes[PROJECT_PRESS] = "ProjectPressCell";
$cellTypes[PROJECT_PRESENTATIONS] = "ProjectPresentationsCell";
$cellTypes[PROJECT_PEOPLE_ROLES] = "ProjectPeopleRolesCell";
$arrayTypes[PROJECT_PEOPLE_ARRAY] = "ProjectPeopleArray";
$arrayTypes[PROJECT_LEADERS_ARRAY] = "ProjectLeadersArray";
$arrayTypes[PROJECT_PEOPLE_NO_LEADERS_ARRAY] = "ProjectPeopleNoLeadersArray";

//DashboardTable Structures
define('NI_PUBLIC_PROFILE_STRUCTURE', 1);
define('NI_PRIVATE_PROFILE_STRUCTURE', 2);
define('HQP_PUBLIC_PROFILE_STRUCTURE', 3);
define('NI_REPORT_STRUCTURE', 4);
define('NI_REPORT_PRODUCTIVITY_STRUCTURE', 5);
define('HQP_REPORT_STRUCTURE', 6);

define('PROJECT_PUBLIC_STRUCTURE', 10);
define('PROJECT_REPORT_PRODUCTIVITY_STRUCTURE', 12);
define('PROJECT_REPORT_TIME_STRUCTURE', 13);

$dashboardStructures = array();
$dashboardStructures[NI_PUBLIC_PROFILE_STRUCTURE] =
    array(array(HEAD."(Projects)", HEAD."(HQP)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)"),
          array(HEAD.'(Total:)', PERSON_HQP, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array(PERSON_PROJECTS,
                                                          PERSON_HQP, 
                                                          PERSON_PUBLICATIONS, 
                                                          PERSON_ARTIFACTS, 
                                                          PERSON_ACTIVITIES,
                                                          PERSON_PRESENTATIONS,
                                                          PERSON_PRESS,
                                                          PERSON_AWARDS,
                                                          PERSON_MULTIMEDIA),
          array(HEAD.'(Total:)', PERSON_HQP, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA)
    );
    
$dashboardStructures[NI_PRIVATE_PROFILE_STRUCTURE] =
    array(array(HEAD."(Projects)", HEAD."(Sponsors)", HEAD."(HQP)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)", HEAD."(Contributions)"),
    array(HEAD.'(Total:)', PERSON_PARTNERS, PERSON_HQP, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA, PERSON_CONTRIBUTIONS),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array(PERSON_PROJECTS,
                                                          PERSON_PARTNERS, 
                                                          PERSON_HQP, 
                                                          PERSON_PUBLICATIONS, 
                                                          PERSON_ARTIFACTS, 
                                                          PERSON_ACTIVITIES,
                                                          PERSON_PRESENTATIONS,
                                                          PERSON_PRESS,
                                                          PERSON_AWARDS,
                                                          PERSON_MULTIMEDIA,
                                                          PERSON_CONTRIBUTIONS),
          array(HEAD.'(Total:)', PERSON_PARTNERS, PERSON_HQP, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA, PERSON_CONTRIBUTIONS)
    );
    
$dashboardStructures[NI_REPORT_STRUCTURE] =
    array(array(STRUCT(HEAD, "Projects"), 
                STRUCT(HEAD, "HQP", "tooltip=\"Under HQP, the network table reports HQP who have been listed as being supervised by the NI during the current reporting period. The detailed listing of the HQP (accessible when you click on and of the HQP column links) documents more precisely why each individual HQP is listed.\""), 
                STRUCT(HEAD, "Sponsors", "tooltip=\"Under Sponsors, the network table reports the organizations that have made a contribution (cash, in-kind or otherwise) towards the GRAND-related activities of the NI (i.e., Partners), and the champions(individuals) with whom the NI is working. An individual is a Champion if the NI has been collaborating with him/her in the context of a project, even if the relation is not formal yet. To specify a \\\"Champion\\\" you have to (a) add them on the forum (using the \\\"add user\\\" tool) and give them the \\\"Champion\\\" role, and (b) specify that you are \\\"working with\\\" them (using the \\\"edit relations\\\" tool).\""), 
                STRUCT(HEAD, "Hours/Week"), 
                STRUCT(HEAD, "Allocated:<br />".(REPORTING_YEAR)." - ".(REPORTING_YEAR+1), "tooltip=\"The last two columns of the network table reports are the (NI) budget allocation for the current funding year (2013-14) and the budget request for the forthcoming funding year (2014-15). If the first budget amount is missing, then you must upload your budget reflecting your actual 2013-14 allocation (not including carryover), through the \\\"My Profile\\\" - \\\"Budget\\\" page. If the second budget amount is missing, then you have not yet uploaded a proper budget request for 2014-15 in the \\\"NI budget\\\" step of the reporting workflow; a corresponding error message will appear in your report-completion status.\""), 
                STRUCT(HEAD, "Requested:<br />".(REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2), "tooltip=\"The last two columns of the network table reports are the (NI) budget allocation for the current funding year (2013-14) and the budget request for the forthcoming funding year (2014-15). If the first budget amount is missing, then you must upload your budget reflecting your actual 2013-14 allocation (not including carryover), through the \\\"My Profile\\\" - \\\"Budget\\\" page. If the second budget amount is missing, then you have not yet uploaded a proper budget request for 2014-15 in the \\\"NI budget\\\" step of the reporting workflow; a corresponding error message will appear in your report-completion status.\"")),
    array(HEAD.'(Total:)',
          STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
          STRUCT(PERSON_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
          STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
          STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
          STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END)), 
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                           STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
                                                           STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
                                                           STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END)), 
      array(HEAD.'(Total:)', 
            STRUCT(PERSON_HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
            STRUCT(PERSON_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
            STRUCT(PERSON_HOURS, REPORTING_CYCLE_START, REPORTING_CYCLE_END),
            STRUCT(PERSON_ALLOCATED_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), 
            STRUCT(PERSON_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END))
    );
    
$dashboardStructures[NI_REPORT_PRODUCTIVITY_STRUCTURE] =
    array(array(STRUCT(HEAD, "Projects"), 
                STRUCT(HEAD, "Publications", "PB: Published", "tooltip=\"Under Publications, the productivity table reports any publication co-authored by the NI, with a date within current reporting year and two years into the future (to account for 'to appear' publications). Publications are of different types but, in principle, are documents that appear in archival venues and can be cited.\""), 
                STRUCT(HEAD, "Artifacts", "PR: Peer Reviewed", "tooltip=\"Under Artifacts, the productivity table reports any artifact co-produced by the NI, with a date within the current reporting year and two years into the future (to account for things like scheduled software releases and planned artistic installations). This category of productivity evidence includes curated data repositories, open-source software, artistic works. They are annotated as \\\"peer reviewed\\\" or \\\"non peer-reviewed\\\".\""), 
                STRUCT(HEAD, "Activities", "tooltip=\"Under Activities, the productivity table reports any activity in which the NI was involved, with a date the current reporting year and two years beyond. This category is meant to include conference organization and similar activities.\""), 
                STRUCT(HEAD, "Presentations", "tooltip=\"Under Presentations, the productivity table reports any activity in which the NI was involved, with a date within the current reporting year and two years into the future. This category is meant to include conference organization and similar activities.\""), 
                STRUCT(HEAD, "Press"), 
                STRUCT(HEAD, "Awards", "tooltip=\"Under Awards, the productivity table reports awards bestowed to the NI within the current reporting year.\""), 
                STRUCT(HEAD, "Multimedia", "tooltip=\"Under Multimedia, the productivity table reports multimedia stories in which the NI was involved within the current reporting year.\""), 
                STRUCT(HEAD, "Contributions", "tooltip=\"Under Contributions, the productivity table reports cash and in-kind contributions to the NI's GRAND-related research activities by agencies outside GRAND during the current reporting year.\"")), 
    array(HEAD.'(Total:)',
          STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
          STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
          STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
          STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
          STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
          STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
          STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
          STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)), 
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                           STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                           STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                           STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                           STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                           STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                           STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                           STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                           STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)), 
      array(HEAD.'(Total:)', 
            STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
            STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
            STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
            STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
            STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
            STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
            STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
            STRUCT(PERSON_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL))
    );
    
$dashboardStructures[HQP_PUBLIC_PROFILE_STRUCTURE] =
    array(array(HEAD."(Projects)", HEAD."(Supervisors)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)"),
          array(HEAD.'(Total:)', PERSON_SUPERVISORS, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array(PERSON_PROJECTS,
                                                          PERSON_SUPERVISORS, 
                                                          PERSON_PUBLICATIONS, 
                                                          PERSON_ARTIFACTS, 
                                                          PERSON_ACTIVITIES,
                                                          PERSON_PRESENTATIONS,
                                                          PERSON_PRESS,
                                                          PERSON_AWARDS,
                                                          PERSON_MULTIMEDIA),
          array(HEAD.'(Total:)', PERSON_SUPERVISORS, PERSON_PUBLICATIONS, PERSON_ARTIFACTS, PERSON_ACTIVITIES, PERSON_PRESENTATIONS, PERSON_PRESS, PERSON_AWARDS, PERSON_MULTIMEDIA)
    );
    
$dashboardStructures[HQP_REPORT_STRUCTURE] =
    array(array(HEAD."(Projects)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)"),
          array(HEAD.'(Total:)', 
                STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY, REPORTING_CYCLE_START, REPORTING_CYCLE_END) => array(PERSON_PROJECTS,
                                                          STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                            STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                            STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                            STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                            STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                            STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                            STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)),
          array(HEAD.'(Total:)', 
                STRUCT(PERSON_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PERSON_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PERSON_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL))
    );
    
$dashboardStructures[PROJECT_PUBLIC_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Roles)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)"),
          array(HEAD.'(Total:)', PROJECT_ROLES, PROJECT_PUBLICATIONS, PROJECT_ARTIFACTS, PROJECT_ACTIVITIES, PROJECT_PRESENTATIONS, PROJECT_PRESS, PROJECT_AWARDS, PROJECT_MULTIMEDIA),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_ARRAY) => array(PROJECT_PEOPLE,
                                                         PROJECT_ROLES,
                                                         PROJECT_PUBLICATIONS, 
                                                         PROJECT_ARTIFACTS, 
                                                         PROJECT_ACTIVITIES,
                                                         PROJECT_PRESENTATIONS,
                                                         PROJECT_PRESS,
                                                         PROJECT_AWARDS,
                                                         PROJECT_MULTIMEDIA),
          array(HEAD.'(Total:)', PROJECT_ROLES, PROJECT_PUBLICATIONS, PROJECT_ARTIFACTS, PROJECT_ACTIVITIES, PROJECT_PRESENTATIONS, PROJECT_PRESS, PROJECT_AWARDS, PROJECT_MULTIMEDIA)
    );
    
$dashboardStructures[PROJECT_REPORT_PRODUCTIVITY_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Publications, PB: Published)", HEAD."(Artifacts, PR: Peer Reviewed)", HEAD."(Activities)", HEAD."(Presentations)", HEAD."(Press)", HEAD."(Awards)", HEAD."(Multimedia)", HEAD."(Partners)",  HEAD."(Contributions)"),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array(PROJECT_PEOPLE_ROLES,
                                                         STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY) => array(PROJECT_PEOPLE_ROLES,
                                                         STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                                                        STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                                                        STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL)),
          array(HEAD.'(Total:)', 
                STRUCT(PROJECT_PUBLICATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_ARTIFACTS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_ACTIVITIES, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_PRESENTATIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_PRESS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL), 
                STRUCT(PROJECT_AWARDS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_MULTIMEDIA, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_PARTNERS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL),
                STRUCT(PROJECT_CONTRIBUTIONS, REPORTING_CYCLE_START, REPORTING_CYCLE_END_ACTUAL))
    );
    
$dashboardStructures[PROJECT_REPORT_TIME_STRUCTURE] = 
    array(array(HEAD."(People)", HEAD."(Roles, PNI: Principle Network Investigator, CNI: Collaborating Network Investigator, PL: Project Leader, COPL: Co Project Leader, PM: Project Manager)", HEAD."(University &amp; Department)", HEAD."(Hours/Week)", HEAD."(Allocated:<br />".(REPORTING_YEAR)." - ".(REPORTING_YEAR+1).")", HEAD."(Requested:<br />".(REPORTING_YEAR+1)." - ".(REPORTING_YEAR+2).")"),
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
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY) => array(PROJECT_PEOPLE,
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
                STRUCT(PROJECT_BUDGET, REPORTING_CYCLE_START, REPORTING_CYCLE_END), )
    );
?>
