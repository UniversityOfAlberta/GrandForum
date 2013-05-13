<?php
require_once('../commandLine.inc');

$subsidy_emails = array(
	"krdillman@gmail.com",
	"msa93@sfu.ca",
	"max.birk@usask.ca",
	"skardan@cs.ubc.ca",
	"nehijain@cim.mcgill.ca",
	"oiscen@sfu.ca",
	"kschenk@ualberta.ca",
	"gail.banaszkiewicz@gmail.com",
	"james.gregson@gmail.com",
	"olivier.remillard@mail.mcgill.ca",
	"yichen.dang@usask.ca",
	"azadehf@sfu.ca",
	"annercares@gmail.com",
	"slucky@ualberta.ca",
	"yingd@sfu.ca",
	"leahxzhang@gmail.com",
	"chang.lin@utoronto.ca",
	"nlapierre@gmail.com",
	"nshireen@sfu.ca",
	"jguenthe@sfu.ca",
	"Maren1@ualberta.ca",
	"abudac@ualberta.ca",
	"kathrin.gerling@usask.ca",
	"hamilton@cs.queensu.ca",
	"hazari@ualberta.ca",
	"jwindsor@ualberta.ca",
	"robert.douglas.ferguson@mail.mcgill.ca",
	"dhsmith@mcmaster.ca",
	"nizam@cs.dal.ca",
	"guana@ualberta.ca",
	"sheldon.andrews@mail.mcgill.ca",
	"lloyd@cs.ubc.ca",
	"arden@ecuad.ca",
	"dquesnel@ecuad.ca",
	"th698731@dal.ca",
	"tomasz.niewiarowski@gmail.com",
	"noreenk@ece.ubc.ca",
	"aashkan@cs.uwaterloo.ca",
	"umdubo26@cc.umanitoba.ca",
	"mnegules@cs.ubc.ca",
	"cam14@sfu.ca",
	"pourya.shirazian@gmail.com",
	"fatso784@gmail.com",
	"vmoulder@sfu.ca",
	"michael.hackett@dal.ca",
	"sadeq@ece.ubc.ca",
	"teburt@ucalgary.ca",
	"herbert.grasberger@gmail.com",
	"rjp.wilson@gmail.com",
	"bortolas@cs.queensu.ca",
	"mike.sheinin@usask.ca",
	"ljfyfe@ucalgary.ca",
	"erajabza@ucalgary.ca",
	"yichent@ece.ubc.ca",
	"nhieda@cim.mcgill.ca",
	"mta45@sfu.ca",
	"dtoker@cs.ubc.ca",
	"hemacleod@shaw.ca",
	"jeffbl@cim.mcgill.ca",
	"brian.gleeson@gmail.com",
	"rxzhao@cs.ualberta.ca",
	"neesha@ualberta.ca",
	"Brittany.White@Dal.Ca",
	"rbajko@ryerson.ca",
	"samuelyperreault@gmail.com",
	"dhuang@uvic.ca",
	"rarmst2@uwo.ca",
	"savery@cs.queensu.ca",
	"gregor.mcewan@usask.ca",
	"shauser@sfu.ca",
	"amacaran@sfu.ca",
	"negar.mohaghegh@gmail.com",
	"andrewkennethho@gmail.com",
	"antonios@ece.ubc.ca",
	"karim@cse.yorku.ca",
	"tsahi.hayat@gmail.com",
	"nobarany@gmail.com",
	"kazemian@cs.toronto.edu",
	"mccay@dal.ca",
	"eshaffer@mail.ubc.ca",
	"cfortin@sfu.ca",
	"elizabeth.stobert@gmail.com",
	"leahxzhang@gmail.com",
	"gregor@ece.ubc.ca",
	"adrian.reetz@usask.ca",
	"andre.doucette@usask.ca",
	"mail@martinweigel.com",
	"roberto@robertocalderon.ca",
	"rtang44@gmail.com",
	"skardan@cs.ubc.ca",
	"david.flatla@usask.ca",
	"delshimy@gmail.com",
	"xiemeng86@gmail.com"
);

foreach($subsidy_emails as $email){
	$person = Person::newFromEmail($email);
	if(!is_null($person)){
		
		$user_id = $person->getId();

		$query = "INSERT INTO grand_travel_forms(user_id, year) VALUES({$user_id}, 2013)";
		execSQLStatement($query, true);
	}
}

function execSQLStatement($sql, $update=false){
	if($update == false){
		$dbr = wfGetDB(DB_SLAVE);
	}
	else {
		$dbr = wfGetDB(DB_MASTER);
		return $dbr->query($sql);
	}
	$result = $dbr->query($sql);
	$rows = null;
	if($update == false){
		$rows = array();
		while ($row = $dbr->fetchRow($result)) {
			$rows[] = $row;
		}
	}
	return $rows;
}
?>