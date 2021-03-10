<?php

class PersonApplicantDataTab extends AbstractEditableTab {

    var $person;
    var $visibility;

    function PersonApplicantDataTab($person, $visibility){
        parent::AbstractEditableTab("Applicant Data");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        // Call APIs here
        $_POST['user_name'] = $this->person->getName();
        $this->person->firstName = @$_POST['first_name'];
        $this->person->middleName = @$_POST['middle_name'];
        $this->person->lastName = @$_POST['last_name'];
        $this->person->realname = @"{$_POST['first_name']} {$_POST['last_name']}";

        $this->person->update();

        $api = new UserApplicantDataAPI();
        $api->doAction(true);
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath, $config;
        if($this->canEdit()) { 
            if($config->getValue('networkName') == 'CSGARS') {

                $this->html .= "<style>
                .flex-container {
                  width: 100%;
                  display: -webkit-flex; /* Safari */
                  display: flex;
                  -webkit-flex-direction: row; /* Safari */
                  flex-direction:         row;
                  -webkit-align-items: baseline; /* Safari */
                  align-items:         baseline;
                  justify-content: space-around;
                  align-content: stretch;
                  flex-wrap: wrap;
                }

                .flex-item {
                  -webkit-flex-grow: <number>; /* Safari */
                  flex-grow:         <number>;
                  min-width: 0px; 
                  max-width: 1000px;
                  flex-grow: 1;
                  /*flex-basis: 350px;*/
                  display: inline-block;
                  vertical-align: top;
                  /*box-shadow: 0 4px 4px 0 rgba(0,0,0,0.2);*/
                  float: left;
                  margin: 10px;
                  margin-right: 0;
                  margin-left: 15px;
                  line-height: 18px;
                  padding: 6px;
                  background: #F5F5F5;
                  border-radius:5px;
                  /*border: 1px solid darkgrey;*/
                  font-family: Helvetica, Verdana, sans-serif;
                  color: #444444;
                }
                </style>";
                $gsms = $this->person->getGSMS()->toArray();
                $this->html .= "<div>";
                $this->html .= "<div style='padding-bottom: 30px'>";
                $this->html .= "<div style='display: inline-block; width: 100%;'>";
                $this->html .= "<h3 style='float:left;'> {$gsms['student_data']['name']}";
                if ($gsms['gsms_id'] != 0) {
                    $this->html .= " ({$gsms['gsms_id']})";
                }
                if ($gsms['program_name'] != '') {
                    $this->html .= " applying to {$gsms['program_name']}";
                }
                $this->html .= "</h3>";
                
                $this->html .= "<h3 style='float:right;'>GPA (normalized): {$gsms['additional']['gpaNormalized']}</h3></div>";
                $this->html .= "{$gsms['student_data']['email']}   {$gsms['gender']}";
                
                $this->html .= "<div class='flex-container' style='display: block;'>
                    <div class='flex-item' style='width:44%; min-width: 400px;'>";
                $this->html .= "<b>Folder:</b> {$gsms['folder']}<br/>";
                $this->html .= "<b>Date of Birth:</b> {$gsms['date_of_birth']}<br/>";
                $this->html .= "<b>Country:</b> {$gsms['additional']['country_of_citizenship_full']}<br/>";
                $this->html .= "<b>Applicant Type:</b> {$gsms['applicant_type']}<br/><br/>";

                $this->html .= "<b>Education History:</b><br/>";
                $ed_hist = preg_replace('/<br \/><br \/>/', '<br />', $gsms['education_history']);
                $ed_hist = preg_replace('/<b>/', '', $ed_hist);
                $ed_hist = preg_replace('/<\/b>/', '', $ed_hist);
                $this->html .= "{$ed_hist}<br/><br/>";

                $this->html .= "<b>Areas of Study:</b><br/>";
                $this->html .= "{$gsms['additional']['areas_of_study']}<br/>";
                $this->html .= "<b>Supervisors:</b><br/>";
                $supers = @implode(", ", array_map(function($sup){ return $sup['last']; }, $gsms['additional']['supervisors']));
                $this->html .= "{$supers}<br/><br/>";
                $this->html .= "<b>Courses:</b><br/>";
                if ($gsms['additional']['courses'] == "") {
                    $this->html .= "No courses were listed";
                } else {
                    $this->html .= "<div title='{$gsms['additional']['courses']}' style='border-bottom: none; max-height: 100px; overflow-y: auto;'>";
                    $this->html .= "{$gsms['additional']['courses']}</div>";
                    
                }
                $this->html .= "</div>";

                $this->html .= "<div class='flex-item' style='width:24%;'><b>Scholarships</b><br/>";
                $held = ($gsms['additional']['scholarships_held'] != "") ? $gsms['additional']['scholarships_held'] : '--';
                $this->html .= "Held: {$held}<br/>";
                $applied = ($gsms['additional']['scholarships_applied'] != "") ? $gsms['additional']['scholarships_applied'] : '--';
                $this->html .= "Applied: {$applied}<br/><br/>";
                $this->html .= "<div style='display:inline-block;'>
                            <table style='float: left'>";
                $this->html .= "<tr>";
                $epltest = ($gsms['epl_test'] != "") ? $gsms['epl_test'] : '--';
                $this->html .= "<td><b>ELP Test:</b></td>
                                    <td align='right'>{$epltest}</td>
                                </tr>
                                <tr>";
                $listening = ($gsms['epl_listen'] != "") ? $gsms['epl_listen'] : '--';
                $this->html .= "<td>Listening:</td>
                                    <td align='right'>{$listening}</td>
                                </tr>
                                <tr>";
                $writing = ($gsms['epl_write'] != "") ? $gsms['epl_write'] : '--';
                $this->html .= "<td>Writing:</td>
                                    <td align='right'>{$writing}</td>
                                </tr>
                            </table>";
                $this->html .= "<table style='float: left; margin-left:5px;'>
                                <tr>";
                $score = ($gsms['epl_score'] != "") ? $gsms['epl_score'] : '--';
                $this->html .= "<td><b>ELP Score:</b></td>
                                    <td align='right'>{$score}</td>
                                </tr>
                                <tr>";
                $reading = ($gsms['epl_read'] != "") ? $gsms['epl_read'] : '--';
                $this->html .= "<td>Reading:</td>
                                    <td align='right'>{$reading}</td>
                                </tr>
                                <tr>";
                $speaking = ($gsms['epl_speaking'] != "") ? $gsms['epl_speaking'] : '--';
                $this->html .= "<td>Speaking:</td>
                                    <td align='right'>{$speaking}</td>
                                </tr>
                            </table>
                        </div><br/><br/>
                        <div>
                            <b>GRE</b><br/>
                            <table>
                                <tr>";
                $gre = ($gsms['additional']['gre1'] != null) ? $gsms['additional']['gre1'] : '--';
                $this->html .= "<td>Verbal:</td>
                                    <td align='right'>{$gre}</td>
                                </tr>
                                <tr>";
                $gre = ($gsms['additional']['gre1'] != null) ? $gsms['additional']['gre2'] : '--';
                $this->html .= "<td>Quantitative:</td>
                                    <td align='right'>{$gre}</td>
                                </tr>
                                <tr>";
                $gre = ($gsms['additional']['gre1'] != null) ? $gsms['additional']['gre3'] : '--';
                $this->html .= "<td>Analytical:</td>
                                    <td align='right'>{$gre}</td>
                                </tr>
                                <tr>";
                $gre = ($gsms['additional']['gre1'] != null) ? $gsms['additional']['gre4'] : '--';
                $this->html .= "<td>CS:</td>
                                    <td align='right'>{$gre}</td>
                                </tr>
                            </table>
                        </div>
                        <br/>";

                $this->html .= "<b>Number of Publications: </b> {$gsms['additional']['num_publications']}<br/>";
                $this->html .= "<b>Number of Awards: </b> {$gsms['additional']['num_awards']}<br/></div>";
                $this->html .= "<div class='flex-item' style='width:24%;'>";
                if (empty($gsms['reviewers'])) {
                    $this->html .= "<b>Reviewers</b><br/>No assigned reviewers<br/>";
                } else {
                    $this->html .= "<table style='width:100%;' class='gsms-mini-table'>
                                <thead>
                                    <tr style='background-color: #F5F5F5;'>
                                        <th><b>Reviewers</b></th>
                                        <th style='text-align: right;'><b>Rank</b></th>
                                    </tr>
                                </thead>";
                    for ($i=0; $i < sizeof($gsms['reviewers']); $i++) {
                        $this->html .= "<tr>
                                            <td>{$gsms['reviewers'][$i]['name']}:</td>
                                            <td align='right'>{$gsms['reviewers'][$i]['rank']}</td>
                                        </tr>";
                    }
                    $this->html .= "<tr style='background-color: #F5F5F5;'>
                                        <td><b>Average Rank:</b></td>";
                    $avg=0;
                    $ignore=0;
                    for($i=0; $i < sizeof($gsms['reviewers']); $i++) {
                        if($gsms['reviewers'][$i]['rank'] != '--') {
                            $avg += (int)$gsms['reviewers'][$i]['rank'];
                        } else {
                            $ignore += 1;
                        }
                    }
                    if ((sizeof($gsms['reviewers']) - $ignore) == 0) {
                        $this->html .= "<td align='right'>--</td>"; 
                    } else {
                        $avg /= sizeof($gsms['reviewers']) - $ignore;
                        $this->html .= "<td align='right'>".number_format($avg, 2)."</td>";
                    }
                    $this->html .= "</tr>";
                    $this->html .= "</table>";
                }
                $this->html .= "<br/>";
                if (empty($gsms['other_reviewers'])) {
                    $this->html .= "<b>Faculty Reviewers</b><br/>No other reviewers<br/>";
                } else {
                    $this->html .= "<table style='width:100%' class='gsms-mini-table'>";
                    $this->html .= "<thead>
                                        <tr style='background-color: #F5F5F5;'>
                                            <th><b>Faculty</b></th>
                                            <th style='text-align: right;'><b>Rank</b></th>
                                        </tr>
                                    </thead>";
                    for ($i=0; $i < sizeof($gsms['other_reviewers']); $i++) {
                        $this->html .= "<tr>
                                            <td>{$gsms['other_reviewers'][$i]['name']}</td><td align='right'>{$gsms['other_reviewers'][$i]['rank']}</td>
                                        </tr>";
                    }
                    $this->html .= "<tr style='background-color: #F5F5F5;'>
                                    <td><b>Average Rank:</b></td>";
                    $avg=0;
                    for ($i=0; $i < sizeof($gsms['other_reviewers']); $i++) {
                        $avg += (int)$gsms['other_reviewers'][$i]['rank'];
                    }
                    $avg /= sizeof($gsms['other_reviewers']);
                    $this->html .= "<td align='right'>".number_format($avg)."</td>
                                </tr>
                            </table>";
                }
                $this->html .= "<br/><b>Notes:</b><br/>";
                if (empty($gsms['additional']['notes'])) {
                    $this->html .= "No notes to show";
                } else {
                    foreach ($gsms['additional']['notes'] as $key => $value) {
                        $this->html .= "{$key}: {$value}<br />";
                    }
                }                
                $this->html .= "</div>
                </div>
                </div>";
            } else { // On OT Forum
                $gsms = $this->person->getGSMS();
                $this->html .= "<br/><table class='gsms'>";
                $this->html .= "<th><font color='green'>".$this->person->getNameForForms()." ({$gsms->gsms_id})</font></th>";
                $this->html .= "<tr>";
                $this->html .= "<td>";
                $this->html .= "<table class='gsms'>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Email</td>";
                $this->html .= "<td class='text'>{$this->person->getEmail()}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Student ID</td>";
                $this->html .= "<td class='text'>{$gsms->student_id}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>CS app#</td>";
                $this->html .= "<td class='text'>{$gsms->cs_app}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Gender</td>";
                $this->html .= "<td class='text'>{$gsms->gender}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>DOB</td>";
                $this->html .= "<td class='text'>{$gsms->date_of_birth}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Country of Birth</td>";
                $this->html .= "<td class='text'>{$gsms->country_of_birth}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Country of Citizenship</td>";
                $this->html .= "<td class='text'>{$gsms->country_of_citizenship}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Applicant Type</td>";
                $this->html .= "<td class='text'>{$gsms->applicant_type}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Folder</td>";
                $this->html .= "<td class='text'>{$gsms->folder}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Education History</td>";
                $this->html .= "<td class='text'>{$gsms->education_history}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>ELP Test</td>";
                $this->html .= "<td class='text'>{$gsms->epl_test}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>ELP Score</td>";
                $this->html .= "<td class='text'>{$gsms->epl_score}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Listen</td>";
                $this->html .= "<td class='text'>{$gsms->epl_listen}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Write</td>";
                $this->html .= "<td class='text'>{$gsms->epl_write}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Read</td>";
                $this->html .= "<td class='text'>{$gsms->epl_read}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Speaking</td>";
                $this->html .= "<td class='text'>{$gsms->epl_speaking}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Academic Year</td>";
                $this->html .= "<td class='text'>{$gsms->academic_year}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Term</td>";
                $this->html .= "<td class='text'>{$gsms->term}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Program Subplan Name</td>";
                $this->html .= "<td class='text'>{$gsms->subplan_name}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Degree Code</td>";
                $this->html .= "<td class='text'>{$gsms->degree_code}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Program Name</td>";
                $this->html .= "<td class='text'>{$gsms->program_name}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Admission Program Name</td>";
                $this->html .= "<td class='text'>{$gsms->admission_program_name}</td>";
                $this->html .= "</tr>";
                $this->html .= "<tr>";
                $this->html .= "<td class='label'>Submitted Date</td>";
                $this->html .= "<td class='text'>{$gsms->submitted_date}</td>";
                $this->html .= "</tr>";
                $this->html .= "</table>";
                $this->html .= "</td>";
                $this->html .= "<td>";
                $this->html .= "</td>";
                $this->html .= "<td>";
                $this->html .= "</td>";
                $this->html .= "</tr>";
                $this->html .= "</table><br />";
            }
        }
        
        return $this->html;
    }
    
    function generateEditBody(){
        global $wgOut, $wgUser, $wgTitle, $wgServer, $wgScriptPath;
        $gsms = $this->person->getGSMS();
 
        $this->html .= "<style>
            input[type=number]::-webkit-inner-spin-button, 
            input[type=number]::-webkit-outer-spin-button { 
                -webkit-appearance: none;
                appearance: none;
                margin: 0; 
            }
            
            input[type=number] {
                -moz-appearance:textfield;
                width: 25px;
            }
            
            input[type=radio] {
                vertical-align: bottom;
            }
        </style>";
            $fSelected = ($gsms->term == "fall") ? "selected='selected'" : "";
            $wSelected = ($gsms->term == "winter") ? "selected='selected'" : "";
            $sSelected = ($gsms->term == "spring") ? "selected='selected'" : "";
            $suSelected = ($gsms->term == "summer") ? "selected='selected'" : "";

            $bSelected = ($gsms->folder == "") ? "selected='selected'" : "";
            $progSelected = ($gsms->folder == "In Progress") ? "selected='selected'" : "";
            $rprogrSelected = ($gsms->folder == "Review in Progress") ? "selected='selected'" : "";
            $newappSelected = ($gsms->folder == "New Applications") ? "selected='selected'" : "";


            $rejSelected = ($gsms->folder == "Rejected Apps") ? "selected='selected'" : "";
            $declinedSelected = ($gsms->folder == "Offer Declined") ? "selected='selected'" : "";
            $withSelected = ($gsms->folder == "Withdrawn") ? "selected='selected'" : "";
            $waitSelected = ($gsms->folder == "Waitlist") ? "selected='selected'" : "";
            $acceptedSelected = ($gsms->folder == "Offer Accepted") ? "selected='selected'" : "";

///----------------------------START HERE -------///

        $this->html .= "<h1 style='margin:0;padding:0;'>{$this->person->getNameForForms()}</h1>";
        $this->html .= "<table id='gsms_bio'>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>First Name: </td>";
        $this->html .= "<td><input name='first_name' type='text' value='{$this->person->getFirstName()}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Middle Name: </td>";
        $this->html .= "<td><input name='middle_name' type='text' value='{$this->person->getMiddleName()}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Last Name: </td>";
        $this->html .= "<td><input name='last_name' type='text' value='{$this->person->getLastName()}' /></td>";
        $this->html .= "</tr>";
 
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Email: </td>";
        $this->html .= "<td><input name='email' type='text' value='{$this->person->getEmail()}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>GSMS ID: </td>";
        $this->html .= "<td><input name='gsms_id' style='width:100px' type='number' value='{$gsms->gsms_id}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Student ID: </td>";
        $this->html .= "<td><input name='student_id' style='width:100px' type='number' value='{$gsms->student_id}' /></td>";
        $this->html .= "</tr>";
        
        $this->html .= "<tr>";

        $this->html .= "<td class='label'>CS App#: </td>";
        $this->html .= "<td><input name='cs_app' style='width:100px' type='number' value='{$gsms->cs_app}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Gender: </td>";
        $this->html .= "<td><input name='gender' type='text' value='{$gsms->gender}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>DOB: </td>";
        $this->html .= "<td><input name='dob' type='date' value='{$gsms->date_of_birth}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Country of Birth: </td>";
        $this->html .= "<td><input name='country_birth' type='text' value='{$gsms->country_of_birth}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Country of Citizenship: </td>";
        $this->html .= "<td><input name='country_citizenship' type='text' value='{$gsms->country_of_citizenship}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Applicant Type:  </td>";
        $this->html .= "<td><input name='applicant_type' type='text' value='{$gsms->applicant_type}' /></td>";
        $this->html .= "</tr>";



        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Folder: </td>";
        $this->html .= "<td>";
        $this->html .= "<select name='folder'>
                        <option value='' $bSelected>--</option>
                        <option value='' $newappSelected>New Applications</option>
                        <option value='In Progress' $progSelected>In Progress</option>
                        <option value='Review in Prgoress' $rprogrSelected>Review in Progress</option>
                        <option value='Rejected Apps' $rejSelected>Rejected Apps</option>
                        <option value='Offer Declined' $declinedSelected>Offer Declined</option>
                        <option value='Offer Accepted' $acceptedSelected>Offer Accepted</option>
                        <option value='Withdrawn' $withSelected>Withdrawn</option>
                        <option value='Waitlist' $waitSelected>Waitlist</option></select>";
        $this->html .= "</td>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Education History: </td>";
        $this->html .= "<td><input name='education_history' type='text' value='{$gsms->education_history}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>ELP Test: </td>";
        $this->html .= "<td><input name='epl_test' style='width:100px' type='number' value='{$gsms->epl_test}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>ELP Score: </td>";
        $this->html .= "<td><input name='epl_score' style='width:100px' type='number' value='{$gsms->epl_score}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Listen: </td>";
        $this->html .= "<td><input name='listen' style='width:100px' type='number' value='{$gsms->epl_listen}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Write: </td>";
        $this->html .= "<td><input name='write' style='width:100px' type='number' value='{$gsms->epl_write}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Read: </td>";
        $this->html .= "<td><input name='read' style='width:100px' type='number' value='{$gsms->epl_read}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Speaking: </td>";
        $this->html .= "<td><input name='speaking' style='width:100px' type='number' value='{$gsms->epl_speaking}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Academic Year: </td>";
        $this->html .= "<td><input name='academic_year' type='text' value='{$gsms->academic_year}' /></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Term: </td>";        $this->html .= "<td><select name='term'><option value='fall' $fSelected>Fall</option><option value='winter' $wSelected>Winter</option><option value='spring' $sSelected>Spring</option><option value='summer' $suSelected>Summer</option></select></td>";
        $this->html .= "</tr>";


        $this->html .= "<tr rowspan=2>";
        $this->html .= "</tr>";

        
        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Program Subplan Name: </td>";
        $this->html .= "<td> <input name='program_subplan' type='text' value='{$gsms->subplan_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Degree Code: </td>";
        $this->html .= "<td> <input name='degree_code' style='width:100px' type='number' value='{$gsms->degree_code}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Program Name: </td>";
        $this->html .= "<td> <input name='program_name' type='text' value='{$gsms->program_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Admission Program Name: </td>";
        $this->html .= "<td> <input name='admission_program' type='text' value='{$gsms->admission_program_name}'/></td>";
        $this->html .= "</tr>";

        $this->html .= "<tr>";
        $this->html .= "<td class='label'>Submitted Date: </td>";
        $this->html .= "<td> <input name='submitted_date' type='date' value='{$gsms->submitted_date}'/></td>";
        $this->html .= "</tr>";





        $this->html .= "</table>";
        
        $this->html .= "<script type='text/javascript'>

                $(document).ready(function(){
                    $('.ui-state-default').hide();
                });
        </script>";
        return $this->html;
    }
    
    function canEdit(){
        $me = Person::newFromWgUser();
        return ($me->isRoleAtLeast(ADMIN));
    }
    
}
?>
