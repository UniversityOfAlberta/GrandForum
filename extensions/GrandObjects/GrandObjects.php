<?php
// The purpose of this file is to simply include the other datastructures
require_once(dirname(__FILE__)."/../Reporting/Addressing.php");

// Relations Constants
define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');
define("CO_SUPERVISES", 'Co-Supervises');
define("MENTORS", 'Mentors');

// Autoloads
autoload_register('GrandObjects');
autoload_register('GrandObjects/API');
autoload_register('GrandObjects/API/Person');
autoload_register('GrandObjects/API/Role');
autoload_register('GrandObjects/API/Project');
autoload_register('GrandObjects/API/Product');
autoload_register('GrandObjects/API/University');
autoload_register('GrandObjects/API/Wiki');
autoload_register('GrandObjects/API/Search');
autoload_register('GrandObjects/API/Grant');
autoload_register('GrandObjects/API/PDF');
autoload_register('GrandObjects/API/MailingList');
autoload_register('GrandObjects/API/Journal');

global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', 'PersonAPI');
$apiRequest->addAction('Hidden','person/:id/projects', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/projects/:personProjectId', 'PersonProjectsAPI');
$apiRequest->addAction('Hidden','person/:id/universities', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/universities/:personUniversityId', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/roles', 'PersonRolesAPI');
$apiRequest->addAction('Hidden','person/:id/relations', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/relations/:relId', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/products', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/private', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/all', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/:productId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','personRoleString/:id', 'PersonRoleStringAPI');
$apiRequest->addAction('Hidden','people', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/managed', 'PeopleManagedAPI');
$apiRequest->addAction('Hidden','people/:role', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university', 'PeopleAPI');
// Role
$apiRequest->addAction('Hidden','role', 'RoleAPI');
$apiRequest->addAction('Hidden','role/:id', 'RoleAPI');
// Project
$apiRequest->addAction('Hidden','project', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id', 'ProjectAPI');
$apiRequest->addAction('Hidden','project/:id/members', 'ProjectMembersAPI');
$apiRequest->addAction('Hidden','project/:id/members/:role', 'ProjectMembersAPI');

$apiRequest->addAction('Hidden','project/:id/products', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','project/:id/products/:productId', 'ProjectProductAPI');
// Product
$apiRequest->addAction('Hidden','product', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand/:start/:count', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors/:personId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','product/:id/projects/:personId', 'ProjectProductAPI');
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', 'ProductDuplicatesAPI');
$apiRequest->addAction('Hidden','productHistories/:id', 'ProductHistoriesAPI');
$apiRequest->addAction('Hidden','productHistories/person/:personId', 'ProductHistoriesAPI');
// University
$apiRequest->addAction('Hidden','university', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:id', 'UniversityAPI');
// Wiki
$apiRequest->addAction('Hidden','wikipage/:id', 'WikiPageAPI');
$apiRequest->addAction('Hidden','wikipage/:namespace/:title', 'WikiPageAPI');
// PDF
$apiRequest->addAction('Hidden','pdf/:id', 'PDFAPI');
// Grants
$apiRequest->addAction('Hidden','grant', 'GrantAPI');
$apiRequest->addAction('Hidden','grant/:id', 'GrantAPI');
$apiRequest->addAction('Hidden','grantaward', 'GrantAwardAPI');
$apiRequest->addAction('Hidden','grantaward/:start/:count', 'GrantAwardAPI');
$apiRequest->addAction('Hidden','grantaward/:id', 'GrantAwardAPI');
$apiRequest->addAction('Hidden','grantpartner', 'GrantPartnerAPI');
$apiRequest->addAction('Hidden','grantpartner/:id', 'GrantPartnerAPI');
// MailingList
$apiRequest->addAction('Hidden','mailingList', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId', 'MailingListAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules', 'MailingListRuleAPI');
$apiRequest->addAction('Hidden','mailingList/:listId/rules/:ruleId', 'MailingListRuleAPI');
// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', 'GlobalSearchAPI');
//Journals
$apiRequest->addAction('Hidden','journal', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/:id', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/search/:search', 'JournalAPI');

function createModels(){
    global $wgServer, $wgScriptPath, $wgOut;
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RelationModel.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RangeCollection.js'></script>\n";
    
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Link.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Role.js'></script>\n";
	echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Project.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Product.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Grant.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/GrantAward.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/GrantPartner.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/University.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Virtu.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/WikiPage.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/PDF.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/MailingList.js'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Journal.js'></script>\n";

    return true;
}
?>
