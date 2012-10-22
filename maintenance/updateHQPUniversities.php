<?php
    require_once('commandLine.inc');
    $people = Person::getAllPeople(HQP);
    $pg = "http://forum.grand-nce.ca/index.php/Special:Report";
    //$pg = "http://hypatia.cs.ualberta.ca/~dwt/grand_forum_test/index.php/Special:Report";
    foreach($people as $person){
		$sd = new SessionData($person->getId(), $pg, SD_REPORT);
		$dt = $sd->fetch();
        if(isset($dt['level'])){
            $level = $dt['level'];
        }
        else{
            $level = "";
        }
        $sql = "SELECT * FROM mw_user_university
                WHERE user_id = '{$person->getId()}'";
        $data = execSQLStatement($sql);
        if(count($data) == 0){
            $creators = $person->getCreators();
            if(count($creators) > 0){
                $creator = $creators[0];
                $university = $creator->getUniversity();
                $sql = "SELECT * FROM mw_universities
                        WHERE university_name = '".addslashes($university['university'])."'";
                $data = execSQLStatement($sql);
                switch($level){
                    case 'master':
                        $level = "Masters Student";
                        break;
                    case 'none':
                        $level = "None";
                        break;
                    case 'other':
                        $level = "None";
                        break;
                    case 'phd':
                        $level = "PHD Student";
                        break;
                    case 'postdoc':
                        $level = "PostDoc";
                        break;
                    case 'tech':
                        $level = "Technician";
                        break;
                    case 'ugrad':
                        $level = "Undergraduate";
                        break;
                }
                $uni = "";
                $dept = "";
                if(isset($data[0])){
                    $uni = $data[0]['university_id'];
                }
            }
            else{
                $university = array();
                $university['department'] = "";
                $level = "";
                $uni = "";
            }
            $sql = "INSERT INTO mw_user_university (`user_id`, `university_id`, `department`, `position`)
                    VALUES ('{$person->getId()}','$uni','".str_replace("&amp;", "&", $university['department'])."','$level')";
            execSQLStatement($sql, true);
            echo "{$person->getName()} University Updated\n";
        }
        if(isset($dt['citizenship'])){
            $sql = "UPDATE mw_user 
                    SET user_nationality = '".ucwords($dt['citizenship'])."'
                    WHERE user_id = '{$person->getId()}'";
            execSQLStatement($sql, true);
            echo "{$person->getName()} Citizenship Updated\n";
        }
        if(isset($dt['gender'])){
            $sql = "UPDATE mw_user 
                    SET user_gender = '".ucwords($dt['gender'])."'
                    WHERE user_id = '{$person->getId()}'";
            execSQLStatement($sql, true);
            echo "{$person->getName()} Gender Updated\n";
        }
        foreach($person->getProjects() as $project){
            if(isset($dt["Iq1{$project->getName()}_mos"])){
                $val = $dt["Iq1{$project->getName()}_mos"];
                $val = str_replace(" ", "", $val);
                $val = str_replace("<", "", $val);
                $val = str_replace("months", "", $val);
                $val = str_replace("all", 12, $val);
                $val = str_replace("3-4", 3.5, $val);
                $val = str_replace("8-9", 8.5, $val);
                $val = str_replace("(sinceMay2010)", "", $val);
                $val = str_replace("May2010-December2010", 8, $val);
                $val = str_replace("Sept2010-present", 4, $val);
                $val = str_replace("SinceJune/2010", 7, $val);
                if($val == ""){
                    $val = "Unknown";
                }
                if(is_numeric($val)){
                    $monthT = 3600*24*30;
                    $time = time() - $monthT*6 - $monthT*$val;
                    $day = date("d", $time);
                    $month = date("m", $time);
                    $year = date("Y", $time);
                    $sql = "UPDATE grand_user_projects
                            SET start_date = '$year-$month-$day 00:00:00'
                            WHERE user = '{$person->getId()}'
                            AND project_id = '{$project->getId()}'";
                    execSQLStatement($sql, true);
                }
                $sql = "INSERT INTO grand_hqp_months (`user_id`,`project_id`,`months`)
                        VALUES ('{$person->getId()}','{$project->getId()}','$val')";
                execSQLStatement($sql, true);
                echo "{$person->getName()} Start Date Updated\n";
            }
        }
    }
    
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '495'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '494'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '530'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '210'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '461'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '478'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '519'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '518'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '323'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '320'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '321'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '555'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '316'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '533'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '241'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '502'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '521'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '184'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '600'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '377'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '188'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '559'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '580'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '552'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '531'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PostDoc'
            WHERE user_id = '532'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '538'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '501'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '539'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '171'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '170'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PostDoc'
            WHERE user_id = '557'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '451'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '471'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '504'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '209'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '243'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '299'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '579'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '248'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '329'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Assistant Professor'
            WHERE user_id = '582'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PostDoc'
            WHERE user_id = '550'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '300'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '333'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '334'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '335'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '247'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Associate Professor'
            WHERE user_id = '365'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '577'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '589'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '382'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '568'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '546'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '393'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '590'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '591'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '327'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '331'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '437'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '303'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '379'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '427'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '522'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '524'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '465'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Associate Professor'
            WHERE user_id = '558'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '459'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '245'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '246'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '585'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '491'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '284'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '211'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '597'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '556'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '581'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PostDoc'
            WHERE user_id = '481'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '500'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '548'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '594'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '484'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '429'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '499'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '167'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '554'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '578'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '399'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '496'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '599'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '493'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '244'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '166'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '180'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '506'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '575'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Associate Professor'
            WHERE user_id = '165'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '523'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '466'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '503'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '570'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Undergraduate'
            WHERE user_id = '520'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '549'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters'
            WHERE user_id = '583'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '537'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '534'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '395'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '330'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '567'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'PHD Student'
            WHERE user_id = '574'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Masters Student'
            WHERE user_id = '535'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '291'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '289'";
    execSQLStatement($sql, true);
    $sql = "UPDATE `mw_user_university`
            SET position = 'Industry Associate'
            WHERE user_id = '238'";
    execSQLStatement($sql, true);
    
    $sql = "UPDATE `mw_user_university`
            SET position = 'Unknown'
            WHERE position = ''";
    execSQLStatement($sql, true);
    
    function execSQLStatement($sql, $update=false) {
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
