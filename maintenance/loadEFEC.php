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
    
    $productCategoryMap = array(
        'Conference'      => array('Publication', 'Conference Paper'),
        'Journal'         => array('Publication', 'Journal Paper'),
        'BookChapter'     => array('Publication', 'Book Chapter'),
        'TechnicalReport' => array('Publication', 'Tech Report'),
        'Other'           => array('Publication', 'Misc'),
        'PosterArticle'   => array('Presentation', 'Poster'),
        'PaperAbstract'   => array('Publication', 'Journal Abstract'),
        'Review'          => array('Activity', 'Review'),
        'Book'            => array('Publication', 'Book'),
        'Patent'          => array('Product', 'Patent')
    );
    
    DBFunctions::execSQL("TRUNCATE grand_notifications", true);
    DBFunctions::execSQL("TRUNCATE grand_personal_fec_info", true);
    DBFunctions::execSQL("TRUNCATE grand_user_university", true);
    DBFunctions::execSQL("TRUNCATE grand_relations", true);
    DBFunctions::execSQL("TRUNCATE grand_movedOn", true);
    DBFunctions::execSQL("TRUNCATE grand_theses", true);
    
    DBFunctions::execSQL("DELETE FROM mw_user WHERE user_id != 1", true);
    DBFunctions::execSQL("DELETE FROM grand_roles WHERE user_id != 1", true);
    
    DBFunctions::execSQL("ALTER TABLE mw_user AUTO_INCREMENT = 2", true);
    DBFunctions::execSQL("ALTER TABLE grand_roles AUTO_INCREMENT = 2", true);
    
    $awards = DBFunctions::select(array('bddEfec2_development.awards' => 'a', 'bddEfec2_development.award_scopes' => 's'),
                                  array('a.name', 'a.category', 'a.faculty_staff_member_id', 'a.reporting_year' => 'year', 's.name' => 'scope'),
                                  array('a.award_scope_id' => EQ(COL('s.id'))));
    
    $staff = DBFunctions::select(array('bddEfec2_development.faculty_staff_members'),
                                 array('*'));
    $responsibilities = DBFunctions::select(array('bddEfec2_development.responsibilities'),
                                            array('*'));
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
                                                  
    $iterationsSoFar = 0;
    $nIterations = count($staff) + count($responsibilities) + count($publications) + count($awards) + count($histories);
    
    // Adding Faculty Staff
    $staffIdMap = array();
    foreach($staff as $row){
        $username = str_replace(" ", "", preg_replace("/\(.*\)/", "", 
            trim(str_replace(".", "", $row['first_name']), " -\t\n\r\0\x0B").".".
            trim(str_replace(".", "", $row['last_name']),  " -\t\n\r\0\x0B")
        ));
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
        Person::$cache[$person->getName()] = $person;
        Person::$cache[$person->getId()] = $person;

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
        show_status(++$iterationsSoFar, $nIterations);
    }
    
    // Adding HQP
    $respIdMap = array();
    $hqpRoles = array();
    $hqpRelations = array();
    foreach($responsibilities as $row){
        /*$username = preg_replace("/\(.*\)/", "", trim(str_replace(".", "", $row['name']), " -\t\n\r\0\x0B"));
        $username = explode(",", $username, 2);
        if(count($username) > 1){
            $username = "{$username[1]}.{$username[0]}";
        }
        else{
            $username = $username[0];
        }
        $realName = $username;
        $username = str_replace(" ", "", $username);
        $email = "";
        $person = new Person(array());
        $person->name = $realName;
        $person = Person::newFromName($username);
        if($person == null || $person->getId() == 0){
            // First create the user
            $user = User::createNew($username, array('real_name' => "$realName", 
                                                     'password' => User::crypt(mt_rand()), 
                                                     'email' => $email));
            if($user == null){
                show_status(++$iterationsSoFar, $nIterations);
                continue;
            }
            $person = new Person(array());
            $person->id = $user->getId();
            $person->name = $user->getName();
            $person->realname = $realName;
            Person::$namesCache[$person->getName()] = $person;
            Person::$idsCache[$person->getId()] = $person;
            Person::$cache[$person->getName()] = $person;
            Person::$cache[$person->getId()] = $person;
        }
        $sup = @$staffIdMap[$row['faculty_staff_member_id']];
        
        $respIdMap[$row['id']] = $person;
        if($person->getId() != 0){
            // Update Role info
            if(!isset($hqpRoles[$person->getId()])){
                $r = new Role(array());
                $r->user = $person->getId();
                $r->role = HQP;
                $hqpRoles[$person->getId()] = $r;
                $nIterations++;
            }
            if($sup != null){
                if(!isset($hqpRelations[$sup->getId()][$person->getId()][$row['role']])){
                    $rel = new Relationship(array());
                    $rel->user1 = $sup->getId();
                    $rel->user2 = $person->getId();
                    $rel->startDate = $row['started'];
                    $rel->endDate = $row['ended'];
                    switch($row['role']){
                        case "":
                        case "co-supervisor":
                        case "supervisor":
                            $rel->type = SUPERVISES;
                            break;
                        default:
                            $rel->type = ucwords($row['role']);
                            break;
                    }
                    $hqpRelations[$sup->getId()][$person->getId()][$row['role']] = $rel;
                    $nIterations++;
                }
                $relation = $hqpRelations[$sup->getId()][$person->getId()][$row['role']];
                $relation->startDate = min($relation->getStartDate(), $row['started']);
                $relation->endDate   = max($relation->getEndDate(),   $row['ended']);
            }
            
            $role = $hqpRoles[$person->getId()];
            $role->startDate = min($role->getStartDate(), $row['started']);
            $role->endDate   = max($role->getEndDate(),   $row['ended']);
            $hqpRoles[$person->getId()] = $role;
        }*/
        show_status(++$iterationsSoFar, $nIterations);
    }
    
    Person::$namesCache = array();
    Person::$idsCache = array();
    Person::$cache = array();
    Person::$cache = array();
    
    foreach($hqpRoles as $id => $role){
        $role->create();
        show_status(++$iterationsSoFar, $nIterations);
    }
    
    foreach($hqpRelations as $sup){
        foreach($sup as $hqp){
            foreach($hqp as $rel){
                $rel->create();
                show_status(++$iterationsSoFar, $nIterations);
            }
        }
    }
    
    // Create Products
    DBFunctions::execSQL("TRUNCATE table `grand_products`", true);
    DBFunctions::execSQL("TRUNCATE table `grand_product_authors`", true);
    foreach($publications as $publication){
        $product = new Product(array());
        $product->category = $productCategoryMap[$publication['type']][0];
        $product->type = $productCategoryMap[$publication['type']][1];
        $product->title = trim(str_replace("•", "", str_replace("￼", "", $publication['title'])));
        $product->date = $publication['publication_date'];
        $product->acceptance_date = $publication['acceptance_date'];
        $product->ratio = $publication['ratio'];
        $product->acceptance_ratio_numerator = $publication['acceptance_ratio_numerator'];
        $product->acceptance_ratio_denominator = $publication['acceptance_ratio_denominator'];
        $product->status = "Published";
        $product->access = "Public";
        
        $product->authors = array();
        $product->projects = array();
        $data = array(
            'publisher' => $publication['publisher'],
            'pages' => $publication['pages'],
            'volume' => $publication['volume'],
            'issue' => $publication['issue'],
            'doi' => $publication['doi'],
            'url' => $publication['url'],
            'venue' => $publication['venue'],
            'event_title' => $publication['venue'],
            'book_title' => $publication['venue'],
            'journal_title' => $publication['venue'],
            'event_location' => $publication['location'],
            'location' => $publication['location'],
            'editors' => $publication['editors'],
            'peer_reviewed' => ucwords($publication['refereed'])
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
        
        $product->create();
        show_status(++$iterationsSoFar, $nIterations);
    }
    
    foreach($awards as $award){
        $product = new Product(array());
        $product->category = 'Award';
        $product->type = 'Award';
        $product->title = ucwords(trim($award['name']));
        $product->date = $award['year']."00-00";
        $product->status = "Published";
        $product->access = "Public";
        $product->data = array('award_category' => $award['category'],
                               'scope' => $award['scope']);
                               
        $product->authors = array();
        $product->projects = array();
                               
        // Add Author
        if($award['faculty_staff_member_id'] != null){
            if(isset($staffIdMap[$award['faculty_staff_member_id']])){
                $product->authors[] = $staffIdMap[$award['faculty_staff_member_id']];
            }
            
            $product->create();
        }
        show_status(++$iterationsSoFar, $nIterations);
    }
    
    // Create Product Histories
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
        show_status(++$iterationsSoFar, $nIterations);
    }

echo "\n";
?>
