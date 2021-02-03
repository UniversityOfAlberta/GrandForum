<?php

class UploadCCVAPI extends API{

    static $diplomaMap = array("00000000000000000000000000000071" => "Undergraduate",
                               "00000000000000000000000000000072" => "Masters Student",
                               "00000000000000000000000000000073" => "PhD Student",
                               "00000000000000000000000000000074" => "PostDoc",
                               "00000000000000000000000000000081" => "Masters Student",
                               "00000000000000000000000000000083" => "Undergraduate",
                               "00000000000000000000000000000084" => "Undergraduate",
                               "00000000000000000000000000000085" => "Masters Student",
                               "00000000000000000000000000000086" => "PhD Student");
                               
    static $genderMap = array("00000000000000000000000000000282" => "Male",
                              "00000000000000000000000000000283" => "Female",
                              "00000000000000000000000000000284" => "");
                              
    static $honorificMap = array("00000000000000000000000000000317" => "Dr.",
                                 "00000000000000000000000000000318" => "Mr.",
                                 "00000000000000000000000000000319" => "Mrs.",
                                 "00000000000000000000000000000320" => "Miss",
                                 "00000000000000000000000000000321" => "Professor",
                                 "00000000000000000000000000000322" => "Reverend");
                                 
    static $languageMap = array("00000000000000000000000000000054" => "English",
                                "00000000000000000000000000000055" => "French");

    var $structure = null;

    function __construct(){
        
    }

    function processParams($params){
        
    }
    
    /**
     * Creates a new Product if it doesn't already exist
     * @param Person $person The Person creating the product
     * @param array $paper The array containing the ccv data for the Product
     * @param string $category The category of the new Product
     * @param string $type The type of the new Product
     * @param string $ccv_id The id of the Product in the ccv
     * @return Product the new Product
     */
    function createProduct($person, $paper, $category, $type, $ccv_id){
        $checkProduct = Product::newFromCCVId($ccv_id);
        if($checkProduct->getId() != 0){
            // Make sure that this entry was not already entered
            return null;
        }
        $checkProduct = Product::newFromTitle($paper['title']);
        if($checkProduct->getId() != 0 && 
           $checkProduct->getCategory() == $category &&
           $checkProduct->getType() == $type){
            // Make sure that a product with the same title/category/type does not already exist
            return null;
        }
        $structure = $this->structure['categories'][$category]['types'][$type];
        $product = new Product(array());
        $product->title = str_replace("&#39;", "'", $paper['title']);
        $product->category = $category;
        $product->type = $type;
        $product->status = (isset($structure['ccv_status'][$paper['status']])) ? $structure['ccv_status'][$paper['status']] : "Published";
        $product->date = "{$paper['date_year']}-{$paper['date_month']}-01";
        $product->data = array();
        $product->projects = array();
        $product->authors = array();
        if(!isset($_POST['id'])){
            $product->access_id = $person->getId();
        }
        else{
            $product->access_id = 0;
        }
        $product->access = "Public";
        $product->ccv_id = $ccv_id;
        $authors1 = explode(",", $paper['authors']);
        $authors2 = explode(" and ", $paper['authors']);
        $commaFirstLast = false;
        foreach($authors1 as $author){
            if(strstr(trim($author), " ") === false){
                // Probably using commas to separate first/last name... *sigh*
                $commaFirstLast = true;
            }
        }
        if($commaFirstLast){
            $authors = $authors2;
        }
        else{
            $authors = $authors1;
        }
        foreach($authors as $author){
            if(strstr($author, " and ") !== false){
                $exploded = explode(" and ", $author);
                $author = $exploded[0];
            }
            if($commaFirstLast){
                $names = explode(",", $author);
                $last = @trim($names[0]);
                $first = @trim($names[1]);
                $author = "$first $last";
            }
            $obj = new stdClass;
            $obj->name = trim($author);
            $obj->fullname = trim($author);
            $product->authors[] = $obj;
        }
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['ccvtk'] == $key){
                        if($dkey == 'peer_reviewed'){
                            $product->data[$dkey] = ($field) ? "Yes" : "No";
                        }
                        else{
                            $product->data[$dkey] = $field;
                        }
                        break;
                    }
                }
            }
        }
        $status = $product->create();
        if($status){
            $product = Product::newFromId($product->getId());
            return $product;
        }
        else{
            return null;
        }
    }
    
    /**
     * Creates or updates an HQP
     * @param Person $supervisor The supervisor for the HQP
     * @param array $hqp The array containing the ccv data for the HQP
     * @return boolean The status of the creation
     */
    function createHQP($supervisor, $hqp){
        $names = explode(", ", $hqp['name']);
        if(count($names) > 1){
            $first = str_replace(" ", "", $names[1]);
            $last = str_replace(" ", "", $names[0]);
        }
        else{
            $names = explode(" ", $hqp['name']);
            if(count($names) > 1){
                $first = str_replace(" ", "", $names[0]);
                $last = str_replace(" ", "", $names[1]);
            }
            else{
                return false;
            }
        }
        
        // Clean up username
        $first = Person::cleanName($first);
        $last = Person::cleanName($last);
        
        $person = Person::newFromName("$first.$last");
        $status = false;
        if($person->getId() == 0){
            // User Does not exist yet
            $person->name = "{$first}.{$last}";
            $person->realname = "{$first} {$last}";
            $person->email = "";
            $status = $person->create();
            $person = Person::newFromName("{$first}.{$last}");
        }
        if($person->getId() != 0){
            // User exists (will exist if creation was successful as well)
            if($hqp['start_year'] == "" && $hqp['start_month'] == ""){
                $start_date = "{$hqp['degree_start_year']}-".str_pad($hqp['degree_start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            }
            else{
                $start_date = "{$hqp['degree_start_year']}-".str_pad($hqp['degree_start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            }
            if(CommonCV::getCaptionFromValue($hqp['status'], "Degree Status") == "In Progress"){
                // HQP is still active
                $end_date = "0000-00-00 00:00:00";
            }
            else{
                if($hqp['end_year'] == "" && $hqp['end_month'] == "" && 
                   $hqp['degree_end_year'] == "" && $hqp['degree_end_month'] == ""){
                    $end_date = "{$hqp['degree_expected_year']}-".str_pad($hqp['degree_expected_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
                }
                else if($hqp['end_year'] == "" && $hqp['end_month'] == ""){
                    $end_date = "{$hqp['degree_end_year']}-".str_pad($hqp['degree_end_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
                }
                else{
                    $end_date = "{$hqp['end_year']}-".str_pad($hqp['end_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
                }
            }
            $university = Person::getDefaultUniversity();
            $universities = Person::getAllUniversities();
            $uniFound = false;
            foreach($universities as $id => $uni){
                if($uni == $hqp['institution']){
                    $university = $id;
                    $uniFound = true;
                    break;
                }
                if($uni == $university){
                    $university = $id;
                }
            }
            if(!$uniFound && $hqp['institution'] != ""){
                // University not Found, so add it
                $otherId = DBFunctions::select(array('grand_provinces'),
                                               array('id'),
                                               array('province' => EQ('Other')));
                $otherId = (isset($otherId[0])) ? $otherId[0]['id'] : 0;
                DBFunctions::insert('grand_universities',
                                    array('university_name' => $hqp['institution'],
                                          'province_id'     => $otherId,
                                          '`order`'    => 10001));
                $university = DBFunctions::select(array('grand_universities'),
                                                  array('university_id'),
                                                  array('university_name' => EQ($hqp['institution'])));
                $university = (isset($university[0])) ? $university[0]['university_id'] : Person::getDefaultUniversity();
            }
            $position = Person::getDefaultPosition();
            $positions = Person::getAllPositions();
            foreach($positions as $id => $pos){
                if(isset(self::$diplomaMap[$hqp['diploma']]) && $pos == self::$diplomaMap[$hqp['diploma']]){
                    $position = $id;
                    break;
                }
                if($pos == $position){
                    $position = $id;
                }
            }
            /*if(count(DBFunctions::select(array('grand_roles'), 
                                         array('*'), 
                                         array('user_id'    => EQ($person->getId()),
                                               'role'       => EQ(HQP),
                                               'start_date' => EQ($start_date)))) == 0){
                // Make sure this exact entry is not already entered (allow end_date to be different)
                DBFunctions::insert('grand_roles',
                                    array('user_id'     => $person->getId(),
                                          'role'        => HQP,
                                          'start_date'  => $start_date,
                                          'end_date'    => $end_date));
            }*/
            if(count(DBFunctions::select(array('grand_relations'),
                                         array('*'),
                                         array('user1'      => EQ($supervisor->getId()),
                                               'user2'      => EQ($person->getId()),
                                               'type'       => EQ('Supervises'),
                                               'start_date' => EQ($start_date)))) == 0){
                // Make sure this exact entry is not already entered (allow end_date to be different)
                DBFunctions::insert('grand_relations',
                                    array('user1'       => $supervisor->getId(),
                                          'user2'       => $person->getId(),
                                          'type'        => 'Supervises',
                                          'start_date'  => $start_date,
                                          'end_date'    => $end_date));
            }
            if(count(DBFunctions::select(array('grand_user_university'),
                                         array('*'),
                                         array('user_id'       => EQ($person->getId()),
                                               'university_id' => EQ($university),
                                               'position_id'   => EQ($position),
                                               'start_date'    => EQ($start_date)))) == 0){
                // Make sure this exact entry is not already entered (allow department and end_date to be different)
                DBFunctions::insert('grand_user_university',
                                    array('user_id'       => $person->getId(),
                                          'university_id' => $university,
                                          'department'    => $supervisor->getDepartment(),
                                          'position_id'   => $position,
                                          'start_date'    => $start_date,
                                          'end_date'      => $end_date));
            }
            $status = true;
        }
        return $status;
    }
    
    /**
     * Creates new contributions from the given ccv data
     * @param Person $person The Person to update
     * @param array $funding The array containing the funding ccv data
     * @return array The array of funding contributions which were successful
     */
    function updateFunding($person, $funding){
        global $wgMessage;
        $return = array();
        foreach($funding as $fund){
            $contribution = Contribution::newFromName($fund['funding_title']);
            unset($_POST['id']);
            if($contribution->getName() != "" && ($contribution->getAccessId() == 0 || $contribution->getAccessId() == $person->getId())){
                // Contribution exists so update it
                $_POST['id'] = $contribution->getId();
                $projects = new Collection($contribution->getProjects());
                $_POST['projects'] = implode(", ", $projects->pluck('name'));
            }
            else {
                $_POST['projects'] = "";
            }
            $_POST['title'] = $fund['funding_title'];
            $users = array();
            $users[] = $person->getName();
            foreach($fund['co_holders'] as $holder){
                $name = $holder['name'];
                if(strstr($name, ",") !== false){
                    $names = explode(",", $name);
                    $name = trim($names[1])." ".trim($names[0]);
                }
                $users[] = $name;
            }
            $_POST['users'] = implode(", ", $users);
            
            switch(CommonCV::getCaptionFromValue($fund['funding_type'], "Funding Type")){
                default:
                case "Grant":
                    $_POST['type'][0] = "grnt";
                    break;
                case "Research Chair":
                    $_POST['type'][0] = "char";
                    break;
                case "Scholarship":
                    $_POST['type'][0] = "scho";
                    break;
                case "Fellowship":
                    $_POST['type'][0] = "fell";
                    break;
                case "Contract":
                    $_POST['type'][0] = "cont";
                    break;
            }
            $_POST['subtype'][0] = "none";
            $_POST['access_id'] = $person->getId();
            $_POST['start_date'] = $fund['start_year']."-".str_pad($fund['start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            $_POST['end_date'] = $fund['end_year']."-".str_pad($fund['end_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            
            $_POST['partners'] = CommonCV::getCaptionFromValue($fund['funder'], "Funding Organization");
            if($_POST['partners'] == "" || $_POST['partners'] == "?"){
                $_POST['partners'] = $fund['otherfunder'];
            }
            $_POST['partners'] = str_replace(",", "&#44;", $_POST['partners']);
            
            // Figure out how far into the funding period we are
            $date1 = new DateTime($_POST['start_date']);
            $date2 = new DateTime();
            $date3 = new DateTime($_POST['end_date']);
            if($date2->getTimestamp() > $date3->getTimestamp()){
                $date2 = $date3;
            }
            $interval = $date1->diff($date2);
            $nYears = max(1, $interval->y + 1);
            
            // Adjust the amount received based on how far into the funding period
            if($fund['received_amount'] == ""){
                $_POST['cash'][0] = $fund['total_amount']/$nYears;
            }
            else{
                if(intval($fund['received_amount']) <= 100){
                    // Assume a percent
                    $_POST['cash'][0] = (($fund['received_amount']/100)*$fund['total_amount'])/$nYears;
                }
                else{
                    $_POST['cash'][0] = $fund['received_amount']/$nYears;
                }
                    
            }
            $_POST['kind'][0] = 0;
            
            $_POST['description'] = "";
            AddContributionAPI::processParams(array());
            $status = APIRequest::doAction('AddContribution', true);
            if($status == ""){
                $return[] = $fund;
            }
        }
        // Workaround to disable the error messages on page load
        $wgMessage->clearCookies();
        return $return;
    }
    
    /**
     * Fills in some of the personal fields from the CCV
     * @param Person $person The Person to update
     * @param array $info The array containing the personal info ccv data
     * @return boolean The status of the update
     */
    function updatePersonalInfo($person, $info){
        $person->honorific = (isset(self::$honorificMap[$info['greeting']])) ? self::$honorificMap[$info['greeting']] : "";
        $person->gender = (isset(self::$genderMap[$info['sex']])) ? self::$genderMap[$info['sex']] : "";
        $person->language = (isset(self::$languageMap[$info['correspondence_language']])) ? self::$languageMap[$info['correspondence_language']] : "";
        $person->firstName = (isset($info['first_name'])) ? $info['first_name'] : "";
        $person->lastName = (isset($info['last_name'])) ? $info['last_name'] : "";
        $person->middleName = (isset($info['middle_name'])) ? $info['middle_name'] : "";
        $person->prevFirstName = (isset($info['prev_first_name'])) ? $info['prev_first_name'] : "";
        $person->prevLastName = (isset($info['prev_last_name'])) ? $info['prev_last_name'] : "";
        
        DBFunctions::delete('grand_user_languages',
                            array('user_id' => EQ($person->getId())));
        if(isset($info['languages']) && count($info['languages']) > 0){
            foreach($info['languages'] as $language){
                $lang = CommonCV::getCaptionFromValue($language['language'], "Language");
                DBFunctions::insert('grand_user_languages',
                                    array('user_id'        => $person->getId(),
                                          'language'       => $lang,
                                          'can_read'       => $language['read'],
                                          'can_write'      => $language['write'],
                                          'can_speak'      => $language['speak'],
                                          'can_understand' => $language['understand'],
                                          'can_review'     => $language['peer_review']));
            }
        }
        DBFunctions::delete('grand_user_addresses',
                            array('user_id' => EQ($person->getId())));
        if(isset($info['addresses']) && count($info['addresses']) > 0){
            foreach($info['addresses'] as $address){
                $addr = CommonCV::getCaptionFromValue($address['type'], "Address Type");
                DBFunctions::insert('grand_user_addresses',
                                    array('user_id'           => $person->getId(),
                                          'type'              => $addr,
                                          'line1'             => $address['line1'],
                                          'line2'             => $address['line2'],
                                          'line3'             => $address['line3'],
                                          'line4'             => $address['line4'],
                                          'line5'             => $address['line5'],
                                          'city'              => $address['city'],
                                          'code'              => $address['postal_code'],
                                          'country'           => $address['location_country'],
                                          'province'          => $address['location_subdivision'],
                                          'start_date'        => "{$address['start_year']}-{$address['start_month']}-{$address['start_day']} 00:00:00",
                                          'end_date'          => "{$address['end_year']}-{$address['end_month']}-{$address['end_day']} 00:00:00",
                                          'primary_indicator' => $address['primary_indicator']));
            }
        }
        DBFunctions::delete('grand_user_telephone',
                            array('user_id' => EQ($person->getId())));
        if(isset($info['telephone']) && count($info['telephone']) > 0){
            foreach($info['telephone'] as $phone){
                $type = CommonCV::getCaptionFromValue($phone['type'], "Phone Type");
                DBFunctions::insert('grand_user_telephone',
                                    array('user_id'           => $person->getId(),
                                          'type'              => $type,
                                          'country_code'      => $phone['country_code'],
                                          'area_code'         => $phone['area_code'],
                                          'number'            => $phone['number'],
                                          'extension'         => $phone['extension'],
                                          'start_date'        => "{$phone['start_year']}-{$phone['start_month']}-{$phone['start_day']} 00:00:00",
                                          'end_date'          => "{$phone['end_year']}-{$phone['end_month']}-{$phone['end_day']} 00:00:00",
                                          'primary_indicator' => $phone['primary_indicator']));
            }
        }
        return $person->update();
    }
    
    /**
     * Adds the employment information to the person's history
     * @param Person $person The Person to update
     * @param array $employment The array containing the employment ccv data
     * @return boolean The status of the update
     */
    function updateEmployment($person, $employment){
        $status = true;
        foreach($employment as $emp){
            // Reload the University/Position Data
            $universities = Person::getAllUniversities();
            $positions = Person::getAllPositions();
            
            $start_date = $emp['start_year']."-".str_pad($emp['start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            if($emp['end_year'] == "" || $emp['end_month'] == ""){
                $end_date = "0000-00-00 00:00:00";
            }
            else{
                $end_date = $emp['end_year']."-".str_pad($emp['end_month'], 2, '0', STR_PAD_LEFT)."-".str_pad(cal_days_in_month(CAL_GREGORIAN, $emp['end_month'], $emp['end_year']), 2, '0', STR_PAD_LEFT)." 00:00:00";
            }
            
            $department = $emp['department'];
            $university = Person::getDefaultUniversity();
            $uniFound = false;
            foreach($universities as $id => $uni){
                if($uni == $emp['organization_name']){
                    $university = $id;
                    $uniFound = true;
                    break;
                }
                if($uni == $university){
                    $university = $id;
                }
            }
            if(!$uniFound && $emp['organization_name'] != ""){
                // University not Found, so add it
                $otherId = DBFunctions::select(array('grand_provinces'),
                                               array('id'),
                                               array('province' => EQ('Other')));
                $otherId = (isset($otherId[0])) ? $otherId[0]['id'] : 0;
                DBFunctions::insert('grand_universities',
                                    array('university_name' => $emp['organization_name'],
                                          'province_id'     => $otherId,
                                          '`order`'    => 10001));
                $university = DBFunctions::select(array('grand_universities'),
                                                  array('university_id'),
                                                  array('university_name' => EQ($emp['organization_name'])));
                $university = (isset($university[0])) ? $university[0]['university_id'] : Person::getDefaultUniversity();
            }
            $position = Person::getDefaultPosition();
            $rank = ($emp['rank'] != "") ? CommonCV::getCaptionFromValue($emp['rank'], "Academic Rank") : $emp['title'];
            $posFound = false;
            foreach($positions as $id => $pos){
                if($pos == $rank){
                    $position = $id;
                    $posFound = true;
                    break;
                }
                if($pos == $position){
                    $position = $id;
                }
            }
            if(!$posFound){
                // Position not Found, so add it
                DBFunctions::insert('grand_positions',
                                    array('position' => $rank,
                                          '`order`'    => 10001));
                $position = DBFunctions::select(array('grand_positions'),
                                                array('position_id'),
                                                array('position' => EQ($rank)));
                $position = (isset($position[0])) ? $position[0]['position_id'] : Person::getDefaultPosition();
            }
            $data = DBFunctions::select(array('grand_user_university'),
                                        array('*'),
                                        array('user_id'       => EQ($person->getId()),
                                              'university_id' => EQ($university),
                                              'department'    => EQ($department),
                                              'position_id'   => EQ($position),
                                              'end_date'      => EQ($end_date)));
            if(count($data) > 0){
                // This is the current university which is in the system, just update the start date
                $status = $status && 
                          DBFunctions::update('grand_user_university',
                                              array('start_date' => $start_date),
                                              array('id' => EQ($data[0]['id'])));
            }
            else if(count(DBFunctions::select(array('grand_user_university'),
                                              array('*'),
                                              array('user_id'       => EQ($person->getId()),
                                                    'university_id' => EQ($university),
                                                    'department'    => EQ($department),
                                                    'position_id'   => EQ($position),
                                                    'start_date'    => EQ($start_date)))) == 0){
                // Make sure this exact entry is not already entered (allow department and end_date to be different)
                $status = $status && 
                          DBFunctions::insert('grand_user_university',
                                              array('user_id'       => $person->getId(),
                                                    'university_id' => $university,
                                                    'department'    => $department,
                                                    'position_id'   => $position,
                                                    'start_date'    => $start_date,
                                                    'end_date'      => $end_date));
            }
        }
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
        $ccv = $_FILES['ccv'];
        if($ccv['type'] == "text/xml" && $ccv['size'] > 0){
            $this->structure = Product::structure();
            $dir = dirname(__FILE__);
            $error = "";
            require_once($dir."/../../Classes/CCCVTK/common-cv.lib.php");
            $file_contents = file_get_contents($ccv['tmp_name']);
            $dom = new DOMDocument();
            $valid = $dom->loadXML($file_contents);
            $json = array('created' => array(),
                          'error' => array());
            if($valid){
                $cv = new CommonCV($ccv['tmp_name']);
                $createdProducts = array();
                $errorProducts = array();
                if(isset($_POST['publications'])){
                    $conferencePapers = $cv->getConferencePapers();
                    $journalPapers = $cv->getJournalPapers();
                    $bookChapters = $cv->getBookChapters();
                    $reviewedConferencePapers = $cv->getReviewedConferencePapers();
                    $reviewedJournalPapers = $cv->getReviewedJournalPapers();
                   
                    foreach($conferencePapers as $ccv_id => $paper){
                        $product = $this->createProduct($person, $paper, "Publication", "Conference Paper", $ccv_id);
                        if($product != null){
                            $createdProducts[] = $product;
                        }
                        else{
                            $errorProducts[] = $paper;
                        }
                    }
                    foreach($journalPapers as $ccv_id => $paper){
                        $product = $this->createProduct($person, $paper, "Publication", "Journal Paper", $ccv_id);
                        if($product != null){
                            $createdProducts[] = $product;
                        }
                        else{
                            $errorProducts[] = $paper;
                        }
                    }
                    foreach($bookChapters as $ccv_id => $paper){
                        $product = $this->createProduct($person, $paper, "Publication", "Book Chapter", $ccv_id);
                        if($product != null){
                            $createdProducts[] = $product;
                        }
                        else{
                            $errorProducts[] = $paper;
                        }
                    }
                }
                if(isset($_POST['supervises'])){
                    $supervises = $cv->getStudentsSupervised();
                    foreach($supervises as $hqp){
                        $status = $this->createHQP($person, $hqp);
                        if($status){
                            $json['supervises'][] = $hqp;
                        }
                    }
                }
                if(isset($_POST['funding'])){
                    $funding = $cv->getFunding();
                    $successful = $this->updateFunding($person, $funding);
                    $json['funding'] = $successful;
                    $json['fundingFail'] = count($funding) - count($successful);
                }
                if(isset($_POST['info'])){
                    $info = $cv->getPersonalInfo();
                    $status = $this->updatePersonalInfo($person, $info);
                    if($status){
                        $json['info'] = $info;
                    }
                }
                if(isset($_POST['employment'])){
                    $employment = $cv->getEmployment();
                    $status = $this->updateEmployment($person, $employment);
                    if($status){
                        $json['employment'] = $employment;
                    }
                }
            }
            else{
                $error = "There was an error reading the CCV file";
            }
            foreach($createdProducts as $product){
                $json['created'][] = $product->toArray();
            }
            foreach($errorProducts as $product){
                $json['error'][] = $product;
            }
            if($error == ""){
                DBFunctions::begin();
                DBFunctions::delete('grand_ccv',
                                    array('user_id' => $person->getId()),
                                    true);
                DBFunctions::insert('grand_ccv',
                                    array('user_id' => $person->getId(),
                                          'ccv' => $file_contents),
                                    true);
                DBFunctions::commit();
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
                        parent.ccvUploaded([], "The uploaded file was not in XML format");
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
