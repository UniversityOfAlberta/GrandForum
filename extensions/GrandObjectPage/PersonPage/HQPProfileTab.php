<?php

class HQPProfileTab extends AbstractEditableTab {

    static $cache = array();

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("HQP Profile");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        $projects = $this->person->getProjects();
        $sameProject = false;
        foreach($projects as $project){
            if($me->isMemberOf($project)){
                $sameProject = true;
                break;
            }
        }
        // Only allow the user, supervisors, people from the same project and STAFF+ to view the tab
        return ($this->visibility['isMe'] || 
                $this->visibility['isSupervisor'] ||
                $sameProject ||
                $me->isRoleAtLeast(STAFF));
    }

    function generateBody($year=false){
        if(!$this->userCanView()){
            return "";
        }
        $research = nl2br($this->getBlobValue(HQP_APPLICATION_RESEARCH, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
        $train    = nl2br($this->getBlobValue(HQP_APPLICATION_TRAIN, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
        $bio      = nl2br($this->getBlobValue(HQP_APPLICATION_BIO, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
        $align    = nl2br($this->getBlobValue(HQP_APPLICATION_ALIGN, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
        $boundary = nl2br($this->getBlobValue(HQP_APPLICATION_BOUNDARY, BLOB_TEXT, HQP_APPLICATION_FORM, true, $year));
        $cv       = $this->getBlobValue(HQP_APPLICATION_CV, BLOB_RAW, HQP_APPLICATION_DOCS, true, $year);
        if($boundary != ""){
            $align .= "<br />{$boundary}";
        }
        if($research    == "" &&
           $train       == "" &&
           $bio         == "" &&
           $align       == "" &&
           $boundary    == "" && 
           !$this->visibility['isMe']){
            return "";
        }
        $this->html .= "<h3>Statement of Research Focus</h3>";
        $this->html .= "<p>{$research}</p>";
        $this->html .= "<h3>Statement of Training or Career Focus</h3>";
        $this->html .= "<p>{$train}</p>";
        $this->html .= "<h3>Biography and Career Goals</h3>";
        $this->html .= "<p>{$bio}</p>";
        $this->html .= "<h3>Alignment to the Mission and Goals of AGE-WELL</h3>";
        $this->html .= "<p>{$align}</p>";
        $this->html .= "<h3>CV</h3>";
        $this->html .= "<p>{$cv}</p>";
        return $this->html;
    }
    
    function generateEditBody(){
        global $config;
        if(!$this->userCanView()){
            return "";
        }
        $research = $this->getBlobValue(HQP_APPLICATION_RESEARCH);
        $train    = $this->getBlobValue(HQP_APPLICATION_TRAIN);
        $bio      = $this->getBlobValue(HQP_APPLICATION_BIO);
        $align    = $this->getBlobValue(HQP_APPLICATION_ALIGN);
        $boundary = $this->getBlobValue(HQP_APPLICATION_BOUNDARY);
        $cv       = $this->getBlobValue(HQP_APPLICATION_CV, BLOB_RAW, HQP_APPLICATION_DOCS);
        
        if($boundary != ""){
            $align .= "\n{$boundary}";
        }

        $this->html .= "<h3>Statement of Research Focus (for HQP completing a research program) (½ page)</h3>
<small>
    <p>Your research project may be linked to one of AGE-WELL’s eight Research Workpackages, but does not need to limited to these. Research should address at least one of our key research questions:</p>
    <ol>
        <li>What are the needs of older adults and caregivers?</li>
        <li>What technology-based systems and services should be used to meet those needs?</li>
        <li>How can we foster innovation in the technology and aging sector?</li>
    </ol>
    <p>
    The project may also support one of AGE-WELL’s four Crosscutting activities - Knowledge Mobilization, Commercialization, Transdisciplinarity and HQP Training.</p>
    <p>In this section please describe:</p>
    <ol>
        <li>How does your research connect to research foci of the AGE-WELL NCE?</li>
        <li>Why is your project important?</li>
        <li>Who is your target user population(s)?</li>
        <li>What is the expected impact of your research?</li>
    </ol>
</small>";
        $this->html .= "<textarea name='research' style='height:200px;'>{$research}</textarea>";
        $this->html .= "<h3>Statement of Training or Career Focus (for research associates and trainees in professional programs) (½ page)</h3>
<small>
    <p>In this section please describe:</p>
    <ol>
        <li>How does your training program and/or background relate to the AGE-WELL NCE?</li>
        <li>What is the expected impact of your work?</li>
    </ol>
</small>";
        $this->html .= "<textarea name='train' style='height:200px;'>{$train}</textarea>";
        $this->html .= "<h3>Biography and Career Goals (½ page)</h3>
<small>
    <p>In this section briefly describe:</p>
    <ol>
        <li>Your current goals</li>
        <li>Where you see yourself in 5-10 years</li>
        <li>How you see AGE-WELL helping you to achieve your goals</li>
    </ol>
</small>";
        $this->html .= "<textarea name='bio' style='height:200px;'>{$bio}</textarea>";
        $this->html .= "<h3>Alignment to the Mission and Goals of AGE-WELL (½ page)</h3>
<small>
    <p>AGE-WELL HQP will need to be aligned with the key strategic goals of AGE-WELL and are strongly encouraged to familiarize themselves with the document called AGE-WELL Network Goals available on the <a target='_blank' href='{$config->getValue('networkSite')}'>AGE-WELL website</a></p>

<p>HQP will need to demonstrate that their research, training, and/or career goals has potential for real world impact. In this section, describe how these goals fit with AGE-WELL’s vision and strategic goals.</p>
</small>";
        $this->html .= "<textarea name='align' style='height:200px;'>{$align}</textarea>";
        $this->html .= "<h3>CV Upload</h3>
        <p>{$cv}</p>
        <input type='file' name='cv' accept='.pdf' /><br />";
        return $this->html;
    }
    
    function getBlobValue($blobItem, $type=BLOB_TEXT, $section=HQP_APPLICATION_FORM, $checkRegistration=true, $checkYear=false){
        global $wgServer, $wgScriptPath;
        
        $data = "";
        $personId = $this->person->getId();
        $projectId = 0;
        
        if(!isset(self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$checkYear])){
        
            $year = 0; // Don't have a year so that it remains the same each year
            
            $blb = new ReportBlob($type, $year, $personId, $projectId);
            $addr = ReportBlob::create_address(RP_HQP_APPLICATION, $section, $blobItem, 0);
            $result = $blb->load($addr, true);
            $tmpdata = $blb->getData();
            if(!$checkYear){
                $data = $blb->getData();
            }
            
            if($checkRegistration){
                $year = (!$checkYear) ? date('Y') : $checkYear;
                $endYear = (!$checkYear) ? substr($this->person->getRegistration(), 0, 4) : $checkYear;
                while($data === null && $year >= $endYear){
                    // If it is empty, check to see if there was an entry for one of the other years
                    if(!isset(self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$year])){
                        $blb = new ReportBlob($type, $year, $personId, $projectId);
                        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, $section, $blobItem, 0);
                        $result = $blb->load($addr, true);
                        $data = $blb->getData();
                        self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$year] = $data;
                    }
                    $data = self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$year];
                    $year--;
                }
            }
            
            if($data === null){
                $data = $tmpdata;
            }
            if($type == BLOB_RAW && $data != null){
                $data = json_decode($data);
                $mime = $data->type;
                $md5 = $blb->getMD5();
                $data = "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime={$mime}'>Download</a>";
            }
            self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$checkYear] = $data;
        }
        else {
            $data = self::$cache[$personId][$blobItem][$type][$section][$checkRegistration][$checkYear];
        }
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value, $type=BLOB_TEXT, $section=HQP_APPLICATION_FORM){
        if($type == BLOB_RAW){
            $contents = base64_encode(file_get_contents($value['tmp_name']));
            $hash = md5($contents);
            $name = $value['name'];
            $size = $value['size'];
            $fileType = $value['type'];
            $data = array('name' => $name,
                          'type' => $fileType,
                          'size' => $size,
                          'hash' => $hash,
                          'file' => $contents);
            $value = json_encode($data);
        }
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob($type, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, $section, $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    /**
     * Returns whether the person has edited their HQP Profile
     * @return boolean Whether the Person has edited their HQP Profile
     */
    function hasEdited(){
        $research = nl2br($this->getBlobValue(HQP_APPLICATION_RESEARCH, BLOB_TEXT, HQP_APPLICATION_FORM, false));
        $train    = nl2br($this->getBlobValue(HQP_APPLICATION_TRAIN, BLOB_TEXT, HQP_APPLICATION_FORM, false));
        $bio      = nl2br($this->getBlobValue(HQP_APPLICATION_BIO, BLOB_TEXT, HQP_APPLICATION_FORM, false));
        $align    = nl2br($this->getBlobValue(HQP_APPLICATION_ALIGN, BLOB_TEXT, HQP_APPLICATION_FORM, false));
        $boundary = nl2br($this->getBlobValue(HQP_APPLICATION_BOUNDARY, BLOB_TEXT, HQP_APPLICATION_FORM, false));
        $cv       = $this->getBlobValue(HQP_APPLICATION_CV, BLOB_RAW, HQP_APPLICATION_DOCS, false);
        
        return ($research != null || 
                $train != null ||
                $bio != null ||
                $align != null ||
                $boundary != null ||
                $cv != null);
    }
    
    /**
     * Returns when the HQP Profile was last updated
     * @param string $maxYear The max year that this can be
     */
    function lastUpdated($maxYear=false){
        $personId = $this->person->getId();
        $projectId = 0;
    
        if($maxYear == false){ $maxYear = date('Y'); }
        $data = DBFunctions::execSQL("SELECT changed
                                      FROM grand_report_blobs
                                      WHERE user_id = '$personId'
                                      AND year <= $maxYear
                                      AND rp_type = ".RP_HQP_APPLICATION."
                                      AND (rp_section = ".HQP_APPLICATION_FORM." AND 
                                           (rp_item = ".HQP_APPLICATION_RESEARCH." OR
                                            rp_item = ".HQP_APPLICATION_TRAIN." OR
                                            rp_item = ".HQP_APPLICATION_BIO." OR
                                            rp_item = ".HQP_APPLICATION_ALIGN." OR
                                            rp_item = ".HQP_APPLICATION_BOUNDARY."))
                                      ORDER BY changed DESC
                                      LIMIT 1");
        return @$data[0]['changed'];
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        
        $this->saveBlobValue(HQP_APPLICATION_RESEARCH,   $_POST['research']);
        $this->saveBlobValue(HQP_APPLICATION_TRAIN,      $_POST['train']);
        $this->saveBlobValue(HQP_APPLICATION_BIO,        $_POST['bio']);
        $this->saveBlobValue(HQP_APPLICATION_ALIGN,      $_POST['align']);
        $this->saveBlobValue(HQP_APPLICATION_BOUNDARY,   @$_POST['boundary']);
        if(isset($_FILES['cv']) && $_FILES['cv']['size'] > 0){
            $this->saveBlobValue(HQP_APPLICATION_CV,     $_FILES['cv'], BLOB_RAW, HQP_APPLICATION_DOCS);
        }
        
        header("Location: {$this->person->getUrl()}?tab=hqp-profile");
        exit;
    }
    
    function canEdit(){
        return ($this->visibility['isMe']);
    }
    
}
?>
