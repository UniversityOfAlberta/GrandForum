<?php

    require_once('commandLine.inc');
    global $wgUser;
    $wgUser = User::newFromId(1);
    
    $awards = DBFunctions::select(array('bddEfec2_development.awards' => 'a', 
                                        'bddEfec2_development.award_scopes' => 's', 
                                        'bddEfec2_development.faculty_staff_members' => 'f'),
                                  array('a.name', 
                                        'a.category', 
                                        'a.faculty_staff_member_id', 
                                        'a.reporting_year' => 'year', 
                                        's.name' => 'scope',
                                        'f.uid'),
                                  array('a.award_scope_id' => EQ(COL('s.id')),
                                        'a.faculty_staff_member_id' => EQ(COL('f.id'))));
    $iterationsSoFar = 0;
    foreach($awards as $award){
        $product = new Product(array());
        $product->category = 'Award';
        $product->type = 'Other';
        if(strstr($award['category'], "teaching")){
            $product->type = "Teaching";
        }
        if(strstr($award['category'], "research")){
            $product->type = "Research";
        }
        if(strstr($award['category'], "service")){
            $product->type = "Service";
        }
        if(strstr($award['category'], "combined")){
            $product->type = "Combined";
        }
        $product->title = ucwords(trim($award['name']));
        $product->date = $award['year']."-00-00";
        $product->status = "Published";
        $product->access = "Public";
        $product->data = array('scope' => ucwords($award['scope']));
                               
        $product->authors = array();
        $product->projects = array();
                               
        // Add Author
        if($award['faculty_staff_member_id'] != null){
            $author = Person::newFromEmployeeId($award['uid']);
            if($author != null && $author->getId() != 0){
                $product->authors[] = $author;
            }
            $product->create(false);
        }
        show_status(++$iterationsSoFar, count($awards));
    }
