<?php

class ApplicationTab extends AbstractTab {

    var $rp;
    var $people;
    var $year;
    var $extraCols;
    var $extra;
    var $showAllWithPDFs;
    var $idProjectRange = array(0, 1);

    function ApplicationTab($rp, $people=null, $year=REPORTING_YEAR, $title=null, $extraCols=array(), $showAllWithPDFs=false, $idProjectRange=null){
        $me = Person::newFromWgUser();
        $this->rp = $rp;
        $this->year = $year;
        $this->extraCols = $extraCols;
        $this->showAllWithPDFs = $showAllWithPDFs;
        if($people !== null){
            $newPeople = array();
            foreach($people as $person){
                $newPeople[$person->getId()] = $person;
            }
            $this->people = $newPeople;
        }
        else{
            $this->people = null;
        }
        
        if(is_array($this->rp)){
            $report = new DummyReport($this->rp[0], $me, null, $year);
        }
        else{
            $report = new DummyReport($this->rp, $me, null, $year);
        }
        if($this->people === null){
            $this->people = array();
            $data = DBFunctions::select(array('grand_report_blobs'),
                                        array('user_id', 'proj_id'),
                                        array('rp_type' => $report->reportType,
                                              'year' => $year));
            foreach($data as $row){
                if($row['user_id'] != 0){
                    $person = Person::newFromId($row['user_id']);
                    if($person != null && $person->getId() != 0){
                        $this->people[$person->getId()] = $person;
                    }
                }
                else{
                    $project = Project::newFromId($row['proj_id']);
                    if($project != null && $project->getId() != 0){
                        $this->people[$project->getId()] = $project;
                    }
                }
            }
        }
        if($title == null){
            parent::AbstractTab($report->name);
        }
        else{
            parent::AbstractTab($title);
        }
        if($idProjectRange != null){
            $this->idProjectRange = $idProjectRange;
        }
        $this->html = "";
    }
    
    function addExtra($html){
        $this->extra = $html;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $rpId = (is_array($this->rp)) ? $this->rp[0] : $this->rp;
        $rpId .= $this->year;
        
        $isPerson = true;
        foreach($this->people as $person){
            if(!($person instanceof Person)){
                $isPerson = false;
            }
            break;
        }
        
        if(is_array($this->rp)){
            $report = array();
            foreach($this->rp as $rp){
                $report[] = new DummyReport($rp, $me, null, $this->year);
            }
        }
        else{
            $report = new DummyReport($this->rp, $me, null, $this->year);
            if($report->allowIdProjects){
                $report = array();
                foreach($this->idProjectRange as $projId){
                    $proj = new Project(array());
                    $proj->id = $projId;
                    $report[] = new DummyReport($this->rp, $me, $proj, $this->year);
                }
            }
        }

        // Set year of Report
        if(is_array($report)){
            foreach($report as $rep){
                $rep->year = $this->year;
            }
        }
        else{
            $report->year = $this->year;
        }
        
        $this->html .= "<table id='application_{$rpId}' frame='box' rules='all'>";
        $this->html .= "<thead>";
        if(is_array($this->rp)){
            // This might fail if there are both multiple reports and multiple submissions (idProjectRange)
            $colspan = ($isPerson) ? "2" : "2";
            $this->html .= "<tr><th width='50%' colspan='$colspan'></th>";
            foreach($report as $rep){
                $colspan = 2 + count($this->extraCols);
                $this->html .= "<th colspan='$colspan'>{$rep->name}</th>";
            }
            $this->html .= "</tr>";
        }
        $this->html .= "<tr>
                            <th>Name</th>";
        if(!$isPerson){
            $this->html .= "<th>Leader</th>";
        }
        else{
            $this->html .= "<th>Email</th>";
        }
        if(is_array($this->rp)){
            // This might fail if there are both multiple reports and multiple submissions (idProjectRange)
            foreach($report as $rep){
                $this->html .= "<th>Generation Date</th>
                                <th width='1%'>PDF&nbsp;Download</th>";
                foreach($this->extraCols as $key => $extra){
                    $this->html .= (!is_numeric($key)) ? "<th>$key</th>" : "<th>Extra</th>";
                }
            }
        }
        else{
            $this->html .= "<th>Generation Date</th>
                            <th width='1%'>PDF&nbsp;Download</th>";
            foreach($this->extraCols as $key => $extra){
                $this->html .= (!is_numeric($key)) ? "<th>$key</th>" : "<th>Extra</th>";
            }
        }
        $this->html .= "</tr>
                        </thead>
                        <tbody>";
        
        foreach($this->people as $person){
            if(is_array($report)){
                foreach($report as $rep){
                    if($person instanceof Project ||
                       $person instanceof Theme){
                        $rep->project = $person;
                        $rep->person = Person::newFromId(0);
                    }
                    else{
                        $rep->person = $person;
                    }
                }
                $first = $report[0];
            }
            else{
                if($person instanceof Project ||
                   $person instanceof Theme){
                    $report->project = $person;
                    $report->person = Person::newFromId(0);
                }
                else{
                    $report->person = $person;
                }
                $first = $report;
                $report = array($report);
            }
            if($first->hasStarted(true) || ($this->showAllWithPDFs && count($first->getPDF()) > 0)){
                foreach($report as $rep){
                    $pName = $person->getName();
                    if($person instanceof Theme){
                        $pName = "{$person->getAcronym()}: {$person->getName()}";
                    }
                    if(is_array($this->rp)){
                        $this->html .= "<tr>
                            <td>{$pName}</td>";
                        if($isPerson){
                            $this->html .= "<td>{$person->getEmail()}</td>";
                        }
                        if($person instanceof Project || $person instanceof Theme){
                            $leader = array_values($person->getLeaders());
                            $leader = (isset($leader[0])) ? $leader[0] : null;
                            if($leader != null){
                                $this->html .= "<td>{$leader->getNameForForms()}</td>";
                            }
                            else{
                                $this->html .= "<td></td>";
                            }
                        }
                        foreach($report as $rep){
                            $pdf = $rep->getPDF();
                            $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                            $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                            $this->html .= "<td align='center'>{$pdfDate}</td>
                                            <td>{$pdfButton}</td>";
                            foreach($this->extraCols as $extra){
                                $section = new EditableReportSection();
                                $section->setParent($rep);
                                $extra->setParent($section);
                                $extra->setPersonId($rep->person->getId());
                                if($rep->project != null){
                                    $extra->setProjectId($rep->project->getId());
                                }
                                else{
                                    $extra->setProjectId(0);
                                }
                                $this->html .= "<td>{$extra->getText()}</td>";
                            }
                        }
                        $this->html .= "</tr>";
                        break;
                    }
                    else if($rep instanceof AbstractReport){
                        $pdf = $rep->getPDF();
                        if($rep->hasStarted(true) || count($pdf) > 0){
                            $this->html .= "<tr>
                            <td>{$pName}</td>";
                            if($isPerson){
                                $this->html .= "<td>{$person->getEmail()}</td>";
                            }
                            if($person instanceof Project || $person instanceof Theme){
                                $leader = array_values($person->getLeaders());
                                $leader = (isset($leader[0])) ? $leader[0] : null;
                                if($leader != null){
                                    $this->html .= "<td>{$leader->getNameForForms()}</td>";
                                }
                                else{
                                    $this->html .= "<td></td>";
                                }
                            }
                            $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                            $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                            $this->html .= "<td align='center'>{$pdfDate}</td>
                                            <td>{$pdfButton}</td>";
                            foreach($this->extraCols as $extra){
                                $section = new EditableReportSection();
                                $section->setParent($rep);
                                $extra->setParent($section);
                                $extra->setPersonId($rep->person->getId());
                                if($rep->project != null){
                                    $extra->setProjectId($rep->project->getId());
                                }
                                else{
                                    $extra->setProjectId(0);
                                }
                                $this->html .= "<td>{$extra->getText()}</td>";
                            }
                            $this->html .= "</tr>";
                        }
                    }
                }
            }
        }
        $this->html .= "</tbody>
                        </table>";
        $this->html .= "<script type='text/javascript'>
            $('#application_{$rpId}').dataTable({
                autoWidth: false,
                aLengthMenu: [
                    [25, 50, 100, -1],
                    [25, 50, 100, 'All']
                ],
                iDisplayLength: -1,
                'columnDefs': [
                    {'type': 'natural', 'targets': 0 }
                ],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
        $this->html .= "<br />".$this->extra;
    }
}
?>
