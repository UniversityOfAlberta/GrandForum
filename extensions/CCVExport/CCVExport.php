<?php
//require_once("CCVImport/CCVImport.php");

$dir = dirname(__FILE__) . '/';

$wgSpecialPages['CCVExport'] = 'CCVExport';
$wgExtensionMessagesFiles['CCVExport'] = $dir . 'CCVExport.i18n.php';
$wgSpecialPageGroups['CCVExport'] = 'network-tools';

$edu_map = 
  array('msc'=>array("00000000000000000000000000000072","Master's Thesis"),
        'phd'=>array("00000000000000000000000000000073","Doctorate"),
        'ugrad'=>array("00000000000000000000000000000071","Bachelor's"),
        'pdf'=>array("00000000000000000000000000000074","Post-doctorate"));
$degree_map = 
  array('msc'=>array("6bb179b92d1d46059bae10f6d21ea096","Master's Thesis"),
        'phd'=>array("971953ad86ca49f3b32ac5c7c2758a1b","Doctorate"),
        'ugrad'=>array("00000000000000000000000000000071","Bachelor's"),
        'pdf'=>array("e0b26301c88d4be5a6f7143981c9b3bb","Post-doctorate"));

function runCCVExport($par) {
    CCVExport::execute($par);
}

class CCVExport extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("CCVExport", HQP.'+', true, 'runCCVExport');
    }
    
    function execute(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgMessage;
        global $userID, $wgDBname;
      
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
        //$wgOut->addHTML("<p>You must click \"Reload Page\" to set a time period.</p>");
        $wgOut->addHTML("<p class='warning'><b>IMPORTANT:</b> Before importing your CV on the Canadian CCV Website, it is advised that you first create a backup CCV XML of your CV.</p>");
        if(isset($_GET['datefrom']) && $_GET['datefrom'] != ""){
            $dateto= date("Y-m-d");
            $datefrom = $_GET['datefrom'];
            if(isset($_GET['dateto']) && $_GET['dateto'] !=""){
                 $dateto = $_GET['dateto'];
            }
            $wgOut->addHTML("<form><p><b>Date:</b> <input type='date' format='yy-mm-dd' id='datefrom' value={$datefrom} name='datefrom' class='hasDatepicker'> - <input type='date' id='dateto' format='yy-mm-dd' name='dateto' value={$dateto} class='hasDatepicker'><button type='submit'>Reload Page</button></p>");
            $wgOut->addHTML("<p><a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:CCVExport?getXML&datefrom={$datefrom}&dateto={$dateto}'>Download XML</a></p>");

        }
        else{
            $wgOut->addHTML("<form><p>Date: <input type='date' format='yy-mm-dd' id='datefrom' name='datefrom' class='hasDatepicker'> - <input type='date' id='dateto' format='yy-mm-dd' name='dateto' class='hasDatepicker'><button type='submit'>Reload Page</button></p>");
            $wgOut->addHTML("<p><a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:CCVExport?getXML'>Download XML</a></p>");
        }
        // Display export preview
        $xml = CCVExport::exportXML();
        $xml = str_replace("<", "&lt;", $xml); // show tags as text
        $xml = str_replace("\n", "<br/>", $xml); // show newlines
        $xml = str_replace(" ", "&nbsp;", $xml); // show indents
        $wgOut->addHTML('<p><pre style="white-space:pre-wrap; max-height: 600px; overflow: auto;">'.$xml."</pre></p>");
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
        $datefrom = @$_GET['datefrom'];
        $dateto = @$_GET['dateto'];
        $dom = new DOMDocument(); 
        $person = Person::newFromId($userID);
        $personCCV = "";

        // Template Files
        $map_file = getcwd()."/extensions/GrandObjects/ProductStructures/{$config->getValue('networkName')}.xml";
        $hqp_file = getcwd()."/extensions/CCVExport/templates/HQP.xml";
        $id_file =  getcwd()."/extensions/CCVExport/templates/Identification.xml";
        $lang_file = getcwd()."/extensions/CCVExport/templates/Language.xml";
        $addr_file = getcwd()."/extensions/CCVExport/templates/Address.xml";
        $phone_file = getcwd()."/extensions/CCVExport/templates/Telephone.xml";
        $grant_file = getcwd()."/extensions/CCVExport/templates/Grant.xml";
        $degree_file = getcwd()."/extensions/CCVExport/templates/Degree.xml";
        $employment_file = getcwd()."/extensions/CCVExport/templates/Employment.xml";
        $investigator_file = getcwd()."/extensions/CCVExport/templates/OtherInvestigator.xml";
        $funding_year_file = getcwd()."/extensions/CCVExport/templates/FundingByYear.xml";
        $funding_source_file = getcwd()."/extensions/CCVExport/templates/FundingSources.xml";

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
        $grant_map = simplexml_load_file($grant_file);
        $degree_map = simplexml_load_file($degree_file);
        $employment_map = simplexml_load_file($employment_file);
        $investigator_map = simplexml_load_file($investigator_file);
        $funding_year_map = simplexml_load_file($funding_year_file);
        $funding_source_map = simplexml_load_file($funding_source_file);

        if($datefrom != "" && $dateto != ""){
            $all_products = $person->getPapersAuthored("Publication", $datefrom, $dateto, true);
            $all_presentations = $person->getPapersAuthored("Presentation", $datefrom, $dateto, true);
            $grants = $person->getGrantsBetween($datefrom, $dateto);
            $rels = array_merge($person->getRelationsDuring(SUPERVISES, $datefrom, $dateto),
                                $person->getRelationsDuring(CO_SUPERVISES, $datefrom, $dateto));
        }
        else{
            $all_products = $person->getPapers("Publication", true, "both",true,"Public");
            $all_presentations = $person->getPapers("Presentation", true, "both", true, "Public");
            $grants = $person->getGrants();
            $rels = array_merge($person->getRelations(SUPERVISES, true),
                                $person->getRelations(CO_SUPERVISES, true));
        }
        $prod_sorted = array();
        $pres_sorted = array();

        foreach($all_products as $p){
            $t = $p->getType();
            if(isset($prod_sorted[$t])){
                $prod_sorted[$t][] = $p;
            } else {
                $prod_sorted[$t] = array();
                $prod_sorted[$t][] = $p;
            }
        }
        
        foreach($all_presentations as $p){
            $t = $p->getType();
            if(isset($pres_sorted[$t])){
                $pres_sorted[$t][] = $p;
            } else {
                $pres_sorted[$t] = array();
                $pres_sorted[$t][] = $p;
            }
        }
        self::setAttribute($ccv, 'dateTimeGenerated', date('Y-m-d H:i:s'));

        // Publications
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
            }
        }
        
        // Presentations
        $section = $ccv->xpath("section[@id='047ec63e32fe450e943cb678339e8102']");
        foreach($pres_sorted as $type => $products){
            foreach($products as $product){
                // CCV does not include 'Rejected' Publishing Status
                if($product->getStatus() == 'Rejected'){
                    continue;
                }
                $res = CCVExport::mapItem($person, 
                                          $map->Presentations->Presentation, 
                                          $product, 
                                          $section[0]);
            }
        }

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

        //=== Grants Start == //
        foreach($grants as $grant){
            $ccv_item = $ccv->addChild("section");
            $ccv_id = "aaedc5454412483d9131f7619d10279e";
            $ccv_name = "Research Funding History";

            self::setAttribute($ccv_item, 'id', $ccv_id);
            self::setAttribute($ccv_item, 'label', $ccv_name);	
            $res = CCVExport::mapGrant($person,
                                       $grant_map,
                                       $grant,
                                       $ccv_item,
                                       $investigator_map,
                                       $funding_year_map,
                                       $funding_source_map 
                                       );
        }
        
        // Education & Employment
        $education = array();
        $employment = array();
        foreach($person->getUniversities() as $university){
            if(in_array(strtolower($university['position']), array_merge(Person::$studentPositions['ugrad'],
                                                                         Person::$studentPositions['msc'], 
                                                                         Person::$studentPositions['phd']))){
                $education[] = $university;
            }
            else{
                $employment[] = $university;
            }
        }
        
        $section = $ccv->xpath("section[@id='0d82220f95f043e0bc9608bbb6bf413a']");
        foreach($education as $uni){
            $ccv_item = $section[0]->addChild("section");
            $res = CCVExport::mapDegree($person,
                                        $degree_map,
                                        $uni,
                                        $ccv_item);
        }
        
        $section = $ccv->xpath("section[@id='94b1e26073ae476da13ede5baf1f1cc0']");
        foreach($employment as $uni){
            $ccv_item = $section[0]->addChild("section");
            $res = CCVExport::mapEmployment($person,
                                            $employment_map,
                                            $uni,
                                            $ccv_item);
        }
        
        //HERE
        //==== Grants End ======== //
        
        // Format and indent the XML
        $xml_string = $ccv->asXML();
        $xml_string = preg_replace('/generic-cv:section/', 'section', $xml_string);
        $xml_string = preg_replace('/generic-cv:field/', 'field', $xml_string);
        $xml_string = preg_replace('/generic-cv:value/', 'value', $xml_string);
        $xml_string = preg_replace('/generic-cv:lov/', 'lov', $xml_string);
        $xml_string = preg_replace('/generic-cv:english/', 'english', $xml_string);
        $xml_string = preg_replace('/generic-cv:bilingual/', 'bilingual', $xml_string);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml_string);
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
                
                if($rel->getType() == CO_SUPERVISES){
                    self::setValue($lov, "Co-Supervisor");
                    $lov->addAttribute('id', '00000000000000000000000100002901');
                }
                else{
                    self::setValue($lov, "Principal Supervisor");
                    $lov->addAttribute('id', '00000000000000000000000100002900');
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
                        $ccv_date = $date;
                    }
                    else {
                        $ccv_date = date('Y/m', time() + 60*60*24*365);
                    }
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('value');
                    $val->addAttribute('type', "YearMonth");
                    $val->addAttribute('format', "yyyy/MM");
                    self::setValue($val, $ccv_date);
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
            elseif($item_name == "Degree Type or Postdoctoral Status"){    //else if($item_name == "Study / Postdoctoral Level"){
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                $group = "";
                if(in_array(strtolower($hqp_pos), Person::$studentPositions['ugrad'])){
                    $group = 'ugrad';
                }
                else if(in_array(strtolower($hqp_pos), Person::$studentPositions['msc'])){
                    $group = 'msc';
                }
                else if(in_array(strtolower($hqp_pos), Person::$studentPositions['phd'])){
                    $group = 'phd';
                }
                else if(in_array(strtolower($hqp_pos), Person::$studentPositions['pdf'])){
                    $group = 'pdf';
                }
                if(!empty($hqp_pos) && isset($degree_map[$group])){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('lov');
                    $lov_id = $degree_map[$group][0];
                    $val->addAttribute('id', $lov_id);
                    self::setValue($val, $degree_map[$group][1]);
                }
            }
            else if($item_name == "Student Degree Status"){
                // If active  Completed 
                // Otherwise  In Progress
                $uni = $hqp->getUniversity();
                $hqp_pos = $uni['position'];
                $start = $uni['start'];
                $end = $uni['date'];
                if(!empty($hqp_pos) && $hqp_pos !== 'PostDoc'){
                    $status_map = array('Completed'=>"00000000000000000000000000000068",
                                        'In Progress'=>"00000000000000000000000000000070");

                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('lov');
        
                    if($end != ZOTT &&
                       $end != ZOT){
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
                $hqp_thesis = $rel->getThesis(); // $hqp->getThesis();
                if(!is_null($hqp_thesis)){
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', $item_id);
                    $field->addAttribute('label', $item_name);
                    $val = $field->addChild('value');
                    $val->addAttribute('type', "String");
                    self::setValue($val, $hqp_thesis);
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
                $ccv_item = $ccv->addChild("section");
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
                        $prod_status = str_replace("Not Invited", "No", $prod_status);
                        $prod_status = str_replace("Invited", "Yes", $prod_status);
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
                       $product_data[$key] !== ''){
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
                    else if ($key == "invited" && 
                             $product->getCategory() == "Publication" && 
                             ($type == "Conference Paper" || $type == "Proceedings Paper")){
                        // Always add Invited field...
                        $field = $ccv_item->addChild("field");
                        $field->addAttribute('id', "720d2f02feaf4aacb06ce60be0c6f603");
                        $field->addAttribute('label', "Invited?");
                        $val = $field->addChild('lov');
                        $val->addAttribute('id', "00000000000000000000000000000401");
                        self::setValue($val, "No");
                    }
                }

                //Date
                $field = self::setChild($ccv_item, 'field', 'id', $item->date['ccv_id']);
                self::setAttribute($field, 'label', $item->date['ccv_name']);
                $val = self::setChild($field, 'value', 'type', 'Year');
                self::setAttribute($val, 'format', 'yyyy/MM');
                if($product->getStatus() == "Published"){
                    $product_date = str_replace('-', '/', substr($product->getDate(), 0, 7));
                }
                else{
                    $product_date = str_replace('-', '/', substr($product->getAcceptanceDate(), 0, 7));
                }
                $field->value = $product_date;
                
                //Authors
                $field = self::setChild($ccv_item, 'field', 'id', $item->authors['ccv_id']);
                self::setAttribute($field, 'label', $item->authors['ccv_name']);
        
                $product_authors = $product->getAuthors();
                $auth_arr = array();
                foreach($product_authors as $a){
                    if($item->authors['ccv_name'] == "Co-Presenters" && $a->getId() == $person->getId()){
                        continue;
                    }
                    $authorName = trim($a->getNameForProduct("{%last} {%f}"));
                    if($product->getCategory() == "Publication" &&
                       ($person->isRelatedToDuring($a, SUPERVISES, SOT, EOT) ||
                        $person->isRelatedToDuring($a, CO_SUPERVISES, SOT, EOT))){
                        $auth_arr[] = $authorName."*";
                    }
                    else{
                        $auth_arr[] = $authorName;
                    }
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
                
                // Presentation Keynote?
                if($product->getCategory() == "Presentation"){
                    // Always add Keynote field...
                    $field = $ccv_item->addChild("field");
                    $field->addAttribute('id', "9b6d317fd53e4b6a9e2e1d9e2001f3f5");
                    $field->addAttribute('label', "Keynote?");
                    if($type == "Keynote"){
                        $val = $field->addChild('lov');
                        $val->addAttribute('id', "00000000000000000000000000000400");
                        self::setValue($val, "Yes");
                    }
                    else {
                        $val = $field->addChild('lov');
                        $val->addAttribute('id', "00000000000000000000000000000401");
                        self::setValue($val, "No");
                    }
                }
                $success = 1;
            }
        }
        return $success;
    }
    
    static function mapEmployment($person, $section, $degree, $ccv){
        self::mapDegree($person, $section, $degree, $ccv);
        $ccv['id'] = "b857f61b33484cb093068bd2da764f99";
        $ccv['label'] = "Academic Work Experience";
    }

    static function mapDegree($person, $section, $degree, $ccv){
        global $edu_map;
        $uni = $degree['university'];
        $position = $degree['position'];
        $department = $degree['department'];
        $start = $degree['start'];
        $end = $degree['end'];
        $ccv->addAttribute('id', "aee5a225a504442fb83f716235cfb587");
        $ccv->addAttribute('label', "Degrees");
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($ccv, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "a83a0af883924c57bb66107cc32b6d5e":
                    $group = "";
                    if(in_array(strtolower($position), Person::$studentPositions['ugrad'])){
                        $group = 'ugrad';
                    }
                    else if(in_array(strtolower($position), Person::$studentPositions['msc'])){
                        $group = 'msc';
                    }
                    else if(in_array(strtolower($position), Person::$studentPositions['phd'])){
                        $group = 'phd';
                    }
                    if($position != "" && $edu_map[$group]){
                        $val = $field->addChild('lov');
                        $lov_id = $edu_map[$group][0];
                        $val->addAttribute('id', $lov_id);
                        self::setValue($val, $edu_map[$group][1]);
                    }
                    break;
                case "7df537009941493789a32bcae3499909":
                case "886807b87b624978bc8ca9045ff56e47";
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $position);
                    break;
                case "35696972e69541dd86a80521d3737b26":
                case "cd6e5e97994e42f893bd5c9e7212c94b":
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $department);
                    break;
                case "020ec1f40f3d4065bf5424f77209b8e4":
                case "69b0f4fab5e64d7da044e0f8cbd70e52":
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $uni);
                    break;
                case "c42a1deb6fe046dfa5502169032760de":
                    $status_map = array('Completed'=>"00000000000000000000000000000068",
                                        'In Progress'=>"00000000000000000000000000000070");

                    $val = $field->addChild('lov');
        
                    if($end != ZOTT &&
                       $end != ZOT){
                        $lov_id = $status_map['Completed'];
                        self::setValue($val, "Completed");
                    } else {
                        $lov_id = $status_map['In Progress'];
                        self::setValue($val, "In Progress");
                    }
                    $val->addAttribute('id', $lov_id);
                    break;
                case "337ee6b2606c4c899f0e0c4ec3bd6ec2":
                case "c7e85d10d10249c68b28c71fc80ec570":
                    $value = self::setChild($field, 'value', 'type', 'YearMonth');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($start, 0, 7)));
                    break;
                case "4b818aef68a84743b19149d376032afb":
                case "b4681f52d85440829faa3160ba3bb31f":
                    $value = self::setChild($field, 'value', 'type', 'YearMonth');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($end, 0, 7)));
                    break;
            }
        }
    }

    static function mapGrant($person, $section, $grant, $ccv, $investigator_map, $funding_year_map, $funding_source_map){
        global $wgUser, $wgOut;
        $success = 0;
        foreach($section->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($ccv, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "931b92a5ffed4e5aa9c7b3a0afd5f8ba": 
                    $gtype = $grant->getGrantType();
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Funding Type", $gtype, "Grant"));
                    self::setValue($lov, self::getLovVal("Funding Type", $gtype, "Grant"));
                    break;
                case "9c1db4674334436ca891b7b8a9e114bd":
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($grant->getStartDate(), 0, 7)));
                    break;
                case "b63179ab0f0e4c9eaa7e9a8130d60ee3":
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($grant->getEndDate(), 0, 7)));
                    break;
                case "735545eb499e4cc6a949b4b375a804e8":
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $grant->getTitle());
                    break;
                case "0674312de78f4647aba3bf202a41d58e":
                    $bilin = $field->addChild("bilingual");
                    $bilin->addChild("english");
                    $bilin->english = $grant->getDescription();
                    break;
                case "0991ead151e3445ca7537aa15acbec57":
                    //$gtype = $grant->getStatus();
                    //$lov = self::setChild($field, 'lov');
                    //self::setAttribute($lov, 'id', self::getLovId("Funding Status", $gtype, "Awarded"));
                    //self::setValue($lov, self::getLovVal("Funding Status", $gtype, "Awarded"));
                    break;
                case "7496de092dc84038a1881e8f9d77e713":
                    $gtype = $grant->getRole();
                    $lov = self::setChild($field, 'lov');
                    self::setAttribute($lov, 'id', self::getLovId("Funding Role", $gtype, "Principal Investigator"));
                    self::setValue($lov, self::getLovVal("Funding Role", $gtype, "Principal Investigator"));
                    break;
                case "32ce1c0c194447c19c6847b1915d35f1":
                    $bilin = $field->addChild("bilingual");
                    $bilin->addChild("english");
                    self::setValue($bilin->english, "");
                    break;
            }
        }
                /*Find different way for grants
        foreach($grant->getPeople() as $guser){
            if($person->getId() != $guser->getId()){
                $ccv_item = $ccv->addChild("section");
                $ccv_id = "c7c473d1237b432fb7f2abd831130fb7";
                $ccv_name = "Other Investigators";

                self::setAttribute($ccv_item, 'id', $ccv_id);
                self::setAttribute($ccv_item, 'label', $ccv_name);
                foreach($investigator_map->field as $item){
                    $id = $item['id'];
                    $label = $item['label'];
                    $field = self::setChild($ccv_item, 'field', 'id', $id);
                    self::setAttribute($field, 'label', $label);
                    switch($id){
                        case "ddd551dfb26344fbb17f07afcffc94ed":
                            $value = self::setChild($field, 'value', 'type', 'String');
                            self::setValue($value, $guser->getReversedName());
                            break;
                        case "13806a6772d248158619261afaab2fe0":
                            //$grole = $guser->getRole();
                            $grole = "Co-applicant";
                            $lov = self::setChild($field, 'lov');
                            self::setAttribute($lov, 'id', self::getLovId("Funding Role", $grole, "Co-applicant"));
                            self::setValue($lov, self::getLovVal("Funding Role", $grole, "Co-applicant"));
                            break;
`                   }
                }
            }   
        }*/
        /*Grants only have one sponsor
        foreach($grant->getPartners() as $gpartner){
            if($gpartner->getOrganization() != ""){
                $ccv_item = $ccv->addChild("section");
                $ccv_id = "376b8991609f46059a3d66028f005360";
                $ccv_name = "Funding Sources";

                self::setAttribute($ccv_item, 'id', $ccv_id);
                self::setAttribute($ccv_item, 'label', $ccv_name);
                foreach($funding_source_map->field as $item){
                    $id = $item['id'];
                    $label = $item['label'];
                    $field = self::setChild($ccv_item, 'field', 'id', $id);
                    self::setAttribute($field, 'label', $label);
                    switch($id){
                        case "1bdead14642545f3971a59997d82da67":
                            $value = self::setChild($field, 'value', 'type', 'String');
                            self::setValue($value, $gpartner->getOrganization());
                            break;
                        case "dfe6a0b34347486aaa677f07306a141e":
                            $value = self::setChild($field, 'value', 'type', 'Number');
                            self::setValue($value, $grant->getCash());
                            break;
                    }
                }
           }
        }*/
        $ccv_item = $ccv->addChild("section");
        $ccv_id = "376b8991609f46059a3d66028f005360";
        $ccv_name = "Funding Sources";

        self::setAttribute($ccv_item, 'id', $ccv_id);
        self::setAttribute($ccv_item, 'label', $ccv_name);
        foreach($funding_source_map->field as $item){
            $id = $item['id'];
            $label = $item['label'];
            $field = self::setChild($ccv_item, 'field', 'id', $id);
            self::setAttribute($field, 'label', $label);
            switch($id){
                case "1bdead14642545f3971a59997d82da67":
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $grant->getSponsor());
                    break;
                case "dfe6a0b34347486aaa677f07306a141e":
                    $value = self::setChild($field, 'value', 'type', 'Number');
                    self::setValue($value, $grant->getTotal());
                    break;
                case "d62313c1cdb9419caf79014f07e1cfe0":
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($grant->getStartDate(), 0, 7)));
                    break;
                case "3fb9015d879f435d937ae9aa7ccd2973":
                    $value = self::setChild($field, 'value', 'type', 'String');
                    self::setValue($value, $grant->getProjectId());
                    break;
                case "efc68e7d74f849eebb59f9a3bb85e5db":
                    $value = self::setChild($field, 'value', 'type', 'Date');
                    self::setAttribute($value, 'format', 'yyyy/MM');
                    self::setValue($value, str_replace("-","/",substr($grant->getEndDate(), 0, 7)));
                    break;

            }
        }

    }

}

?>
