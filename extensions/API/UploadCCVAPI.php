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

    var $structure = null;

    function UploadCCVAPI(){
        
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
        $product->status = (isset($structure['ccv_status'][$paper['status']])) ? $structure['ccv_status'][$paper['status']] : "Rejected";
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
        $product->ccv_id = $ccv_id;
        $authors = explode(",", $paper['authors']);
        foreach($authors as $author){
            $obj = new stdClass;
            $obj->name = trim($author);
            $product->authors[] = $obj;
        }
        foreach($paper as $key => $field){
            if($field != ""){
                foreach($structure['data'] as $dkey => $dfield){
                    if($dfield['ccvtk'] == $key){
                        $product->data[$dkey] = $field;
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
     * @return boolean Returns the status of the creation
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
        
        $person = Person::newFromName("$first.$last");
        $status = false;
        if($person->getId() == 0){
            // User Does not exist yet
            $person->name = "{$first}.{$last}";
            $person->realname = "{$names[1]} {$names[0]}";
            $person->email = "";
            $status = $person->create();
            $person = Person::newFromName("{$first}.{$last}");
        }
        if($person->getId() != 0){
            // User exists (will exist if creation was successful as well)
            $start_date = "{$hqp['start_year']}-".str_pad($hqp['start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            if(CommonCV::getCaptionFromValue($hqp['status'], "Degree Status") == "In Progress"){
                // HQP is still active
                $end_date = "0000-00-00 00:00:00";
            }
            else{
                $end_date = "{$hqp['end_year']}-".str_pad($hqp['end_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            }
            $university = Person::getDefaultUniversity();
            $universities = Person::getAllUniversities();
            foreach($universities as $id => $uni){
                if($uni == $hqp['institution']){
                    $university = $id;
                    break;
                }
                if($uni == $university){
                    $university = $id;
                }
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
            if(count(DBFunctions::select(array('grand_roles'), 
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
            }
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
            MailingList::subscribeAll($person);
        }
        return $status;
    }
    
    /**
     * Fills in some of the personal fields from the CCV
     * @param Person $person The Person to update
     * @param array $info The array containing the personal info ccv data
     * @return boolean Returns the status of the update
     */
    function updatePersonalInfo($person, $info){
        $person->gender = (isset(self::$genderMap[$info['sex']])) ? self::$genderMap[$info['sex']] : "";
        return $person->update();
    }
    
    /**
     * Adds the employment information to the person's history
     * @param Person $person The Person to update
     * @param array $employment The array containing the employment ccv data
     * @return boolean Returns the status of the update
     */
    function updateEmployment($person, $employment){
        $universities = Person::getAllUniversities();
        $positions = Person::getAllPositions();
        
        $status = true;
        foreach($employment as $emp){
            $start_date = $emp['start_year']."-".str_pad($emp['start_month'], 2, '0', STR_PAD_LEFT)."-01 00:00:00";
            $end_date = $emp['end_year']."-".str_pad($emp['end_month'], 2, '0', STR_PAD_LEFT)."-".cal_days_in_month(CAL_GREGORIAN,$emp['end_month'],$emp['end_year'])." 00:00:00";
            if($emp['end_year'] == "" || $emp['end_month'] == ""){
                $end_date = "0000-00-00 00:00:00";
            }
            $department = $emp['department'];
            $university = Person::getDefaultUniversity();
            foreach($universities as $id => $uni){
                if($uni == $emp['organization_name']){
                    $university = $id;
                    break;
                }
                if($uni == $university){
                    $university = $id;
                }
            }
            $position = Person::getDefaultPosition();
            foreach($positions as $id => $pos){
                if($pos == CommonCV::getCaptionFromValue($emp['rank'], "Academic Rank")){
                    $position = $id;
                    break;
                }
                if($pos == $position){
                    $position = $id;
                }
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
                    $json['funding'] = $funding;
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
            $obj = json_encode($json);
            echo <<<EOF
            <html>
                <head>
                    <script type='text/javascript'>
                        parent.ccvUploaded($obj, "$error");
                        console.log($obj);
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
