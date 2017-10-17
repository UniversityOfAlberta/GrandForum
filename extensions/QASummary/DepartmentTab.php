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
        global $wgOut, $config;
        if(isset($_GET['generatePDF']) && isset($_GET['tab']) && $_GET['tab'] != $this->id){
            // Wanting to generate a pdf, but it isn't for this tab, don't waste time
            return;
        }
        $me = Person::newFromWgUser();
        $year = YEAR-1;
        $people = array();
        $hqps = array();
        $ugrads = array();
        $masters = array();
        $phds = array();
        $techs = array();
        $pdfs = array();
        foreach(Person::getAllPeople(NI) as $person){
            foreach($person->getUniversitiesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if($uni['department'] == $this->department){
                    $people[$person->getId()] = $person;
                    break;
                }
            }
        }
        foreach(Person::getAllPeople(HQP) as $person){
            foreach($person->getUniversitiesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $uni){
                if($uni['department'] == $this->department){
                    $hqps[$person->getId()] = $person;
                    if(in_array(strtolower($uni['position']), array("ugrad", "undergraduate", "undergraduate student"))){
                        $ugrads[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("msc", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s"))){
                        $masters[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("phd", "phd student", "graduate student - doctoral"))){
                        $phds[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("technician", "ra", "research/technical assistant", "professional end user"))){
                        $techs[$person->getId()] = $person;
                    }
                    else if(in_array(strtolower($uni['position']), array("pdf","post-doctoral fellow"))){
                        $pdfs[$person->getId()] = $person;
                    }
                }
            }
        }
        $courses = array();
        $merged = array();
        foreach($this->depts as $dept){
            $merged = array_merge($merged, Course::newFromSubjectCatalog($dept, ""));
        }
        foreach($merged as $course){
            $courses["{$course->subject} {$course->catalog}"][] = $course;
        }
        
        ksort($courses);
        $html = "<h1>Department of {$this->department}</h1>";
        $html .= "<h2>Quality Assurance Reporting Period: July 1, ".($year-5)." - June 30, $year</h2>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Course Descriptions and Associated Instructors\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>Course Descriptions and Associated Instructors</h2>";
        foreach($courses as $key => $course){
            $html .= "<h3>{$key}</h3>";
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
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Distribution of Courses across Instructors\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>Distribution of Courses across Instructors</h2>";
        $html .= "<table>";
        foreach($people as $person){
            $html .= "<tr><td>{$person->getReversedName()}</td>";
            $personCourses = array();
            foreach($person->getCoursesDuring(($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $course){
                if(in_array($course->subject, $this->depts)){
                    $personCourses["{$course->subject} {$course->catalog}"] = "{$course->subject} {$course->catalog}";
                }
            }
            ksort($personCourses);
            $html .= "<td>".implode(", ", $personCourses)."</td></tr>";
        }
        $html .= "</table>";
        
        $awards = array();
        foreach($people as $person){
            foreach($person->getPapersAuthored("Award", ($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH) as $award){
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
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Awards Summary Table\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>Awards Summary Table</h2>";
        foreach($awards as $scope => $as){
            $html .= "<h3>{$scope}</h3>
                <table class='wikitable' frame='box' rules='all' width='100%'>
                    <tr>
                        <th width='35%'>Title</th>
                        <th width='35%'>Awarded By</th>
                        <th width='20%'>Recipient Last Names</th>
                        <th width='10%'>Year</th>
                    </tr>";
            foreach($as as $award){
                $authors = array();
                foreach($award->getAuthors() as $author){
                    $authors[] = $author->getLastName();
                }
                $html .= "<tr><td>{$award->getTitle()}</td><td>{$award->getData('awarded_by')}</td><td>".implode(", ", $authors)."</td><td>{$award->getYear()}</td></tr>";
            }
            $html .= "</table>";
        }
        
        $gradPapers = array();
        $ugradPapers = array();
        
        foreach($hqps as $hqp){
            $papers = $hqp->getPapersAuthored("Publication", ($year-5).CYCLE_START_MONTH, $year.CYCLE_END_MONTH);
            foreach($papers as $paper){
                $uni = $hqp->getUniversityDuring($paper->getDate(), $paper->getDate());
                $pos = @$uni['position'];
                if(in_array(strtolower($pos), array("phd","msc","phd student", "msc student", "graduate student - master's course", "graduate student - master's thesis", "graduate student - master's", "graduate student - master&#39;s", "graduate student - doctoral", "pdf","post-doctoral fellow"))){
                    $gradPapers[$paper->getId()] = $paper;
                }
                else if(in_array(strtolower($pos), array("ugrad", "undergraduate", "undergraduate student"))){
                    $ugradPapers[$paper->getId()] = $paper;
                }
            }
        }
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Graduate Student Publications\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>Graduate Student Publications</h2>";
        $html .= "<p>Total # of publications: ".count($gradPapers)."</p>";
        $html .= "<small>Graduate student name boldfaced</small><br />";
        $html .= "<ul>";
        foreach($gradPapers as $paper){
            $html .= "<li>{$paper->getCitation()}</li>";
        }
        $html .= "</ul>";
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"Undergradate Student Publications\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>Undergradate Student Publications</h2>";
        $html .= "<p>Total # of publications: ".count($ugradPapers)."</p>";
        $html .= "<small>Graduate student name boldfaced</small><br />";
        $html .= "<ul>";
        foreach($ugradPapers as $paper){
            $html .= "<li>{$paper->getCitation()}</li>";
        }
        $html .= "</ul>";
        
        $html .= "<div class='pagebreak'></div>";
        $html .= "<script type='text/php'>
                      \$GLOBALS['chapters'][] = array('title' => \"HQP Supervision Summary Document\", 
                                                      'page' => \$pdf->get_page_number(),
                                                      'subs' => array());
                  </script>";
        $html .= "<h2>HQP Supervision Summary Document</h2>";
        
        $html .= "<p>Total number of undergraduate students: ".count($ugrads)."</p>";
        
        $html .= "<p>Total number of MSc students: ".count($masters)."</p>";
        
        $html .= "<p>Total number of  PhD students: ".count($phds)."</p>";
        
        $html .= "<p>Total number of technicians: ".count($techs)."</p>";
        
        $html .= "<p>Total number of PDFs: ".count($pdfs)."</p>";
        
        $report = new Report();
        $report->pdfType = "QA_SUMMARY: {$this->department}";
        $report->year = YEAR;
        if(isset($_GET['generatePDF']) && isset($_GET['tab']) && $_GET['tab'] == $this->id){
            $report->headerName = " ";
            $wgOut->clearHTML();
            $wgOut->addHTML($html);
            $wgOut->disable();
            $report->generatePDF();
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
                $this->html .= "<button  type='button' id='download{$this->id}' disabled>Download PDF</button>";
            }
            $this->html .= "&nbsp;<span id='generate{$this->id}_throbber' class='throbber' style='display:none;'></span>";
            $this->html .= "<script type='text/javascript'>
                $('#generate{$this->id}').click(function(){
                    $('#generate{$this->id}').prop('disabled', true);
                    $('#download{$this->id}').prop('disabled', true);
                    $('#generate{$this->id}_throbber').css('display', 'inline-block');
                    $.ajax({
                        url : wgServer + wgScriptPath +'/index.php/Special:QASummary?tab={$this->id}&generatePDF', 
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
