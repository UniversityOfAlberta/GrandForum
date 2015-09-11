<?php
	/**used to transfer data from centralrepo to main database.**/

	require_once( "commandLine.inc" );
	$servername = "199.116.235.47";
	$username = "new_root";
	$password = "shoutTEARstreamTAIL";

	  //create connection
	$conn = new mysqli($servername, $username, $password);
	if($conn->connect_error){
		echo($conn->connect_error);
	}
	else{
		print_r("connected");
		  //deleting from tables so can update them
		DBFunctions::execSQL("TRUNCATE TABLE grand_contributions",true);
		DBFunctions::execSQL("TRUNCATE TABLE grand_contributions_partners",true);
		  //inserting data from nserc grants
		$sql = "select * from dev.nserc_grant,dev.author_has_nserc_grant,dev.author where
			 dev.author.id_number = dev.author_has_nserc_grant.author_id and
			 dev.nserc_grant.nserc_grant_id = dev.author_has_nserc_grant.nserc_grant_id";

		$result = $conn->query($sql);
		$count=1;
		if ($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$fullname = $row["full_name"];
				$email = $row["email"];
				$description = $row["nserc_program"];
				$name = $row["nserc_project_name"];
				$cash = $row["nserc_amount_awarded"];
				  //splitting fiscal year into start and end dates
				$date = explode("-",$row["nserc_fiscal_year"]);
				$startDate = $date[0];
				$endDate = $date[1];
				  //grabbing user info from database using email
				$person = Person::newFromEmail($email);
				$nserc = "NSERC";
				$type = "cash";
				  //getting rid of characters from varchar to make into int
				$cash= preg_replace('/[\$,]/', '', $cash); 
				if($person !=null && $email != ""){
					$id = $person->getId();
                                        if($id == 0){
                                                continue;
                                        }
					$idArray = array($id);
					$searchStatement = "SELECT * FROM grand_contributions WHERE id = ";
					$statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
						'$startDate-01-01 00:00:00', 
						'$endDate-01-01 00:00:00', $id)"; 
					DBFunctions::execSQL($statement,true);
					$statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`) VALUES
					 ($count, '$type', '$type', '$nserc',$cash)";
					DBFunctions::execSQL($statement,true);
				}
				else{
					  //if can't get user info using email use fullname instead
					$person = Person::newFromName($fullname);
					$id = $person->getId();
					if($id == 0){
						continue;
					}
					$idArray = array($id);
					$statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
                                                '$startDate-01-01 00:00:00', 
                                                '$endDate-01-01 00:00:00', $id)"; 
					DBFunctions::execSQL($statement,true);
					$statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`) VALUES
							 ($count,'$type','$type','$nserc',$cash)";
                                        DBFunctions::execSQL($statement,true);
		
				}
				$count++;	
			}
		}
		  //inserting for cihr grants
		$sql = "select * from dev.cihr_grant,dev.author_has_cihr_grant,dev.author where
                         dev.author.id_number = dev.author_has_cihr_grant.author_id and
                         dev.cihr_grant.cihr_grant_id = dev.author_has_cihr_grant.cihr_grant_id";

                $result = $conn->query($sql);
		if ($result->num_rows > 0){
                	while($row = $result->fetch_assoc()){
                                $fullname = $row["full_name"];
                                $email = $row["email"];
                                $description = $row["cihr_program"];
                                $name = $row["cihr_project_title"];
                                $cash = $row["cihr_amount_awarded"];
				$kind = $row["cihr_equipment_amount_awarded"];
				  //splitting fiscal year into startdate enddate
                                $startDate = $row["cihr_fiscal_year"];
				$endDate = $row["cihr_fiscal_year"];
                                $person = Person::newFromEmail($email);
                                $nserc = "CIHR";
				  //taking out characters from varchar to make into int
                                $cash= preg_replace('/[\$,]/', '', $cash);
				$kind = preg_replace('/[\$,]/', '', $kind);
				  //making sure that the kind/cash is not blank
				if($kind == ""){
					$kind = 0;
				}
				if($cash ==""){
					$cash = 0;
				}
				  //checking what type of grant was given
				if($cash > 0 && $kind >0){
					$type = "caki";
				}
				elseif($cash>0){
					$type = "cash";
				}
				elseif($kind>0){
					$type="kind";
				}
				else{
					$type="cash";
				}

                                if($person !=null && $email != ""){
                                        $id = $person->getId();
                                        if($id == 0){
						continue;
					}
					$idArray = array($id);
                                        $statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
                                                '$startDate-01-01 00:00:00', 
                                                '$endDate-01-01 00:00:00', $id)";
                                        DBFunctions::execSQL($statement,true);
                                        $statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`,`kind`) VALUES
                                         ($count, '$type', '$type', '$nserc',$cash,$kind)";
                                        DBFunctions::execSQL($statement,true);
                                }
                                else{
                                        $person = Person::newFromName($fullname);
					$id = $person->getId();
					if($id == 0){
						continue;
					}
                                        $idArray = array($id);
                                        $statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
                                                '$startDate-01-01 00:00:00', 
                                                '$endDate-01-01 00:00:00', $id)";
                                        DBFunctions::execSQL($statement,true);
                                        $statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`,`kind`) VALUES
                                                         ($count,'$type','$type','$nserc',$cash,$kind)";
                                        DBFunctions::execSQL($statement,true);

                                }
                                $count++;
                        }
                }
		  //inserting for crsh grants
                $sql = "select * from dev.crsh_grant,dev.author_has_crsh_grant,dev.author where
                         dev.author.id_number = dev.author_has_crsh_grant.author_id and
                         dev.crsh_grant.crsh_grant_id = dev.author_has_crsh_grant.crsh_grant_id";

                $result = $conn->query($sql);
                if ($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
				print_r($row);
                                $fullname = $row["full_name"];
                                $email = $row["email"];
                                $description = $row["crsh_program_code"];
                                $name = $row["crsh_project"];
                                $cash = $row["crsh_amount_awarded"];
                                  //splitting fiscal year into start and end dates
                                $date = explode("-",$row["crsh_fiscal_year"]);
                                $startDate = $date[0];
                                $endDate = $date[1];
                                $person = Person::newFromEmail($email);
				$nserc = "CRSH";
                                $type = "cash";
                                  //getting rid of characters from varchar to make into int
                                $cash= preg_replace('/[\$,]/', '', $cash);
                                if($person !=null && $email != ""){
                                        $id = $person->getId();
                                        if($id == 0){
                                                continue;
                                        }
					$idArray = array($id);
                                        $statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
                                                '$startDate-01-01 00:00:00', 
                                                '$endDate-01-01 00:00:00', $id)";
                                        DBFunctions::execSQL($statement,true);
                                        $statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`) VALUES
                                         ($count, '$type', '$type', '$nserc',$cash)";
                                        DBFunctions::execSQL($statement,true);
                                }
                                else{
                                        $person = Person::newFromName($fullname);
                                        $id = $person->getId();
                                        if($id == 0){
                                                continue;
                                        }
					$idArray = array($id);
                                        $statement = "INSERT INTO grand_contributions(`id`,`name`,
                                                `users`, `description`, `start_date`, `end_date`, `access_id`) VALUES
                                                ($count,'".str_replace("'","&#39;",$name)."','".serialize($idArray)."','".str_replace("'","&#39;",$description)."', 
                                                '$startDate-01-01 00:00:00', 
                                                '$endDate-01-01 00:00:00', $id)";
                                        DBFunctions::execSQL($statement,true);
                                        $statement = "INSERT INTO grand_contributions_partners(`contribution_id`,`type`,`subtype`,`partner`,`cash`) VALUES
                                                         ($count,'$type','$type','$nserc',$cash)";
                                        DBFunctions::execSQL($statement,true);

                                }
                                $count++;
                        }
                }

		$conn->close();
	}
?>
