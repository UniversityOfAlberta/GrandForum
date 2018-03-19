<?php

require_once( 'commandLine.inc' );

/* Example:
sid 1549275
term F17
surname Golestan-Irani
firstname Shadan
middlename
gender Male
email shgolestan@ut.ac.ir
citizenship Iran
immigration INT
degree PhD
specialization
status FT
*/

$wgUser = User::newFromId(1); // Admin user
$people = Person::getAllPeople(CI);

$outdir = "outputAdmittedStudents";
$filenames = [];

@mkdir($outdir);
$peopleSoFar = 0;
$nPeople = count($people);
foreach($people as $person) {
	$gsms = $person->getGSMS();
	if ($gsms->id != null) {
		$sop = $gsms->getSOP();
		// Only export Admitted students
		if ($sop->getFinalAdmit() == "Admit") {
			$array = $gsms->toArray();
			if ($array['term'] == "Fall Term") {
				$year = explode("/", $array['academic_year'])[0];
				$term = "F" . substr($year, 2); // '2018' becomes '18'
			} else if ($array['term'] == "Winter Term") {
				$year = explode("/", $array['academic_year'])[1];
				$term = "W" . $year;
			}
			
			if ($array['gender'] == "M") {
				$gender = "Male";
			} else if ($array['gender'] == "F") {
				$gender = "Female";
			} else {
				$gender = $array['gender'];
			}

			$output = array(
				"sid " . $array['student_id'],
				"term " . $term,
				"surname " . $person->getLastName(),
				"firstname " . $person->getFirstName(),
				"middlename " . $person->getMiddleName(),
				"gender " . $gender,
				"email " . $array['student_data']['email'],
				"citizenship " . $array['additional']['country_of_citizenship_full'],
				"immigration " . $array['additional']['immigration'],
				"degree " . $array['degree'],
				"specialization " . $array['area'],
				"status " . $array['ftpt']
			);
			if ($array['student_id'] != 0) {
				$loc = $outdir . "/" . $array['student_id'];
				array_push($filenames, $array['student_id']);
			} else {
				$f = "gsms" . $array['gsms_id'];
				$loc = $outdir . "/" . $f;
				array_push($filenames, $f);
			}
			
			file_put_contents($loc, implode("\n", $output) . "\n");
		}
	}
	show_status(++$peopleSoFar, $nPeople);
}

// Copy the files to the GradDB server
/*exec("scp -i graddb.pem" . $outdir . "/* docsdb@csora-app:/local/oracle3/cshome/docsdb/graddb/Data/Applicants/AppFiles/");
$loadCommand = "/local/oracle3/cshome/docsdb/graddb/Data/Applicants/load_applicant_file";
$commandToRun = "";
foreach($filenames as $f) {
	$commandToRun .= $loadCommand . " " . $f . "; ";
}
//var_dump($commandToRun);
if (count($filenames) != 0){
	exec("ssh -i graddb.pem docsdb@csora-app '" . $commandToRun . "'");
}
*/
