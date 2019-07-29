<?php
// The purpose of this file is to simply include the other datastructures
require_once(dirname(__FILE__)."/../Reporting/Addressing.php");

// Relations Constants
define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');
define("MENTORS", 'Mentors');

// Freeze Constants
define('FREEZE_DESCRIPTION', 'Description');
define('FREEZE_MILESTONES', 'Schedule/Milestones');
define('FREEZE_BUDGET', 'Budget');

// Autoloads
autoload_register('GrandObjects');
autoload_register('GrandObjects/API');
autoload_register('GrandObjects/API/Person');
autoload_register('GrandObjects/API/Role');
autoload_register('GrandObjects/API/Project');
autoload_register('GrandObjects/API/Collaboration');
autoload_register('GrandObjects/API/Contribution');
autoload_register('GrandObjects/API/Freeze');
autoload_register('GrandObjects/API/Product');
autoload_register('GrandObjects/API/University');
autoload_register('GrandObjects/API/Wiki');
autoload_register('GrandObjects/API/MessageBoard');
autoload_register('GrandObjects/API/PDF');
autoload_register('GrandObjects/API/MailingList');
autoload_register('GrandObjects/API/Diversity');
autoload_register('GrandObjects/API/Search');
autoload_register('GrandObjects/API/Journal');

global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', 'PersonAPI');
$apiRequest->addAction('Hidden','person/:id/projects', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/projects/:personProjectId', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/leaderships', 'PersonLeadershipAPI');
$apiRequest->addAction('Hidden','person/:id/leaderships/:personProjectId', 'PersonLeadershipAPI');
$apiRequest->addAction('Hidden','person/:id/themes', 'PersonThemesAPI');
$apiRequest->addAction('Hidden','person/:id/themes/:personThemeId', 'PersonThemesAPI');
$apiRequest->addAction('Hidden','person/:id/universities', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/universities/:personUniversityId', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/roles', 'PersonRolesAPI');
$apiRequest->addAction('Hidden','person/:id/subroles', 'PersonSubRolesAPI');
$apiRequest->addAction('Hidden','person/:id/relations', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/relations/inverse', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/relations/:relId', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/products', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/private', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/all', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/:productId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/contributions', 'PersonContributionsAPI');
$apiRequest->addAction('Hidden','person/:id/allocations', 'PersonAllocationsAPI');
$apiRequest->addAction('Hidden','personRoleString/:id', 'PersonRoleStringAPI');
$apiRequest->addAction('Hidden','people', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/managed', 'PeopleManagedAPI');
$apiRequest->addAction('Hidden','people/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university/:department/simple', 'PeopleAPI');

// Role
$apiRequest->addAction('Hidden','role', 'RoleAPI');
$apiRequest->addAction('Hidden','role/:id', 'RoleAPI');

// Project
$apiRequest->addAction('Hidden','theme', 'ThemeAPI');
$apiRequest->addAction('Hidden','theme/:id', 'ThemeAPI');
$apiRequest->addAction('Hidden','theme/:id/projects', 'ThemeProjectsAPI');
$apiRequest->addAction('Hidden','project', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id/members', 'ProjectMembersAPI');
$apiRequest->addAction('Hidden','project/:id/members/:role', 'ProjectMembersAPI');
$apiRequest->addAction('Hidden','project/:id/contributions', 'ProjectContributionsAPI');
$apiRequest->addAction('Hidden','project/:id/allocations', 'ProjectAllocationsAPI');
$apiRequest->addAction('Hidden','project/:id/products', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','project/:id/products/:productId', 'ProjectProductAPI');

// Freeze
$apiRequest->addAction('Hidden','freeze', 'FreezeAPI');
$apiRequest->addAction('Hidden','freeze/:id', 'FreezeAPI');

// Product
$apiRequest->addAction('Hidden','product', 'ProductAPI');
$apiRequest->addAction('Hidden','product/tags', 'ProductTagsAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand/:start/:count', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/bibtex', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/citation', 'ProductCitationAPI');
$apiRequest->addAction('Hidden','product/:id/authors', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors/:personId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects/:projectId', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', 'ProductDuplicatesAPI');

// Bibliography
$apiRequest->addAction('Hidden','bibliography', 'BibliographyAPI');
$apiRequest->addAction('Hidden','bibliography/:id', 'BibliographyAPI');
$apiRequest->addAction('Hidden','bibliography/person/:person_id', 'BibliographyAPI');

// Collaboration
$apiRequest->addAction('Hidden','collaboration', 'CollaborationAPI');
$apiRequest->addAction('Hidden','collaboration/:id', 'CollaborationAPI');
//$apiRequest->addAction('Hidden','bibliography/person/:person_id', 'BibliographyAPI');

// Contribution
$apiRequest->addAction('Hidden','contribution', 'ContributionAPI');
$apiRequest->addAction('Hidden','contribution/:id', 'ContributionAPI');
$apiRequest->addAction('Hidden','contribution/:id/:rev_id', 'ContributionAPI');

// University
$apiRequest->addAction('Hidden','university', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:id', 'UniversityAPI');
$apiRequest->addAction('Hidden','departments', 'DepartmentAPI');

// Wiki
$apiRequest->addAction('Hidden','wikipage/:id', 'WikiPageAPI');
$apiRequest->addAction('Hidden','wikipage/:namespace/:title', 'WikiPageAPI');

//Board
$apiRequest->addAction('Hidden','board/:id', 'BoardAPI');
$apiRequest->addAction('Hidden','boards', 'BoardsAPI');

//Thread
$apiRequest->addAction('Hidden','thread', 'ThreadAPI');
$apiRequest->addAction('Hidden','thread/:id', 'ThreadAPI');
$apiRequest->addAction('Hidden','threads/:board', 'ThreadsAPI');
$apiRequest->addAction('Hidden','threads/:board/:search', 'ThreadsAPI');

//Post
$apiRequest->addAction('Hidden','post', 'PostAPI');
$apiRequest->addAction('Hidden','post/:id', 'PostAPI');
$apiRequest->addAction('Hidden','posts', 'PostsAPI');

// PDF
$apiRequest->addAction('Hidden','pdf/:id', 'PDFAPI');

// MailingList
$apiRequest->addAction('Hidden','mailingList', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules', 'MailingListRuleAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules/:ruleId', 'MailingListRuleAPI');

// Diversity Survey
$apiRequest->addAction('Hidden','diversity', 'DiversityAPI');

// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', 'GlobalSearchAPI');

//Journals
$apiRequest->addAction('Hidden','journal', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/:id', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/search/:search', 'JournalAPI');

function createModels(){

    function addScript($file){
        global $wgserver, $wgScriptPath;
        echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/$file.js?".filemtime("extensions/GrandObjects/BackboneModels/$file.js")."'></script>\n";
    }

    addScript("RelationModel");
    addScript("RangeCollection");
    addScript("Thread");
    addScript("Board");
    addScript("Post");
    addScript("Link");
    addScript("Person");
    addScript("Role");
    addScript("SubRoles");
    addScript("Project");
    addScript("Product");
    addScript("Contribution");
    addScript("Diversity");
    addScript("Bibliography");
    addScript("Collaboration");
    addScript("University");
    addScript("Virtu");
    addScript("WikiPage");
    addScript("PDF");
    addScript("MailingList");
    addScript("Freeze");
    addScript("Journal");
    
    return true;
}
?>
