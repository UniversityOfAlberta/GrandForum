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
define('PROJECT_HEAD', 1001);
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
define('PROJECT_CHAMPIONS_ARRAY', 1128);

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

$cellTypes[PROJECT_HEAD] = "ProjectHeadCell";
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
$arrayTypes[PROJECT_CHAMPIONS_ARRAY] = "ProjectChampionsArray";

//DashboardTable Structures
define('NI_PUBLIC_PROFILE_STRUCTURE', 1);
define('NI_PRIVATE_PROFILE_STRUCTURE', 2);
define('HQP_PUBLIC_PROFILE_STRUCTURE', 3);

define('PROJECT_PUBLIC_STRUCTURE', 10);

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

?>
