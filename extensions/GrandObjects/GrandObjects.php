<?php
// The purpose of this file is to simply include the other datastructures
require_once(dirname(__FILE__)."/../Reporting/Addressing.php");

// Relations Constants
define("SUPERVISES", 'Supervises');
define("CO_SUPERVISES", 'Co-Supervises');
define("SUPERVISES_BOTH", "Supervises Both");
define("SUPERVISORY_COMMITTEE", "Supervisory-Committee member");
define("EXAMINER", "Examining-Committee member");
define("COMMITTEE_CHAIR", "Examining-Committee chair");

// Autoloads
require_once("BackboneModel.php");
require_once("Person.php");
autoload_register('GrandObjects');
autoload_register('GrandObjects/API');
autoload_register('GrandObjects/API/Person');
autoload_register('GrandObjects/API/Role');
autoload_register('GrandObjects/API/Product');
autoload_register('GrandObjects/API/University');
autoload_register('GrandObjects/API/Search');
autoload_register('GrandObjects/API/Grant');
autoload_register('GrandObjects/API/Keyword');
autoload_register('GrandObjects/API/PDF');
autoload_register('GrandObjects/API/Journal');

global $apiRequest;
// Person
$apiRequest->addAction('Hidden','person/:id', 'PersonAPI');
$apiRequest->addAction('Hidden','person/:id/universities', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/universities/:personUniversityId', 'PersonUniversitiesAPI');
$apiRequest->addAction('Hidden','person/:id/roles', 'PersonRolesAPI');
$apiRequest->addAction('Hidden','person/:id/relations', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/relations/:relId', 'PersonRelationsAPI');
$apiRequest->addAction('Hidden','person/:id/products', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/bibtex', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/private', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/all', 'PersonProductAPI');
$apiRequest->addAction('Hidden','person/:id/products/:productId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','personRoleString/:id', 'PersonRoleStringAPI');
$apiRequest->addAction('Hidden','people/managed', 'PeopleManagedAPI');
$apiRequest->addAction('Hidden','people/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university/simple', 'PeopleAPI');
$apiRequest->addAction('Hidden','people/:role/:university/:department/simple', 'PeopleAPI');
// Role
$apiRequest->addAction('Hidden','role', 'RoleAPI');
$apiRequest->addAction('Hidden','role/:id', 'RoleAPI');

// Product
$apiRequest->addAction('Hidden','product', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:projectId/:category/:grand/:start/:count', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id', 'ProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors', 'PersonProductAPI');
$apiRequest->addAction('Hidden','product/:id/authors/:personId', 'PersonProductAPI');
$apiRequest->addAction('Hidden','productDuplicates/:category/:title/:id', 'ProductDuplicatesAPI');
$apiRequest->addAction('Hidden','productHistories/:id', 'ProductHistoriesAPI');
$apiRequest->addAction('Hidden','productHistories/person/:personId', 'ProductHistoriesAPI');
// University
$apiRequest->addAction('Hidden','university', 'UniversityAPI');
$apiRequest->addAction('Hidden','university/:id', 'UniversityAPI');
// PDF
$apiRequest->addAction('Hidden','pdf/:id', 'PDFAPI');
// Grants
$apiRequest->addAction('Hidden','grant', 'GrantAPI');
$apiRequest->addAction('Hidden','grant/:id', 'GrantAPI');
// Keywords
$apiRequest->addAction('Hidden','keyword', 'KeywordAPI');
$apiRequest->addAction('Hidden','keyword/keywords', 'KeywordAPI');
$apiRequest->addAction('Hidden','keyword/partners', 'KeywordAPI');
$apiRequest->addAction('Hidden','keyword/:id', 'KeywordAPI');
// NewSearch
$apiRequest->addAction('Hidden','globalSearch/:group/:search', 'GlobalSearchAPI');
//Journals
$apiRequest->addAction('Hidden','journal', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/:id', 'JournalAPI');
$apiRequest->addAction('Hidden','journal/search/:search', 'JournalAPI');

function createModels(){
    global $wgServer, $wgScriptPath, $wgOut;
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RelationModel.js?".filemtime("extensions/GrandObjects/BackboneModels/RelationModel.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/RangeCollection.js?".filemtime("extensions/GrandObjects/BackboneModels/RangeCollection.js")."'></script>\n";
    
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Link.js?".filemtime("extensions/GrandObjects/BackboneModels/Link.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js?".filemtime("extensions/GrandObjects/BackboneModels/Person.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Role.js?".filemtime("extensions/GrandObjects/BackboneModels/Role.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Product.js?".filemtime("extensions/GrandObjects/BackboneModels/Product.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Grant.js?".filemtime("extensions/GrandObjects/BackboneModels/Grant.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Keyword.js?".filemtime("extensions/GrandObjects/BackboneModels/Keyword.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/University.js?".filemtime("extensions/GrandObjects/BackboneModels/University.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/PDF.js?".filemtime("extensions/GrandObjects/BackboneModels/PDF.js")."'></script>\n";
    echo "<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Journal.js?".filemtime("extensions/GrandObjects/BackboneModels/Journal.js")."'></script>\n";
    return true;
}
?>
