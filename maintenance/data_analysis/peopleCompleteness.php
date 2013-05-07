<?php

require_once('../commandLine.inc');


$pni_data = array(
	"inactive"=>0,
	//"moved_on"=>0,
	"active"=>array(
		'total'=>0,
		'email'=>0,
		'gender'=>0,
		'nationality'=>0,
		'university'=>0,
		'department'=>0,
		'position'=>0,

		'on_projects'=>0,
		'supervises'=>0,
		'works'=>0,
	)
);

$cni_data = array(
	"inactive"=>0,
	//"moved_on"=>0,
	"active"=>array(
		'total'=>0,
		'email'=>0,
		'gender'=>0,
		'nationality'=>0,
		'university'=>0,
		'department'=>0,
		'position'=>0,

		'on_projects'=>0,
		'supervises'=>0,
		'works'=>0,
	)
);

$hqp_data = array(
	"inactive"=>array(
		"total"=>0,
		"movedon_empty"=>0,
		"movedon_filled"=>0,
	),
	"active"=>array(
		'total'=>0,
		'email'=>0,
		'gender'=>0,
		'nationality'=>0,
		'university'=>0,
		'department'=>0,
		'position'=>0,

		'on_projects'=>0,
		'supervised'=>0,
	)
);



$all_people = Person::getAllPeople('all');

foreach($all_people as $person){

	if($person->isActive()){

		$gender = $person->getGender();
		$nationality = $person->getNationality();
		$email = $person->getEmail();
		$email = ($email == "support@forum.grand-nce.ca")? "" : $email;
		$profile_pub = $person->getProfile();
		$profile_pri = $person->getProfile(true);

		$uni = $person->getUniversity();
		$university = $uni['university'];
		$department = $uni['department'];
		$position = $uni['position'];

		//$university = $person->getUni();
		//$department = $person->getDepartment();
		//$position = $person->getPosition();
		$projects = $person->getProjects();	

		if($person->isPNI()){
			$pni_data['active']['total']++;
			if(!empty($projects)){ $pni_data['active']['on_projects']++; }

			if(!empty($gender)){ $pni_data['active']['gender']++; }
			if(!empty($email)){ $pni_data['active']['email']++; }
			if(!empty($nationality)){ $pni_data['active']['nationality']++; }
			if(!empty($university)){ $pni_data['active']['university']++; }
			if(!empty($department)){ $pni_data['active']['department']++; }
			if(!empty($position)){ $pni_data['active']['position']++; }

			$rel_sup = $person->getRelations('Supervises');
			if(!empty($rel_sup)){
				$pni_data['active']['supervises']++;
			}
			$rel_wor = $person->getRelations('Works With');
			if(!empty($rel_wor)){
				$pni_data['active']['works']++;
			}
			
		}
		else if($person->isCNI()){
			$cni_data['active']['total']++;
			if(!empty($projects)){ $cni_data['active']['on_projects']++; }

			if(!empty($gender)){ $cni_data['active']['gender']++; }
			if(!empty($email)){ $cni_data['active']['email']++; }
			if(!empty($nationality)){ $cni_data['active']['nationality']++; }
			if(!empty($university)){ $cni_data['active']['university']++; }
			if(!empty($department)){ $cni_data['active']['department']++; }
			if(!empty($position)){ $cni_data['active']['position']++; }

			$rel_sup = $person->getRelations('Supervises');
			if(!empty($rel_sup)){
				$cni_data['active']['supervises']++;
			}
			$rel_wor = $person->getRelations('Works With');
			if(!empty($rel_wor)){
				$cni_data['active']['works']++;
			}

		}
		else if($person->isHQP()){
			$hqp_data['active']['total']++;
			if(!empty($projects)){ $hqp_data['active']['on_projects']++; }

			if(!empty($gender)){ $hqp_data['active']['gender']++; }
			if(!empty($email)){ $hqp_data['active']['email']++; }
			if(!empty($nationality)){ $hqp_data['active']['nationality']++; }
			if(!empty($university)){ $hqp_data['active']['university']++; }
			if(!empty($department)){ $hqp_data['active']['department']++; }
			if(!empty($position)){ $hqp_data['active']['position']++; }

			$supervisors = $person->getSupervisors();
			if(!empty($supervisors)){ $hqp_data['active']['supervised']++; }
		}
	}

	//INACTIVE
	//If the last role is HQP get the moved-on data
	else{
		
		if($person->wasLastRole(PNI)){
			$pni_data['inactive']++;
		}
		
		else if($person->wasLastRole(CNI)){
			$cni_data['inactive']++;
		}

		else if($person->wasLastRole(HQP)){
			$hqp_data['inactive']['total']++;
			$movedon = $person->getMovedOn();
			if( @$movedon['studies'] || @$movedon['city'] || @$movedon['works'] || @$movedon['employer'] || @$movedon['country'] ){
				$hqp_data['inactive']['movedon_filled']++;
			}
			else{
				$hqp_data['inactive']['movedon_empty']++;
			}
		}
	}

}

echo "PNI:\n";
//print_r($pni_data);

foreach($pni_data as $type => $details){
	if($type == "inactive"){
		echo "Total Inactive: {$details} \n";
	}
	else{
		$total = $details['total'];
		echo "Total Active: {$total} \n";

		echo "  --with email: ". round(($details['email']/$total) *100, 1) ."% \n";
		echo "  --with gender: ". round(($details['gender']/$total) *100, 1) ."% \n";
		echo "  --with nationality: ". round(($details['nationality']/$total) *100, 1) ."% \n";
		echo "  --with university: ". round(($details['university']/$total) *100, 1) ."% \n";
		echo "  --with department: ". round(($details['department']/$total) *100, 1) ."% \n";
		echo "  --with position: ". round(($details['position']/$total) *100, 1) ."% \n";

		echo "  --on projects: ". round(($details['on_projects']/$total) *100, 1) ."% \n";
		echo "  --with 'Supervises' relations: ". round(($details['supervises']/$total) *100, 1) ."% \n";
		echo "  --with 'Works With' relations: ". round(($details['works']/$total) *100, 1) ."% \n";
	}
}

echo "\n\n";

echo "CNI:\n";
//print_r($cni_data);
foreach($cni_data as $type => $details){
	if($type == "inactive"){
		echo "Total Inactive: {$details} \n";
	}
	else{
		$total = $details['total'];
		echo "Total Active: {$total} \n";

		echo "  --with email: ". round(($details['email']/$total) *100, 1) ."% \n";
		echo "  --with gender: ". round(($details['gender']/$total) *100, 1) ."% \n";
		echo "  --with nationality: ". round(($details['nationality']/$total) *100, 1) ."% \n";
		echo "  --with university: ". round(($details['university']/$total) *100, 1) ."% \n";
		echo "  --with department: ". round(($details['department']/$total) *100, 1) ."% \n";
		echo "  --with position: ". round(($details['position']/$total) *100, 1) ."% \n";

		echo "  --on projects: ". round(($details['on_projects']/$total) *100, 1) ."% \n";
		echo "  --with 'Supervises' relations: ". round(($details['supervises']/$total) *100, 1) ."% \n";
		echo "  --with 'Works With' relations: ". round(($details['works']/$total) *100, 1) ."% \n";
	}
}

echo "\n\n";

echo "HQP:\n";
//print_r($hqp_data);
foreach($hqp_data as $type => $details){
	if($type == "inactive"){
		echo "Total Inactive: ".$details['total'] ."\n";
		echo "  --with (some) moved-on info: ". round(($details['movedon_filled']/$details['total']) *100, 1) ."% \n";
		//echo "  --with no moved-on info: ". round(($details['movedon_empty']/$details['total']) *100, 1) ."% \n";
	}
	else{
		$total = $details['total'];
		echo "Total Active: {$total} \n";

		echo "  --with email: ". round(($details['email']/$total) *100, 1) ."% \n";
		echo "  --with gender: ". round(($details['gender']/$total) *100, 1) ."% \n";
		echo "  --with nationality: ". round(($details['nationality']/$total) *100, 1) ."% \n";
		echo "  --with university: ". round(($details['university']/$total) *100, 1) ."% \n";
		echo "  --with department: ". round(($details['department']/$total) *100, 1) ."% \n";
		echo "  --with position: ". round(($details['position']/$total) *100, 1) ."% \n";

		echo "  --on projects: ". round(($details['on_projects']/$total) *100, 1) ."% \n";
		echo "  --being supervised: ". round(($details['supervised']/$total) *100, 1) ."% \n";
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
<<<<<<< HEAD
?>
=======
?>
>>>>>>> master
