<?php
autoload_register('QueryableTable/DashboardTable/Arrays');
autoload_register('QueryableTable/DashboardTable/Cells');

//CellTypes
//// Person Types
define('PERSON_NAME', 100);
define('PERSON_ROLES', 101);
define('PERSON_HQP', 102);
define('PERSON_UNIVERSITY', 104);
define('PERSON_HOURS', 105);
define('PERSON_PRODUCTS', 118);
define('PERSON_CONTRIBUTIONS', 112);
define('PERSON_SUPERVISORS', 113);
define('PERSON_BUDGET', 114);
define('PERSON_ALLOCATED_BUDGET', 115);
define('PERSON_MULTIMEDIA', 116);
//// Person Array Types

$cellTypes[PERSON_NAME] = "PersonNameCell";
$cellTypes[PERSON_ROLES] = "PersonRolesCell";
$cellTypes[PERSON_HQP] = "PersonHQPCell";
$cellTypes[PERSON_SUPERVISORS] = "PersonSupervisorsCell";
$cellTypes[PERSON_BUDGET] = "PersonBudgetCell";
$cellTypes[PERSON_ALLOCATED_BUDGET] = "PersonAllocatedBudgetCell";
$cellTypes[PERSON_UNIVERSITY] = "PersonUniversityCell";
$cellTypes[PERSON_HOURS] = "PersonHoursCell";
$cellTypes[PERSON_PRODUCTS] = "PersonProductsCell";
$cellTypes[PERSON_MULTIMEDIA] = "PersonMultimediaCell";
$cellTypes[PERSON_CONTRIBUTIONS] = "PersonContributionsCell";

//DashboardTable Structures
define('NI_PUBLIC_PROFILE_STRUCTURE', 1);
define('NI_PRIVATE_PROFILE_STRUCTURE', 2);
define('HQP_PUBLIC_PROFILE_STRUCTURE', 3);
define('HQP_PRODUCTIVITY_STRUCTURE', 4);

function initDashboardGlobals(){
    global $head, $persRow, $projRow;
    if($head == null && $persRow == null && $projRow == null){
        $productStructure = Product::structure();
        $categories = @array_keys($productStructure['categories']);

        $head = array();
        $persRow = array();
        $projRow = array();
        foreach($categories as $category){
            $head[] = HEAD."(".Inflect::pluralize($category).")";
            $persRow[] = PERSON_PRODUCTS."(".$category.")";
        }
    }
}

$dashboardStructures = array();

?>
