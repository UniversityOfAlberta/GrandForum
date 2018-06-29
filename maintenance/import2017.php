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
        'PosterArticle'   => array('Publication', 'Poster'),
        'PaperAbstract'   => array('Publication', 'Journal Abstract'),
        'Review'          => array('Publication', 'Book Review'),
        'Book'            => array('Publication', 'Book'),
        'Patent'          => array('Patent/Spin-Off', 'Patent')
    );
    
    $wgUser = User::newFromId(1);
    
    DBFunctions::execSQL("TRUNCATE grand_salary_scales", true);
    DBFunctions::execSQL("TRUNCATE grand_user_salaries", true);
    
    $faculty = DBFunctions::select(array('bddEfec2_production.faculty_staff_members'),
                                   array('*'));
    
    $salary_scales = DBFunctions::select(array('bddEfec2_production.salary_scales'),
                                         array('*'));
                                         
    $salaries = DBFunctions::select(array('bddEfec2_production.salaries'),
                                    array('*'));
                                    
    $publications = DBFunctions::select(array('bddEfec2_production.publications'),
                                        array('*'),
                                        array('id' => GT(10253)));
                                        
    $authorships = DBFunctions::select(array('bddEfec2_production.authorships'),
                                       array('*'),
                                       array(),
                                       array('position' => 'ASC'));
                                       
    $external_authors = DBFunctions::select(array('bddEfec2_production.external_authors'),
                                            array('*'));
                                            
    $responsibility_authors = DBFunctions::select(array('bddEfec2_production.responsibility_coauthors'),
                                                  array('*'));
                                            
    $responsibilities = DBFunctions::select(array('bddEfec2_production.responsibilities'),
                                            array('*'));
     
    // Index Authorships by publication_id
    $newAuthorships = array();
    foreach($authorships as $author){
        $newAuthorships[$author['publication_id']][] = $author;
    }
    $authorships = $newAuthorships; 
    
    // Index Externals by id
    $newExternals = array();
    foreach($external_authors as $s){
        $newExternals[$s['id']] = $s;
    }
    $external_authors = $newExternals;
    
    $respIdMap = array();
    foreach($responsibilities as $resp){
        $person = Person::newFromNameLike($resp['name']);
        if($person != null && $person->getId() > 0){
            $respIdMap[$resp['id']] = $person;
        }
        else{
            $respIdMap[$resp['id']] = $resp;
        }
    }
    
    $facultyMap = array();                                 
    foreach($faculty as $f){
        if($f['uid'] != 0){
            $person = Person::newFromEmployeeId($f['uid']);
            if($person == null || $person->getId() == null){
                // Might have an account from their email
                $person = Person::newFromEmail($f['ccid']."@ualberta.ca");
            }
            if($person == null || $person->getId() == 0){
                // Need to create
                $username = str_replace(" ", "", preg_replace("/\(.*\)/", "", 
                    trim(str_replace(".", "", $f['first_name']), " -\t\n\r\0\x0B").".".
                    trim(str_replace(".", "", $f['last_name']),  " -\t\n\r\0\x0B")
                ));
                $username = str_replace("'", "", $username);
                $username = preg_replace("/\".*\"/", "", $username);
                $fname = $f['first_name'];
                $lname = $f['last_name'];
                $email = $f['ccid']."@ualberta.ca";
                $ldap = "http://webapps.srv.ualberta.ca/search/?type=simple&uid=true&c={$f['ccid']}";
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
                Person::$employeeIdCache[$f['uid']] = $person;
                Person::$cache[strtolower($person->getName())] = $person;
                Person::$cache[$person->getId()] = $person;
                Person::$cache['eId'.$f['uid']] = $person;



                // Update user name(s)
                DBFunctions::update('mw_user',
                                    array('user_name'   => $username,
                                          'first_name'  => $fname,
                                          'last_name'   => $lname,
                                          'ldap_url'    => $ldap),
                                    array('user_email'  => EQ($email)));
                                    
                // Update Role info
                addUserRole($person, CI, $f['date_of_appointment'], $f['date_retirement']);
                
                // Update University Info
                addUserUniversity($person, "University of Alberta", ucwords($f['department']), ucwords($f['rank']), $f['date_of_appointment'], $f['date_retirement']);
                $person->university = false;
            }
            
            if($person != null && $person->getId() != 0){
                $facultyMap[$f['id']] = $person;
                // Update FEC Personal Info
                DBFunctions::delete('grand_personal_fec_info',
                                    array('user_id' => $person->getId()));
                DBFunctions::insert('grand_personal_fec_info',
                                    array('user_id' => $person->getId(),
                                          'date_of_phd' => $f['date_of_phd'],
                                          'date_of_appointment' => $f['date_of_appointment'],
                                          'date_assistant' => $f['date_assistant'],
                                          'date_associate' => $f['date_associate'],
                                          'date_professor' => $f['date_professor'],
                                          'date_tenure' => $f['date_tenure'],
                                          'date_retirement' => $f['date_retirement'],
                                          'date_last_degree' => $f['date_last_degree'],
                                          'last_degree' => $f['last_degree'],
                                          'publication_history_refereed' => $f['publication_history_refereed'],
                                          'publication_history_books' => $f['publication_history_books'],
                                          'publication_history_patents' => $f['publication_history_patents'],
                                          'date_fso2' => $f['date_fso2'],
                                          'date_fso3' => $f['date_fso3'],
                                          'date_fso4' => $f['date_fso4']));
                
                DBFunctions::update('mw_user',
                                    array('employee_id' => $f['uid']),
                                    array('user_id' => EQ($person->getId())));
                
                if($person->getPosition() != ucwords($f['rank']) && $f['date_retirement'] == ""){
                    $university = $person->getUniversity();
                    DBFunctions::update('grand_user_university',
                                        array('end_date' => '2017-07-01'),
                                        array('id' => $university['id']));
                    addUserUniversity($person, "University of Alberta", ucwords($f['department']), ucwords($f['rank']), '2017-07-01', "");
                }
            }
        }
    }
                                         
    foreach($salary_scales as $scale){
        DBFunctions::insert('grand_salary_scales',
                            array('year'              => $scale['year'],
                                  'min_salary_assoc'  => $scale['min_salary_associate'],
                                  'min_salary_assist' => $scale['min_salary_assistant'],
                                  'min_salary_prof'   => $scale['min_salary_prof'],
                                  'min_salary_fso2'   => $scale['min_salary_fso2'],
                                  'min_salary_fso3'   => $scale['min_salary_fso3'],
                                  'min_salary_fso4'   => $scale['min_salary_fso4'],
                                  'max_salary_assoc'  => $scale['max_salary_assoc'],
                                  'max_salary_assist' => $scale['max_salary_assistant'],
                                  'max_salary_prof'   => $scale['max_salary_prof'],
                                  'max_salary_fso2'   => $scale['max_salary_fso2'],
                                  'max_salary_fso3'   => $scale['max_salary_fso3'],
                                  'max_salary_fso4'   => $scale['max_salary_fso4'],
                                  'increment_assoc'   => $scale['increment_assoc'],
                                  //'increment_assist'  => $scale['increment_assistant'],
                                  'increment_prof'    => $scale['increment_prof'],
                                  'increment_fso2'    => $scale['increment_fso2'],
                                  'increment_fso3'    => $scale['increment_fso3'],
                                  'increment_fso4'    => $scale['increment_fso4']));
    }
    
    foreach($salaries as $salary){
        if(isset($facultyMap[$salary['faculty_staff_member_id']])){
            $person = $facultyMap[$salary['faculty_staff_member_id']];
            DBFunctions::insert('grand_user_salaries',
                                array('user_id' => $person->getId(),
                                      'year' => $salary['year'],
                                      'salary' => $salary['salary']));
        }
    }

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
        $product->contributors = array();
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
                    if(isset($facultyMap[$author['author_id']])){
                        $product->authors[] = $facultyMap[$author['author_id']];
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
                    if(isset($respIdMap[$author['author_id']]) && $respIdMap[$author['author_id']] instanceof Person){
                        $product->authors[] = $respIdMap[$author['author_id']];
                    }
                    else{
                        $person = new Person(array());
                        $person->name = $respIdMap[$author['author_id']]['name'];
                        $product->authors[] = $person;
                    }
                }
            }
        }
        
        $check = Product::newFromTitle($product->title, $product->category);
        if($check == null || $check->getId() == 0){
            echo $product->getTitle()."\n";
            $product->create(false);
            if($product->date != ""){
                if(isset($authorships[$publication['id']])){
                    foreach($authorships[$publication['id']] as $author){
                        if($author['author_type'] == 'FacultyStaffMember'){
                            // Faculty Staff
                            if(isset($facultyMap[$author['author_id']])){
                                $faculty = $facultyMap[$author['author_id']];
                                $reportedYear = substr($publication['created_at'], 0, 4);
                                $reportedMonth = substr($publication['created_at'], 5, 5);
                                if($reportedMonth < "12-01"){
                                    $reportedYear--;
                                }
                                DBFunctions::insert('grand_products_reported',
                                                    array('product_id' => $product->getId(),
                                                          'user_id' => $faculty->getId(),
                                                          'year' => $reportedYear));
                                                          
                            }
                        }
                    }
                }
            }
        }
    }

?>
