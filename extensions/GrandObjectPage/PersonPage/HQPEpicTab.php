<?php

class HQPEpicTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function __construct($person, $visibility){
        parent::__construct("EPIC");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        // Only allow the user, supervisors, and STAFF+ to view the tab
        if($this->person->isEpic()){
            return ($this->visibility['isMe'] || 
                    $this->visibility['isSupervisor'] ||
                    $me->isRoleAtLeast(SD) ||
                    $me->isEvaluatorOf($this->person, "RP_SUMMER", YEAR, "Person"));
        }
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if(!$this->userCanView()){
            return "";
        }
        if($this->visibility['isMe'] && $this->person->isSubRole("SIP/CAT HQP")){
            $this->html .= "<div class='info'>Further information about the EPIC program and requirements is available below.  Please contact the AGE-WELL Education and Training administrator (training@agewell-nce.ca) if you would like to complete the EPIC program requirements to earn the Innovators of Tomorrow Certificate.</div>";
        }
        $position = strtolower($this->person->getPosition());
        if(($this->person->isSubRole("WP/CC Funded HQP") || 
            $this->person->isSubRole("Project Funded HQP") ||
            $this->person->isSubRole("Alumni HQP") ||
            $this->person->isSubRole("SIP/CAT HQP")) &&
           ($position != "graduate student - doctoral" &&
            $position != "medical student" &&
            $position != "graduate student - master's" &&
            $position != "post-doctoral fellow")){
            $this->generateWPCC();
        }
        else if($this->person->isSubRole("Affiliate HQP") || ($position == "undergraduate student" && $this->person->isSubRole("Alumni HQP"))){
            $this->generateAffiliate();
        }
        else if($position == "graduate student - doctoral"){
            $this->generatePhD();
        }
        else if($position == "graduate student - master's" || $position == "medical student"){
            $this->generateMasters();
        }
        else if($position == "post-doctoral fellow"){
            $this->generatePDF();
        }
        if($me->getId() == $this->person->getId()){
            $this->html .= "<p><b>When your training is complete, please fill out the <a href='{$wgServer}{$wgScriptPath}/index.php/Special:Report?report=EPICReport'>Annual Report - EPIC Survey</a></b></p>";
        }
    }
    
    function generateEditBody(){
        $this->generateBody();
    }
    
    function generatePhD(){
        $this->html .= "<div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For Doctoral Students</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC. You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
                        <p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
                        <p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on the members' intranet (<a href='http://members.agewell-nce.ca/videos/' target='_blank'>AGE-WELL recorded webinars</a>, <a href='http://members.agewell-nce.ca/events/' target='_blank'>AGE-WELL upcoming events</a>, and <a href='http://members.agewell-nce.ca/hqp-nmo-resources/' target='_blank'>HQP Resources</a>), you are strongly encouraged to consider eligible activities available through your departments and institutions. A collected list of these is available <a href='http://members.agewell-nce.ca/wp-content/uploads/2016/02/Non_AGE-WELL_HQP_activities.pdf' target='_blank'>here</a> and <a href='http://www.ca.cags.ca/gdps/index.php' target='_blank'>here</a>.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>HQP must complete all of their EPIC requirements in one calendar year to earn the Innovators of Tomorrow Certificate. There are four major steps as outlined below.</p>
<p><b>INSTRUCTIONS</b></p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist on this page and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved.
        <ul>
            <li>Use this checklist on your Forum EPIC tab as your training plan. To submit a list of activities you are interested in completing, please scroll to the bottom of the page and click on the “edit EPIC” button on the lower left corner.  Your EPIC table is now editable and all changes are submitted automatically to the Education and Training Administrator for review. </li>
            <li>For those with an “Approved (supervisor)” column: Once you have entered your planned activities, please show your plan to your principal supervisor for approval. At this point, your supervisor, if an AGE-WELL investigator, may check off the “Approved (Supervisor)” box.  If your supervisor is not an AGE-WELL investigator, please share your plans (screenshot, email etc.) and check off the box yourself once you have obtained their approval.</li>
            <li>Once your completed plan has been reviewed by the Education and Training Administrator, a check mark will appear in the “Completed (NMO)” box.</li>
            <li>Training plans should be completed and submitted within 2 months of accepting AGE-WELL funding.</li>
        </ul>
    </li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly.
        <ul>
            <li>Please check the appropriate “Completed (HQP)” box when you have completed an activity and retain evidence of completion/participation for the final annual report.</li>
            <li>Evidence of activity completion or participation may include: a screenshot, an abstract, conference proceedings, emails, a letter from your supervisor to name a few.</li>
        </ul>
    </li>
    <li><b>COMPLETE AND SUBMIT EPIC REPORTING:</b> After completing your activities please ensure that your EPIC checklist is completed in full. To receive your certificate you must complete and submit the EPIC Survey located at the bottom of your EPIC table. This survey is a brief evaluation of the EPIC program and allows you to upload your evidence of activity completion.  Once this is submitted, the Education and Training Administrator will contact you with a certificate or follow-up questions.</li>
</ol>";
        $this->html .= "<table class='wikitable' style='border: 1px !important;'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (Doctoral)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum Doctoral Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Approved<br />(supervisor)</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or view <a href='http://members.agewell-nce.ca/age-well-hqp-orientation/' target='_blank'>recorded session</a> on members' intranet",
                               array(HQP_EPIC_ORIENTATION_DESC, HQP_EPIC_ORIENTATION_SUP, HQP_EPIC_ORIENTATION_HQP, HQP_EPIC_ORIENTATION_NMO)).
            $this->generateRow("Training Plan submitted to and approved by AGE-WELL Education and Training Administrator",
                               array(HQP_EPIC_TRAINING_DESC, HQP_EPIC_TRAINING_SUP, HQP_EPIC_TRAINING_HQP, HQP_EPIC_TRAINING_NMO)).
            "<tr>
                <th colspan='4'>Courses/Workshops</th>
            </tr>".
            $this->generateRow("1 Course/Workshop in KTEE (e.g. attend a MITACS Step workshop)",
                               array(HQP_EPIC_WORKSHOP_DESC, HQP_EPIC_WORKSHOP_SUP, HQP_EPIC_WORKSHOP_HQP, HQP_EPIC_WORKSHOP_NMO)).
            $this->generateRow("1 Course/Workshop in any core competency (e.g. complete a course on research ethics)", 
                                array(HQP_EPIC_CORE_DESC, HQP_EPIC_CORE_SUP, HQP_EPIC_CORE_HQP, HQP_EPIC_CORE_NMO)).
            "<tr>
                <th colspan='4'>Online Activities</th>
            </tr>".
            $this->generateRow("1 Online Activity in KTEE (e.g. participate in a journal club)",
                               array(HQP_EPIC_KTEE_DESC, HQP_EPIC_KTEE_SUP, HQP_EPIC_KTEE_HQP, HQP_EPIC_KTEE_NMO)).
            $this->generateRow("1 Online Activity in Transdisciplinary Research (e.g. view a webinar on project planning)",
                               array(HQP_EPIC_TRANS_DESC, HQP_EPIC_TRANS_SUP, HQP_EPIC_TRANS_HQP, HQP_EPIC_TRANS_NMO)).
            $this->generateRow("1 Online Activity in Ethical Issues (e.g. write a blog post about data security)",
                               array(HQP_EPIC_ETHICS_DESC, HQP_EPIC_ETHICS_SUP, HQP_EPIC_ETHICS_HQP, HQP_EPIC_ETHICS_NMO)).
            $this->generateRow("1 Online Activity in Understanding Impact (e.g. participate in discussion forum on quality of life)",
                               array(HQP_EPIC_IMPACT_DESC, HQP_EPIC_IMPACT_SUP, HQP_EPIC_IMPACT_HQP, HQP_EPIC_IMPACT_NMO)).
            "<tr>
                <th colspan='4'>Self-Selected Activities</th>
            </tr>".
            $this->generateRow("1 Unique Activity of your choice that involves experiential 
learning, mentorship, or AGE-WELL network activities (e.g. be mentored by an expert stakeholder)",
                               array(HQP_EPIC_EXP1_DESC, HQP_EPIC_EXP1_SUP, HQP_EPIC_EXP1_HQP, HQP_EPIC_EXP1_NMO)).
            $this->generateRow("1 Unique Activity of your choice that involves experiential 
learning, mentorship, or AGE-WELL network activities (e.g. participate in a Pitch Event)",
                               array(HQP_EPIC_EXP2_DESC, HQP_EPIC_EXP2_SUP, HQP_EPIC_EXP2_HQP, HQP_EPIC_EXP2_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("NCE Reporting: EPIC tabs, outputs (publications, presentations etc.) & contributions (awards, grants etc.) must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_SUP, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateSpecialRow("EPIC Reporting: EPIC survey w/ evidence of completed activities submitted to Forum by ",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_SUP, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generatePDF(){
        $this->html .= "<div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For Post-Doctoral Fellows</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC. You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
                        <p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
                        <p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on the members' intranet (<a href='http://members.agewell-nce.ca/videos/' target='_blank'>AGE-WELL recorded webinars</a>, <a href='http://members.agewell-nce.ca/events/' target='_blank'>AGE-WELL upcoming events</a>, and <a href='http://members.agewell-nce.ca/hqp-nmo-resources/' target='_blank'>HQP Resources</a>), you are strongly encouraged to consider eligible activities available through your departments and institutions. A collected list of these is available <a href='http://members.agewell-nce.ca/wp-content/uploads/2016/02/Non_AGE-WELL_HQP_activities.pdf' target='_blank'>here</a> and <a href='http://www.ca.cags.ca/gdps/index.php' target='_blank'>here</a>.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>HQP must complete all of their EPIC requirements in one calendar year to earn the Innovators of Tomorrow Certificate. There are four major steps as outlined below.</p>
<p><b>INSTRUCTIONS</b></p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist on this page and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved.
        <ul>
            <li>Use this checklist on your Forum EPIC tab as your training plan. To submit a list of activities you are interested in completing, please scroll to the bottom of the page and click on the “edit EPIC” button on the lower left corner.  Your EPIC table is now editable and all changes are submitted automatically to the Education and Training Administrator for review. </li>
            <li>For those with an “Approved (supervisor)” column: Once you have entered your planned activities, please show your plan to your principal supervisor for approval. At this point, your supervisor, if an AGE-WELL investigator, may check off the “Approved (Supervisor)” box.  If your supervisor is not an AGE-WELL investigator, please share your plans (screenshot, email etc.) and check off the box yourself once you have obtained their approval.</li>
            <li>Once your completed plan has been reviewed by the Education and Training Administrator, a check mark will appear in the “Completed (NMO)” box.</li>
            <li>Training plans should be completed and submitted within 2 months of accepting AGE-WELL funding.</li>
        </ul>
    </li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly.
        <ul>
            <li>Please check the appropriate “Completed (HQP)” box when you have completed an activity and retain evidence of completion/participation for the final annual report.</li>
            <li>Evidence of activity completion or participation may include: a screenshot, an abstract, conference proceedings, emails, a letter from your supervisor to name a few.</li>
        </ul>
    </li>
    <li><b>COMPLETE AND SUBMIT EPIC REPORTING:</b> After completing your activities please ensure that your EPIC checklist is completed in full. To receive your certificate you must complete and submit the EPIC Survey located at the bottom of your EPIC table. This survey is a brief evaluation of the EPIC program and allows you to upload your evidence of activity completion.  Once this is submitted, the Education and Training Administrator will contact you with a certificate or follow-up questions.</li>
</ol>";
        $this->html .= "<table class='wikitable' style='border: 1px !important;'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (Post-Doctoral Fellows)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum Post-Doctoral Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Approved<br />(supervisor)</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or view <a href='http://members.agewell-nce.ca/age-well-hqp-orientation/' target='_blank'>recorded session</a> on members' intranet",
                               array(HQP_EPIC_ORIENTATION_DESC, HQP_EPIC_ORIENTATION_SUP, HQP_EPIC_ORIENTATION_HQP, HQP_EPIC_ORIENTATION_NMO)).
            $this->generateRow("Training Plan submitted to and approved by AGE-WELL Education and Training Administrator",
                               array(HQP_EPIC_TRAINING_DESC, HQP_EPIC_TRAINING_SUP, HQP_EPIC_TRAINING_HQP, HQP_EPIC_TRAINING_NMO)).
            "<tr>
                <th colspan='4'>Courses/Workshops</th>
            </tr>".
            $this->generateRow("1 Course/Workshop in KTEE (e.g. attend a MITACS Step workshop)",
                               array(HQP_EPIC_WORKSHOP_DESC, HQP_EPIC_WORKSHOP_SUP, HQP_EPIC_WORKSHOP_HQP, HQP_EPIC_WORKSHOP_NMO)).
            $this->generateRow("1 Course/Workshop in any core competency (e.g. complete a course on research ethics)", 
                                array(HQP_EPIC_CORE_DESC, HQP_EPIC_CORE_SUP, HQP_EPIC_CORE_HQP, HQP_EPIC_CORE_NMO)).
            "<tr>
                <th colspan='4'>Online Activities</th>
            </tr>".
            $this->generateRow("1 Online Activity in KTEE (e.g. participate in a journal club)",
                               array(HQP_EPIC_KTEE_DESC, HQP_EPIC_KTEE_SUP, HQP_EPIC_KTEE_HQP, HQP_EPIC_KTEE_NMO)).
            $this->generateRow("1 Online Activity in Transdisciplinary Research (e.g. view a webinar on project planning)",
                               array(HQP_EPIC_TRANS_DESC, HQP_EPIC_TRANS_SUP, HQP_EPIC_TRANS_HQP, HQP_EPIC_TRANS_NMO)).
            $this->generateRow("1 Online Activity in Ethical Issues (e.g. write a blog post about data security)",
                               array(HQP_EPIC_ETHICS_DESC, HQP_EPIC_ETHICS_SUP, HQP_EPIC_ETHICS_HQP, HQP_EPIC_ETHICS_NMO)).
            $this->generateRow("1 Online Activity in Understanding Impact (e.g. participate in discussion forum on quality of life)",
                               array(HQP_EPIC_IMPACT_DESC, HQP_EPIC_IMPACT_SUP, HQP_EPIC_IMPACT_HQP, HQP_EPIC_IMPACT_NMO)).
            "<tr>
                <th colspan='4'>Self-Selected Activities</th>
            </tr>".
            $this->generateRow("1 Unique Activity of your choice that involves experiential 
learning, mentorship, or AGE-WELL network activities (e.g. be mentored by an expert stakeholder)",
                               array(HQP_EPIC_EXP1_DESC, HQP_EPIC_EXP1_SUP, HQP_EPIC_EXP1_HQP, HQP_EPIC_EXP1_NMO)).
            $this->generateRow("1 Unique Activity of your choice that involves experiential 
learning, mentorship, or AGE-WELL network activities (e.g. participate in a Pitch Event)",
                               array(HQP_EPIC_EXP2_DESC, HQP_EPIC_EXP2_SUP, HQP_EPIC_EXP2_HQP, HQP_EPIC_EXP2_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("NCE Reporting: EPIC tabs, outputs (publications, presentations etc.) & contributions (awards, grants etc.) must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_SUP, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateSpecialRow("EPIC Reporting: EPIC survey w/ evidence of completed activities submitted to Forum by ",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_SUP, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generateMasters(){
        $this->html .= "<div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For Master’s Students</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC. You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
                        <p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
                        <p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on the members' intranet (<a href='http://members.agewell-nce.ca/videos/' target='_blank'>AGE-WELL recorded webinars</a>, <a href='http://members.agewell-nce.ca/events/' target='_blank'>AGE-WELL upcoming events</a>, and <a href='http://members.agewell-nce.ca/hqp-nmo-resources/' target='_blank'>HQP Resources</a>), you are strongly encouraged to consider eligible activities available through your departments and institutions. A collected list of these is available <a href='http://members.agewell-nce.ca/wp-content/uploads/2016/02/Non_AGE-WELL_HQP_activities.pdf' target='_blank'>here</a> and <a href='http://www.ca.cags.ca/gdps/index.php' target='_blank'>here</a>.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>HQP must complete all of their EPIC requirements in one calendar year to earn the Innovators of Tomorrow Certificate. There are four major steps as outlined below.</p>
<p><b>INSTRUCTIONS</b></p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist on this page and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved.
        <ul>
            <li>Use this checklist on your Forum EPIC tab as your training plan. To submit a list of activities you are interested in completing, please scroll to the bottom of the page and click on the “edit EPIC” button on the lower left corner.  Your EPIC table is now editable and all changes are submitted automatically to the Education and Training Administrator for review. </li>
            <li>For those with an “Approved (supervisor)” column: Once you have entered your planned activities, please show your plan to your principal supervisor for approval. At this point, your supervisor, if an AGE-WELL investigator, may check off the “Approved (Supervisor)” box.  If your supervisor is not an AGE-WELL investigator, please share your plans (screenshot, email etc.) and check off the box yourself once you have obtained their approval.</li>
            <li>Once your completed plan has been reviewed by the Education and Training Administrator, a check mark will appear in the “Completed (NMO)” box.</li>
            <li>Training plans should be completed and submitted within 2 months of accepting AGE-WELL funding.</li>
        </ul>
    </li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly.
        <ul>
            <li>Please check the appropriate “Completed (HQP)” box when you have completed an activity and retain evidence of completion/participation for the final annual report.</li>
            <li>Evidence of activity completion or participation may include: a screenshot, an abstract, conference proceedings, emails, a letter from your supervisor to name a few.</li>
        </ul>
    </li>
    <li><b>COMPLETE AND SUBMIT EPIC REPORTING:</b> After completing your activities please ensure that your EPIC checklist is completed in full. To receive your certificate you must complete and submit the EPIC Survey located at the bottom of your EPIC table. This survey is a brief evaluation of the EPIC program and allows you to upload your evidence of activity completion.  Once this is submitted, the Education and Training Administrator will contact you with a certificate or follow-up questions.</li>
</ol>";
        $this->html .= "<table class='wikitable' style='border: 1px !important;'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (Master's)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum Master's Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Approved<br />(supervisor)</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or view <a href='http://members.agewell-nce.ca/age-well-hqp-orientation/' target='_blank'>recorded session</a> on members' intranet",
                               array(HQP_EPIC_ORIENTATION_DESC, HQP_EPIC_ORIENTATION_SUP, HQP_EPIC_ORIENTATION_HQP, HQP_EPIC_ORIENTATION_NMO)).
            $this->generateRow("Training Plan submitted to and approved by AGE-WELL Education and Training Administrator",
                               array(HQP_EPIC_TRAINING_DESC, HQP_EPIC_TRAINING_SUP, HQP_EPIC_TRAINING_HQP, HQP_EPIC_TRAINING_NMO)).
            "<tr>
                <th colspan='4'>Courses/Workshops</th>
            </tr>".
            $this->generateRow("1 Course/Workshop in KTEE (e.g. attend a MITACS Step workshop)",
                               array(HQP_EPIC_WORKSHOP_DESC, HQP_EPIC_WORKSHOP_SUP, HQP_EPIC_WORKSHOP_HQP, HQP_EPIC_WORKSHOP_NMO)).
            "<tr>
                <th colspan='4'>Online Activities</th>
            </tr>".
            $this->generateRow("1 Online Activity in any core competency (e.g. view a webinar on working with big data)",
                               array(HQP_EPIC_WEB_DESC, HQP_EPIC_WEB_SUP, HQP_EPIC_WEB_HQP, HQP_EPIC_WEB_NMO)).
            $this->generateRow("1 Online Activity in a different core competency than above (e.g. write a blog entry about a conference you attended)",
                               array(HQP_EPIC_BLOG_DESC, HQP_EPIC_BLOG_SUP, HQP_EPIC_BLOG_HQP, HQP_EPIC_BLOG_NMO)).
            "<tr>
                <th colspan='4'>Self-Selected Activities</th>
            </tr>".
            $this->generateRow("1 Activity of your choice from remaining core competency area(s) (e.g. discuss end-user concerns with a stakeholder)",
                               array(HQP_EPIC_SELF_DESC, HQP_EPIC_SELF_SUP, HQP_EPIC_SELF_HQP, HQP_EPIC_SELF_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("NCE Reporting: EPIC tabs, outputs (publications, presentations etc.) & contributions (awards, grants etc.) must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_SUP, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateSpecialRow("EPIC Reporting: EPIC survey w/ evidence of completed activities submitted to Forum by ",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_SUP, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generateAffiliate(){
        $this->html .= "<div class='info'>Further information about the EPIC program and requirements is available below.  Please contact the AGE-WELL Education and Training administrator (training@agewell-nce.ca) if you would like to complete the EPIC program requirements to earn the Innovators of Tomorrow Certificate.</div>
                        <div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For Affiliate HQP</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC. You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
                        <p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
                        <p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on the members' intranet (<a href='http://members.agewell-nce.ca/videos/' target='_blank'>AGE-WELL recorded webinars</a>, <a href='http://members.agewell-nce.ca/events/' target='_blank'>AGE-WELL upcoming events</a>, and <a href='http://members.agewell-nce.ca/hqp-nmo-resources/' target='_blank'>HQP Resources</a>), you are strongly encouraged to consider eligible activities available through your departments and institutions. A collected list of these is available <a href='http://members.agewell-nce.ca/wp-content/uploads/2016/02/Non_AGE-WELL_HQP_activities.pdf' target='_blank'>here</a> and <a href='http://www.ca.cags.ca/gdps/index.php' target='_blank'>here</a>.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>HQP must complete all of their EPIC requirements in one calendar year to earn the Innovators of Tomorrow Certificate. There are four major steps as outlined below.</p>
<p><b>INSTRUCTIONS</b></p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist on this page and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved.
        <ul>
            <li>Use this checklist on your Forum EPIC tab as your training plan. To submit a list of activities you are interested in completing, please scroll to the bottom of the page and click on the “edit EPIC” button on the lower left corner.  Your EPIC table is now editable and all changes are submitted automatically to the Education and Training Administrator for review. </li>
            <li>For those with an “Approved (supervisor)” column: Once you have entered your planned activities, please show your plan to your principal supervisor for approval. At this point, your supervisor, if an AGE-WELL investigator, may check off the “Approved (Supervisor)” box.  If your supervisor is not an AGE-WELL investigator, please share your plans (screenshot, email etc.) and check off the box yourself once you have obtained their approval.</li>
            <li>Once your completed plan has been reviewed by the Education and Training Administrator, a check mark will appear in the “Completed (NMO)” box.</li>
            <li>Training plans should be completed and submitted within 2 months of accepting AGE-WELL funding.</li>
        </ul>
    </li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly.
        <ul>
            <li>Please check the appropriate “Completed (HQP)” box when you have completed an activity and retain evidence of completion/participation for the final annual report.</li>
            <li>Evidence of activity completion or participation may include: a screenshot, an abstract, conference proceedings, emails, a letter from your supervisor to name a few.</li>
        </ul>
    </li>
    <li><b>COMPLETE AND SUBMIT EPIC REPORTING:</b> After completing your activities please ensure that your EPIC checklist is completed in full. To receive your certificate you must complete and submit the EPIC Survey located at the bottom of your EPIC table. This survey is a brief evaluation of the EPIC program and allows you to upload your evidence of activity completion.  Once this is submitted, the Education and Training Administrator will contact you with a certificate or follow-up questions.</li>
</ol>";
        $this->html .= "<table class='wikitable' style='border: 1px !important;'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (Affiliate HQP)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum Affiliate HQP Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or view <a href='http://members.agewell-nce.ca/age-well-hqp-orientation/' target='_blank'>recorded session</a> on members' intranet",
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
                <th colspan='4'>Self-Selected Activities</th>
            </tr>".
            $this->generateRow("1 Activity of your choice from remaining core competency area(s) (e.g. discuss end-user concerns with a stakeholder)",
                               array(HQP_EPIC_SELF_DESC, HQP_EPIC_SELF_HQP, HQP_EPIC_SELF_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("NCE Reporting: EPIC tabs, outputs (publications, presentations etc.) & contributions (awards, grants etc.) must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateSpecialRow("EPIC Reporting: EPIC survey w/ evidence of completed activities submitted to Forum by ",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generateWPCC(){
        $this->html .= "<div class='info'>Further information about the EPIC program and requirements is available below.  Please contact the AGE-WELL Education and Training administrator (training@agewell-nce.ca) if you would like to complete the EPIC program requirements to earn the Innovators of Tomorrow Certificate.</div>
                        <div style='font-size:1.2em;text-align:center;'><h3>AGE-WELL EPIC Program Requirements For WP/CC Funded HQP</h3>
                        <b>E</b>arly <b>P</b>rofessionals, <b>I</b>nspired <b>C</b>areers</div>
                        <p><i>WP/CC HQP include AGE-WELL funded research associates, research assistants, technicians, summer students, and undergraduate students.</i></p>
                        <p>As an AGE-WELL HQP you have the opportunity to be truly EPIC. You have unprecedented access to a wide variety of aging and technology resources and experts in a unique training environment based on scientific excellence and real-world applicability.</p>
                        <p>By completing the EPIC Program and earning the AGE-WELL Innovators of Tomorrow certificate your CV/resume will speak to the full range of economic, social, and ethical implications of developing technologies for older adults through workshops, seminar series and other events and activities that transcend the typical training offered through academia.</p>
                        <p>The core of our training program revolves around “Knowledge Partnerships” between higher education and industry, service, and public sectors working together towards mutually beneficial results and outcomes. To that end, training activities are available through AGE-WELL, industry and service partners, and through your institution. While a preliminary list of these opportunities is available on the members' intranet (<a href='http://members.agewell-nce.ca/videos/' target='_blank'>AGE-WELL recorded webinars</a>, <a href='http://members.agewell-nce.ca/events/' target='_blank'>AGE-WELL upcoming events</a>, and <a href='http://members.agewell-nce.ca/hqp-nmo-resources/' target='_blank'>HQP Resources</a>), you are strongly encouraged to consider eligible activities available through your departments and institutions. A collected list of these is available <a href='http://members.agewell-nce.ca/wp-content/uploads/2016/02/Non_AGE-WELL_HQP_activities.pdf' target='_blank'>here</a> and <a href='http://www.ca.cags.ca/gdps/index.php' target='_blank'>here</a>.</p>
<p><b>Be EPIC - Earning the AGE-WELL Innovators of Tomorrow Certificate</b></p>
<p>HQP must complete all of their EPIC requirements in one calendar year to earn the Innovators of Tomorrow Certificate. There are four major steps as outlined below.</p>
<p><b>INSTRUCTIONS</b></p>
<ol>
    <li><b>AGE-WELL ORIENTATION SESSION:</b> Attend session at the AGM or view on Forum.</li>
    <li><b>CREATE AND SUBMIT A TRAINING PLAN:</b> Where would you like to build capacity? Take a look at the minimum requirements checklist on this page and the opportunities listed on Forum, consider additional activities you know of or those in which you are already involved.
        <ul>
            <li>Use this checklist on your Forum EPIC tab as your training plan. To submit a list of activities you are interested in completing, please scroll to the bottom of the page and click on the “edit EPIC” button on the lower left corner.  Your EPIC table is now editable and all changes are submitted automatically to the Education and Training Administrator for review. </li>
            <li>For those with an “Approved (supervisor)” column: Once you have entered your planned activities, please show your plan to your principal supervisor for approval. At this point, your supervisor, if an AGE-WELL investigator, may check off the “Approved (Supervisor)” box.  If your supervisor is not an AGE-WELL investigator, please share your plans (screenshot, email etc.) and check off the box yourself once you have obtained their approval.</li>
            <li>Once your completed plan has been reviewed by the Education and Training Administrator, a check mark will appear in the “Completed (NMO)” box.</li>
            <li>Training plans should be completed and submitted within 2 months of accepting AGE-WELL funding.</li>
        </ul>
    </li>
    <li><b>COMPLETE TRAINING PLAN:</b> Once your plan has been approved, complete your planned activities and let us know about it on Forum! If a planned activity is cancelled, made unavailable, or if you have found a more appropriate activity, please choose another, edit your Forum training plan accordingly.
        <ul>
            <li>Please check the appropriate “Completed (HQP)” box when you have completed an activity and retain evidence of completion/participation for the final annual report.</li>
            <li>Evidence of activity completion or participation may include: a screenshot, an abstract, conference proceedings, emails, a letter from your supervisor to name a few.</li>
        </ul>
    </li>
    <li><b>COMPLETE AND SUBMIT EPIC REPORTING:</b> After completing your activities please ensure that your EPIC checklist is completed in full. To receive your certificate you must complete and submit the EPIC Survey located at the bottom of your EPIC table. This survey is a brief evaluation of the EPIC program and allows you to upload your evidence of activity completion.  Once this is submitted, the Education and Training Administrator will contact you with a certificate or follow-up questions.</li>
</ol>";
        $this->html .= "<table class='wikitable' style='border: 1px !important;'>
            <tr>
                <th style='font-size:1.2em;' colspan='4'>AGE-WELL EPIC CHECKLIST (WP/CC Funded HQP)</th>
            </tr>
            <tr>
                <th width='25%'>Minimum WP/CC (staff) HQP Certificate Requirements</th>
                <th width='50%'>Details of Activity Chosen</th>
                <th>Completed<br />(HQP)</th>
                <th>Completed<br />(NMO)</th>
            </tr>".
            $this->generateRow("Attend a 1 hour AGE-WELL Orientation Session in-person or view <a href='http://members.agewell-nce.ca/age-well-hqp-orientation/' target='_blank'>recorded session</a> on members' intranet",
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
                <th colspan='4'>Self-Selected Activities</th>
            </tr>".
            $this->generateRow("1 Activity of your choice from remaining core competency area(s) (e.g. discuss end-user concerns with a stakeholder)",
                               array(HQP_EPIC_SELF_DESC, HQP_EPIC_SELF_HQP, HQP_EPIC_SELF_NMO)).
            "<tr>
                <th colspan='4'>Reporting</th>
            </tr>".
            $this->generateRow("NCE Reporting: EPIC tabs, outputs (publications, presentations etc.) & contributions (awards, grants etc.) must be updated in Forum by 1 March",
                               array(HQP_EPIC_PUBS_DESC, HQP_EPIC_PUBS_HQP, HQP_EPIC_PUBS_NMO)).
            $this->generateSpecialRow("EPIC Reporting: EPIC survey w/ evidence of completed activities submitted to Forum by ",
                               array(HQP_EPIC_REP_DESC, HQP_EPIC_REP_HQP, HQP_EPIC_REP_NMO));
        
        $this->html .= "</table>
        <p><small><b>* HQP Defined Activities:</b> Are you already involved in an activity related to the core competencies? Let us know - it can be credited toward your Innovators of Tomorrow Certificate! A maximum of 3 HQP Defined, non AGE-WELL activities can be credited towards the Innovators of Tomorrow Certificate.</small></p>";
    }
    
    function generateSpecialRow($description, $cells=array()){
        $me = Person::newFromWgUser();
        $str = "<tr>";
        $str .= "<td><small>{$description}";
        $value = $this->getBlobValue('HQP_EPIC_REP_DATE');
        if($value == ""){
            $value = "Date not set";
        }
        $value = str_replace("'", "&#39;", $value);
        if($me->isRoleAtLeast(STAFF) && $this->visibility['edit']){
            $str .= "<input type='text' value='{$value}' name='epic_HQP_EPIC_REP_DATE' />";
        }
        else{
            $str .= $value;
        }
        $str .= "</small></td>";
        foreach($cells as $key => $cell){
            $value = $this->getBlobValue($cell);
            if($key == 0){
                // Description
                if($this->visibility['edit']){
                    $str .= "<td align='center'><textarea style='width:100%;height:100px;box-sizing:border-box;margin:0;' name='epic_{$cell}'>{$value}</textarea></td>";
                }
                else{
                    $str .= "<td><div style='max-height:100px;overflow-y:auto;'>".nl2br($value)."</td>";
                }
            }
            else if($key == count($cells) - 1){
                // NMO Checkboxes
                if($this->visibility['edit'] && $me->isRoleAtLeast(STAFF)){
                    $checked = ($value != "") ? "checked='checked'" : "";
                    $str .= "<td align='center'><input type='hidden' name='epic_{$cell}' value='' /><input type='checkbox' name='epic_{$cell}' value='&#10003;' {$checked} /></td>";
                }
                else{
                    $str .= "<td align='center'><span style='font-size:2em;'>{$value}</span></td>";
                }
            }
            else{
                // Checkboxes
                if($this->visibility['edit']){
                    $checked = ($value != "") ? "checked='checked'" : "";
                    $str .= "<td align='center'><input type='hidden' name='epic_{$cell}' value='' /><input type='checkbox' name='epic_{$cell}' value='&#10003;' {$checked} /></td>";
                }
                else{
                    $str .= "<td align='center'><span style='font-size:2em;'>{$value}</span></td>";
                }
            }
        }
        $str .= "</tr>";
        return $str;
    }
    
    function generateRow($description, $cells=array()){
        $me = Person::newFromWgUser();
        $str = "<tr>";
        $str .= "<td><small>{$description}</small></td>";
        foreach($cells as $key => $cell){
            $value = $this->getBlobValue($cell);
            if($key == 0){
                // Description
                if($this->visibility['edit']){
                    $str .= "<td align='center'><textarea style='width:100%;height:100px;box-sizing:border-box;margin:0;' name='epic_{$cell}'>{$value}</textarea></td>";
                }
                else{
                    $str .= "<td><div style='max-height:100px;overflow-y:auto;'>".nl2br($value)."</td>";
                }
            }
            else if($key == count($cells) - 1){
                // NMO Checkboxes
                if($this->visibility['edit'] && $me->isRoleAtLeast(STAFF)){
                    $checked = ($value != "") ? "checked='checked'" : "";
                    $str .= "<td align='center'><input type='hidden' name='epic_{$cell}' value='' /><input type='checkbox' name='epic_{$cell}' value='&#10003;' {$checked} /></td>";
                }
                else{
                    $str .= "<td align='center'><span style='font-size:2em;'>{$value}</span></td>";
                }
            }
            else{
                // Checkboxes
                if($this->visibility['edit']){
                    $checked = ($value != "") ? "checked='checked'" : "";
                    $str .= "<td align='center'><input type='hidden' name='epic_{$cell}' value='' /><input type='checkbox' name='epic_{$cell}' value='&#10003;' {$checked} /></td>";
                }
                else{
                    $str .= "<td align='center'><span style='font-size:2em;'>{$value}</span></td>";
                }
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
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        foreach($_POST as $key => $value){
            if(strstr($key, "epic_") !== false){
                $this->saveBlobValue(str_replace("epic_", "", $key), $value);
            }
        }
        $user = Person::newFromName("Stephanie.Markowitz");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        $user = Person::newFromName("Samantha.Sandassie");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        $user = Person::newFromName("Pam.Borghardt");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        $user = Person::newFromName("Bridgette.Murphy");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        $user = Person::newFromName("Emeline.Janigan");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        $user = Person::newFromName("Jenna.McClelland");
        Notification::addNotification($this->person, $user, "EPIC Tab Updated", "{$this->person->getNameForForms()} updated their EPIC Tab", "{$this->person->getUrl()}?tab=epic", false);
        DBFunctions::commit();
        header("Location: {$this->person->getUrl()}?tab=epic");
        exit;
    }
    
    function canEdit(){
        return ($this->userCanView() && ($this->visibility['isMe'] || $this->visibility['isSupervisor']));
    }
    
}
?>
