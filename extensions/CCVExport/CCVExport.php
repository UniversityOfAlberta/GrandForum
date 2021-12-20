<?php
require_once("CCVImport/CCVImport.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['CCVExport'] = 'CCVExport';
$wgExtensionMessagesFiles['CCVExport'] = $dir . 'CCVExport.i18n.php';
$wgSpecialPageGroups['CCVExport'] = 'network-tools';

$degree_map = 
  array('MSc Student'=>array("6bb179b92d1d46059bae10f6d21ea096","Master's Thesis"),
        'PhD Student'=>array("971953ad86ca49f3b32ac5c7c2758a1b","Doctorate"),
        'Undergraduate'=>array("00000000000000000000000000000071","Bachelor's"),
        'PostDoc'=>array("e0b26301c88d4be5a6f7143981c9b3bb","Post-doctorate"));


function runCCVExport($par) {
    CCVExport::execute($par);
}

class CCVExport extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("CCVExport", HQP.'+', true, 'runCCVExport');
    }
    
    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
        global $userID, $wgDBname;
        $this->getOutput()->setPageTitle("CCV Export");
        $userID = $wgUser->getId();

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
            header("Content-Type: application/download");
            header("Content-Disposition: attachment;filename=export.xml"); 
            header("Content-Transfer-Encoding: binary ");
            echo $xml;
            exit;
        }
      
        $wgOut->setPageTitle("Export To CCV");

        $wgOut->addHTML("<p><a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:CCVExport?getXML'>Download XML</a></p>");

        // Display export preview
        $xml = CCVExport::exportXML();
        $xml = str_replace("<", "&lt;", $xml); // show tags as text
        $xml = str_replace("\n", "<br/>", $xml); // show newlines
        $xml = str_replace(" ", "&nbsp;", $xml); // show indents
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
    
    static function setValue($el, $value){
        $el[0] = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8', false);
    }
    
    static function setAttribute($el, $attr, $val){
        if(isset($el[$attr])){
            $el[$attr] = $val;
        }
        else{
            $el->addAttribute($attr, $val);
        }
    }
    
    static function setChild($el, $tag, $attr="", $val=""){
        if($attr != ""){
            $children = $el->xpath("{$tag}[@{$attr}='{$val}']");
        }
        else{
            $children = $el->xpath("{$tag}");
        }
        if(count($children) > 0){
            return $children[0];
        }
        else{
            $field = $el->addChild($tag);
            if($attr != ""){
                $field->addAttribute($attr, $val);
            }
            return $field;
        }
    }

    static function exportXML(){
        global $wgOut, $wgUser, $config, $userID;
        
        $person = Person::newFromId($userID);
        $personCCV = $person->getCCV();

        // Template Files
        $map_file = getcwd()."/extensions/GrandObjects/ProductStructures/{$config->getValue('networkName')}.xml";
        $hqp_file = getcwd()."/extensions/CCVExport/templates/HQP.xml";
        $id_file =  getcwd()."/extensions/CCVExport/templates/Identification.xml";
        $lang_file = getcwd()."/extensions/CCVExport/templates/Language.xml";
        $addr_file = getcwd()."/extensions/CCVExport/templates/Address.xml";
        $phone_file = getcwd()."/extensions/CCVExport/templates/Telephone.xml";
        
        if($personCCV != ""){
            $ccv = simplexml_load_string($personCCV);
        }
        else{
            $ccv_tmpl = getcwd()."/extensions/CCVExport/templates/ccv_template.xml";
            $ccv = simplexml_load_file($ccv_tmpl);
        }

        // Load the templates
        $map = simplexml_load_file($map_file);
        $hqp_map = simplexml_load_file($hqp_file);
        $id_map = simplexml_load_file($id_file);
        $lang_map = simplexml_load_file($lang_file);
        $addr_map = simplexml_load_file($addr_file);
        $phone_map = simplexml_load_file($phone_file);

        $all_products = $person->getPapers("Publication", false, "both",true,"Public");
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
        
        self::setAttribute($ccv, 'dateTimeGenerated', date('Y-m-d H:i:s'));

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
                // CCV does not include 'Rejected' Publishing Status
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
        $section = $ccv->xpath("section[@id='95c29504d0aa4b51b84659cafaf2b38d']/section[@id='90cc172e54904b45948d17cba24d3f25']");
        unset($section[0]->section);
        foreach($sortedRels as $rel){
            $res = CCVExport::mapHQP($person, 
                                     $hqp_map->HQP->data, 
                                     $rel, 
                                     $section[0]);
        }

        // Format and indent the XML
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($ccv->asXML());
        $xml = $dom->saveXML();

        return $xml;
    }
    
    static function mapId($person, $section, $ccv){
        global $wgUser;

        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($ccv, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "5c6f17e8a67241e19667815a9e95d9d0": // Family Name
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $person->getLastName());
                    break;
                case "98ad36fee26a4d6b8953ea764f4fed04": // First Name
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $person->getFirstName());
                    break;
                case "4ca83c1aaa6a42a78eac0290368e70f3": // Middle Name
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $person->getMiddleName());
                    break;
                case "84e9fa08f7334db79ed5310e5f7a961b": // Previous Family Name
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $person->getPrevLastName());
                    break;
                case "0fb359a7d809457d9392bb1ca577f1b3": // Previous First Name
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $person->getPrevFirstName());
                    break;
                case "ee8beaea41f049d8bcfadfbfa89ac09e": // Title
                    $title = $person->getHonorific();
		    if($title != ''){
                        $value = self::setChild($field, 'lov');
                        self::setAttribute($value, 'id', self::getLovId("Title", $title, ""));
                        self::setValue($value, self::getLovVal("Title", $title, ""));
                    }    
		    break;
                case "3d258d8ceb174d3eb2ae1258a780d91b": // Sex
                    $gender = $person->getGender();
                    $value = self::setChild($field, 'lov');
                    self::setAttribute($value, 'id', self::getLovId("Sex", $gender, "No Response"));
                    self::setValue($value, self::getLovVal("Sex", $gender, "No Response"));
                    break;
                case "2b72a344523c467da0c896656b5290c0": // Correspondence language
                    $language = $person->getCorrespondenceLanguage();
		    if($language != ''){
                        $value = self::setChild($field, 'lov');
                        self::setAttribute($value, 'id', self::getLovId("Correspondance Language", $language, ""));
                        self::setValue($value, self::getLovVal("Correspondance Language", $language, ""));
		    }
                    break;
            }
        }
    }
    
    static function mapLanguage($person, $section, $language, $ccv){
        global $wgUser;
        $lang = $language->getLanguage();
        $lang_el = $ccv->xpath("section/field/lov[@id='".self::getLovId("Language", $lang, "English")."']/../..");
        if(count($lang_el) > 0){
            $sect = $lang_el[0];
        }
        else{
            $sect = $ccv->addChild('section');
            $sect->addAttribute('id', $section['id']);
        }
        self::setAttribute($sect, 'label', $section['label']);
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($sect, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "ee161805b4f941e48f05e050e364e585": // Language
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Language", $lang, "English"));
                    self::setValue($lov, self::getLovVal("Language", $lang, "English"));
                    break;
                case "a9d0f0666e5b47dcb9acb30bd5cab407": // Read
                    $read = ($language->canRead()) ? "Yes" : "No";
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Yes-No", $read, "Yes"));
                    self::setValue($lov, self::getLovVal("Yes-No", $read, "Yes"));
                    break;
                case "12173f36422446479799578ba07d96c8": // Write
                    $write = ($language->canWrite()) ? "Yes" : "No";
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Yes-No", $write, "Yes"));
                    self::setValue($lov, self::getLovVal("Yes-No", $write, "Yes"));
                    break;
                case "e670ac0f2c3e48a3b13d487e66ea7889": // Speak
                    $speak = ($language->canSpeak()) ? "Yes" : "No";
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Yes-No", $speak, "Yes"));
                    self::setValue($lov, self::getLovVal("Yes-No", $speak, "Yes"));
                    break;
                case "aa02c54f1e5b4672a0b96def14e5b02e": // Understand
                    $understand = ($language->canUnderstand()) ? "Yes" : "No";
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Yes-No", $understand, "Yes"));
                    self::setValue($lov, self::getLovVal("Yes-No", $understand, "Yes"));
                    break;
                case "fc6ac63e9ec04129aec7b26e5a729920": // Review
                    $review = ($language->canReview()) ? "Yes" : "No";
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Yes-No", $review, "Yes"));
                    self::setValue($lov, self::getLovVal("Yes-No", $review, "Yes"));
                    break;
            }
        }
    }
    
    static function mapAddress($person, $section, $address, $ccv){
        global $wgUser;
        $addr = $address->getType();
        $addr_el = $ccv->xpath("section/field/lov[@id='".self::getLovId("Address Type", $addr, "Primary Affiliation")."']/../..");
        if(count($addr_el) > 0){
            $sect = $addr_el[0];
        }
        else{
            $sect = $ccv->addChild("section");
            $sect->addAttribute("id", $section['id']);
        }
        self::setAttribute($sect, 'label', $section['label']);
        if($address->isPrimary()){
            self::setAttribute($sect, 'primaryIndicator', 'true');
        }
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($sect, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "35c302c36fe9479287206171087fb185": // Address Type
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Address Type", $addr, "Primary Affiliation"));
                    self::setValue($lov, self::getLovVal("Address Type", $addr, "Primary Affiliation"));
                    break;
                case "2de0fe4994f546c695a060d68e8e03ca": // Address Line 1
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getLine1());
                    break;
                case "dafdb980e181416abc5e26c0770df662": // Address Line 2
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getLine2());
                    break;
                case "fc390eae1fbc45c89789f2ecbb5bed8e": // Address Line 3
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getLine3());
                    break;
                case "d51e2de9122744489ac2231d85995617": // Address Line 4
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getLine4());
                    break;
                case "5365d87b9ff145d3a8d0d4fc21af57bb": // Address Line 5
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getLine5());
                    break;
                case "499d69637b4148d0a49463a2881e9d09": // City
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getCity());
                    break;
                case "b1071063df03484ebec65cd1a3464438": // Location (Country/Subdivision)
                    $country = $address->getCountry();
                    $province = $address->getProvince();
                    $table = self::setChild($field, 'refTable', 'refValueId', '00000000000000000000039564242160');
                    self::setAttribute($table, 'label', 'Country-Subdivision');
                    $l_country = self::setChild($table, 'linkedWith', 'refOrLovId', '00000000000000000000000000002000');
                    $l_subdivision = self::setChild($table, 'linkedWith', 'refOrLovId', '00000000000000000000000000100000');
                    self::setAttribute($l_country, 'label', 'Country');
                    self::setAttribute($l_subdivision, 'label', 'Subdivision');
                    self::setAttribute($l_country, 'value', $country);
                    self::setAttribute($l_subdivision, 'value', $province);
                    break;
                case "a41f1e118e61482eb3cdde4aaeb783e8": // Postal/Zip Code
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $address->getPostalCode());
                    break;
                case "b77ff4a2c49247e0af668be52704da91": // Start Date
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy-MM-dd');
                    self::setValue($value, substr($address->getStartDate(), 0, 10));
                    break;
                case "4ab2497d7a0f471ebc6a50e32dd4f22d": // End Date
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy-MM-dd');
                    self::setValue($value, substr($address->getEndDate(), 0, 10));
                    break;
            }
        }
    }
    
    static function mapTelephone($person, $section, $phone, $ccv){
        global $wgUser;
        $type = $phone->getType();
        $phone_el = $ccv->xpath("section/field/lov[@id='".self::getLovId("Phone Type", $type, "Work")."']/../..");
        if(count($phone_el) > 0){
            $sect = $phone_el[0];
        }
        else{
            $sect = $ccv->addChild("section");
            $sect->addAttribute("id", $section['id']);
        }
        self::setAttribute($sect, 'label', $section['label']);
        if($phone->isPrimary()){
            self::setAttribute($sect, 'primaryIndicator', 'true');
        }
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($sect, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "ccef121ae875427f829024aabb39fa8c": // Address Type
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Phone Type", $type, "Work"));
                    self::setValue($lov, self::getLovVal("Phone Type", $type, "Work"));
                    break;
                case "63dedd46a5204cda8257227bbb3b6675": // Country Code
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $phone->getCountryCode());
                    break;
                case "13cdf3a5e13643f5bc74566bf075253c": // Area Code
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $phone->getAreaCode());
                    break;
                case "1ca756fe70964371a2b9f57bdf567a5d": // Telephone Number
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $phone->getPhoneNumber());
                    break;
                case "afe0657785084098bb718345280eb840": // Extension
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $phone->getExtension());
                    break;
                case "69c67fae5d4849d08f4f9799ae0a2335": // Start Date
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy-MM-dd');
                    self::setValue($value, substr($phone->getStartDate(), 0, 10));
                    break;
                case "a90e95e1d278467eaf1847464f09f39f": // End Date
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy-MM-dd');
                    self::setValue($value, substr($phone->getEndDate(), 0, 10));
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
                if(count($supers) > 1){
                    self::setValue($lov, "Co-Supervisor");
                }
                else{
                    self::setValue($lov, "Principal Supervisor");
                }
            }
            else if($item_name == "Supervision Start Date"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "YearMonth");
                $val->addAttribute('format', "yyyy/MM");
                $start_date = preg_split('/\-/', $rel->getStartDate());
                self::setValue($val, $start_date[0].'/'.$start_date[1]);
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
                        self::setValue($val, $date);
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
                self::setValue($val, $hqp_name);
            }
            else if($item_name == "Student Institution"){
                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $val = $field->addChild('value');
                $val->addAttribute('type', "String");
        
                $hqp_uni = $hqp->getUni();
                self::setValue($val, $hqp_uni);
            }
            else if($item_name == "Student Canadian Residency Status"){
                $status_map = array('Canadian'=>array("00000000000000000000000000000034","Canadian Citizen"),
                                    'Landed Immigrant'=>array("00000000000000000000000000000035","Permanent Resident"),
                                    'Foreign'=>array("00000000000000000000000000000040","Study Permit"),
                                    'Visa Holder'=>array("00000000000000000000000000000040","Study Permit"));

                $field = $ccv_item->addChild("field");
                $field->addAttribute('id', $item_id);
                $field->addAttribute('label', $item_name);
                $hqp_status = $hqp->getNationality();
                if(!empty($hqp_status) && isset($status_map[$hqp_status])){
                    $val = $field->addChild('lov');
                    $lov_id = $status_map[$hqp_status][0];
                    $val->addAttribute('id', $lov_id);
                    self::setValue($val, $status_map[$hqp_status][1]);
                }
            }
            else if($item_name == "Degree Type or Postdoctoral Status"){
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                if(!empty($hqp_pos) && isset($degree_map[$hqp_pos])){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('lov');
                    $lov_id = $degree_map[$hqp_pos][0];
                    $val->addAttribute('id', $lov_id);
                    self::setValue($val, $degree_map[$hqp_pos][1]);
                }
            }
            else if($item_name == "Student Degree Status"){
                // If active  Completed 
                // Otherwise  In Progress
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
                        self::setValue($val, "Completed");
                    } else {
                        $lov_id = $status_map['In Progress'];
                        self::setValue($val, "In Progress");
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
                            self::setValue($val, $date);
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
                            self::setValue($val, $date);
                        }        
                    }        
                }
            }
            else if($item_name == "Student Degree Expected Date"){
                // Not available in the Forum 
            }
            else if($item_name == "Thesis/Project Title"){
                $hqp_thesis = $hqp->getThesis();
                if(!is_null($hqp_thesis)){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('value');
                    $val->addAttribute('type', "String");
                    self::setValue($val, $hqp_thesis->getTitle());
                } 
            }
            else if($item_name == "Project Description"){
                $hqp_proj = $hqp->getThesis();
                if(!is_null($hqp_proj)){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    
                    $bilin = $field->addChild("bilingual");
                    $bilin->addChild("english");
                    self::setValue($bilin->english, $hqp_proj->getTitle());
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
                    self::setValue($val, $hqp_pos);
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

                $title = htmlentities($product->getTitle(), ENT_COMPAT);

                $ccv_el = $ccv->xpath("section[@recordId='{$product->getCCVId()}']");
                $ccv_el_title = $ccv->xpath("section/field/value[.=\"{$title}\"]/../..");
                if(count($ccv_el) > 0){
                    $ccv_item = $ccv_el[0];
                }
                else if(count($ccv_el_title) > 0){
                    $ccv_item = $ccv_el_title[0];
                }
                else{
                    $ccv_item = $ccv->addChild("section");
                }
                $ccv_id = $item['ccv_id'];
                $ccv_name = $item['ccv_name'];

                self::setAttribute($ccv_item, 'id', $ccv_id);
                self::setAttribute($ccv_item, 'label', $ccv_name);

                // Publication Type
                $pub_type = $item->pub_type;
                if ((string)$pub_type->type !== ''){
                    $field = self::setChild($ccv_item, 'field', 'id', $pub_type['ccv_id']);
                    self::setAttribute($field, 'label', $pub_type['ccv_name']);
                    $type_tag = self::setChild($field, 'lov', 'id', $pub_type->type['lov_id']);
                    $type_tag[0] = (string) $pub_type->type;
                }

                //Title
                $title = $product->getTitle();
                $field = self::setChild($ccv_item, 'field', 'id', $item->title['ccv_id']);
                self::setAttribute($field, 'label', $item->title['ccv_name']);
                $val = self::setChild($field, 'value', 'type', 'String');
                $field->value = $title;

                //Status
                $prod_status = $product->getStatus();
                if(isset($item->statuses)){
                    foreach($item->statuses->status as $status){
                        if ($prod_status != $status)
                            continue;
                        $field = self::setChild($ccv_item, 'field', 'id', $item->statuses['ccv_id']);
                        self::setAttribute($field, 'label', $item->statuses['ccv_name']);
                        $status_tag = self::setChild($field, 'lov', 'id', $status['lov_id']);
                        $status_tag[0] = (string) $prod_status;
                    }
                }

                //Add Data Fields
                $product_data = $product->getData();

                foreach($item->data->field as $data_field){
                    $key = (string) $data_field;
                    if(isset($data_field['ccv_id']) && 
                       isset($product_data[$key]) && 
                       $product_data[$key] !== '' ){
                        $field = self::setChild($ccv_item, 'field', 'id', $data_field['ccv_id']);
                        self::setAttribute($field, 'label', $data_field['ccv_name']);
                        
                        if($data_field['options'] == 'Yes|No'){
                            $val = self::setChild($field, 'lov', 'id', self::getLovId("Yes-No", $product_data[$key], "Yes"));
                            $field->lov = $product_data[$key];
                        }
                        else{
                            $val = self::setChild($field, 'value', 'type', 'String');
                            $field->value = $product_data[$key];
                        }
                    }
                }

                //Date
                $field = self::setChild($ccv_item, 'field', 'id', $item->date['ccv_id']);
                self::setAttribute($field, 'label', $item->date['ccv_name']);
                $val = self::setChild($field, 'value', 'type', 'YearMonth');
                self::setAttribute($val, 'format', 'yyyy/MM');
                $product_date = preg_split('/\-/', $product->getDate());
                $field->value = $product_date[0].'/'.$product_date[1];
                
                //Authors
                $field = self::setChild($ccv_item, 'field', 'id', $item->authors['ccv_id']);
                self::setAttribute($field, 'label', $item->authors['ccv_name']);
        
                $product_authors = $product->getAuthors();
                $auth_arr = array();
                foreach($product_authors as $a){
                    $auth_arr[] = $a->getNameForForms();
                }

                $val = self::setChild($field, 'value', 'type', 'String');
                $field->value = implode(', ', $auth_arr);

                //Description
                if ($product->getDescription() !== ''){
                    $field = self::setChild($ccv_item, 'field', 'id', $item->description['ccv_id']);
                    self::setAttribute($field, 'label', $item->description['ccv_name']);
                    
                    $bilin = self::setChild($field, 'bilingual');
                    self::setChild($bilin, 'english');
                    $bilin->english = substr($product->getDescription(), 0, 1000);
                }

                $success = 1;
            }
        }
        return $success;
    }

}

?>
