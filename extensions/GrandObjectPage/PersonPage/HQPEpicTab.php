<?php

class HQPEpicTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function HQPEpicTab($person, $visibility){
        parent::AbstractEditableTab("EPIC");
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
        $this->generateAffiliate();
    }
    
    function generateEditBody(){
        global $config;
        
        return $this->html;
    }
    
    function generatePhD(){
    
    }
    
    function generatePDF(){
    
    }
    
    function generateMasters(){
    
    }
    
    function generateAffiliate(){
        $this->html .= "<div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For Affiliate HQP</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC.  You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
<p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
<p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on Forum, you are strongly encouraged to consider eligible activities available through your departments and institutions.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>All activities must be completed within 1 year of enrolling in the certificate program. There are four major steps:</p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist attached and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved. Using the attached checklist as your training plan, submit within 2 months of enrollment a list of the activities you would like to complete to the Education and Training Administrator via Forum and await approval.</li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly, and submit for approval.</li>
    <li><b>COMPLETE AND SUBMIT ANNUAL REPORT:</b> After completing your activities, submit via Forum an annual report that includes your completed checklist, evidence of satisfactory completion of your planned activities, and a brief evaluation of the activities chosen in terms of learning outcomes.</li>
</ol>";
        $this->html .= "<table class='wikitable'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (Affiliate HQP)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum Affiliate HQP Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or through Forum",
                               array(HQP_EPIC_ORIENTATION_DESC, HQP_EPIC_ORIENTATION_HQP, HQP_EPIC_ORIENTATION_NMO)).
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or through Forum",
                               array(HQP_EPIC_ORIENTATION_DESC, HQP_EPIC_ORIENTATION_HQP, HQP_EPIC_ORIENTATION_NMO)).
            $this->generateRow("Training Plan submitted to and approved by AGE-WELL Education and Training Administrator",
                               array(HQP_EPIC_TRAINING_DESC, HQP_EPIC_TRAINING_HQP, HQP_EPIC_TRAINING_NMO)).
            "<tr>
                <th colspan='4'>Courses/Workshops</th>
            </tr>".
            $this->generateRow("1 Course/Workshop in KTEE (e.g. attend a MITACS Step workshop)",
                               array(HQP_EPIC_WORKSHOP_DESC, HQP_EPIC_WORKSHOP_HQP, HQP_EPIC_WORKSHOP_NMO)).
            "<tr>
                <th colspan='4'>Online Activities</th>
            </tr>".
            $this->generateRow("1 Online Activity in any core competency (e.g. view a webinar on working with big data)",
                               array(HQP_EPIC_WEB_DESC, HQP_EPIC_WEB_HQP, HQP_EPIC_WEB_NMO)).
            $this->generateRow("1 Online Activity in a different core competency than above (e.g. write a blog entry about a conference you attended)",
                               array(HQP_EPIC_BLOG_DESC, HQP_EPIC_BLOG_HQP, HQP_EPIC_BLOG_NMO)).
            "<tr>
                <th colspan='4'>Self-Selected Activites</th>
            </tr>".
            $this->generateRow("1 Activity of your choice from remaining core competency area(s) (e.g. discuss end-user concerns with a stakeholder)",
                               array(HQP_EPIC_SELF_DESC, HQP_EPIC_SELF_HQP, HQP_EPIC_SELF_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("Training activities, publications, presentations must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateRow("Annual Report w/ evidence of completed activities submitted to Forum by 15 August",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generateRow($description, $cells=array()){
        $str = "<tr>";
        $str .= "<td><small>{$description}</small></td>";
        foreach($cells as $key => $cell){
            $value = $this->getBlobValue($cell);
            if($key == 0){
                // Description
                $str .= "<td>{$value}</td>";
            }
            else{
                // Checkboxes
                $str .= "<td align='center'>{$value}</td>";
            }
        }
        $str .= "</tr>";
        return $str;
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_EPIC, $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address(RP_HQP_APPLICATION, HQP_APPLICATION_EPIC, $blobItem, 0);
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
