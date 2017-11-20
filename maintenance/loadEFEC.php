<?php

    require_once('commandLine.inc');
    global $wgUser;
    
    function addUserUniversity($person, $university, $department, $title, $startDate="", $endDate="0000-00-00 00:00:00"){
        $_POST['university'] = $university;
        $_POST['department'] = $department;
        $_POST['startDate'] = $startDate;
        $_POST['endDate'] = $endDate;
        $_POST['researchArea'] = "";
        $_POST['position'] = $title;
        $api = new PersonUniversitiesAPI();
        $api->params['id'] = $person->getId();
        $api->doPOST();
    }
    
    function addUserRole($person, $role, $startDate="", $endDate="0000-00-00 00:00:00"){
        $r = new Role(array());
        $r->user = $person->getId();
        $r->role = $role;
        $r->startDate = $startDate;
        $r->endDate = $endDate;
        $r->create();
    }
    
    $titleMap = array(
        '' => "",
        'phd' => "Graduate Student - Doctoral",
        'bsc' => "Undergraduate",
        'msc' => "Graduate Student - Master's",
        'research/technical assistant' => "Research/Technical Assistant",
        'pdf' => "Post-Doctoral Fellow",
        'research associate' => "Research Associate",
        'honors thesis' => "Honors Thesis",
        'ma' => "Graduate Student - Master's",
        'high school' => "High School Student",
        'meng' => "Graduate Student - Master's"
    );
    
    $productCategoryMap = array(
        'Conference'      => array('Publication', 'Conference Paper'),
        'Journal'         => array('Publication', 'Journal Paper'),
        'BookChapter'     => array('Publication', 'Book Chapter'),
        'TechnicalReport' => array('Publication', 'Tech Report'),
        'Other'           => array('Publication', 'Misc'),
        'PosterArticle'   => array('Publication', 'Poster'),
        'PaperAbstract'   => array('Publication', 'Journal Abstract'),
        'Review'          => array('Publication', 'Book Review'),
        'Book'            => array('Publication', 'Book'),
        'Patent'          => array('Patent/Spin-Off', 'Patent')
    );
    
    $presentationMap = array(
        'colloquium/seminar presentation at another university or other institution' => 'Seminar',
        'panel member' => 'Panel Member',
        'conference/symposium/workshop keynote or plenary presentation (no parallel sessions during presentation)' => 'Keynote',
        'colloquium/seminar presentation at the university of alberta' => 'Seminar',
        'conference/symposium/workshop oral presentation' => 'Oral Presentation',
        'public (non-academic audience) off campus presentation' => 'Public Presentation',
        'conference/symposium/workshop poster presentation' => 'Poster Presentation',
        'tutorial presentation' => 'Tutorial',
        'presentation on the web' => 'Web Presentation',
        'public (non-academic audience) presentation' => 'Public Presentation',
        'colloquium/seminar presentation' => 'Seminar',
        'conference/symposium/workshop keynote or plenary presentation' => 'Keynote'
    );
    
    DBFunctions::execSQL("TRUNCATE grand_pdf_index", true);
    DBFunctions::execSQL("TRUNCATE grand_pdf_report", true);
    DBFunctions::execSQL("TRUNCATE grand_notifications", true);
    DBFunctions::execSQL("TRUNCATE grand_personal_fec_info", true);
    DBFunctions::execSQL("TRUNCATE grand_user_university", true);
    DBFunctions::execSQL("TRUNCATE grand_positions", true);
    DBFunctions::execSQL("TRUNCATE grand_relations", true);
    DBFunctions::execSQL("TRUNCATE grand_movedOn", true);
    DBFunctions::execSQL("TRUNCATE grand_ccv_employment_outcome", true);
    DBFunctions::execSQL("TRUNCATE grand_theses", true);
    DBFunctions::execSQL("TRUNCATE grand_managed_people", true);
    DBFunctions::execSQL("TRUNCATE grand_new_grants", true);
    DBFunctions::execSQL("TRUNCATE grand_new_grant_partner", true);
    
    DBFunctions::execSQL("DELETE FROM mw_user WHERE user_id > 2", true);
    DBFunctions::execSQL("DELETE FROM grand_roles WHERE user_id > 2", true);
    
    DBFunctions::execSQL("ALTER TABLE mw_user AUTO_INCREMENT = 3", true);
    DBFunctions::execSQL("ALTER TABLE grand_roles AUTO_INCREMENT = 3", true);
    
    $awards = DBFunctions::select(array('bddEfec2_development.awards' => 'a', 'bddEfec2_development.award_scopes' => 's'),
                                  array('a.name', 'a.category', 'a.faculty_staff_member_id', 'a.reporting_year' => 'year', 's.name' => 'scope'),
                                  array('a.award_scope_id' => EQ(COL('s.id'))));
                                  
    $otherAwards = DBFunctions::execSQL("SELECT * FROM bddEfec2_development.all_awards");
    
    $staff = DBFunctions::select(array('bddEfec2_development.faculty_staff_members'),
                                 array('*'));
                                 
    $students = DBFunctions::select(array('bddEfec2_development.students'),
                                    array('*'),
                                    array('ROLE_DESCR' => EQ('Supervisor'),
                                          WHERE_OR('ROLE_DESCR') => EQ('Co-Supervisor'),
                                          WHERE_OR('ROLE_DESCR') => EQ('')));
                                          
    $patents = DBFunctions::select(array('bddEfec2_development.patents'),
                                   array('*'));
                                    
    $presentations = DBFunctions::select(array('bddEfec2_development.presentations'),
                                         array('*'));
                                    
    $spinoffs = DBFunctions::select(array('bddEfec2_development.spinoffs'),
                                    array('*'));
    
    $community_outreach_committees = DBFunctions::select(array('bddEfec2_development.community_outreach_committees'),
                                                         array('*'));
                                                         
    $departmental_committees = DBFunctions::select(array('bddEfec2_development.departmental_committees'),
                                                   array('*'));
                                                   
    $faculty_committees = DBFunctions::select(array('bddEfec2_development.faculty_committees'),
                                                   array('*'));
                                                   
    $scientific_committees = DBFunctions::select(array('bddEfec2_development.scientific_committees'),
                                                   array('*'));
                                                   
    $university_committees = DBFunctions::select(array('bddEfec2_development.university_committees'),
                                                   array('*'));
    
    $other_committees = DBFunctions::select(array('bddEfec2_development.other_committees'),
                                                   array('*'));
    
    $responsibilities = DBFunctions::select(array('bddEfec2_development.responsibilities'),
                                            array('*'));
    
    // Basic Grant Information
    $grants1 = DBFunctions::select(array('bddEfec2_development.grants1'),
                                   array('*'));
    
    // Co-Applicant Information
    $grants2 = DBFunctions::select(array('bddEfec2_development.grants2'),
                                   array('*'));
    
    // Sponsor Information (Mainly for MULTI)
    $grants3 = DBFunctions::select(array('bddEfec2_development.grants3'),
                                   array('*'));
    
    $newGrants2 = array();
    foreach($grants2 as $grant){
        $newGrants2[$grant['Project']][] = $grant;
    }
    $grants2 = $newGrants2;
    
    $newGrants3 = array();
    foreach($grants3 as $grant){
        $newGrants3[$grant['Project']][] = $grant;
    }
    $grants3 = $newGrants3;
                                            
    $publications = DBFunctions::select(array('bddEfec2_development.publications'),
                                    array('*'));
    $authorships = DBFunctions::select(array('bddEfec2_development.authorships'),
                                       array('*'),
                                       array(),
                                       array('position' => 'ASC'));
    $external_authors = DBFunctions::select(array('bddEfec2_development.external_authors'),
                                            array('*'));
    $responsibility_authors = DBFunctions::select(array('bddEfec2_development.responsibility_coauthors'),
                                                  array('*'));
    $histories = DBFunctions::select(array('bddEfec2_development.publication_histories'),
                                     array('*'));
    
    $wgUser = User::newFromId(1);
    Person::$idsCache = array();
    Person::$employeeIdsCache = array();
    Person::$cache = array();
    Person::$namesCache = array();
    
    // Index Authorships by publication_id
    $newAuthorships = array();
    foreach($authorships as $author){
        $newAuthorships[$author['publication_id']][] = $author;
    }
    $authorships = $newAuthorships;
    
    // Index Staff by id
    $newStaff = array();
    foreach($staff as $s){
        $newStaff[$s['id']] = $s;
    }
    $staff = $newStaff;
    
    // Index Externals by id
    $newExternals = array();
    foreach($external_authors as $s){
        $newExternals[$s['id']] = $s;
    }
    $external_authors = $newExternals;
    
    // Adding Faculty Staff
    $iterationsSoFar = 0;
    echo "\nImporting Faculty\n";
    $staffIdMap = array();
    foreach($staff as $row){
        $username = str_replace(" ", "", preg_replace("/\(.*\)/", "", 
            trim(str_replace(".", "", $row['first_name']), " -\t\n\r\0\x0B").".".
            trim(str_replace(".", "", $row['last_name']),  " -\t\n\r\0\x0B")
        ));
        $username = str_replace("'", "", $username);
        $username = preg_replace("/\".*\"/", "", $username);
        $fname = $row['first_name'];
        $lname = $row['last_name'];
        $email = $row['ccid']."@ualberta.ca";
        $ldap = "http://webapps.srv.ualberta.ca/search/?type=simple&uid=true&c={$row['ccid']}";
        $realName = "$fname $lname";

        // First create the user
        $user = User::createNew($username, array('real_name' => $realName, 
                                                 'password' => User::crypt(mt_rand()), 
                                                 'email' => $email));
        if($user == null){
            continue;
        }
        $person = new Person(array());
        $person->id = $user->getId();
        $person->name = $user->getName();
        $person->realname = $realName;
        Person::$namesCache[$person->getName()] = $person;
        Person::$idsCache[$person->getId()] = $person;
        Person::$employeeIdsCache[$row['uid']] = $person;
        Person::$cache[strtolower($person->getName())] = $person;
        Person::$cache[$person->getId()] = $person;
        Person::$cache['eId'.$row['uid']] = $person;

        $staffIdMap[$row['id']] = $person;

        // Update user name(s)
        DBFunctions::update('mw_user',
                            array('user_name'   => $username,
                                  'first_name'  => $fname,
                                  'last_name'   => $lname,
                                  'ldap_url'    => $ldap),
                            array('user_email'  => EQ($email)));
                            
        // Update Role info
        addUserRole($person, CI, $row['date_of_appointment'], $row['date_retirement']);
        
        // Update FEC Personal Info
        DBFunctions::insert('grand_personal_fec_info',
                            array('user_id' => $person->getId(),
                                  'date_of_phd' => $row['date_of_phd'],
                                  'date_of_appointment' => $row['date_of_appointment'],
                                  'date_assistant' => $row['date_assistant'],
                                  'date_associate' => $row['date_associate'],
                                  'date_professor' => $row['date_professor'],
                                  'date_tenure' => $row['date_tenure'],
                                  'date_retirement' => $row['date_retirement'],
                                  'date_last_degree' => $row['date_last_degree'],
                                  'last_degree' => $row['last_degree'],
                                  'publication_history_refereed' => $row['publication_history_refereed'],
                                  'publication_history_books' => $row['publication_history_books'],
                                  'publication_history_patents' => $row['publication_history_patents'],
                                  'date_fso2' => $row['date_fso2'],
                                  'date_fso3' => $row['date_fso3'],
                                  'date_fso4' => $row['date_fso4']));
                                  
        DBFunctions::update('mw_user',
                            array('employee_id' => $row['uid']),
                            array('user_id' => EQ($person->getId())));
        
        // Update University Info
        addUserUniversity($person, "University of Alberta", ucwords($row['department']), ucwords($row['rank']), $row['date_of_appointment'], $row['date_retirement']);
        $person->university = false;
        show_status(++$iterationsSoFar, count($staff));
    }
    
    // Adding Employment History
    echo "\nImporting Employment History from CCV\n";
    require_once("ccvEmploymentUpload.php");
    
    Person::$universityCache = array();
    
    // Adding HQP
    $respIdMap = array();
    $hqpRoles = array();
    $hqpRelations = array();
    $hqpUniversities = array();
    
    $iterationsSoFar = 0;
    echo "\nImporting HQP From Spreadsheet\n";
    foreach($students as $student){
        $person = Person::newFromEmployeeId($student['EMPLID']);
        if(($person == null || $person->getId() == 0)){
            // First create the user
            $usernames = explode(",", $student['NAME']);
            $firsts = explode(" ", $usernames[1]);
            $realName = trim($usernames[1])." ".trim($usernames[0]);
            $username = str_replace(" ", "", $firsts[0].".".$usernames[0]);
            $username = str_replace("'", "", $username);
            $username = preg_replace("/\".*\"/", "", $username);
            $email = ($student['CAMPUS_ID'] != "") ? $student['CAMPUS_ID']."@ualberta.ca" : "";
            $user = User::createNew($username, array('real_name' => "$realName", 
                                                     'password' => User::crypt(mt_rand()), 
                                                     'email' => $email));
            if($user == null){
                show_status(++$iterationsSoFar, count($students));
                continue;
            }
            DBFunctions::update('mw_user',
                                array('employee_id' => $student['EMPLID'],
                                      'first_name' => $usernames[1],
                                      'last_name' => $usernames[0]),
                                array('user_id' => EQ($user->getId())));
            
            $person = new Person(array());
            $person->id = $user->getId();
            $person->name = $user->getName();
            $person->realname = $realName;
            $person->firstName = $usernames[1];
            $person->lastName = $usernames[0];
            Person::$namesCache[$person->getName()] = $person;
            Person::$employeeIdsCache[$student['EMPLID']] = $person;
            Person::$idsCache[$person->getId()] = $person;
            Person::$cache[strtolower($person->getName())] = $person;
            Person::$cache[$person->getId()] = $person;
            Person::$cache['eId'.$student['EMPLID']] = $person;
        }
        
        if($person->getId() != 0){
            // Update Role info
            if(!isset($hqpRoles[$person->getId()])){
                $r = new Role(array());
                $r->user = $person->getId();
                $r->role = HQP;
                $r->startDate = $student['ADMISSION_START_DT'];
                $hqpRoles[$person->getId()] = $r;
            }
            
            $endDate = $student['COMPLETION_DT'];
            if(trim($endDate) == ""){
                $endDate = $student['END_DATE'];
            }
            if(strstr($endDate, "/") !== false){
                $endYear = substr($endDate, 6, 4);
                $endDay = substr($endDate, 0, 2);
                $endMonth = substr($endDate, 3, 2);
                $endDate = "$endYear-$endMonth-$endDay";
            }
            
            
            $supervisor = Person::newFromEmployeeId($student['SUPERVISOR_ID']);
            if($supervisor != null && $supervisor->getId() != 0){
                switch($student['ROLE_DESCR']){
                    default:
                    case "Supervisor":
                        $roleType = SUPERVISES;
                        break;
                    case "Co-Supervisor":
                        $roleType = CO_SUPERVISES;
                        break;
                }
                
                if(!isset($hqpRelations[$supervisor->getId()][$person->getId()][$roleType])){
                    $rel = new Relationship(array());
                    $rel->user1 = $supervisor->getId();
                    $rel->user2 = $person->getId();
                    $rel->startDate = $student['ADMISSION_START_DT'];
                    $rel->endDate = $endDate;
                    $rel->type = $roleType;
                    
                    $hqpRelations[$supervisor->getId()][$person->getId()][$roleType] = $rel;
                }
                $relation = $hqpRelations[$supervisor->getId()][$person->getId()][$roleType];
                $relation->startDate = min($relation->getStartDate(), $student['ADMISSION_START_DT']);
                $relation->endDate   = max($relation->getEndDate(),   $endDate);
            }
            
            if(!isset($hqpUniversities[$person->getId()][$student['PROG_TYPE']])){
                $uni = array();
                $uni['university'] = "University of Alberta";
                $uni['department'] = $student['UASA_ACAD_PLN1_D30'];
                $uni['startDate'] = $student['ADMISSION_START_DT'];
                $uni['title'] = "";
                $uni['endDate'] = $endDate;
                switch($student['PROG_TYPE']){
                    case "Masters Course":
                        $uni['title'] = "Graduate Student - Master's Course";
                        break;
                    case "Masters Thesis":
                        $uni['title'] = "Graduate Student - Master's Thesis";
                        break;
                    case "Doctoral Program":
                        $uni['title'] = "Graduate Student - Doctoral";
                        break;
                }
                $hqpUniversities[$person->getId()][$student['PROG_TYPE']] = $uni;
            }
            
            $university = $hqpUniversities[$person->getId()][$student['PROG_TYPE']];
            $university['startDate'] = min($university['startDate'], $student['ADMISSION_START_DT']);
            $university['endDate'] = max($university['endDate'], $endDate);
            
            $role = $hqpRoles[$person->getId()];
            $role->startDate = min($role->getStartDate(), $student['ADMISSION_START_DT']);
            $role->endDate   = max($role->getEndDate(),   $endDate);
            $hqpRoles[$person->getId()] = $role;
        }
        show_status(++$iterationsSoFar, count($students));
    }
    
    Person::$namesCache = array();
    Person::$idsCache = array();
    Person::$cache = array();
    
    $iterationsSoFar = 0;
    echo "\nImporting HQP From eFEC\n";
    foreach($responsibilities as $row){
        $username = preg_replace("/\(.*\)/", "", trim(str_replace(".", "", $row['name']), " -\t\n\r\0\x0B"));
        $username = explode(",", $username, 2);
        if(count($username) > 1){
            $username = trim("{$username[1]}.{$username[0]}");
        }
        else{
            $username = trim($username[0]);
        }
        
        if($row['ended'] == "" && $row['status'] == "withdrew"){
            $row['ended'] = substr($row['created_at'], 0, 4)."-06-30";
        }
        
        $realName = $username;
        $username = str_replace(" ", ".", $username);
        $username = str_replace("'", ".", $username);
        $username = preg_replace("/\".*\"/", "", $username);
        $sup = @$staffIdMap[$row['faculty_staff_member_id']];
        $person = null;
        $potentials = Person::newFromNameLike(str_replace(".", " ", $realName), true);
        foreach($potentials as $person){
            $found = false;
            if($sup != null && isset($hqpRelations[$sup->getId()][$person->getId()])){
                // Match found, has the same supervisor
               $found = true;
            }
            else {
                if($sup != null && isset($hqpUniversities[$person->getId()])){
                    // Do a second check looking for name matches for people having the same dept/position
                    foreach($hqpUniversities[$person->getId()] as $pos => $uni){
                        $otherStart = ($uni['startDate'] != "") ? $uni['startDate'] : "0000-00-00";
                        $otherEnd = ($uni['endDate'] != "" || $uni['endDate'] != "0000-00-00") ? $uni['endDate'] : "9999-99-99";
                        $thisStart = ($row['started'] != "") ? $row['started'] : "0000-00-00";
                        $thisEnd = ($row['ended'] != "" || $row['ended'] != "0000-00-00") ? $row['ended'] : "9999-99-99";

                        if($uni['title'] == $titleMap[$row['responsibility']] && $uni['department'] == $sup->getDepartment() &&
                           (($thisStart >= $otherStart && $thisStart <= $otherEnd) || 
                            ($thisEnd   <= $otherEnd   && $thisEnd   >= $otherStart) ||
                            ($thisStart <= $otherStart && $thisEnd   >= $otherEnd))){
                            $found = true;
                            break;
                        }
                    }
                }
                
                if(!$found && isset($hqpUniversities[$person->getId()])){
                    // Do a third check looking for name matches for hqp with no supervisor yet
                    $found = true;
                    foreach($hqpRelations as $supId => $hqpIds){
                        if(isset($hqpIds[$person->getId()])){
                            // Supervisor was found, so don't use them
                            $found = false;
                            break;
                        }
                    }
                }
            }
            if($found){
                break;
            }
        }
        
        if(!$found){
            // Match not found, only consider exact matches
            //$person = Person::newFromName($username);
            $person = null;
        }

        $email = "";
        if($person == null || $person->getId() == 0){
            // First create the user
            $extra = "";
            do {
                $user = User::createNew($username.$extra, array('real_name' => str_replace(".", " ", "$realName"), 
                                                                'password' => User::crypt(mt_rand()), 
                                                                'email' => $email));
                $extra++;
            } while ($user == null);
            
            $data = DBFunctions::select(array('mw_user'),
                                        array('*'),
                                        array('user_id' => EQ($user->getId())));
            Person::addRowToNamesCache($data[0]);
            $person = Person::newFromId($user->getId());
        }

        $respIdMap[$row['id']] = $person;
        if($person->getId() != 0){
            // Update Role info
            if(!isset($hqpRoles[$person->getId()])){
                $r = new Role(array());
                $r->user = $person->getId();
                $r->role = HQP;
                $hqpRoles[$person->getId()] = $r;
            }
            if($sup != null){
                switch($row['role']){
                    case "":
                    case "supervisor":
                        $roleType = SUPERVISES;
                        break;
                    case "co-supervisor":
                        $roleType = CO_SUPERVISES;
                        break;
                    default:
                        $roleType = ucwords($row['role']);
                        break;
                }
            
                if(!isset($hqpRelations[$sup->getId()][$person->getId()][$roleType])){
                    $rel = new Relationship(array());
                    $rel->user1 = $sup->getId();
                    $rel->user2 = $person->getId();
                    $rel->startDate = $row['started'];
                    $rel->endDate = $row['ended'];
                    $rel->type = $roleType;
                    
                    $hqpRelations[$sup->getId()][$person->getId()][$roleType] = $rel;
                }
                
                $relation = $hqpRelations[$sup->getId()][$person->getId()][$roleType];
                $relation->startDate = min($relation->getStartDate(), $row['started']);
                $relation->endDate   = max($relation->getEndDate(),   $row['ended']);
            }
            
            $role = $hqpRoles[$person->getId()];
            $role->startDate = min($role->getStartDate(), $row['started']);
            $role->endDate   = max($role->getEndDate(),   $row['ended']);
            $hqpRoles[$person->getId()] = $role;
        }
        
        if(!isset($hqpUniversities[$person->getId()][$row['responsibility']])){
            $uni = array();
            $uni['university'] = "University of Alberta";
            $uni['department'] = ($sup != null) ? $sup->getDepartment() : "";
            $uni['startDate'] = $row['started'];
            $uni['endDate'] = $row['ended'];
            $uni['title'] = $titleMap[$row['responsibility']];
            $hqpUniversities[$person->getId()][$row['responsibility']] = $uni;
        }
        
        $university = $hqpUniversities[$person->getId()][$row['responsibility']];
        $university['startDate'] = min($university['startDate'], $row['started']);
        $university['endDate'] = max($university['endDate'], $row['ended']);
        
        show_status(++$iterationsSoFar, count($responsibilities));
    }
    
    Person::$namesCache = array();
    Person::$idsCache = array();
    Person::$cache = array();
    
    $iterationsSoFar = 0;
    echo "\nCreating HQP Roles\n";
    foreach($hqpRoles as $id => $role){
        $role->create();
        show_status(++$iterationsSoFar, count($hqpRoles));
    }
    
    $iterationsSoFar = 0;
    echo "\nCreating HQP Relations\n";
    $nRelations = 0;
    foreach($hqpRelations as $supId => $sup){
        // First check to make sure that the person isn't listed as both Supervisor and Co-Supervisor
        foreach($sup as $hqpId => $hqp){
            if(isset($hqp[SUPERVISES]) && isset($hqp[CO_SUPERVISES])){
                unset($hqp[CO_SUPERVISES]);
                $sUser = Person::newFromId($supId);
                $hqpUser = Person::newFromId($hqpId);
                echo "DUPLICATE: {$sUser->getName()} -> {$hqpUser->getName()}\n";
            }
            $hqpRelations[$supId][$hqpId] = $hqp;
            foreach($hqp as $rel){
                $nRelations++;
            }
        }
    }
    foreach($hqpRelations as $sup){
        foreach($sup as $hqp){
            foreach($hqp as $rel){
                $rel->create();
                show_status(++$iterationsSoFar, $nRelations);
            }
        }
    }
    
    $iterationsSoFar = 0;
    echo "\nCreating HQP Universities\n";
    foreach($hqpUniversities as $id => $unis){
        foreach($unis as $uni){
            $person = Person::newFromId($id);
            addUserUniversity($person, $uni['university'], $uni['department'], $uni['title'], $uni['startDate'], $uni['endDate']);
        }
        show_status(++$iterationsSoFar, count($hqpUniversities));
    }
    
    // Import Grants
    $iterationsSoFar = 0;
    echo "\nImporting Grants\n";
    DBFunctions::execSQL("TRUNCATE table `grand_grants`", true);
    DBFunctions::execSQL("TRUNCATE table `grand_grant_contributions`", true);
    foreach($grants1 as $row){
        $person = Person::newFromEmployeeId($row['PI/ Student Employee ID']);
        if($person != null && $person->getId() != 0){
            $newGrant = new Grant(array());
            $newGrant->user_id = $person->getId();
            $newGrant->total = $row['Total Award (Budget)'];
            $newGrant->title = $row['Project Title'];
            $newGrant->description = $row['Scientific Title (Awd Long Description)'];
            $newGrant->start_date = $row['Proj Start Date'];
            $newGrant->end_date = $row['Proj End Date'];
            $newGrant->project_id = $row['Project'];
            $newGrant->role = str_replace("Principal Investigat", "Principal Investigator", $row['PI/ Student Role']);
            $newGrant->seq_no = $row['Awd Spons Prog Seq No.'];
            $newGrant->prog_description = $row['Awd Spons Program Description'];
            if($row['Award Sponsor ID'] == "MULTI" && isset($grants3[$row['Project']])){
                $sponsors = array();
                foreach($grants3[$row['Project']] as $sponsor){
                    $sponsors[$sponsor['Sponsor Description']] = $sponsor['Sponsor Description'];
                }
                $newGrant->sponsor = implode(", ", $sponsors);
            }
            else{
                $newGrant->sponsor = $row['Award Sponsor Name'];
            }
            if(isset($grants2[$row['Project']])){
                foreach($grants2[$row['Project']] as $row2){
                    if($row2['Proposal Team Member Role Description'] == "Co-PI"){
                        $member = Person::newFromEmployeeId($row2['Prop Team Member Emplid (if available)']);
                        if($member != null && $member->getId() != 0){
                            $newGrant->copi[] = $member->getId();
                        }
                    }
                }
            }
            $newGrant->create();
        }
        show_status(++$iterationsSoFar, count($grants1));
    }
    
    // Create Products
    $iterationsSoFar = 0;
    echo "\nImporting Publications\n";
    DBFunctions::execSQL("TRUNCATE table `grand_products`", true);
    DBFunctions::execSQL("TRUNCATE table `grand_product_authors`", true);
    foreach($publications as $publication){
        $product = new Product(array());
        $product->category = $productCategoryMap[$publication['type']][0];
        $product->type = $productCategoryMap[$publication['type']][1];
        $product->title = trim(str_replace("•", "", str_replace("￼", "", $publication['title'])));
        $product->date = $publication['publication_date'];
        $product->acceptance_date = $publication['acceptance_date'];
        if($product->date == ""){
            $product->date = $product->acceptance_date;
        }
        $product->status = "Published";
        $product->access = "Public";
        
        $product->authors = array();
        $product->projects = array();
        
        $pages = "";
        $pageRange = "";
        if(!is_numeric(str_replace(array("-", " "), "", $publication['pages'])) && strstr($publication['pages'], "-") === false){
            $pages = $publication['pages'];
        }
        else {
            $pageRange = $publication['pages'];
        }
        $data = array(
            'publisher' => $publication['publisher'],
            'ms_pages' => $pages,
            'pages' => $pageRange,
            'volume' => $publication['volume'],
            'issue' => $publication['issue'],
            'number' => $publication['issue'],
            'doi' => $publication['doi'],
            'url' => $publication['url'],
            'venue' => $publication['venue'],
            'event_title' => $publication['venue'],
            'book_title' => $publication['venue'],
            'journal_title' => $publication['venue'],
            'published_in' => $publication['venue'],
            'event_location' => $publication['location'],
            'location' => $publication['location'],
            'editors' => $publication['editors'],
            'peer_reviewed' => ucwords($publication['refereed']),
            'acceptance_ratio' => "{$publication['acceptance_ratio_numerator']}/{$publication['acceptance_ratio_denominator']}"
        );
        $product->data = $data;
        
        // Add Authors
        if(isset($authorships[$publication['id']])){
            foreach($authorships[$publication['id']] as $author){
                if($author['author_type'] == 'FacultyStaffMember'){
                    // Faculty Staff
                    if(isset($staffIdMap[$author['author_id']])){
                        $product->authors[] = $staffIdMap[$author['author_id']];
                    }
                }
                else if($author['author_type'] == 'ExternalAuthor'){
                    // External Author
                    $person = new Person(array());
                    $person->name = $external_authors[$author['author_id']]['name'];
                    $product->authors[] = $person;
                }
                else if($author['author_type'] == 'Responsibility'){
                    // HQP
                    if(isset($respIdMap[$author['author_id']])){
                        $product->authors[] = $respIdMap[$author['author_id']];
                    }
                }
            }
        }
        
        $product->create(false);
        show_status(++$iterationsSoFar, count($publications));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Presentations\n";
    foreach($presentations as $presentation){
        $product = new Product(array());
        $product->category = 'Presentation';
        $product->type = $presentationMap[$presentation['category']];
        $product->title = trim($presentation['organization'])." {$product->type}";
        $product->date = $presentation['date'];
        $product->access = "Public";
        
        if($presentation['invited'] == "formal invitation"){
            $product->status = "Invited";
        }
        else{
            $product->status = "Not Invited";
        }
        
        $product->authors = array();
        $product->projects = array();
        $data = array(
            'organizing_body' => $presentation['organization'],
            'location' => $presentation['country'],
            'length' => $presentation['duration']
        );
        if($presentation['refereed'] == "not refereed" ||
           $presentation['refereed'] == ""){
            $data['peer_reviewed'] = "No";
        }
        else{
            $data['peer_reviewed'] = "Yes";
        }
        
        $product->data = $data;
        
        // Add Authors
        if(isset($staffIdMap[$presentation['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$presentation['faculty_staff_member_id']];
            
            $product->create(false);
        }
        show_status(++$iterationsSoFar, count($presentations));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Awards\n";
    /*foreach($awards as $award){
        $product = new Product(array());
        $product->category = 'Award';
        $product->type = 'Award';
        $product->title = ucwords(trim($award['name']));
        $product->date = $award['year']."-00-00";
        $product->status = "Published";
        $product->access = "Public";
        $product->data = array('award_category' => ucwords($award['category']),
                               'scope' => ucwords($award['scope']));
                               
        $product->authors = array();
        $product->projects = array();
                               
        // Add Author
        if($award['faculty_staff_member_id'] != null){
            if(isset($staffIdMap[$award['faculty_staff_member_id']])){
                $product->authors[] = $staffIdMap[$award['faculty_staff_member_id']];
            }
            
            $product->create(false);
        }
        show_status(++$iterationsSoFar, count($awards) + count($otherAwards));
    }*/
    
    foreach($otherAwards as $award){
        $years = explode("-", $award['Year']);
        $startYear = trim($years[0]);
        $endYear = (isset($years[1])) ? trim($years[1]) : trim($years[0]);
        if(strlen($startYear) == 2){
            $startYear = "20{$startYear}";
        }
        if(strlen($endYear) == 2){
            $endYear = "20{$endYear}";
        }
        $product = new Product(array());
        $product->category = 'Award';
        $product->type = 'Other';
        
        $types = array();
        if(strstr($award['Award Category'], "Teaching")){
            $types[] = "Teaching";
        }
        if(strstr($award['Award Category'], "Research")){
            $types[] = "Research";
        }
        if(strstr($award['Award Category'], "Service")){
            $types[] = "Service";
        }
        if(count($types) > 1){
            $product->type = "Combined";
        }
        else if(count($types) == 1){
            $product->type = $types[0];
        }
        
        $product->title = ucwords(trim($award['Award']));
        $product->acceptance_date = trim($startYear)."-01-01";
        $product->date = trim($endYear)."-01-01";
        $product->status = "Published";
        $product->access = "Public";
        $product->description = trim($award['award description']);
        $product->data = array('awarded_by' => $award['Awarded by'],
                               'scope' => ucwords($award['Type']));
                               
        $product->authors = array();
        $product->projects = array();
        
        // Add Author
        if($award['Employee ID'] != null){
            $author = Person::newFromEmployeeId($award['Employee ID']);
            if($author->getId() != 0){
                $product->authors[] = $author;
            }
            
            $product->create(false);
        }
        show_status(++$iterationsSoFar, count($otherAwards));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Patents\n";
    foreach($patents as $patent){
        $product = new Product(array());
        $product->category = 'Patent/Spin-Off';
        $product->type = 'Patent';
        $product->title = ucwords(trim($patent['TECH_TITLE']));
        $product->date = $patent['ISSUEDATE'];
        $product->status = "Published";
        $product->access = "Public";
        $product->data = array('number' => $patent['PATENTNO'],
                               'country' => $patent['COUNTRYNAME'],
                               'tech_department' => $patent['TECH_CLIENTDEPARTMENTS']);
                               
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        $inventors = explode(",", $patent['INVENTORS']);
        foreach($inventors as $inventor){
            $inventor = trim($inventor);
            $person = Person::newFromNameLike($inventor);
            if($person->getId() == 0){
                $person->name = $inventor;
            }
            $product->authors[] = $person;
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($patents));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Spin-Offs\n";
    foreach($spinoffs as $spinoff){
        $product = new Product(array());
        $product->category = 'Patent/Spin-Off';
        $product->type = 'Spin-Off';
        $product->title = ucwords(trim($spinoff['Spin-Off Company']));
        $product->date = $spinoff['Creation Date'];
        $product->status = "Published";
        $product->access = "Public";
                       
        $product->data = array();        
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        $spinoff['Researcher'] = str_replace(" and ", " & ", $spinoff['Researcher']);
        $inventors = explode("&", $spinoff['Researcher']);
        foreach($inventors as $inventor){
            $inventor = trim($inventor);
            $person = Person::newFromNameLike($inventor);
            if($person->getId() == 0){
                $person->name = $inventor;
            }
            $product->authors[] = $person;
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($spinoffs));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Community Outreach Committees\n";
    foreach($community_outreach_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'Community Outreach Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                    
        $product->data = array();
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($community_outreach_committees));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Departmental Committees\n";
    foreach($departmental_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'Departmental Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                               
        $product->data = array();
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($departmental_committees));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Faculty Committees\n";
    foreach($faculty_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'Faculty Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                               
        $product->data = array();
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($faculty_committees));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Other Committees\n";
    foreach($other_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'Other Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                       
        $product->data = array();        
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($other_committees));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting Scientific Committees\n";
    foreach($scientific_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'Scientific Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                               
        $product->authors = array();
        $product->projects = array();
        $product->data = array(
            'scope' => ucfirst($committee['scientific_committee_scope']),
            'organization' => trim($committee['organization'])
        );
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($scientific_committees));
    }
    
    $iterationsSoFar = 0;
    echo "\nImporting University Committees\n";
    foreach($university_committees as $committee){
        $product = new Product(array());
        $product->category = 'Activity';
        $product->type = 'University Committee';
        $committee['description'] = str_replace("￼", "", $committee['description']);
        $product->description = trim($committee['description']);
        $product->title = trim(substr($committee['description'], 0, 100));
        if($product->title != $committee['description']){
            $product->title .= "...";
        }
        $product->acceptance_date = ($committee['reporting_year'])."-07-01";
        $product->date = ($committee['reporting_year']+1)."-06-30";
        $product->access = "Public";
                               
        $product->data = array();  
        $product->authors = array();
        $product->projects = array();
                               
        // Add Authors
        if(isset($staffIdMap[$committee['faculty_staff_member_id']])){
            $product->authors[] = $staffIdMap[$committee['faculty_staff_member_id']];
        }
        $product->create(false);
        show_status(++$iterationsSoFar, count($university_committees));
    }
    
    // Create Product Histories
    $iterationsSoFar = 0;
    echo "\nImporting Product Histories\n";
    DBFunctions::execSQL("TRUNCATE grand_product_histories", true);
    foreach($histories as $history){
        if(isset($staffIdMap[$history['faculty_staff_member_id']])){
            $person = $staffIdMap[$history['faculty_staff_member_id']];
            
            DBFunctions::insert('grand_product_histories',
                                array('user_id' => $person->getId(),
                                      'year' => $history['year'],
                                      'type' => $history['publication_type'],
                                      'value' => $history['count'],
                                      'created' => $history['created_at'],
                                      'updated' => $history['updated_at']));
        }
        show_status(++$iterationsSoFar, count($histories));
    }
    
    echo "\nSyncing Authors\n";
    require_once("syncAuthors.php");
    
    echo "\n";
    
    require_once("importAllCourses.php");

    echo "\n";
    
    require_once("2importAwardsXls.php");
    
    echo "\n";

?>
