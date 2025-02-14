<?php

class DepartmentTab extends AbstractTab {

    var $department;
    var $depts;

    function DepartmentTab($department, $depts){
        parent::AbstractTab($depts[0]);
        $this->department = $department;
        $this->depts = $depts;
    }
    
    function generateBody(){
        global $wgOut, $config, $wgServer, $wgScriptPath;
        if(isset($_GET['generatePDF']) && isset($_GET['showTab']) && $_GET['showTab'] != $this->id){
            // Wanting to generate a pdf, but it isn't for this tab, don't waste time
            return;
        }
        $me = Person::newFromWgUser();
        $year = YEAR;
        $people = array();
        $hqps = array();
        $ugrads = array();
        $masters = array();
        $phds = array();
        $techs = array();
        $pdfs = array();
        $faculty = array_merge(Person::getAllPeopleDuring(NI, ($year-6).CYCLE_START_MONTH, "2100-01-01"),
                               Person::getAllPeopleDuring("ATS", ($year-6).CYCLE_START_MONTH, "2100-01-01"), 
                               Person::getAllPeopleDuring("Faculty of Arts", ($year-6).CYCLE_START_MONTH, "2100-01-01"));
        foreach($faculty as $person){
            $person->getFecPersonalInfo();
            $depts = array_keys($person->departments);
            foreach($depts as $dept){
                if($dept == $this->department){
                    $people[$person->getId()] = $person;
                    break;
                }
            }
        }
        foreach(Person::getAllPeopleDuring(HQP, ($year-6).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $person){
            foreach($person->getUniversitiesDuring(($year-6).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if(strstr($uni['department'], $this->department) !== false){
                    $hqps[$person->getId()] = $person;
                    if(in_array(strtolower($uni['position']), Person::$studentPositions['ugrad'])){
                        $ugrads[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), Person::$studentPositions['msc'])){
                        $masters[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), Person::$studentPositions['phd'])){
                        $phds[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), Person::$studentPositions['tech'])){
                        $techs[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), Person::$studentPositions['pdf'])){
                        $pdfs[$person->getId()] = $person;
                    }
                }
            }
        }
        $courses = array();
        $merged = array();
        foreach($people as $person){
            foreach($person->getCoursesDuring(($year-6).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $course){
                $merged[] = $course;
            }
        }
        /*foreach($this->depts as $dept){
            $merged = array_merge($merged, Course::newFromSubjectCatalog($dept, ""));
        }*/
        foreach($merged as $course){
            $courses["{$course->subject} {$course->catalog}"][] = $course;
        }
        
        ksort($courses);
        $html = "<h1 style='color:#1155cc !important;'>Department of {$this->department}. Reporting Period July 1, ".($year-6)." - June 30, $year</h1>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Course Descriptions and Instructors</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Course Descriptions and Instructors\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        foreach($courses as $key => $course){
            $html .= "<h3>{$key} - {$course[0]->descr}</h3>";
            $html .= "<p>{$course[0]->courseDescr}</p>";
            $html .= "<b>Instructors:</b> ";
            $profs = array();
            foreach($course as $c){
                $professors = $c->getProfessors();
                foreach($professors as $prof){
                    $profs[$prof->getId()] = "<a href='{$prof->getUrl()}'>{$prof->getReversedName()}</a>";                
                }
            }
            $html .= implode("; ", $profs);
        }
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Distribution of Courses across Instructors</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Distribution of Courses across Instructors\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<table>";
        foreach($people as $person){
            $html .= "<tr><td>{$person->getReversedName()}</td>";
            $personCourses = array();
            foreach($person->getCoursesDuring(($year-6).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $course){
                $personCourses["{$course->subject} {$course->catalog}"] = "{$course->subject} {$course->catalog}";
            }
            ksort($personCourses);
            $html .= "<td>".implode(", ", $personCourses)."</td></tr>";
        }
        $html .= "</table>";
        
        $awards = array();
        foreach($people as $person){
            foreach($person->getPapersAuthored("Award", "1900-01-01", ($year+1)."-12-31") as $award){
                if($award->getData('scope') != ''){
                    $awards[$award->getData('scope')][] = $award;
                }
            }
        }
        
        uksort($awards, function($a, $b){
            $dict = array("International" => 0,
                          "National" => 1,
                          "Provincial" => 2,
                          "University" => 3,
                          "Faculty" => 4,
                          "Departmental" => 5);
            $aVal = (isset($dict[$a])) ? $dict[$a] : 1000;
            $bVal = (isset($dict[$b])) ? $dict[$b] : 1000;
            
            return $aVal - $bVal;
        });
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Faculty Awards</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Faculty Awards\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<div id='{$this->id}Awards'>";
        foreach($awards as $scope => $as){
            usort($as, function($a, $b){
                return $b->getAcceptanceYear() - $a->getAcceptanceYear();
            });
            $html .= "<h3>{$scope}</h3>
                <table class='wikitable awards' frame='box' rules='all' width='100%'>
                    <thead>
                        <tr>
                            <th width='35%'>Title</th>
                            <th width='35%'>Awarded By</th>
                            <th width='20%'>Recipient Last Names</th>
                            <th width='10%'>Years</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach($as as $award){
                $authors = array();
                foreach($award->getAuthors() as $author){
                    $authors[] = $author->getLastName();
                }
                $startYear = $award->getAcceptanceYear();
                $endYear = $award->getYear();
                $years = "";
                if($startYear == $endYear){
                    $years = $endYear;        
                }
                else if($startYear == "0000"){
                    $years = $endYear;
                }
                else if($endYear == "0000"){
                    $years = "{$startYear} - Present";
                }
                else{
                    $years = "{$startYear} - {$endYear}";
                }
                $html .= "<tr><td>{$award->getTitle()}</td><td>{$award->getData('awarded_by')}</td><td>".implode(", ", $authors)."</td><td>{$years}</td></tr>";
            }
            $html .= "</tbody></table>";
        }
        $html .= "</div>";
        $html .= "<script type='text/javascript'>
            $('#{$this->id}Awards .awards').dataTable({
                'aLengthMenu': [[-1], ['All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        
        // Patents
        $patents = array();
        $spinOffs = array();
        foreach($people as $person){
            foreach($person->getPapersAuthored("Patent/Spin-Off", "1900-01-01", ($year+1)."-12-31") as $patent){
                if($patent->getType() == "Patent"){
                    $patents[] = $patent;
                }
                else {
                    $spinOffs[] = $patent;
                }
            }
        }
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Faculty Patents</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Faculty Patents\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<div id='{$this->id}Patents'>";
        $html .= "<table class='wikitable patents' frame='box' rules='all' width='100%'>
                    <thead>
                        <tr>
                            <th width='35%'>Title</th>
                            <th width='10%'>Number</th>
                            <th width='15%'>Country</th>
                            <th width='20%'>Inventors</th>
                            <th width='10%'>Status</th>
                            <th width='10%'>Date</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach($patents as $patent){
            $authors = array();
            foreach($patent->getAuthors() as $author){
                $authors[] = $author->getLastName();
            }
            $html .= "<tr><td>{$patent->getTitle()}</td><td>{$patent->getData('number')}</td><td>{$patent->getData('country')}</td><td>".implode(", ", $authors)."</td><td>{$patent->getStatus()}</td><td>{$patent->getDate()}</td></tr>";
        }
        $html .= "</tbody></table>";
        $html .= "</div>";
        $html .= "<script type='text/javascript'>
            $('#{$this->id}Patents .patents').dataTable({
                'aLengthMenu': [[-1], ['All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        
        // Spin-Offs
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Faculty Spin-Offs (and others)</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Faculty Spin-Offs\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<div id='{$this->id}SpinOffs'>";
        $html .= "<table class='wikitable spinoffs' frame='box' rules='all' width='100%'>
                    <thead>
                        <tr>
                            <th width='35%'>Title</th>
                            <th width='10%'>Type</th>
                            <th width='20%'>Authors</th>
                            <th width='10%'>Date</th>
                        </tr>
                    </thead>
                    <tbody>";
        foreach($spinOffs as $patent){
            $authors = array();
            foreach($patent->getAuthors() as $author){
                $authors[] = $author->getLastName();
            }
            $html .= "<tr><td>{$patent->getTitle()}</td><td>{$patent->getType()}</td><td>".implode(", ", $authors)."</td><td>{$patent->getDate()}</td></tr>";
        }
        $html .= "</tbody></table>";
        $html .= "</div>";
        $html .= "<script type='text/javascript'>
            $('#{$this->id}SpinOffs .spinoffs').dataTable({
                'aLengthMenu': [[-1], ['All']],
                'iDisplayLength': -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        
        $gradPapers = array();
        $ugradPapers = array();
        
        foreach($hqps as $hqp){
            $papers = $hqp->getPapersAuthored("Publication", ($year-6).CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
            foreach($papers as $paper){
                if(($paper->getType() == "Conference Paper" || 
                    $paper->getType() == "Proceedings Paper" || 
                    $paper->getType() == "Poster") && 
                    $this->department == "Biological Sciences"){
                    continue;
                }
                if($paper->getData('peer_reviewed') == "Yes"){
                    $yearAgo = strtotime("{$paper->getDate()} -1 year"); // Extend the year to last year so that publications after graduation are still counted
                    $yearAgo = date('Y-m-d', $yearAgo);
                    $unis = $hqp->getUniversitiesDuring($yearAgo, $paper->getDate());
                    foreach($unis as $uni){
                        $pos = @$uni['position'];
                        if(in_array(strtolower($pos), Person::$studentPositions['grad'])){
                            $gradPapers[$paper->getDate().$paper->getAcceptanceDate().$paper->getId()] = $paper;
                            break;
                        }
                        else if(in_array(strtolower($pos), Person::$studentPositions['ugrad'])){
                            $ugradPapers[$paper->getDate().$paper->getAcceptanceDate().$paper->getId()] = $paper;
                            break;
                        }
                    }
                }
            }
        }
        
        ksort($gradPapers);
        ksort($ugradPapers);
        $gradPapers = array_reverse($gradPapers);
        $ugradPapers = array_reverse($ugradPapers);
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Example Graduate Student Publications</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Example Graduate Student Publications\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<p>Total # of publications: ".count($gradPapers)."</p>";
        $html .= "<small>Graduate student name boldfaced</small><br />";
        //$html .= "<ul>";
        foreach($gradPapers as $paper){
            $html .= "<p style='margin-bottom: 1em;'>{$paper->getCitation(false, false, true, false)}</p>";
        }
        //$html .= "</ul>";
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: Example Undergraduate Student Publications</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Example Undergraduate Student Publications\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<p>Total # of publications: ".count($ugradPapers)."</p>";
        $html .= "<small>Undergraduate student name underlined</small><br />";
        //$html .= "<ul>";
        foreach($ugradPapers as $paper){
            $html .= "<p style='margin-bottom: 1em;'>{$paper->getCitation(false, false, true, false)}</p>";
        }
        //$html .= "</ul>";
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<h2 style='color:#1155cc !important;'>Appendix XX: HQP Supervision Summary Document</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"HQP Supervision Summary Document\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        
        $html .= "<p>Total number of undergraduate students: ".count($ugrads)."</p>";
        
        $html .= "<p>Total number of MSc students: ".count($masters)."</p>";
        
        $html .= "<p>Total number of PhD students: ".count($phds)."</p>";
        
        $html .= "<p>Total number of technicians: ".count($techs)."</p>";
        
        $html .= "<p>Total number of PDFs: ".count($pdfs)."</p>";
        
        $report = new Report();
        $report->pdfType = "QA_SUMMARY: {$this->department}";
        $report->year = YEAR;
        if(isset($_GET['generatePDF']) && isset($_GET['showTab']) && $_GET['showTab'] == $this->id){
            $report->headerName = " ";
            $report->pageCount = false;
            $wgOut->clearHTML();
            $wgOut->addHTML($html);
            $wgOut->disable();
            $report->generatePDF();
        }
        else if(isset($_GET['downloadCSV']) && isset($_GET['showTab']) && $_GET['showTab'] == $this->id){
            $ids = array();
            foreach($people as $person){
                $ids[] = $person->getId();
            }
            $data = DBFunctions::execSQL("SELECT u.user_real_name, ccv.hqp, ccv.date, ccv.present_position, ccv.present_organization, ccv.institution, ccv.status, ccv.degree
                                          FROM mw_user u, grand_ccv_employment_outcome ccv
                                          WHERE u.user_id = ccv.supervisor_id
                                          AND supervisor_id IN('".implode("','", $ids)."')
                                          AND (status = 'Completed' OR status = 'Withdrawn')
                                          AND date BETWEEN '".(($year-6).CYCLE_START_MONTH)."' AND '".($year.CYCLE_END_MONTH)."'");
            $strings = array();
            $strings[] = '"'.implode('","', array("Supervisor", "HQP", "Date", "Present Position", "Present Organization", "Institution", "Status", "Degree", "Reason")).'"';;
            foreach($data as $row){
                $strings[] = '"'.implode('","', $row).'",""';
            }
            $strings[] = ""; // Separate CCV Entries from MovedOn entries
            foreach($hqps as $hqp){
                $movedOn = $hqp->getAllMovedOn();
                foreach($movedOn as $mo){
                    $mo['employer'] = ($mo['employer'] == "") ? $mo['studies'] : $mo['employer'];
                    if($mo['employer'] != "" && $mo['effective_date'] != ZOT){
                        $supervisors = $hqp->getSupervisors($mo['effective_date']);
                        if(count($supervisors) == 0){
                            $supervisors = $hqp->getSupervisors(true);
                        }
                        $status = $mo['status'];
                        $reason = $mo['reason'];
                        $sups = array();
                        foreach($supervisors as $sup){
                            $sups[] = $sup->getNameForForms();
                            $relations = $sup->getRelationsDuring('all', SOT, $mo['effective_date']);
                            if(count($relations) == 0){
                                $relations = $sup->getRelationsDuring('all', SOT, EOT);
                            }
                            foreach($relations as $rel){
                                if($rel->user2 == $hqp->getId()){
                                    if($rel->status != ""){
                                        $status = $rel->status;
                                        break;
                                    }
                                }
                            }
                        }
                        $sups = array_unique($sups);
                        $row = array();
                        $row[] = implode(", ", $sups);
                        $row[] = $hqp->getNameForForms();
                        $row[] = $mo['effective_date'];
                        $row[] = $mo['position'];
                        $row[] = $mo['employer'];
                        $row[] = $hqp->getUni();
                        $row[] = $status;
                        $row[] = $hqp->getPosition();
                        $row[] = $reason;
                        $strings[] = '"'.implode('","', $row).'"';
                    }
                }
            }
            header("Content-Type: text/csv");
            header('Content-Disposition: attachment; filename="'.$this->department.' HQP Moved On.csv"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            ini_set('zlib.output_compression','0');
            echo implode("\n", $strings);
            exit;
        }
        else{
            $this->html .= "<button type='button' id='generate{$this->id}'>Generate PDF</button>&nbsp;";
            $pdf = $report->getLatestPDF();
            if(isset($pdf[0]['token'])){
                $pdf = PDF::newFromToken($pdf[0]['token']);
                $this->html .= "<button type='button' id='download{$this->id}'>Download PDF</button>";
                $this->html .= "<script type='text/javascript'>
                    $('#download{$this->id}').on('click', function(){
                        window.open('{$pdf->getUrl()}', '_blank');
                    });
                </script>";
            }
            else{
                $this->html .= "<button type='button' id='download{$this->id}' disabled>Download PDF</button>";
            }
            $this->html .= "<a class='button' href='{$wgServer}{$wgScriptPath}/index.php/Special:QASummary?showTab={$this->id}&downloadCSV' target='_blank'>Download HQP Moved On</a>";
            $this->html .= "&nbsp;<span id='generate{$this->id}_throbber' class='throbber' style='display:none;'></span>";
            $this->html .= "<script type='text/javascript'>
                $('#generate{$this->id}').click(function(){
                    $('#generate{$this->id}').prop('disabled', true);
                    $('#download{$this->id}').prop('disabled', true);
                    $('#generate{$this->id}_throbber').css('display', 'inline-block');
                    $.ajax({
                        url : wgServer + wgScriptPath +'/index.php/Special:QASummary?showTab={$this->id}&generatePDF', 
                        success : function(data){
                            for(index in data){
                                val = data[index];
                                if(typeof val.tok != 'undefined'){
                                    index = index.replace('/', '');
                                    var tok = val.tok;
                                    clearAllMessages();
                                    addSuccess('PDF Generated Successfully.');
                                    $('#download{$this->id}').off('click');
                                    $('#download{$this->id}').on('click', function(){
                                        window.open(wgServer + wgScriptPath + '/index.php/Special:ReportArchive?getpdf=' + tok,'_blank');
                                    });
                                }
                                else{
                                    clearAllMessages();
                                    addError('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                                }
                            }
                            $('#generate{$this->id}_throbber').css('display', 'none');
                            $('#generate{$this->id}').prop('disabled', false);
                            $('#download{$this->id}').prop('disabled', false);
                        },
                        error : function(response){
                            // Error
                            clearAllMessages();
                            addError('There was an error generating the PDF.  Please try again, and if it still fails, contact <a href=\"mailto:{$config->getValue('supportEmail')}\">{$config->getValue('supportEmail')}</a>');
                            $('#generate{$this->id}').prop('disabled', false);
                            $('#generate{$this->id}_throbber').css('display', 'none');
                        }
                    });
                });
            </script>";
        }
        $this->html .= $html;
    }

}
?>
