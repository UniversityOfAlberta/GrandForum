<?php

class HQPProfileTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function HQPProfileTab($person, $visibility){
        parent::AbstractEditableTab("HQP Profile");
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

    function generateBody(){
        if(!$this->userCanView()){
            return "";
        }
        $research = nl2br($this->getBlobValue(HQP_APPLICATION_RESEARCH));
        $train    = nl2br($this->getBlobValue(HQP_APPLICATION_TRAIN));
        $bio      = nl2br($this->getBlobValue(HQP_APPLICATION_BIO));
        $align    = nl2br($this->getBlobValue(HQP_APPLICATION_ALIGN));
        $boundary = nl2br($this->getBlobValue(HQP_APPLICATION_BOUNDARY));
        if($research    == "" &&
           $train       == "" &&
           $bio         == "" &&
           $align       == "" &&
           $boundary    == "" && 
           !$this->visibility['isMe']){
            return "";
        }
        $this->html .= "<h2>Core Research Program Highly Qualified Personnel Application</h2>";
        $this->html .= "<h3>Statement of Research Focus</h3>";
        $this->html .= "<p>{$research}</p>";
        $this->html .= "<h3>Statement of Training Focus</h3>";
        $this->html .= "<p>{$train}</p>";
        $this->html .= "<h3>Biography and Career Goals</h3>";
        $this->html .= "<p>{$bio}</p>";
        $this->html .= "<h3>Alignment of research, training, and/or career goals to the mission and goals of AGE-WELL</h3>";
        $this->html .= "<p>{$align}</p>";
        $this->html .= "<h3>In what ways are you interested in going beyond conventional disciplinary boundaries?</h3>";
        $this->html .= "<p>{$boundary}</p>";
        return $this->html;
    }
    
    function generateEditBody(){
        global $config;
        $research = $this->getBlobValue(HQP_APPLICATION_RESEARCH);
        $train    = $this->getBlobValue(HQP_APPLICATION_TRAIN);
        $bio      = $this->getBlobValue(HQP_APPLICATION_BIO);
        $align    = $this->getBlobValue(HQP_APPLICATION_ALIGN);
        $boundary = $this->getBlobValue(HQP_APPLICATION_BOUNDARY);
        
        $this->html .= "<h2>Core Research Program Highly Qualified Personnel Application</h2>";
        $this->html .= "<h3>Statement of Research Focus</h3>
<small>
    <p>AGE-WELL is inviting trainees and other highly qualified personnel in the broad area of technology and aging. The research project may be linked to one of AGE-WELL’s eight Research Workpackages, but does not need to limited to these.  Research should address at least one of our key research questions: </p>
    <ol>
        <li>What are the needs of older adults and caregivers?</li>
        <li>What technology-based systems and services should be used to meet those needs?</li>
        <li>How can we foster innovation in the technology and aging sector?</li>
    </ol>
    <p>
    The project may also support one of AGE-WELL’s four Crosscutting activities- Knowledge Mobilization, Commercialization, Transdisciplinarity and HQP Training. 
    </p>
    <p>In this section, applicants should describe:</p>
    <ol>
        <li>How does your research connect to research foci of the AGE-WELL NCE?</li>
        <li>Why is your project important?</li>
        <li>Who is your target user population(s)?</li>
        <li>What is the expected impact of your research?</li>
    </ol>
</small>";
        $this->html .= "<textarea name='research' style='height:200px;'>{$research}</textarea>";
        $this->html .= "<h3>Statement of Training Focus</h3>
<small>
    <p>AGE-WELL is inviting trainees and other highly qualified personnel in the broad area of technology and aging, including students in professional programs (i.e. non-thesis bases programs) such as gerontology and allied health. The training program and the work being undertaken may be linked to one of AGE-WELL’s eight Research Workpackages, but does not need to limited to these.</p>

    <p>In this section, applicants should describe:</p>
    <ol>
        <li>How does your training program and/or background relate to the AGE-WELL NCE?</li>
        <li>What is the expected impact of your work?</li>
    </ol>
</small>";
        $this->html .= "<textarea name='train' style='height:200px;'>{$train}</textarea>";
        $this->html .= "<h3>Biography and Career Goals</h3>
<small>
    <p>In this section briefly describe:</p>
    <ol>
        <li>Your current goals</li>
        <li>Where you see yourself in 5-10 years</li>
        <li>How you see AGE-WELL helping you to achieve your goals</li>
    </ol>
</small>";
        $this->html .= "<textarea name='bio' style='height:200px;'>{$bio}</textarea>";
        $this->html .= "<h3>Alignment of research, training, and/or career goals to the mission and goals of AGE-WELL</h3>
<small>
    <p>Successful applications will need to be aligned with the key strategic goals of AGE-WELL. Applicants are strongly encouraged to familiarize themselves with the document called AGE-WELL Network Goals available on the <a target='_blank' href='{$config->getValue('networkSite')}'>AGE-WELL website</a></p>

<p>Applicants will need to demonstrate that their research, training, and/or career goals has potential for real world impact.  In this section, describe how these goals fits with AGE-WELL’s vision and strategic goals.</p>
</small>";
        $this->html .= "<textarea name='align' style='height:200px;'>{$align}</textarea>";
        $this->html .= "<h3>In what ways are you interested in going beyond conventional disciplinary boundaries?</h3>
<small>
    <p>Transdisciplinary working - that is working across and with other disciplines than your own – is an important aspect of AGE-WELL.  In this section please address the following:</p>
    <ol>
        <li>Describe networking that may occur across disciplines and sites within AGE-WELL.</li>
        <li>How does your work and goals link with other projects/activities in the AGE-WELL Network?</li>
    </ol>
</small>";
        $this->html .= "<textarea name='boundary' style='height:200px;'>{$boundary}</textarea>";
        
        return $this->html;
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        return $data;
    }
    
    function saveBlobValue($blobItem, $value){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_FORM, $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        
        $this->saveBlobValue(HQP_APPLICATION_RESEARCH,   $_POST['research']);
        $this->saveBlobValue(HQP_APPLICATION_TRAIN,      $_POST['train']);
        $this->saveBlobValue(HQP_APPLICATION_BIO,        $_POST['bio']);
        $this->saveBlobValue(HQP_APPLICATION_ALIGN,      $_POST['align']);
        $this->saveBlobValue(HQP_APPLICATION_BOUNDARY,   $_POST['boundary']);
        
        header("Location: {$this->person->getUrl()}?tab=hqp-crp");
        exit;
    }
    
    function canEdit(){
        return ($this->visibility['isMe']);
    }
    
}
?>
