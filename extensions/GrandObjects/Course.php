<?php
    /**
    * @package GrandObjects
    */

class Course extends BackboneModel{

    static $cache = array();
    var $id;
    var $acadOrg;
    var $term;
    var $term_string;
    var $shortDesc;
    var $classNbr;
    var $subject;
    var $catalog;
    var $component;
    var $sect;
    var $descr;
    var $crsStatus;
    var $facilId;
    var $place;
    var $pat;
    var $startDate;
    var $endDate;
    var $hrsFrom;
    var $hrsTo;
    var $mon;
    var $tues;
    var $wed;
    var $thurs;
    var $fri;
    var $sat;
    var $sun;
    var $classType;
    var $capEnrl;
    var $totEnrl;
    var $campus;
    var $location;
    var $notesNbr;
    var $noteNbr;
    var $note;
    var $rqGroup;
    var $restrictionDescr;
    var $approvedHrs;
    var $duration;
    var $career;
    var $consent;
    var $courseDescr;
    var $maxUnits;
    var $courseName;
    var $comment;

    // Constructor
    function Course($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->acadOrg = $data[0]['Acad Org'];
            $this->term = $data[0]['Term'];
            $this->shortDesc = $data[0]['Short Desc'];
            $this->classNbr = $data[0]['Class Nbr'];
            $this->subject = $data[0]['Subject'];
            $this->catalog = $data[0]['Catalog'];
            $this->component = $data[0]['Component'];
            $this->sect = $data[0]['Sect'];
            $this->descr = $data[0]['Descr'];
            $this->crsStatus= $data[0]['Crs Status'];
            $this->facilId = $data[0]['Facil ID'];
            $this->place = $data[0]['Place'];
            $this->pat = $data[0]['Pat'];
            $this->startDate = $data[0]['Start Date'];
            $this->endDate = $data[0]['End Date'];
            $this->hrsFrom = $data[0]['Hrs From'];
            $this->hrsTo = $data[0]['Hrs To'];
            $this->mon = $data[0]['Mon'];
            $this->tues = $data[0]['Tues'];
            $this->wed = $data[0]['Wed'];
            $this->thurs = $data[0]['Thurs'];
            $this->fri = $data[0]['Fri'];
            $this->sat = $data[0]['Sat'];
            $this->sun = $data[0]['Sun'];
            $this->classType = $data[0]['Class Type'];
            $this->capEnrl = $data[0]['Cap Enrl'];
            $this->totEnrl = $data[0]['Tot Enrl'];
            $this->campus = $data[0]['Campus'];
            $this->location = $data[0]['Location'];
            $this->notesNbr = $data[0]['Notes Nbr'];
            $this->noteNbr = $data[0]['Note Nbr'];
            $this->note = $data[0]['Note'];
            $this->rqGroup = $data[0]['Rq Group'];
            $this->restrictionDescr = $data[0]['Restriction Descr'];
            $this->approvedHrs = $data[0]['Approved Hrs'];
            $this->duration = $data[0]['Duration'];
            $this->career = $data[0]['Career'];
            $this->consent = $data[0]['Consent'];
            $this->courseDescr = $data[0]['Course Descr'];
            $this->maxUnits = $data[0]['Max Units'];
            $this->courseName = "{$data[0]['Sect']} {$data[0]['Descr']}";
        }
    }

    /**
    * Returns a new Course from the given id
    * @param integer $id The id of the course
    * @return Course The Course with the given id. If no
    * course exists with that id, it will return an empty course.
    */
    static function newFromId($id){
        //check if exists in cache for easy access
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $data = DBFunctions::select(array('grand_courses'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $course = new Course($data);
        //$self::$cache[$course->id] = &$course;
        return $course;
    }

    /**
     * Returns an array of courses that match the given subject and id
     * @param string $subject The name of the course
     * @param integer $catalog The catalog number of the course
     * @return array The array of Courses
    */
    static function newFromSubjectCatalog($subject, $catalog){
        $data = DBFunctions::select(array('grand_courses'),
                                    array('*'),
                                    array('Subject' => LIKE("%$subject%"),
                                          'Catalog' => LIKE("%$catalog%")));
        $courses = array();
        foreach($data as $row){
            $course = new Course(array($row));
            //$self::$cache[$course->id] = &$course;
            array_push($courses, $course);
        }
        return $courses;
    }

    /**
     * Returns an array of courses that match the given subject and id
     * @param string $subject The name of the course
     * @param integer $catalog The catalog number of the course
     * @return array The array of Courses
    */
    static function newFromSubjectCatalogSectStartDateTerm($subject, $catalog,$sect,$startDate,$term){
        $data = DBFunctions::select(array('grand_courses'),
                                    array('*'),
                                    array('Subject' => LIKE("%$subject%"),
                                          'Catalog' => LIKE("%$catalog%"),
                                          'Sect' => LIKE("%$sect%"),
                                          '`Start Date`' => LIKE("%$startDate%"),
                                          'Term' => LIKE("%$term%")));
        $data = DBFunctions::execSQL($sql);
        if(count($data)>0){
            $course = new Course(array($data[0]));
            return $course;
        }
        return new Course(array());
    }
        
    /**
     * Returns an array of courses that match the given subject and id
     * @param string $subject The name of the course
     * @param integer $catalog The catalog number of the course
     * @return array The array of Courses
    */
    static function newFromSubjectCatalogSectStartDateTermLike($subject = '%', $catalog = '%' ,$sect = '%', $startDate = '%', $term = '%'){
        $data = DBFunctions::select(array('grand_courses'),
                                    array('*'),
                                    array('Subject' => LIKE("%$subject%"),
                                          'Catalog' => LIKE("%$catalog%"),
                                          'Sect' => LIKE("%$sect%"),
                                          '`Start Date`' => LIKE("%$startDate%"),
                                          'Term' => LIKE("%$term%")));
        $data = DBFunctions::execSQL($sql);
        if(count($data)>0){
                $course = new Course(array($data[0]));
            return $course;
        }
        return new Course(array());
    }

    /**
     * Returns True if the course is saved correctly to the course table in the database
     * @return boolean True if the database accepted the new course
    */
    function create(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::insert('grand_courses',
                                    array('`Acad Org`'          => $this->acadOrg,
                                          '`Term`'              => $this->term,
                                          '`term_string`'       => $this->term_string,
                                          '`Short Desc`'        => $this->shortDesc,
                                          '`Class Nbr`'         => $this->classNbr,
                                          '`Subject`'           => $this->subject,
                                          '`Catalog`'           => $this->catalog,
                                          '`Component`'         => $this->component,
                                          '`Sect`'              => $this->sect,
                                          '`Descr`'             => $this->descr,
                                          '`Crs Status`'        => $this->crsStatus,
                                          '`Facil ID`'          => $this->facilId,
                                          '`Place`'             => $this->place,
                                          '`Pat`'               => $this->pat,
                                          '`Start Date`'        => $this->startDate,
                                          '`End Date`'          => $this->endDate,
                                          '`Hrs From`'          => $this->hrsFrom,
                                          '`Hrs To`'            => $this->hrsTo,
                                          '`Mon`'               => $this->mon,
                                          '`Tues`'              => $this->tues,
                                          '`Wed`'               => $this->wed,
                                          '`Thurs`'             => $this->thurs,
                                          '`Fri`'               => $this->fri,
                                          '`Sat`'               => $this->sat,
                                          '`Sun`'               => $this->sun,
                                          '`Class Type`'        => $this->classType,
                                          '`Cap Enrl`'          => $this->capEnrl,
                                          '`Tot Enrl`'          => $this->totEnrl,
                                          '`Campus`'            => $this->campus,
                                          '`Location`'          => $this->location,
                                          '`Notes Nbr`'         => $this->notesNbr,
                                          '`Note Nbr`'          => $this->noteNbr,
                                          '`Note`'              => $this->note,
                                          '`Rq Group`'          => $this->rqGroup,
                                          '`Restriction Descr`' => $this->restrictionDescr,
                                          '`Approved Hrs`'      => $this->approvedHrs,
                                          '`Duration`'          => $this->duration,
                                          '`Career`'            => $this->career,
                                          '`Consent`'           => $this->consent,
                                          '`Course Descr`'      => $this->courseDescr,
                                          '`Max Units`'         => $this->maxUnits));
            $this->id = DBFunctions::insertId();

                DBFunctions::insert('grand_user_courses',
                                    array('`user_id`'          => $me->getId(),
					  '`course_id`'        => $this->id));

        }
	return true;
    }

    /**
     * Returns True if the course is updated correctly to the course table in the database
     * @return boolean True if the database accepted the updated course
    */
    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()){
                DBFunctions::update('grand_courses',
                                    array('`Acad Org`'          => $this->acadOrg,
                                          '`Term`'              => $this->term,
                                          '`term_string`'       => $this->term_string,
                                          '`Short Desc`'        => $this->shortDesc,
                                          '`Class Nbr`'         => $this->classNbr,
                                          '`Subject`'           => $this->subject,
                                          '`Catalog`'           => $this->catalog,
                                          '`Component`'         => $this->component,
                                          '`Sect`'              => $this->sect,
                                          '`Descr`'             => $this->descr,
                                          '`Crs Status`'        => $this->crsStatus,
                                          '`Facil ID`'          => $this->facilId,
                                          '`Place`'             => $this->place,
                                          '`Pat`'               => $this->pat,
                                          '`Start Date`'        => $this->startDate,
                                          '`End Date`'          => $this->endDate,
                                          '`Hrs From`'          => $this->hrsFrom,
                                          '`Hrs To`'            => $this->hrsTo,
                                          '`Mon`'               => $this->mon,
                                          '`Tues`'              => $this->tues,
                                          '`Wed`'               => $this->wed,
                                          '`Thurs`'             => $this->thurs,
                                          '`Fri`'               => $this->fri,
                                          '`Sat`'               => $this->sat,
                                          '`Sun`'               => $this->sun,
                                          '`Class Type`'        => $this->classType,
                                          '`Cap Enrl`'          => $this->capEnrl,
                                          '`Tot Enrl`'          => $this->totEnrl,
                                          '`Campus`'            => $this->campus,
                                          '`Location`'          => $this->location,
                                          '`Notes Nbr`'         => $this->notesNbr,
                                          '`Note Nbr`'          => $this->noteNbr,
                                          '`Note`'              => $this->note,
                                          '`Rq Group`'          => $this->rqGroup,
                                          '`Restriction Descr`' => $this->restrictionDescr,
                                          '`Approved Hrs`'      => $this->approvedHrs,
                                          '`Duration`'          => $this->duration,
                                          '`Career`'            => $this->career,
                                          '`Consent`'           => $this->consent,
                                          '`Course Descr`'      => $this->courseDescr,
                                          '`Max Units`'         => $this->maxUnits),
                                    array('id' => EQ($this->id)));
        }
	return true;
    }

    function getAllCourses(){
        $data = DBFunctions::select(array('grand_courses'),
                                    array('id'));
        $courses = array();
        foreach($data as $row){
            $courses[] = Course::newFromId($row['id']);
        }
        return $courses;
    }

    function getUserCourses($id=0){
	global $wgUser;
	if($id == 0){
	    $id = $wgUser->getId();    
	}
        $sql = "SELECT DISTINCT course_id
                FROM `grand_user_courses`
                WHERE (user_id = '$id')";
        $data = DBFunctions::execSQL($sql);
        $courses = array();
        foreach($data as $row){
            //$course = Course::newFromId($row['course_id']);
            //$courses["{$course->subject} {$course->catalog} {$course->startDate} {$course->component} {$course->sect}"] = $course;
            $courses[] = Course::newFromId($row['course_id']);

        }
        ksort($courses);
        return $courses;
    }

    function getProfessors(){
        $data= DBFunctions::select(array('grand_user_courses'),
                                    array('user_id' => 'id'),
                                    array('course_id' => EQ($this->id)));
        $profs = array();
        foreach($data as $row){
            $profs[] = Person::newFromId($row['id']);
        }
        return $profs;
    }

    function toArray(){
        global $wgUser;
        if(!$wgUser->isLoggedIn()){
            return array();
	}
	$comments = "";
	$person_name = "";
	$student_url = "";
	if($this->getStudentInfo() != ""){
	     $student = $this->getStudentInfo();
	     $person_name = $student->getNameForForms();
	     $student_url = $student->getUrl();
	     $comments = $this->getUserComment();
	}
        $json = array('id' => $this->getId(),
                      'acadOrg' => $this->acadOrg,
                      'term' => $this->term,
                      'shortDesc' => $this->shortDesc,
                      'classNbr' => $this->classNbr,
                      'subject' => $this->subject,
                      'catalog' => $this->catalog,
                      'component' => $this->component,
                      'sect' => $this->sect,
                      'descr' => $this->descr,
		      'crsStatus' => $this->crsStatus,
                      'place' => $this->place,
                      'pat' => $this->pat,
                      'startDate' => $this->startDate,
                      'endDate' => $this->endDate,
                      'hrsFrom' => $this->hrsFrom,
                      'hrsTo' => $this->hrsTo,
                      'classType' => $this->classType,
                      'capEnrl' => $this->capEnrl,
                      'totEnrl' => $this->totEnrl,
                      'campus' => $this->campus,
                      'location' => $this->location,
                      'rqGroup' => $this->rqGroup,
                      'courseDescr' => $this->courseDescr,
                      'maxUnits' => $this->maxUnits,
                      'courseName' => $this->courseName,
		      'course_comment' => $comments,
		      'person_name' => $person_name,
		      'student_url' => $student_url,
		      'course_url' => $this->getUrl());

	return $json;
        
    }
    function delete(){
            //TODO:implement function
    }
    function exists(){
            //TODO:implement function
    }
    function getCacheId(){
            //TODO:implement function
    }

    function getId(){
        return $this->id;
    }

    function getStartDate(){
        $date = strtotime("01 January 1900 +{$this->startDate} days");
        return date("Y-m-d", $date);
    }
        
    function getEndDate(){
        $date = strtotime("01 January 1900 +{$this->endDate} days");
        return date("Y-m-d", $date);
    }

    function getStartMonth(){
        $date = strtotime("01 January 1900 +{$this->startDate} days");
        return date("M", $date);
    }

    function getStartYear(){
        $date = strtotime("01 January 1900 +{$this->startDate} days");
        return date("Y", $date);
    }

    function getTerm(){
        $year = $this->getStartYear();
        $term = $this->getTermUsingStartMonth($this->getStartMonth());
        return "$term $year";
    }

    function getTermUsingStartMonth($month){
        if($month == "Sep"){
            return "Fall";
        }
        else if($month == "Jan"){
            return "Winter";
        }
        else if($month == "Apr"){
            return "Spring";
        }
        else{
            return "Summer";
        }
    }

    function getStudentInfo(){
        $sql = "SELECT user_id
                FROM `grand_user_courses`
                WHERE (course_id = '$this->id')";
        $data = DBFunctions::execSQL($sql);
        if(count($data) >0){
            $user_id = $data[0]['user_id'];
	    return Person::newFromId($user_id);
        }
        return "";
    }


    function getUserComment(){
        $sql = "SELECT course_comments
                FROM `grand_user_courses`
                WHERE (course_id = '$this->id')";
        $data = DBFunctions::execSQL($sql);
	if(count($data) >0){
	    return $data[0]['course_comments'];
	}
        return "";	
    }

    function updateUserComment($comment, $user_id = 0){
        $sql = "SELECT course_comments
                FROM `grand_user_courses`
                WHERE (course_id = '$this->id')";
        $data = DBFunctions::execSQL($sql);
        if(count($data) > 0){
                DBFunctions::update('grand_user_courses',
                                    array('`course_comments`'=>$comment),
                                    array('course_id' => EQ($this->id)));
        }
	else{
                DBFunctions::insert('grand_user_courses',
                                    array('`user_id`' => $user_id,
                                          '`course_id`' => $this->id,
                                          '`course_comments`' => $comment));

	}
        return "";
    }
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        if(!isset($_GET['embed']) || $_GET['embed'] == 'false'){
            return "{$wgServer}{$wgScriptPath}/index.php/Special:Courses#/{$this->getId()}";
        }
        return "{$wgServer}{$wgScriptPath}/index.php/Special:Courses?embed#/{$this->getId()}";
    }
}

?>
