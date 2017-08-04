<?php
class UploadCSVAPI extends API{

    var $csvData = array();
    var $csvPersonData = array();
    var $csvPubs = array();
    var $csvPresentations = array();
    var $csvGrants = array();
    var $csvCourses = array();
    var $csvAdditionalInfo = array();
    var $csvStudents = array();
    var $csvAwards = array();

    function processParams($params){
        //TODO
    }

    function setCsvData($data){
        $lines = str_replace("\r\n","__", $data);
        $regex = "/(.+?)__(,){25}__/";
	preg_match_all($regex, $lines, $array);
	$sectioned = $array[1];
	$sections = array();
     	foreach($sectioned as $section){
	    $p = explode("__", $section);
	    $sections[] = $p;
	}
	$Endarray = array();
    	foreach($sections as $section){
	    $array = array();
	    $header = "";
	    for($i=0;$i<count($section);$i++){
	        if($section[$i] == ""){
		    continue;
		}
		else{
		    $row = $this->deleteCsvTrailingCommas($section[$i]);
		}
		$array[] = $row;
	    }
	    $Endarray[] = $array;
	}
	$formattedArray = array();
	foreach($Endarray as $info){
	    $header = "";
	    $key = "";
	    $array = array();
	    for($i=0;$i<count($info);$i++){
	        if($i == 0){
		    $key = $info[$i];
		    continue;
		}
		else if($i == 1){
		    $header = str_getcsv($info[$i]);
		    continue;
		}
		else{
		    $row = array();
		     //having to change some keys to match ImportBibTex
		    if($key == 'publications'){
 		        $xrow = str_getcsv($info[$i]);
                        for($x=0;$x<count($xrow); $x++){
                            switch ($header[$x]){
				case 'refereed':
			            $row['peer_reviewed'] = $xrow[$x];
				     break;
				case 'editors':
				    $row['editor'] = $xrow[$x];
				    break;
				case 'type':
				    switch ($xrow[$x]){
					case 'Conference':
					    $xrow[$x] = 'conference';
					    break;
					case 'Journal':
					    $xrow[$x] = 'article';
					    break;
					case 'BookChapter':
					    $xrow[$x] = 'inbook';
					    break;
					case 'Other':
					    $xrow[$x] = 'misc';
					    break;	
				    }
				    $row['bibtex_type'] = trim($xrow[$x]);
				    break;
				case 'publication_date':
				    $dateArray = explode("-", $xrow[$x]);
                                    if(count($dateArray)>1){
                                        $row['year'] = $dateArray[0];
                                        $row['month'] = $dateArray[1];
                                        $row['day'] = $dateArray[2];
                                    }
                                    else{
                                        $row['year'] = "";
                                        $row['month'] = "";
                                        $row['day'] = "";
                                    }
				    break;
				case 'location':
				    $row['city'] = $xrow[$x];
			        default:
				    $row[$header[$x]] = $xrow[$x];
			    }
                        }
		    }
		    else if($key == 'footnotes'){
			$row = $info[$i];
		    }
		    else{
                        $xrow = str_getcsv($info[$i]);
		        for($x=0;$x<count($xrow); $x++){
			     $row[$header[$x]] = $xrow[$x];
			}
		    }
		}
		$array[] = $row;
	    }
	    $formattedArray[$key] = $array;
	}
	$addedPubs = array();
	$newPubs = array();
    	$publications = $formattedArray['publications'];
        for($i = 0; $i<count($publications); $i++){
	     $pub = $publications[$i];
	     if(in_array($pub['title'], $addedPubs)){
		continue;
	     }
	     $addedPubs[] = $pub['title'];
	     $pub['author'][] = $pub['author_name'];
	     for($n = $i+1; $n<count($publications); $n++){
		$pub2 = $publications[$n];
		if($pub['title'] == $pub2['title']){
		    $pub['author'][] = $pub2['author_name'];
		}
	     }
	     $pub['author'] = implode(" and ",$pub['author']);
	      //unsetting all the rows not needed for the ImportBibtex
	     unset($pub['author_name']);
	     unset($pub['ccid']);
	     unset($pub['department']);
	     unset($pub['author_type']);
	     unset($pub['location']);
	     $newPubs[] = $pub;
        }
	$formattedArray['publications'] = $newPubs;
	$data = $formattedArray;
	$this->csvPersonData = $data['faculty_staff_member'];
	$this->csvData = $data;
	$this->csvPubs = $data['publications'];
	$this->csvPresentations = $data['presentations'];
	$this->csvGrants = $data['grants'];
	$this->csvCourses = $data ['courses'];
        $this->csvStudents = $data['responsibilities'];
        $this->csvAdditionalInfo['leaves']= $data['leaves'];      
	$this->csvAdditionalInfo['reduced teaching reasons']= $data['reduced teaching reasons'];
        $this->csvAdditionalInfo['teaching_developments'] = $data['teaching_developments'];
        $this->csvAdditionalInfo['other_teachings'] = $data['other_teachings'];
        $this->csvAdditionalInfo['supplementary_professional_activities'] = $data['supplementary_professional_activities.csv'];
        $this->csvAdditionalInfo['community_outreach_committees'] = $data['community_outreach_committees'];
        $this->csvAdditionalInfo['departmental_committees'] = $data['departmental_committees'];
        $this->csvAdditionalInfo['faculty_committees'] = $data['faculty_committees'];
        $this->csvAdditionalInfo['other_committees'] = $data['other_committees'];
        $this->csvAdditionalInfo['scientific_committees'] = $data['scientific_committees'];
        $this->csvAdditionalInfo['university_committees'] = $data['university_committees'];
        $this->csvAdditionalInfo['additional_data'] = $data['additional_data'];
	$this->csvAwards = $data['awards'];
    }

    function deleteCsvTrailingCommas($data){
        return trim(preg_replace("/(.*?)((,|\s)*)$/m", "$1", $data));
    }

    function multiexplode ($delimiters,$string) {
        $ready = str_replace($delimiters, $delimiters[0], $string);
    	$launch = explode($delimiters[0], $ready);
    	return  $launch;
    }
    
    function addUserProfile($name, $profile){
        $_POST['user_name'] = $name;
    	$_POST['profile'] = $profile;
    	$_POST['type'] = 'public';
    	APIRequest::doAction('UserProfile', true);
    	$_POST['type'] = 'private';
    	APIRequest::doAction('UserProfile', true);
    }

    function addUserRole($userId, $role){
        $new_role = new Role(array());
        $new_role->user = $userId;
        $new_role->role = $role;
        $new_role->startDate = date("Y-m-d 00:00:00");
        $new_role->create();
    }

    function addUserUniversity($name, $university, $department, $title){
        $_POST['user'] = $name;
    	$_POST['user_name'] = $name;
    	$_POST['university'] = $university;
    	$_POST['department'] = $department;
    	$_POST['title'] = $title;
    	APIRequest::doAction('UserUniversity', true);
    }

         //creating new user and assuming department is same as supervisor ??
    function createUser($fullname, $role, $title, $department, $university){
	$nameArray = explode(".",$fullname);
	if(count($nameArray) < 2){
	    $nameArray = explode(" ", $fullname);
	}
	$firstname = trim($nameArray[0]);
	$lastname = trim($nameArray[1]);
	$username = str_replace(" ", "", str_replace("'", "", "$firstname.$lastname"));
	User::createNew($username, array('real_name' => "$firstname $lastname",
					 'password' => User::crypt(mt_rand()),
					 'email' => ""
					 ));
	         //resetting cache?
        Person::$cache = array();
        Person::$namesCache = array();
        Person::$idsCache = array();
        Person::$rolesCache = array();
	  //adding user info     
	$this->addUserUniversity($username, $university, $department, $title);
        $student = Person::newFromNameLike("$firstname $lastname");
	$this->addUserRole($student->getId(), $role);
	return $student;
    }

    function createAwardInfo($person, $awards){
	$success = array();
	foreach($awards as $award){
	    if($award['id'] == 'null'){
		continue;
	    }
	    $year = explode("-", $award['created_at']);
	    $year = $year[0];
            $sql = "SELECT * FROM grand_products 
		    WHERE category LIKE 'Awards'
		    AND type LIKE 'Misc: {$award['name']}'
		    AND title LIKE '{$person->getRealName()}'
		    AND date = '$year-01-01 00:00:00'";
	    $data = DBFunctions::execSQL($sql);
	    if(count($data)>0){
	        DBFunctions::begin();
                $status = DBFunctions::insert('grand_products',
                                     array('category' => 'Awards',
                                           'type' => "Misc: ".$title,
                                           'title' =>$student->getRealName(),
					   'authors'=>serialize(array($student->id)),
                                           'date' => "$year-01-01 00:00:00"),
                                     true);
                if($status){
                    DBFunctions::commit();
		    $success[] = $status;
                }
	    }
        }
	return $success;
    }
	//TODO: MIGHT WANT TO SHORTEN THIS BY CREATING A METHOD TO CHECK AND INSERT/UPDATE
    function createAdditionalInfo($person,$additionals){
	foreach($additionals as $key=>$additional){
	    switch($key){
	        case 'leaves':
		    if($additional[0]['id'] == 'null'){
			break;
		    }
		    $found = false;
		    $dateArray = explode("-", $additional[0]['created_at']);
		    $year = $dateArray[0];
		    $leaves_array = array();
		    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_INFORMATION',
                                                          'rp_item' => 'FEC_INFO_LEAVES',
							  'year' => $year));
		    if(count($data)>0){
                    	$leaves_array = unserialize($data[0]['data']);
			$found=true;
		    }
		    foreach($additional as $item){
			$leave = array('category'=>$item['category'],
				       'startdate'=>$item['start_date'],
				       'enddate'=>$item['end_date']);
			$leaves_array['leaves'][] = $leave;
 
		    }
		    if($found){
			$status = DBFunctions::update('grand_report_blobs',
						       array('data'=>serialize($leaves_array)),
						       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_LEAVES',
                                                             'year' => $year), true);
		    }
		    else{
			$status = DBFunctions::insert('grand_report_blobs',
						      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_LEAVES',
                                                             'year' => $year,
							     'proj_id' => 0,
							     'edited_by' => $person->getId(),
							     'rp_subitem' => 0,
							     'blob_type' => 1024,
							     'data' => serialize($leaves_array),
							     'md5' => md5(serialize($leaves_array))),true);
		    }
		    if ($status){
			DBFunctions::commit();
		    }
		    break;
                case 'reduced teaching reasons':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
	            $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_INFORMATION',
                                                          'rp_item' => 'FEC_INFO_REASONS',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['reason'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_REASONS',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_REASONS',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
							     'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'teaching_developments':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_INFORMATION',
                                                          'rp_item' => 'FEC_INFO_DEVELOPMENT',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_DEVELOPMENT',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_INFORMATION',
                                                             'rp_item' => 'FEC_INFO_DEVELOPMENT',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'other_teachings':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_COURSES',
                                                          'rp_item' => 'FEC_INFO_TEACHING',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_COURSES',
                                                             'rp_item' => 'FEC_INFO_TEACHING',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_COURSES',
                                                             'rp_item' => 'FEC_INFO_TEACHING',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'supplementary_professional_activities':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_ACTIVITIES',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_ACTIVITIES',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_ACTIVITIES',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'community_outreach_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_COMMUNITY',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_COMMUNITY',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_COMMUNITY',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'departmental_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_DEPARTMENTAL',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_DEPARTMENTAL',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_DEPARTMENTAL',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'faculty_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_FACULTY',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_FACULTY',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_FACULTY',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'other_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_OTHER',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_OTHER',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_OTHER',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'scientific_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $leaves_array = array();
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_SCIENTIFIC',
                                                          'year' => $year));
                    if(count($data)>0){
                        $leaves_array = unserialize($data[0]['data']);
                        $found=true;
                    }
                    foreach($additional as $item){
                        $leave = array('scope'=>$item['scientific_committee_scope'],
                                       'organization'=>$item['organization'],
                                       'description'=>$item['description']);
                        $leaves_array['serviceOutreachScientific'][] = $leave;

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>serialize($leaves_array)),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_SCIENTIFIC',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_SCIENTIFIC',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1024,
                                                             'data' => serialize($leaves_array),
                                                             'md5' => md5(serialize($leaves_array))),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'university_committees':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_UNIVERSITY',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_UNIVERSITY',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_UNIVERSITY',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
                case 'additional_data':
                    if($additional[0]['id'] == 'null'){
                        break;
                    }
                    $reasons = "";
                    $found = false;
                    $dateArray = explode("-", $additional[0]['created_at']);
                    $year = $dateArray[0];
                    $data = DBFunctions::select(array('grand_report_blobs'),
                                                    array('data'),
                                                    array('user_id' => $person->getId(),
                                                          'rp_type' => 'RP_FEC',
                                                          'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                          'rp_item' => 'FEC_INFO_ADDITIONAL',
                                                          'year' => $year));
                    if(count($data)>0){
                        $reasons = $data[0]['data'];
                        $found=true;
                    }
                    foreach($additional as $item){
                        $reasons = $reasons.$item['description'];

                    }
                    if($found){
                        $status = DBFunctions::update('grand_report_blobs',
                                                       array('data'=>$reasons),
                                                       array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_ADDITIONAL',
                                                             'year' => $year), true);
                    }
                    else{
                        $status = DBFunctions::insert('grand_report_blobs',
                                                      array('user_id' => $person->getId(),
                                                             'rp_type' => 'RP_FEC',
                                                             'rp_section' => 'FEC_ADDITIONAL_INFO',
                                                             'rp_item' => 'FEC_INFO_ADDITIONAL',
                                                             'year' => $year,
                                                             'proj_id' => 0,
                                                             'edited_by' => $person->getId(),
                                                             'rp_subitem' => 0,
                                                             'blob_type' => 1,
                                                             'data' => $reasons,
                                                             'md5'=>md5($reasons)),true);
                    }
                    if ($status){
                        DBFunctions::commit();
                    }
                    break;
		default:
		    break;
	    }
	}
	return "AdditionalAdded";
    }

    function createPresentationInfo($person, $presentations){
	$success = array();
	foreach($presentations as $presentation){
            $data = DBFunctions::select(array('grand_products'),
				        array('id'),
					array('category'=>'Presentation',
					      'title' => "{$person->getNameForForms()} {$presentation['organization']} {$presentation['date']}"));
	    if(count($data) >0){
            continue;
	    }
        $newPresentation = new Paper(array());
        $newPresentation->type = 'Invited Presentation';
        $newPresentation->category = 'Presentation';
        $newPresentation->description = $presentation['description'];
        $newPresentation->data = array('location'=>$presentation['country'],
		                               'refereed'=>$presentation['refereed'],
		                               'organization'=>$presentation['organization'],
		                               'duration'=>$presentation['duration']);
        $newPresentation->date = $presentation['date'];
        $newPresentation->title = "{$person->getNameForForms()} {$presentation['organization']} {$presentation['date']}";
        $newPresentation->authors = array($person);
        $status = $newPresentation->create();
        if($status){
        $success[] = $status;
        }	
	}
	return $success;
    }

    function createGrantInfo($person, $grants){
	$fundings = array();
    	foreach($grants as $grant){
   	    $contribution = new Contribution(array());
	    $contribution->name = $grant['program'];
	    $contribution->scope = $grant['grant_scope'];
	    $contribution->access_id = $person->getId();
	    $contribution->start_date = "{$grant['starts']} 00:00:00";
	    $contribution->end_date = "{$grant['ends']} 00:00:00";
	    $partner = new Partner(array());
	    $partner->organization = $grant['agency'];
	    $contribution->partners = array($partner);
	    $id = md5(serialize($partner));
	    $contribution->cash = array("$id"=>$grant['total_amount']);
	    $contribution->kind = array("$id"=>0);
	    $contribution->unknown = array("$id"=>0);
	    $contribution->type = array("$id"=>"cash");
	    $contribution->subtype = array("$id"=>"cash");
	    $piArray = array();
	    $pis = explode("&",$grant['pi']);
	    foreach($pis as $pi){
		$newPi = Person::newFromNameLike($pi);
		if($newPi->getId() != ""){
		    $piArray[] = $newPi->getId();
		} 
		else{
		    $piArray[] = $pi;
		}
	    }
	    $contribution->pi = $piArray;
	    $userArray = array();
	    $users = $this->multiexplode(array(",","+"), $grant['corecipients']);
	    foreach($users as $user){
		$newUser = Person::newFromNameLike($user);
		if($newUser->getId() != ""){
		    $userArray[] = $newUser->getId();
		}
		else{
		    $userArray[] = $user;
		}
	    }
	    $contribution->people = $userArray;
	    $status = $contribution->create();
	    if($status){
		$fundings[] = $status;
	    }
	}
	return $fundings;
    }

    function createStudentInfo($person, $students){
	$supervises = array();
	foreach($students as $student){
	    $newStudent = Person::newFromNameLike($student['name']);
	    $university = $person->getUniversity();
	    if($newStudent->getId() == ""){
		$newStudent = $this->createUser($student['name'], "Student", $student['responsibility'], $university['department'], $university['university']);		
	    }
	    if(strtolower($student['role']) == "supervisor" || strtolower($student['role']) == "co-supervisor"){
		$student['role'] = "Supervises";
	    }
	    $student['started'] = "{$student['started']} 00:00:00";
	    if($student['ended'] == "NULL"){
		$student['ended'] = "0000-00-00 00:00:00";
	    }
	    else{
		$student['ended'] = "{$student['ended']} 00:00:00";
	    }
	    $oldRelation = Relationship::newFromUser1User2TypeStartDate($person->getId(), $newStudent->getId(), $student['role'], $student['started']);
	    if($oldRelation->getId() == ""){
		$newRelation = new Relationship(array());
		$newRelation->user1 = $person->getId();
		$newRelation->user2 = $newStudent->getId();
		$newRelation->type = $student['role'];
		$newRelation->startDate = $student['started'];
		$newRelation->endDate = $student['ended']; 
		$status = $newRelation->create();
		if($status){
		    $supervises[] = $status;
		}
	    }
	    continue;
	}
	return $supervises;
    }

    function createPublicationsInfo($publications){
        $_POST['bibtex'] = "hi";
	$_POST['fec'] = $publications;
        $response = APIRequest::doAction('importBibTeX', true);
	return array('success'=>$response['created'], 'fail'=>$response['error']);
    }

    function calculateCapEnrl($enrollment, $percentage){
        return round($enrollment/($percentage/100));
    }

    function parseCsvDate($date){
   	switch ($date[0]){
            case 'fall':
                $date = "{$date[1]}-09-01 00:00:00";
                break;
            case 'winter':
                $date = "{$date[1]}-01-01 00:00:00";
                break;
            case 'spring':
                $date = "{$date[1]}-04-01 00:00:00";
                break;
            case 'summer':
                $date = "{$date[1]}-06-01 00:00:00";
                break;
        }
        return $date;
    }

    function getTermDays($date){
	$first_date = new DateTime("1900-01-01 00:00:00");
        $second_date = new DateTime($date);
        $difference = $first_date->diff($second_date)->format("%a");
	return $difference;
    }

    function createCourseInfo($person, $courses){
	$course_status = array();
	foreach($courses as $course){
	    $yearString = explode("-", $course['updated_at']);
	    $term = strtolower($course['term']);
            $year = ($term =='fall') ? $yearString[0]-1 : $yearString[0];
	    $dateString = "$term $year";
	    $dateArray = explode(" ", $dateString);
	    $date = $this->parseCsvDate($dateArray);
	    $startDate = $this->getTermDays($date);
	    $endDate = $startDate + 97;
	    $courseCheck = Course::newFromSubjectCatalogSectStartDateTerm($course['subject'], $course['number'],$course['section'], $startDate, $course['term_number']);
	    if($courseCheck->id != ""){
		$professors = $courseCheck->getProfessors();
		$professorCheck = false;
		foreach($professors as $professor){
		    if($professor->getId() == $person->getId()){
		    	$professorCheck = true;
		    }
		}
		if($professorCheck){
			continue;
		}
		$status = DBFunctions::insert('grand_user_courses',
                                    array('user_id' => $person->getId(),
                                          'course_id' => $courseCheck->id),
                                           true);
		if($status){
		    DBFunctions::commit();
		}
		$course_status[] = $status;
		continue;
	    }
        	//if not create courses
	    $newcourse = new Course(array());
	    $newcourse->subject = $course['subject'];
	    $newcourse->catalog = $course['number'];
	    $newcourse->component = $course['component'];
	    $newcourse->sect = $course['section'];
	    $newcourse->term = $course['term_number'];
	    $newcourse->term_string = $course['term'];
	    $newcourse->startDate = $startDate;
	    $newcourse->endDate = $endDate;
	    $newcourse->totEnrl = $course['enrollment'];
	    $newcourse->capEnrl = $this->calculateCapEnrl($newcourse->totEnrl,$course['percentage']);
	    $newcourse->create();
            $courseCheck = Course::newFromSubjectCatalogSectStartDateTerm($course['subject'], $course['number'],$course['section'], $startDate, $course['term_number']);
            $status = DBFunctions::insert('grand_user_courses',
                                    array('user_id' => $person->getId(),
                                          'course_id' => $courseCheck->id),
                                           true);
            if($status){
                DBFunctions::commit();
            }
	    $course_status[] =$status;

	}
	return $course_status;
    }

    function createFecInfo($person, $data){
	if($person->dateOfPhd == "0000-00-00 00:00:00" || $person->dateOfPhd == ""){ $person->dateOfPhd = $data['date_of_phd']." 00:00:00";}
        if($person->dateOfAppointment == "0000-00-00 00:00:00" || $person->dateOfAppointment == ""){ $person->dateOfAppointment = $data['date_of_appointment']." 00:00:00";}
        if($person->dateOfAssistant == "0000-00-00 00:00:00" || $person->dateOfAssistant == ""){ $person->dateOfAssistant = $data['date_assistant']." 00:00:00";}
        if($person->dateOfAssociate == "0000-00-00 00:00:00" || $person->dateOfAssociate == ""){ $person->dateOfAssociate = $data['date_associate']." 00:00:00";}
        if($person->dateOfProfessor == "0000-00-00 00:00:00" || $person->dateOfProfessor == ""){ $person->dateOfProfessor = $data['date_professor']." 00:00:00";}
        if($person->dateOfTenure == "0000-00-00 00:00:00" || $person->dateOfTenure == ""){ $person->dateOfTenure = $data['date_tenure']." 00:00:00";}
        if($person->dateOfRetirement == "0000-00-00 00:00:00" || $person->dateOfRetirement == ""){ $person->dateOfRetirement = $data['date_retirement']." 00:00:00";}
        if($person->dateOfLastDegree == "0000-00-00 00:00:00" || $person->dateOfLastDegree == ""){ $person->dateOfLastDegree = $data['date_last_degree']." 00:00:00";}
        if($person->lastDegree == ""){ $person->lastDegree = $data['last_degree'];}
        if($person->publicationHistoryRefereed == "" || $person->publicationHistoryRefereed == 0){ $person->publicationHistoryRefereed = $data['publication_history_refereed'];}
        if($person->publicationHistoryBooks == "" || $person->publicationHistoryBooks == 0){ $person->publicationHistoryBooks = $data['publication_history_books'];}
        if($person->publicationHistoryPatents == "" || $person->publicationHistoryPatents == 0){ $person->publicationHistoryPatents = $data['publication_history_patents'];}
        if($person->dateFso2 == ""){ $person->dateFso2 = $data['date_fso2'];}
        if($person->dateFso3 == ""){ $person->dateFso3 = $data['date_fso3'];}
        if($person->dateFso4 == ""){ $person->dateFso4 = $data['date_fso4'];}
        $status = $person->updateFecInfo();
	return $status;
    }

    function doAction($noEcho=false){
        global $wgMessage;
        $me = Person::newFromWgUser();
        if(isset($_POST['id']) && $me->isRoleAtLeast(MANAGER)){
            $person = Person::newFromId($_POST['id']);
        }
        else{
            $person = $me;
        }
        $csv = $_FILES['csv'];
	if($csv['type'] == "text/csv" && $csv['size'] > 0){
	    $person->getFecPersonalInfo();
            $file_contents = file_get_contents($csv['tmp_name']);
	    $hi = $this->setCsvData($file_contents);
	    $error = "";
	    $json = array('created' => array(),
			  'error' => array()); 
	    if(isset($_POST['info'])){
		$json['fec_info'] = $this->createFecInfo($person, $this->csvPersonData[0]);
	    }
	    if(isset($_POST['courses']) && $this->csvCourses[0]['id'] != 'null'){
		 $json['courses'] = $this->createCourseInfo($person, $this->csvCourses);
	    }
	    if(isset($_POST['funding']) && $this->csvGrants[0]['id'] != 'null'){
		$funding= $this->csvGrants;
		$json['funding'] = $this->createGrantInfo($person, $this->csvGrants);
		$json['fundingFail'] = count($funding) - count($json['funding']);
	    }
	    if(isset($_POST['supervises']) && $this->csvStudents[0]['id'] != 'null'){
		$json['supervises'] = $this->createStudentInfo($person, $this->csvStudents);
	    }
	    if(isset($_POST['publications']) && $this->csvPubs[0]['id'] != 'null'){
		$publications = $this->createPublicationsInfo($this->csvPubs);
		$json['created'] = $publications['success'];
		$json['error'] = $publications['fail'];
	    }
		//starts here ----------->
            if(isset($_POST['presentations']) && $this->csvPresentations[0]['id'] != 'null'){
                $json['presentations'] = $this->createPresentationInfo($person, $this->csvPresentations);
            }	
            if(isset($_POST['additionals']) && $this->csvAdditionalInfo[0]['id'] != 'null'){
                $json['additionals']  = $this->createAdditionalInfo($person,$this->csvAdditionalInfo);
            }
	    if(isset($_POST['awards']) && $this->csvAwards[0]['id'] != 'null'){
		$json['awards'] = $this->createAwardInfo($person, $this->csvAwards);
	    }
	    $obj = json_encode($json);

            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded($obj, "$error");
		    </script>
                </head>
            </html>
EOF;
            exit;
	}
	else{
	    echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                                            parent.ccvUploaded([], "The uploaded file was not in CSV format");
                    </script>
                </head>
            </html>
EOF;
            exit;
	}
    }
    
    function isLoginRequired(){
        return true;
    }
}
?>
