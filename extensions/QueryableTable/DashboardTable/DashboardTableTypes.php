<?php
autoload_register('QueryableTable/DashboardTable/Arrays');
autoload_register('QueryableTable/DashboardTable/Cells');

//CellTypes
//// Person Types
define('PERSON_NAME', 100);
define('PERSON_ROLES', 101);
define('PERSON_HQP', 102);
define('PERSON_UNIVERSITY', 104);
define('PERSON_PROJECTS', 106);
define('PERSON_PRODUCTS', 118);
define('PERSON_CONTRIBUTIONS', 112);
define('PERSON_SUPERVISORS', 113);
define('PERSON_MULTIMEDIA', 116);
//// Person Array Types
define('PERSON_PROJECTS_ARRAY', 125);

//// Project Types
define('PROJECT_HEAD', 1001);
define('PROJECT_PEOPLE', 1002);
define('PROJECT_ROLES', 1003);
define('PROJECT_UNIVERSITY', 1005);
define('PROJECT_PRODUCTS', 1009);
define('PROJECT_CONTRIBUTIONS', 1014);
define('PROJECT_MULTIMEDIA', 1016);
define('PROJECT_PEOPLE_ROLES', 1017);
define('PROJECT_HQP', 1018);
//// Project Array Types
define('PROJECT_PEOPLE_ARRAY', 1125);
define('PROJECT_LEADERS_ARRAY', 1126);
define('PROJECT_PEOPLE_NO_LEADERS_ARRAY', 1127);
define('PROJECT_NI_NO_LEADERS_ARRAY', 1128);
define('PROJECT_CHAMPIONS_ARRAY', 1131);
define('PROJECT_HQP_ARRAY', 1132);

$cellTypes[PERSON_NAME] = "PersonNameCell";
$cellTypes[PERSON_ROLES] = "PersonRolesCell";
$cellTypes[PERSON_HQP] = "PersonHQPCell";
$cellTypes[PERSON_SUPERVISORS] = "PersonSupervisorsCell";
$cellTypes[PERSON_UNIVERSITY] = "PersonUniversityCell";
$cellTypes[PERSON_PROJECTS] = "PersonProjectsCell";
$cellTypes[PERSON_PRODUCTS] = "PersonProductsCell";
$cellTypes[PERSON_MULTIMEDIA] = "PersonMultimediaCell";
$cellTypes[PERSON_CONTRIBUTIONS] = "PersonContributionsCell";
$arrayTypes[PERSON_PROJECTS_ARRAY] = "PersonProjectsArray";

$cellTypes[PROJECT_HEAD] = "ProjectHeadCell";
$cellTypes[PROJECT_PEOPLE] = "ProjectPeopleCell";
$cellTypes[PROJECT_HQP] = "ProjectHQPCell";
$cellTypes[PROJECT_ROLES] = "ProjectRolesCell";
$cellTypes[PROJECT_UNIVERSITY] = "ProjectUniversityCell";
$cellTypes[PROJECT_PRODUCTS] = "ProjectProductsCell";
$cellTypes[PROJECT_MULTIMEDIA] = "ProjectMultimediaCell";
$cellTypes[PROJECT_CONTRIBUTIONS] = "ProjectContributionsCell";
$cellTypes[PROJECT_PEOPLE_ROLES] = "ProjectPeopleRolesCell";
$arrayTypes[PROJECT_PEOPLE_ARRAY] = "ProjectPeopleArray";
$arrayTypes[PROJECT_HQP_ARRAY] = "ProjectHQPArray";
$arrayTypes[PROJECT_LEADERS_ARRAY] = "ProjectLeadersArray";
$arrayTypes[PROJECT_PEOPLE_NO_LEADERS_ARRAY] = "ProjectPeopleNoLeadersArray";
$arrayTypes[PROJECT_NI_NO_LEADERS_ARRAY] = "ProjectNINoLeadersArray";
$arrayTypes[PROJECT_CHAMPIONS_ARRAY] = "ProjectChampionsArray";

//DashboardTable Structures
define('NI_PUBLIC_PROFILE_STRUCTURE', 1);
define('NI_PRIVATE_PROFILE_STRUCTURE', 2);
define('HQP_PUBLIC_PROFILE_STRUCTURE', 3);
define('HQP_PRODUCTIVITY_STRUCTURE', 4);

define('PROJECT_PUBLIC_STRUCTURE', 10);
define('THEME_PUBLIC_STRUCTURE', 11);

function initDashboardGlobals(){
    global $head, $persRow, $projRow;
    if($head == null && $persRow == null && $projRow == null){
        $productStructure = Product::structure();
        $categories = @array_keys($productStructure['categories']);

        $head = array();
        $persRow = array();
        $projRow = array();
        foreach($categories as $category){
            $head[] = HEAD."(".str_replace("and", "and<br />", str_replace("-", "<br />", Inflect::pluralize($category))).")";
            $persRow[] = STRUCT(PERSON_PRODUCTS, $category);
            $projRow[] = STRUCT(PROJECT_PRODUCTS, $category);
        }
    }
}

$dashboardStructures = array();
$dashboardStructures[NI_PUBLIC_PROFILE_STRUCTURE] = function(){
    global $head, $persRow, $projRow;
    initDashboardGlobals();
    return
    array(array_merge(array(HEAD."(Projects)", HEAD."(HQP)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)', PERSON_HQP), $persRow, array(PERSON_MULTIMEDIA)),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array_merge(array(PERSON_PROJECTS,
                                                                       PERSON_HQP),
                                                                 $persRow, 
                                                                 array(PERSON_MULTIMEDIA)),
          array_merge(array(HEAD.'(Total:)', PERSON_HQP), $persRow, array(PERSON_MULTIMEDIA)),
    );
};
    
$dashboardStructures[NI_PRIVATE_PROFILE_STRUCTURE] = function(){
    global $head, $persRow, $projRow, $config;
    initDashboardGlobals();
    if($config->getValue('contributionsEnabled')){
        $otherHead = array(HEAD."(Multimedia)", HEAD."(Contributions)");
        $otherRow = array(PERSON_MULTIMEDIA, PERSON_CONTRIBUTIONS);
    }
    else{
        $otherHead = array(HEAD."(Multimedia)");
        $otherRow = array(PERSON_MULTIMEDIA);
    }
    return 
    array(array_merge(array(HEAD."(Projects)", HEAD."(HQP)"), $head, $otherHead),
          array_merge(array(HEAD.'(Total:)', PERSON_HQP), $persRow, $otherRow),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array_merge(array(PERSON_PROJECTS,
                                                                       PERSON_HQP),
                                                                 $persRow, 
                                                                 $otherRow),
          array_merge(array(HEAD.'(Total:)', PERSON_HQP), $persRow, $otherRow),
    );
};
    
$dashboardStructures[HQP_PUBLIC_PROFILE_STRUCTURE] = function(){
    global $head, $persRow, $projRow;
    initDashboardGlobals();
    return 
    array(array_merge(array(HEAD."(Projects)", HEAD."(Supervisors)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)', PERSON_SUPERVISORS), $persRow, array(PERSON_MULTIMEDIA)),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array_merge(array(PERSON_PROJECTS,
                                                                       PERSON_HQP),
                                                                 $persRow, 
                                                                 array(PERSON_MULTIMEDIA)),
          array_merge(array(HEAD.'(Total:)', PERSON_SUPERVISORS), $persRow, array(PERSON_MULTIMEDIA)),
    );
};
    
$dashboardStructures[HQP_PRODUCTIVITY_STRUCTURE] = function(){
    global $head, $persRow, $projRow;
    initDashboardGlobals();
    return 
    array(array_merge(array(HEAD."(Projects)"), $head, array(HEAD."(Multimedia)")),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(PERSON_MULTIMEDIA)),
          STRUCT(GROUP_BY, PERSON_PROJECTS_ARRAY) => array_merge(array(PERSON_PROJECTS,
                                                                       PERSON_HQP),
                                                                 $persRow, 
                                                                 array(PERSON_MULTIMEDIA)),
          array_merge(array(HEAD.'(Total:)'), $persRow, array(PERSON_MULTIMEDIA)),
    );
};
    
$dashboardStructures[PROJECT_PUBLIC_STRUCTURE] = function($start="0000-00-00", $end="2100-00-00"){
    global $head, $persRow, $projRow, $config;
    initDashboardGlobals();
    if($config->getValue('contributionsEnabled')){
        $otherHead = array(HEAD."(Multimedia)", HEAD."(Contributions)");
        $otherRow = array(STRUCT(PROJECT_MULTIMEDIA, $start, $end), 
                          STRUCT(PROJECT_CONTRIBUTIONS, $start, $end));
    }
    else{
        $otherHead = array(HEAD."(Multimedia)");
        $otherRow = array(STRUCT(PROJECT_MULTIMEDIA, $start, $end));
    }
    $specialProjRow = array();
    foreach($projRow as $key => $row){
        $specialProjRow[$key] = str_replace(")(", ",$start,$end)(", $row);
    }
    return 
    array(array_merge(array(HEAD."(People)", HEAD."(Roles)", HEAD."(".HQP.")"), $head, $otherHead),
          array_merge(array(HEAD.'(Total:)', PROJECT_ROLES, PROJECT_HQP), $specialProjRow, $otherRow),
          STRUCT(GROUP_BY, PROJECT_LEADERS_ARRAY) => array_merge(array(PROJECT_PEOPLE,
                                                                       PROJECT_ROLES,
                                                                       PROJECT_HQP),
                                                                 $specialProjRow,
                                                                 $otherRow),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_NO_LEADERS_ARRAY, $start, $end) => array_merge(array(PROJECT_PEOPLE,
                                                                                               PROJECT_ROLES,
                                                                                               PROJECT_HQP),
                                                                                         $specialProjRow,
                                                                                         $otherRow),
          array_merge(array(HEAD.'(Total:)', PROJECT_ROLES, PROJECT_HQP), $specialProjRow, $otherRow),
    );
};
    
$dashboardStructures[THEME_PUBLIC_STRUCTURE] = function(){
    global $head, $persRow, $projRow, $config;
    initDashboardGlobals();
    if($config->getValue('contributionsEnabled')){
        $otherHead = array(HEAD."(Multimedia)", HEAD."(Contributions)");
        $otherRow = array(PROJECT_MULTIMEDIA, PROJECT_CONTRIBUTIONS);
    }
    else{
        $otherHead = array(HEAD."(Multimedia)");
        $otherRow = array(PROJECT_MULTIMEDIA);
    }
    return 
    array(array_merge(array(HEAD."(People)", HEAD."(Roles)"), $head, $otherHead),
          array_merge(array(HEAD.'(Total:)', PROJECT_ROLES), $projRow, $otherRow),
          STRUCT(GROUP_BY, PROJECT_PEOPLE_ARRAY) => array_merge(array(PROJECT_PEOPLE,
                                                                      PROJECT_ROLES),
                                                                $projRow,
                                                                $otherRow),
          STRUCT(GROUP_BY, PROJECT_HQP_ARRAY) => array_merge(array(PROJECT_PEOPLE,
                                                                   PROJECT_ROLES),
                                                             $projRow,
                                                             $otherRow),
          array_merge(array(HEAD.'(Total:)', PROJECT_ROLES), $projRow, $otherRow),
    );
}

?>
