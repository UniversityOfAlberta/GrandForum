<?php

require_once('commandLine.inc');

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

$dir = dirname(__FILE__);

$outdir = "{$dir}/outputAdmittedStudents";
$filenames = [];

@mkdir($outdir);
$peopleSoFar = 0;
$nPeople = count($people);
for($y=YEAR;$y<=YEAR;$y++){
    @mkdir("{$outdir}/{$y}");
    foreach($people as $person){
	    $gsms = $person->getGSMS($y);
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
			    } else if ($array['term'] == "Spring Term") {
				    $year = explode("/", $array['academic_year'])[1];
				    $term = "SP" . $year;
			    } else if ($array['term'] == "Summer Term") {
				    $year = explode("/", $array['academic_year'])[1];
				    $term = "SU" . $year;
			    } 
			
			    if ($array['gender'] == "M") {
				    $gender = "Male";
			    } else if ($array['gender'] == "F") {
				    $gender = "Female";
			    } else {
				    $gender = $array['gender'];
			    }
			    
			    $supervisors = $gsms->getAssignedSupervisors();
			    $sups = array();
			    if(isset($supervisors['q5'])){
			        foreach($supervisors['q5'] as $supervisor){
			            $sup = Person::newFromReversedName($supervisor);
			            $sups[] = str_replace("@ualberta.ca", "", $sup->getEmail());
			        }
			    }
			    
			    if(count($sups) == 0){
			        $supervisors = $gsms->getAgreeToSupervise();
			        foreach($supervisors as $sup){
			            $sups[] = str_replace("@ualberta.ca", "", $sup->getEmail());
			        }
			    }

                $array['student_id'] = str_pad($array['student_id'], 7, "0", STR_PAD_LEFT);

			    $output = array(
				    "sid " . $array['student_id'],
				    "term " . $term,
				    "surname " . str_replace("&#39;", "'", $person->getLastName()),
				    "firstname " . str_replace("&#39;", "'", $person->getFirstName()),
				    "middlename " . str_replace("&#39;", "'", $person->getMiddleName()),
				    "gender " . $gender,
				    "email " . $array['student_data']['email'],
				    "citizenship " . $array['additional']['country_of_citizenship_full'],
				    "immigration " . $array['additional']['immigration'],
				    "degree " . $array['degree'],
				    "specialization " . $array['area'],
				    "status " . $array['ftpt'],
				    "supervisor " . implode("; ", $sups)
			    );
			    if ($array['student_id'] != 0) {
				    $loc = "{$outdir}/{$y}/" . $array['student_id'];
				    array_push($filenames, $array['student_id']);
			    } else {
				    $f = "gsms" . $array['gsms_id'];
				    $loc = "{$outdir}/{$y}/" . $f;
				    array_push($filenames, $f);
			    }

			    $oldContents = @file_get_contents($loc);
			    $newContents = implode("\n", $output) . "\n";
			    
			    if($oldContents != $newContents){
			        file_put_contents($loc, $newContents);
		            $command = "ssh -T -i /home/srvadmin/srvadmin docsdb@csora-app.cs.ualberta.ca < {$loc}";
		            echo "{$command}\n";
		            system("{$command}");
			    }
		    }
	    }
	    //show_status(++$peopleSoFar, $nPeople);
    }
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
