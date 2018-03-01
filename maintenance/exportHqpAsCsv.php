<?php

/*
 * For exporting all HQP into a csv
 */

require_once( 'commandLine.inc' );

$wgUser = User::newFromId(1);
$supervisors = Person::getAllPeopleDuring(CI, "0000-00-00", "2100-00-00");

$outdir = "exportedHqpCsvs";
@mkdir($outdir);
$peopleSoFar = 0;
$nPeople = count($supervisors);


foreach($supervisors as $supervisor) {
	$firstRow  = "Last Name, First Name, Middle Name, Employee Id, Email, ";
	$firstRow .= "Relationship, Start, End, Position\n";
	$output = $firstRow;

	$rels = $supervisor->getRelations('all', true);
	$relations = array();
	if(isset($rels[SUPERVISES]) && isset($rels[CO_SUPERVISES])) {
		$relations = array_merge($rels[SUPERVISES], $rels[CO_SUPERVISES]);
	} else if (isset($rels[SUPERVISES])) {
		$relations = $rels[SUPERVISES];
	} else if (isset($rels[CO_SUPERVISES])) {
		$relations = $rels[CO_SUPERVISES];
	}

	if (!isset($relations)) {
		continue;
	}
	foreach($relations as $relation) {
		$hqp = $relation->getUser2();
		$output .= $hqp->getLastName() . ", ";
		$output .= $hqp->getFirstName() . ", ";
		$output .= $hqp->getMiddleName() . ", ";
		$output .= $hqp->getEmployeeId() . ", ";
		$output .= $hqp->getEmail() . ", ";
		$output .= $relation->getType() . ", ";
		$output .= substr($relation->getStartDate(), 0, 10) . ", ";
		$end = $relation->getEndDate();
		$output .= substr($end, 0, 10) . ", ";
		if ($end == "0000-00-00 00:00:00") {
			$end = "2100-01-01 00:00:00";
		}
		$uni = $hqp->getUniversityDuring($relation->getStartDate(), $end);
		$output .= @$uni['position'] . "\n";
	}
	file_put_contents($outdir . "/" . $supervisor->getLastName() . ".csv", $output);

	show_status(++$peopleSoFar, $nPeople);
}