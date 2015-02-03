<?php
require_once("CCVImport/CCVImport.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['CCVExport'] = 'CCVExport';
$wgExtensionMessagesFiles['CCVExport'] = $dir . 'CCVExport.i18n.php';
$wgSpecialPageGroups['CCVExport'] = 'network-tools';

$degree_map = 
  array('Masters Student'=>array("00000000000000000000000000000072","Master's Thesis"),
        'PhD Student'=>array("00000000000000000000000000000073","Doctorate"),
        'Undergraduate'=>array("00000000000000000000000000000071","Bachelor's"),
        'PostDoc'=>array("00000000000000000000000000000074","Post-doctorate"));

function runCCVExport($par) {
    CCVExport::run($par);
}

class CCVExport extends SpecialPage {

    function __construct() {
        wfLoadExtensionMessages('CCVExport');
        SpecialPage::SpecialPage("CCVExport", HQP.'+', true, 'runCCVExport');
    }
    
    static function run(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
        global $userID, $wgDBname;
      
        $userID = $wgUser->getId();
        #$userID = 1392;  // TEST
        #$userID = 3;     // TEST

        if(isset($_GET['getXML'])){
            $table_type = $_GET['getXML'];
            $xml = CCVExport::exportXML();
            $wgOut->disable();
            ob_clean();

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");;
            header("Content-Disposition: attachment;filename=export.xml"); 
            header("Content-Transfer-Encoding: binary ");
            echo $xml;
            exit;
        }
      
        $wgOut->setPageTitle("Export To CCV");

        $wgOut->addHTML("<p><a target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:CCVExport?getXML'>[Download XML]</a></p>");

        # Display export preview
        $xml = CCVExport::exportXML();
        $xml = str_replace("<", "&lt;", $xml); # show tags as text
        $xml = str_replace("\n", "<br/>", $xml); # show newlines
        $xml = str_replace(" ", "&nbsp;", $xml); # show indents
        #$wgOut->addHTML('<p><b>userID</b> '.$userID);    // TEST
        #$wgOut->addHTML('<p><b>dbase</b> '.$wgDBname);   // TEST
        # 'pre-wrap' for extra-long lines:
        $wgOut->addHTML('<p><pre style="white-space:pre-wrap;">'.$xml."</pre></p>");
    }
  
    static function getLovId($cat, $val, $default){
        require_once(dirname(__FILE__) . "/../../Classes/CCCVTK/constants.lib.php");
        global $CCV_CONST;
        if(isset($CCV_CONST[$cat][$val])){
            return @$CCV_CONST[$cat][$val];
        }
        return @$CCV_CONST[$cat][$default];
    }
    
    static function getLovVal($cat, $val, $default){
        require_once(dirname(__FILE__) . "/../../Classes/CCCVTK/constants.lib.php");
        global $CCV_CONST;
        if(isset($CCV_CONST[$cat][$val])){
            return $val;
        }
        return $default;
    }

    static function exportXML(){
        global $wgOut, $wgUser;
        global $userID;

        // Template Files
        $map_file = getcwd()."/extensions/GrandObjects/Products.xml";
        $hqp_file = getcwd()."/extensions/CCVExport/templates/HQP.xml";
        $id_file =  getcwd()."/extensions/CCVExport/templates/Identification.xml";
        $lang_file = getcwd()."/extensions/CCVExport/templates/Language.xml";
        $addr_file = getcwd()."/extensions/CCVExport/templates/Address.xml";
        $phone_file = getcwd()."/extensions/CCVExport/templates/Telephone.xml";
        $ccv_tmpl = getcwd()."/extensions/CCVExport/templates/ccv_template.xml";

        // Load the templates
        $map = simplexml_load_file($map_file);
        $hqp_map = simplexml_load_file($hqp_file);
        $id_map = simplexml_load_file($id_file);
        $lang_map = simplexml_load_file($lang_file);
        $addr_map = simplexml_load_file($addr_file);
        $phone_map = simplexml_load_file($phone_file);
        $ccv = simplexml_load_file($ccv_tmpl);

        $person = Person::newFromId($userID); // Set at top in case testing

        $all_products = $person->getPapers("Publication", false, "both");

        $prod_sorted = array();

        foreach($all_products as $p){
            $t = $p->getType();
            if(isset($prod_sorted[$t])){
                $prod_sorted[$t][] = $p;
            } else {
                $prod_sorted[$t] = array();
                $prod_sorted[$t][] = $p;
            }
        }

        $section = $ccv->xpath("section[@id='f589cbc028c64fdaa783da01647e5e3c']/section[@id='2687e70e5d45487c93a8a02626543f64']");
        $res = CCVExport::mapId($person, 
                                $id_map, 
                                $section[0]);
        
        $section = $ccv->xpath("section[@id='f589cbc028c64fdaa783da01647e5e3c']");                    
        foreach($person->getLanguages() as $language){
            $res = CCVExport::mapLanguage($person,
                                          $lang_map,
                                          $language,
                                          $section[0]);
        }
        
        $section = $ccv->xpath("section[@id='f589cbc028c64fdaa783da01647e5e3c']");
        foreach($person->getAddresses() as $address){
            $res = CCVExport::mapAddress($person,
                                         $addr_map,
                                         $address,
                                         $section[0]);
        }
        
        $section = $ccv->xpath("section[@id='f589cbc028c64fdaa783da01647e5e3c']");
        foreach($person->getTelephones() as $phone){
            $res = CCVExport::mapTelephone($person,
                                           $phone_map,
                                           $phone,
                                           $section[0]);
        }

        $counter = 0;
        $section = $ccv->xpath("section[@id='047ec63e32fe450e943cb678339e8102']/section[@id='46e8f57e67db48b29d84dda77cf0ef51']");
        foreach($prod_sorted as $type => $products){
            foreach($products as $product){
                # CCV does not include 'Rejected' Publishing Status
                if($product->getStatus() == 'Rejected'){
                    continue;
                }

                $res = CCVExport::mapItem($person, 
                                          $map->Publications->Publication, 
                                          $product, 
                                          $section[0]);
                $counter += $res;
            }
        }

        $rels = $person->getRelations('Supervises', true);
        $sortedRels = array();
        foreach($rels as $rel){
            $sortedRels[$rel->getStartDate().$rel->getId()] = $rel;
        }
        ksort($sortedRels);
        $sortedRels = array_reverse($sortedRels);
        $ccv->xpath("section[@id='95c29504d0aa4b51b84659cafaf2b38d']/section[@id='90cc172e54904b45948d17cba24d3f25']");
        foreach($sortedRels as $rel){
            $res = CCVExport::mapHQP($person, 
                                     $hqp_map->HQP->data, 
                                     $rel, 
                                     $section[0]);
        }

        # Format and indent the XML
        $dom = new DOMDocument ();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML ( $ccv->asXML() );
        $xml = $dom->saveXML();

        return $xml;
    }
    
    static function mapId($person, $section, $ccv){
        global $wgUser;

        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = $ccv->addChild("field");
            $field->addAttribute("id", $id);
            $field->addAttribute("label", $label);
            switch($id){
                case "5c6f17e8a67241e19667815a9e95d9d0": // Family Name
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $person->getLastName();
                    break;
                case "98ad36fee26a4d6b8953ea764f4fed04": // First Name
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $person->getFirstName();
                    break;
                case "4ca83c1aaa6a42a78eac0290368e70f3": // Middle Name
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $person->getMiddleName();
                    break;
                case "84e9fa08f7334db79ed5310e5f7a961b": // Previous Family Name
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $person->getPrevLastName();
                    break;
                case "ee8beaea41f049d8bcfadfbfa89ac09e": // Title
                    $title = $person->getHonorific();
                    $value = $field->addChild("lov");
                    $value->addAttribute("id", self::getLovId("Title", $title, ""));
                    $field->lov = self::getLovVal("Title", $title, "");
                    break;
                case "0fb359a7d809457d9392bb1ca577f1b3": // Previous First Name
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $person->getPrevFirstName();
                    break;
                case "3d258d8ceb174d3eb2ae1258a780d91b": // Sex
                    $gender = $person->getGender();
                    $value = $field->addChild("lov");
                    $value->addAttribute("id", self::getLovId("Sex", $gender, "No Response"));
                    $field->lov = self::getLovVal("Sex", $gender, "No Response");
                    break;
                case "2b72a344523c467da0c896656b5290c0": // Correspondence language
                    $language = $person->getCorrespondenceLanguage();
                    $value = $field->addChild("lov");
                    $value->addAttribute("id", self::getLovId("Correspondance Language", $language, ""));
                    $field->lov = self::getLovVal("Correspondance Language", $language, "");
                    break;
            }
        }
    }
    
    static function mapLanguage($person, $section, $language, $ccv){
        global $wgUser;
        $sect = $ccv->addChild("section");
        $sect->addAttribute("id", $section['id']);
        $sect->addAttribute("label", $section['label']);
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = $sect->addChild("field");
            $field->addAttribute("id", $id);
            $field->addAttribute("label", $label);
            switch($id){
                case "ee161805b4f941e48f05e050e364e585": // Language
                    $lang = $language->getLanguage();
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Language", $lang, "English"));
                    $field->lov = self::getLovVal("Language", $lang, "English");
                    break;
                case "a9d0f0666e5b47dcb9acb30bd5cab407": // Read
                    $read = ($language->canRead()) ? "Yes" : "No";
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Yes-No", $read, "Yes"));
                    $field->lov = (self::getLovVal("Yes-No", $read, "Yes"));
                    break;
                case "12173f36422446479799578ba07d96c8": // Write
                    $write = ($language->canWrite()) ? "Yes" : "No";
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Yes-No", $write, "Yes"));
                    $field->lov = (self::getLovVal("Yes-No", $write, "Yes"));
                    break;
                case "e670ac0f2c3e48a3b13d487e66ea7889": // Speak
                    $speak = ($language->canSpeak()) ? "Yes" : "No";
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Yes-No", $speak, "Yes"));
                    $field->lov = (self::getLovVal("Yes-No", $speak, "Yes"));
                    break;
                case "aa02c54f1e5b4672a0b96def14e5b02e": // Understand
                    $understand = ($language->canUnderstand()) ? "Yes" : "No";
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Yes-No", $understand, "Yes"));
                    $field->lov = (self::getLovVal("Yes-No", $understand, "Yes"));
                    break;
                case "fc6ac63e9ec04129aec7b26e5a729920": // Review
                    $review = ($language->canReview()) ? "Yes" : "No";
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Yes-No", $review, "Yes"));
                    $field->lov = (self::getLovVal("Yes-No", $review, "Yes"));
                    break;
            }
        }
    }
    
    static function mapAddress($person, $section, $address, $ccv){
        global $wgUser;
        $sect = $ccv->addChild("section");
        $sect->addAttribute("id", $section['id']);
        $sect->addAttribute("label", $section['label']);
        if($address->isPrimary()){
            $sect->addAttribute("primaryIndicator", "true");
        }
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = $sect->addChild("field");
            $field->addAttribute("id", $id);
            $field->addAttribute("label", $label);
            switch($id){
                case "35c302c36fe9479287206171087fb185": // Address Type
                    $addr = $address->getType();
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Address Type", $addr, "Primary Affiliation"));
                    $field->lov = self::getLovVal("Address Type", $addr, "Primary Affiliation");
                    break;
                case "2de0fe4994f546c695a060d68e8e03ca": // Address Line 1
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getLine1();
                    break;
                case "dafdb980e181416abc5e26c0770df662": // Address Line 2
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getLine2();
                    break;
                case "fc390eae1fbc45c89789f2ecbb5bed8e": // Address Line 3
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getLine3();
                    break;
                case "d51e2de9122744489ac2231d85995617": // Address Line 4
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getLine4();
                    break;
                case "5365d87b9ff145d3a8d0d4fc21af57bb": // Address Line 5
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getLine5();
                    break;
                case "499d69637b4148d0a49463a2881e9d09": // City
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getCity();
                    break;
                case "b1071063df03484ebec65cd1a3464438": // Location (Country/Subdivision)
                    $country = $address->getCountry();
                    $province = $address->getProvince();
                    $table = $field->addChild("refTable");
                    $table->addAttribute("refValueId", "00000000000000000000039564242160");
                    $table->addAttribute("label", "Country-Subdivision");
                    $l_country = $table->addChild("linkedWith");
                    $l_subdivision = $table->addChild("linkedWith");
                    $l_country->addAttribute("refOrLovId", "00000000000000000000000000002000");
                    $l_subdivision->addAttribute("refOrLovId", "00000000000000000000000000100000");
                    $l_country->addAttribute("label", "Country");
                    $l_subdivision->addAttribute("label", "Subdivision");
                    $l_country->addAttribute("value", $country);
                    $l_subdivision->addAttribute("value", $province);
                    break;
                case "a41f1e118e61482eb3cdde4aaeb783e8": // Postal/Zip Code
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $address->getPostalCode();
                    break;
                case "b77ff4a2c49247e0af668be52704da91": // Postal/Zip Code
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $value->addAttribute("format", "yyyy-MM-dd");
                    $field->value = substr($address->getStartDate(), 0, 10);
                    break;
                case "4ab2497d7a0f471ebc6a50e32dd4f22d": // Postal/Zip Code
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "Date");
                    $value->addAttribute("format", "yyyy-MM-dd");
                    $field->value = substr($address->getEndDate(), 0, 10);
                    break;
            }
        }
    }
    
    static function mapTelephone($person, $section, $phone, $ccv){
        global $wgUser;
        $sect = $ccv->addChild("section");
        $sect->addAttribute("id", $section['id']);
        $sect->addAttribute("label", $section['label']);
        if($phone->isPrimary()){
            $sect->addAttribute("primaryIndicator", "true");
        }
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = $sect->addChild("field");
            $field->addAttribute("id", $id);
            $field->addAttribute("label", $label);
            switch($id){
                case "ccef121ae875427f829024aabb39fa8c": // Address Type
                    $type = $phone->getType();
                    $lov = $field->addChild("lov");
                    $lov->addAttribute("id", self::getLovId("Phone Type", $type, "Work"));
                    $field->lov = self::getLovVal("Phone Type", $type, "Work");
                    break;
                case "63dedd46a5204cda8257227bbb3b6675": // Country Code
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $phone->getCountryCode();
                    break;
                case "13cdf3a5e13643f5bc74566bf075253c": // Area Code
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $phone->getAreaCode();
                    break;
                case "1ca756fe70964371a2b9f57bdf567a5d": // Telephone Number
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $phone->getPhoneNumber();
                    break;
                case "afe0657785084098bb718345280eb840": // Extension
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $field->value = $phone->getExtension();
                    break;
                case "69c67fae5d4849d08f4f9799ae0a2335": // Start Date
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $value->addAttribute("format", "yyyy-MM-dd");
                    $field->value = substr($phone->getStartDate(), 0, 10);
                    break;
                case "a90e95e1d278467eaf1847464f09f39f": // End Date
                    $value = $field->addChild("value");
                    $value->addAttribute("type", "String");
                    $value->addAttribute("format", "yyyy-MM-dd");
                    $field->value = substr($phone->getEndDate(), 0, 10);
                    break;
            }
        }
    }

    static function mapHQP($person, $section, $rel, $ccv){
        global $wgUser, $degree_map;

        $hqp = $rel->getUser2();

        $success = 0;
    
        $ccv_item = $ccv->addChild("section");
        $ccv_item->addAttribute('id', $section['lov_id']);
        $ccv_item->addAttribute('label', $section['lov_name']);
    
        foreach($section->field as $item){
        
            $item_id = $item['lov_id'];
            $item_name = $item['lov_name'];

            if($item_name == "Supervision Role"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $lov = $field->addChild('lov');
                $lov->addAttribute('id', '00000000000000000000000100002900');
                $supers = $hqp->getSupervisors();
                #echo 'SUPERS<br/>';
                #echo '<pre>'.var_dump($supers).'</pre>'; // TEST DUMP
                if (count($supers) > 1) 
                    $field->lov =  "Co-Supervisor";
                else
                    $field->lov =  "Principal Supervisor";
            }
            else if($item_name == "Supervision Start Date"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $start_date = preg_split('/\-/', $rel->getStartDate());
                $field->value = $start_date[0].'/'.$start_date[1];
            }
            else if($item_name == "Supervision End Date"){
                $date = $rel->getEndDate();
                if (!is_null($date)){
                    $date = preg_split('/\-/', $date);
                    $date = $date[0].'/'.$date[1];
                    if ($date !== '0000/00'){
                        $field = $ccv_item->addChild("field");
                        $field->addAttribute('id', $item_id);
                        $field->addAttribute('label', $item_name);
                        $val = $field->addChild('value');
                        $val->addAttribute('type', "YearMonth");
                        $val->addAttribute('format', "yyyy/MM");
                        $field->value = $date;
                    }        
                }        
            }
            else if($item_name == "Student Name"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
        
                $hqp_name = $hqp->getReversedName();
                $field->value = $hqp_name;
            }
            else if($item_name == "Student Institution"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
        
                $hqp_uni = $hqp->getUni();
                $field->value = $hqp_uni;
            }
            else if($item_name == "Student Canadian Residency Status"){
                $status_map = array('Canadian'=>array("00000000000000000000000000000034","Canadian Citizen"),
                                    'Landed Immigrant'=>array("00000000000000000000000000000035","Permanent Resident"),
                                    'Foreign'=>array("00000000000000000000000000000040","Study Permit"),
                                    'Visa Holder'=>array("00000000000000000000000000000040","Study Permit"));

                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('lov');
                $hqp_status = $hqp->getNationality();
                if(!empty($hqp_status) && isset($status_map[$hqp_status])){
                    $lov_id = $status_map[$hqp_status][0];
                    $val->addAttribute('id', $lov_id);
                    $field->lov = $status_map[$hqp_status][1];
                }
            }
            else if($item_name == "Study / Postdoctoral Level"){
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                #echo 'UNI POSITION<br/>';
                #echo '<pre>'.$hqp_pos.'</pre>'; // TEST DUMP
                #echo 'DEGREE MAP<br/>';
                #echo '<pre>'.$degree_map.'</pre>'; // TEST DUMP
                if(!empty($hqp_pos) && isset($degree_map[$hqp_pos])){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('lov');
                    $lov_id = $degree_map[$hqp_pos][0];
                    $val->addAttribute('id', $lov_id);
                    $field->lov = $degree_map[$hqp_pos][1];
                }
            }
            else if($item_name == "Student Degree Status"){
                # If active  Completed 
                # Otherwise  In Progress
                $hqp_pos = $hqp->getPosition();
                if(!empty($hqp_pos) && $hqp_pos !== 'PostDoc'){
                    $status_map = array('Completed'=>"00000000000000000000000000000068",
                                        'In Progress'=>"00000000000000000000000000000070");

                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('lov');
        
                    if (!$hqp->isActive()){
                        $lov_id = $status_map['Completed'];
                        $field->lov = "Completed";
                    } else {
                        $lov_id = $status_map['In Progress'];
                        $field->lov = "In Progress";
                    }
                    $val->addAttribute('id', $lov_id);
                }
            }
            else if($item_name == "Student Degree Start Date"){
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                if(!empty($hqp_pos) && $hqp_pos !== 'PostDoc'){
                    $degree_date = $hqp->getDegreeStartDate();
                    if (!is_null($degree_date)){
                        $date = preg_split('/\-/', $degree_date);
                        $date = $date[0].'/'.$date[1];
                        if ($date !== '0000/00'){
                            $field = $ccv_item->addChild("field");
                            $field->addAttribute('id', $item_id);
                            $field->addAttribute('label', $item_name);
                            $val = $field->addChild('value');
                            $val->addAttribute('type', "YearMonth");
                            $val->addAttribute('format', "yyyy/MM");
                            $field->value = $date;
                        }        
                    }        
                }        
            }
            else if($item_name == "Student Degree Received Date"){
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                if(!empty($hqp_pos) && $hqp_pos !== 'PostDoc'){
                    $degree_date = $hqp->getDegreeReceivedDate();
                    if (!is_null($degree_date)){
                        $date = preg_split('/\-/', $degree_date);
                        $date = $date[0].'/'.$date[1];
                        if ($date !== '0000/00'){
                            $field = $ccv_item->addChild("field");
                            $field->addAttribute('id', $item_id);
                            $field->addAttribute('label', $item_name);
                            $val = $field->addChild('value');
                            $val->addAttribute('type', "YearMonth");
                            $val->addAttribute('format', "yyyy/MM");
                            $field->value = $date;
                        }        
                    }        
                }
            }
            else if($item_name == "Student Degree Expected Date"){
                ## Not available in the Forum 
            }
            else if($item_name == "Thesis/Project Title"){
                $hqp_thesis = $hqp->getThesis();
                if(!is_null($hqp_thesis)){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('value');
                    $val->addAttribute('type', "String");
                    $field->value = $hqp_thesis->getTitle(); 
                } 
            }
            else if($item_name == "Project Description"){
                $hqp_proj = $hqp->getThesis();
                if(!is_null($hqp_proj)){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    #$val = $field->addChild('value');
                    #$val->addAttribute('type', "Bilingual");
                    $bilin = $field->addChild("bilingual");
                    $bilin->addChild("english");
                    $bilin->english = $hqp_proj->getTitle(); 
                } 
            }
            else if($item_name == "Present Position"){
                $hqp_pos = $hqp->getPresentPosition();
                if ($hqp_pos !== ''){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('value');
                    $val->addAttribute('type', "String");
                    $field->value = $hqp_pos;
                }
            }
            $success = 1;
        }
        return $success;
    }

    static function mapItem($person, $section, $product, $ccv){
        global $wgUser, $wgOut;

        $type = $product->getType();
        $success = 0;
        #echo 'SECTION<br/>';
        #echo '<pre>'.var_dump($section).'</pre>'; // TEST DUMP

        foreach($section as $item){
            if((($type == "Masters Thesis" || $type == "PHD Thesis") 
             && ($type == $item['type']) && $person->isAuthorOf($product) 
             && isset($item['supervised']) && $item['supervised']=="false" 
             && isset($item['ccv_id']) && isset($item['ccv_name'])) 
            || 
               (($type == "Masters Thesis" || $type == "PHD Thesis") 
             && ($type == $item['type']) && !$person->isAuthorOf($product) 
             && isset($item['supervised']) && $item['supervised']=="true" 
             && isset($item['ccv_id']) && isset($item['ccv_name']))
            ||
               (($type != "Masters Thesis" && $type != "PHD Thesis") 
             && ($type == $item['type'])
             && isset($item['ccv_id']) && isset($item['ccv_name']))){ 
                #echo 'PERSON<br/>';
                #echo '<pre>'.var_dump($person).'</pre>'; // TEST DUMP
                #echo 'ITEM<br/>';
                #echo '<pre>'.var_dump($item).'</pre>';  // TEST DUMP
                #echo 'ITEM DATA<br/>';
                #echo '<pre>'.var_dump($item->data).'</pre>';  // TEST DUMP
                #echo 'ITEM STATUS<br/>';
                #echo '<pre>'.var_dump($item->statuses).'</pre>';  // TEST DUMP
                #echo 'PRODUCT<br/>';
                #echo '<pre>'.var_dump($product).'</pre>';  // TEST DUMP

                $ccv_item = $ccv->addChild("section");
                $ccv_id = $item['ccv_id'];
                $ccv_name = $item['ccv_name'];

                $ccv_item->addAttribute('id', $ccv_id);
                $ccv_item->addAttribute('label', $ccv_name);

                // Publication Type
                $pub_type = $item->pub_type;
                if ((string)$pub_type->type !== ''){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $pub_type['ccv_id']);
                    $field->addAttribute('label', $pub_type['ccv_name']);
                    $type_tag = $field->addChild('lov');
                    $type_tag->addAttribute('id', $pub_type->type['lov_id']);
                    $type_tag[0] = (string) $pub_type->type;
                }

                //Title
                $title = $product->getTitle();
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item->title['ccv_id']);
                $field->addAttribute('label', $item->title['ccv_name']);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                $field->value = $title;

                //Status
                $prod_status = $product->getStatus();
                #echo 'PROD STATUS<br/>';
                #echo '<pre>'.var_dump($prod_status).'</pre>';  // TEST DUMP
                #echo 'ITEM STATUSES<br/>';
                #echo '<pre>'.var_dump($item->statuses).'</pre>';  // TEST DUMP
                if(isset($item->statuses)){
                    foreach($item->statuses->status as $status){
                        if ($prod_status != $status)
                            continue;
                        #echo 'STATUS<br/>';
                        #echo '<pre>'.var_dump($status).'</pre>';  // TEST DUMP
                        #echo '<pre>'.var_dump($status['lov_id']).'</pre>';  // TEST DUMP
                        #echo '<pre>'.var_dump($status['lov_name']).'</pre>';  // TEST DUMP
                        #echo '<pre>'.(string)$status.'</pre>';  // TEST DUMP
                        $field = $ccv_item->addChild("field");
                        $field->addAttribute('id', $item->statuses['ccv_id']);
                        $field->addAttribute('label', $item->statuses['ccv_name']);
                        $status_tag = $field->addChild('lov');
                        $status_tag->addAttribute('id', $status['lov_id']);
                        $status_tag->addAttribute('name', $status['lov_name']);
                        $status_tag[0] = (string) $prod_status;
                    }
                }

                //Add Data Fields
                $product_data = $product->getData();

                foreach($item->data->field as $data_field){
                    #echo 'DATA FIELD<br/>';
                    #echo '<pre>'.var_dump($data_field).'</pre>';  // TEST DUMP
                    $key = (string) $data_field;
                    if(isset($data_field['ccv_id']) && 
                       isset($product_data[$key]) && 
                       $product_data[$key] !== '' ){
                        #echo 'SET<br><br>';
                        $field = $ccv_item->addChild("field");
                        $field->addAttribute('id', $data_field['ccv_id']);
                        $field->addAttribute('label', $data_field['ccv_name']);

                        $val = $field->addChild('value');
                        $val->addAttribute('type', "String");
                        $field->value = $product_data[$key];
                    }
                }

                #exit("<p>test exit"); // TEST - to interrupt after one product

                //Date
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item->date['ccv_id']);
                $field->addAttribute('label', $item->date['ccv_name']);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $product_date = preg_split('/\-/', $product->getDate());
                $field->value = $product_date[0].'/'.$product_date[1];
                
                //Authors
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item->authors['ccv_id']);
                $field->addAttribute('label', $item->authors['ccv_name']);
        
                $product_authors = $product->getAuthors();
                $auth_arr = array();
                foreach($product_authors as $a){
                    $auth_arr[] = $a->getNameForForms();
                }

                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
                $field->value = implode(', ', $auth_arr);

                //Description
                if ($product->getDescription() !== ''){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item->description['ccv_id']);
                    $field->addAttribute('label', $item->description['ccv_name']);
                    #$val = $field->addChild('value');
                    #$val->addAttribute('type', "Bilingual");
                    $bilin = $field->addChild("bilingual");
                    $bilin->addChild("english");
                    $bilin->english = substr($product->getDescription(), 0, 1000);
                }

                $success = 1;
            }
        }
        return $success;
    }

}

?>
