<?php
require_once('commandLine.inc');

global $wgUser;
$wgUser = User::newFromName("Admin");

chdir(dirname(__FILE__));

$newMembers = array();
$oldMembers = @unserialize(file_get_contents("members.txt"));
if($oldMembers == null){
    $oldMembers = array();
}

$currentMembers = Person::getAllPeople('all');
foreach($currentMembers as $member){
    if(!$member->isCandidate()){
        if(!isset($oldMembers[$member->getId()])){
            echo "{$member->getName()}: {$member->getEmail()} ({$member->getRoleString()})\n";
        }
        $newMembers[$member->getId()] = $member->getId();
    }
}

file_put_contents("members.txt", serialize($newMembers));

?>
