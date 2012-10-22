<?php
    require_once('commandLine.inc');
    $pnis = Person::getAllPeople(PNI);
    $cnis = Person::getAllPeople(CNI);
    $sql = "SELECT * FROM grand_partners";
    $partners = array();
    $partnersData = execSQLStatement($sql);
    foreach($partnersData as $row){
        $partners[$row['organization']] = $row['id'];
    }
    $i = 1;
    foreach (array($pnis, $cnis) as $groups) {
		foreach ($groups as $person) {
		    $rp = new ResearcherProductivity($person);

		    // Contributions.
		    $tmp = $rp->get_metric(RPST_CONTRIBUTIONS);
		    $longname = $person->getNameForForms();

		    // Loop over contributions to sort them according to
		    // source of contribution.
		    $conts = ArrayUtils::get_array($tmp, 'table2');
		    foreach ($conts as &$cont) {
				// Check whether this contribution is valid for inclusion.
				if (ArrayUtils::get_string($cont, 'Internal') == 'yes')
					continue;

				// Select from the proper array: cash or inkind.
				$src = ArrayUtils::get_string($cont, 'Source', 'invalid');
				$type = ArrayUtils::get_string($cont, 'Type', 'none');
				$cash = ResearcherProductivity::extract_number(ArrayUtils::get_field($cont, 'Cash', 0));
				$inki = ResearcherProductivity::extract_number(ArrayUtils::get_field($cont, 'Inkind', 0));

				// Skip empty contributions.
				if ($cash == 0 && $inki == 0)
					continue;

				// Sub-array for this type of contribution.
				$parr = ArrayUtils::get_array($contributions, $type);

				// Include an entry for this researcher/contribution combo.
				$oarr = ArrayUtils::get_array($parr, $src);
				$iarr = ArrayUtils::get_array($oarr, $longname);
				$iarr[] = array('amount' => $cash + $inki,
						'cash' => $cash,
						'inki' => $inki,
						'desc' => ArrayUtils::get_string($cont, 'Desc'));
				// Store.
				$oarr[$longname] = $iarr;
				$parr[$src] = $oarr;
				$projects = array();
				foreach($cont['Proj'] as $proj){
				    $project = Project::newFromName($proj);
				    if($project != null){
				        $projects[] = $project->getId();
				    }
				}
				$sources = array();
				if($src == "SSHRC" || 
				   $src == "SSHRC standard research grant" || 
				   $src == "Digital Natives SSHRC" ||
				   $src == "SFU Small SSHRC grant" ||
				   $src == "SSHRC Knowledge Sythesis Grant" ||
				   $src == "SSHRC, Center for the Study of the United States"){
				    $sources[] = array("name" => $src, "id" => $partners["Social Sciences and Humanities Research Council of Canada"]);
				}
				else if($src == "NSERC" || $src == "NSERC Discovery Grant Individual"){
				    $sources[] = array("name" => $src, "id" => $partners["Natural Sciences and Engineering Research Council"]);
				}
				else if($src == "IBM Center for Advanced Studies"){
				    $sources[] = array("name" => $src, "id" => $partners["IBM Centre for Advanced Studies"]);
				}
				else if($src == "Concordia UNiversity ENCS Faculty Research Grants"){
				    $sources[] = array("name" => $src, "id" => $partners["Concordia University"]);
				}
				else if($src == "NYU Faculty of Law; Government of Canada"){
				    $sources[] = array("name" => $src, "id" => $partners["New York University"]);
				}
				else if($src == "Teaching and Leanrign Enhancement Fund - University of Alberta."){
				    $sources[] = array("name" => $src, "id" => $partners["University of Alberta"]);
				}
				else if($src == "FQRSC"){
				    $sources[] = array("name" => $src, "id" => $partners["Fonds québécois de la recherche sur la société et la culture"]);
				}
				else if($src == "NSERC/iCORE/IBM"){
				    $sources[] = array("name" => "NSERC", "id" => $partners["Natural Sciences and Engineering Research Council"]);
				    $sources[] = array("name" => "iCORE", "id" => $partners["iCore"]);
				    $sources[] = array("name" => "IBM", "id" => $partners["IBM"]);
				}
				else if($src == "MapleSoft"){
				    $sources[] = array("name" => $src, "id" => $partners["Maplesoft"]);
				}
				else if($src == "Concordia" || $src == "Fondation ntretiens Jacques Cartier and Concordia"){
				    $sources[] = array("name" => $src, "id" => $partners["Concordia University"]);
				}
				else if($src == "BC Hydro PowerSmart"){
				    $sources[] = array("name" => $src, "id" => $partners["British Columbia Hydro"]);
				}
				else if($src == "Nokia Canada"){
				    $sources[] = array("name" => $src, "id" => $partners["Nokia"]);
				}
				else if($src == "PARC, Inc.:  Eric Saund"){
				    $sources[] = array("name" => $src, "id" => $partners["Xerox PARC"]);
				}
				else if($src == "University of Victoria Centre on Aging"){
				    $sources[] = array("name" => $src, "id" => $partners["University of Victoria"]);
				}
				else if($src == "National Science Foundation" ||
				        $src == "National Science Foundation (U.S.)"){
				    $sources[] = array("name" => $src, "id" => $partners["The National Science Foundation"]);
				}
				else if($src == "Adobe systems"){
				    $sources[] = array("name" => $src, "id" => $partners["Adobe Systems Canada"]);
				}
				else if($src == "BCIC NRAS "){
				    $sources[] = array("name" => "BCIC", "id" => $partners["British Columbia Innovation Council"]);
				}
				else if($src == "NSERC Engage program and STMicroelectronics"){
				    $sources[] = array("name" => "NSERC Engage program", "id" => $partners["Natural Sciences and Engineering Research Council"]);
				    $sources[] = array("name" => "STMicroelectronics", "id" => $partners["STMicroelectronics (Canada) Inc."]);
				}
				else{
				    $sources[] = array("name" => $src, "id" => $partners["Other"]);
				}
				
                if(isset($partners[$src])){
                    $sources[] = array("name" => $src, "id" => $partners[$src]);
                }
                $sources = addslashes(serialize($sources));
                $sql = "INSERT INTO grand_contributions
                        (`id`,`name`,`users`,`projects`,`partner_id`,`type`,`cash`,`kind`,`description`,`year`)
                        VALUES ('$i','".str_replace("'", "&#39;", substr($iarr[0]['desc'], 0, 60))."...','".serialize(array($person->getID()))."','".serialize($projects)."','{$sources}','{$type}','{$cash}','{$inki}','".str_replace("'", "&#39;", $iarr[0]['desc'])."','2010')";
                execSQLStatement($sql, true);
                $i++;
			}
			echo "{$person->getName()}'s contributions Upgraded!\n";
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
