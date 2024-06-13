<?php
    /**
    * @package GrandObjects
    */

class Course extends BackboneModel{

    static $evalMap = array(5022  => "Contact time was used effectively",
                            23    => "I am motivated to learn more about these subject areas",
                            24    => "I increased my knowledge of the subject areas in this course",
                            25	  => "Overall, the quality of the course content was excellent",
                            221   => "Overall, this instructor was excellent",
                            21    => "The goals and objectives of the course were clear",
                            5674  => "The instructor communicated effectively",
                            26    => "The instructor provided constructive feedback throughout this course",
                            9     => "The instructor treated the students with respect",
                            51    => "The instructor was well prepared",
                            13025 => "Overall, the lab component was excellent",                                                                                
                            3009  => "The teaching assistant treated students with respect",
                            13674 => "The teaching assistant communicated effectively",                                                                   
                            3221  => "Overall, the teaching assistant was excellent",
                            3026  => "The teaching assistant provided constructive feedback throughout this course",
                            3051  => "The teaching assistant was well prepared",
                            674   => "The instructor spoke clearly",
                            22    => "In-class time was used effectively",
                            3674  => "The teaching assistant spoke clearly");

    static $cache = array();
    static $userCoursesCache = array();
    
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

    // Constructor
    function Course($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->term = $data[0]['Term'];
            $this->term_string = $data[0]['term_string'];
            $this->shortDesc = $data[0]['Short Desc'];
            $this->classNbr = $data[0]['Class Nbr'];
            $this->subject = $data[0]['Subject'];
            $this->catalog = $data[0]['Catalog'];
            $this->component = $data[0]['Component'];
            $this->sect = $data[0]['Sect'];
            $this->descr = $data[0]['Descr'];
            $this->startDate = $data[0]['Start Date'];
            $this->endDate = $data[0]['End Date'];
            $this->capEnrl = $data[0]['Cap Enrl'];
            $this->totEnrl = $data[0]['Tot Enrl'];
            $this->courseDescr = $data[0]['Course Descr'];
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
        if(Cache::exists("course_$id")){
            $data = Cache::fetch("course_$id");
        }
        else{
            $data = DBFunctions::select(array('grand_courses'),
                                        array('id', 
                                              'Term', 
                                              'term_string', 
                                              '`Short Desc`', 
                                              '`Class Nbr`',
                                              'Subject',
                                              'Catalog',
                                              'Component',
                                              'Sect',
                                              'Descr',
                                              '`Start Date`',
                                              '`End Date`',
                                              '`Cap Enrl`',
                                              '`Tot Enrl`',
                                              '`Course Descr`'),
                                        array('id' => EQ($id)));
            Cache::store("course_$id", $data);
        }
        $course = new Course($data);
        self::$cache[$course->id] = &$course;
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
                                    array('id', 
                                          'Term', 
                                          'term_string', 
                                          '`Short Desc`', 
                                          '`Class Nbr`',
                                          'Subject',
                                          'Catalog',
                                          'Component',
                                          'Sect',
                                          'Descr',
                                          '`Start Date`',
                                          '`End Date`',
                                          '`Cap Enrl`',
                                          '`Tot Enrl`',
                                          '`Course Descr`'),
                                    array('Subject' => LIKE("%$subject%"),
                                          'Catalog' => LIKE("%$catalog%")));
        $courses = array();
        foreach($data as $row){
            $course = new Course(array($row));
            //self::$cache[$course->id] = &$course;
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
                                    array('id', 
                                          'Term', 
                                          'term_string', 
                                          '`Short Desc`', 
                                          '`Class Nbr`',
                                          'Subject',
                                          'Catalog',
                                          'Component',
                                          'Sect',
                                          'Descr',
                                          '`Start Date`',
                                          '`End Date`',
                                          '`Cap Enrl`',
                                          '`Tot Enrl`',
                                          '`Course Descr`'),
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
                                    array('id', 
                                          'Term', 
                                          'term_string', 
                                          '`Short Desc`', 
                                          '`Class Nbr`',
                                          'Subject',
                                          'Catalog',
                                          'Component',
                                          'Sect',
                                          'Descr',
                                          '`Start Date`',
                                          '`End Date`',
                                          '`Cap Enrl`',
                                          '`Tot Enrl`',
                                          '`Course Descr`'),
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
        if($me->isLoggedIn() 
            && $this->subject != "" 
            && $this->catalog != ""){
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
            Cache::delete("course_{$this->id}");
        }
    }

    /**
     * Returns True if the course is updated correctly to the course table in the database
     * @return boolean True if the database accepted the updated course
    */
    function update(){
        $me = Person::newFromWGUser();
        if($me->isLoggedIn()
            && $this->subject != ""
            && $this->catalog != ""){
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
            Cache::delete("course_{$this->id}");
        }
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

    function getUserCourses($id){
        if(!isset(self::$userCoursesCache[$id])){
            $sql = "SELECT DISTINCT course_id
                    FROM `grand_user_courses`
                    WHERE (user_id = '$id')";
            $data = DBFunctions::execSQL($sql);
            $courses = array();
            foreach($data as $row){
                $course = Course::newFromId($row['course_id']);
                $sect = str_replace("SEM", "C", str_replace("LAB", "B", str_replace("LEC", "A", $course->sect)));
                $courses["{$course->subject} {$course->catalog} {$course->startDate} {$course->component} {$sect}"] = $course;
            }
            ksort($courses);
            self::$userCoursesCache[$id] = $courses;
        }
        return self::$userCoursesCache[$id];
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
        return array('id' => $this->id,
                     'term' => $this->term,
                     'term_string' => $this->term_string,
                     'shortDesc' => $this->shortDesc,
                     'classNbr' => $this->classNbr,
                     'subject' => $this->subject,
                     'catalog' => $this->catalog,
                     'component' => $this->component,
                     'sect' => $this->sect,
                     'descr' => $this->descr,
                     'startDate' => $this->startDate,
                     'endDate' => $this->endDate,
                     'capEnrl' => $this->capEnrl,
                     'totEnrl' => $this->totEnrl,
                     'courseDescr' => $this->courseDescr,
                     'courseName' => $this->courseName);
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
    
    function getCalendarString(){
        $data = DBFunctions::select(array('grand_course_calendar'),
                                    array('hours'),
                                    array('subject' => EQ($this->subject),
                                          'catalog' => EQ($this->catalog)));
        return @$data[0]['hours'];
    }

    function getTermUsingStartMonth($month){
        if($month == "Sep"){
            return "Fall";
        }
        else if($month == "Jan"){
            return "Winter";
        }
        else if($month == "Apr" || $month == "May"){
            return "Spring";
        }
        else{
            return "Summer";
        }
    }

}

?>
