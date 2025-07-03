<?php

class EOITab extends AbstractTab {

    var $rp;
    var $people;
    var $year;
    var $extraCols;
    var $showAllWithPDFs;
    var $idProjectRange = array(0, 1);

    function __construct($rp, $people, $year=REPORTING_YEAR, $title=null, $extraCols=array(), $showAllWithPDFs=false){
        $me = Person::newFromWgUser();
        $this->rp = $rp;
        $this->year = $year;
        $this->extraCols = $extraCols;
        $this->showAllWithPDFs = $showAllWithPDFs;
        $newPeople = array();
        foreach($people as $person){
            $newPeople[$person->getId()] = $person;
        }
        $this->people = $newPeople;
        if(is_array($this->rp)){
            $report = new DummyReport($this->rp[0], $me, null, $year);
        }
        else{
            $report = new DummyReport($this->rp, $me, null, $year);
        }
        if($title == null){
            parent::__construct($report->name);
        }
        else{
            parent::__construct($title);
        }
        $this->html = "";
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
        
        //create table header
        $this->html .= "<table id='application_{$rpId}' frame='box' rules='all'>";
        $this->html .= "<thead>";
        $colspan = ($isPerson) ? "2" : "2";
        $this->html .= "<tr><th width='50%' colspan='$colspan'></th>";
        $colspan = 2 + count($this->extraCols);
        $this->html .= "<th colspan='$colspan'>{$report[0]->name}</th>";
        $this->html .= "</tr>";
        $this->html .= "<tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Generation Date</th>
                            <th width='1%'>PDF&nbsp;Download</th>";
        foreach($this->extraCols as $key => $extra){
            $this->html .= (!is_numeric($key)) ? "<th>$key</th>" : "<th>Extra</th>";
        }
        $this->html .= "</tr>
                        </thead>
                        <tbody>";
                        
        //create the table body
        //each person can have up to 3 rows
        //if title is empty we will ignore the row
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
            }
            if($first->hasStarted() || ($this->showAllWithPDFs && count($first->getPDF()) > 0)){
                
                if(is_array($report)){
                    foreach($report as $rep){
                        $row = "";
                        $incomplete = false;
                        $pName = $person->getName();
                        if($person instanceof Theme){
                            $pName = "{$person->getAcronym()}: {$person->getName()}";
                        }
                        $row .= "<tr>
                                        <td>{$pName}</td>";
                        if($isPerson){
                            $row .= "<td>{$person->getEmail()}</td>";
                        }
                        if($person instanceof Project || $person instanceof Theme){
                            $leader = array_values($person->getLeaders());
                            $leader = (isset($leader[0])) ? $leader[0] : null;
                            if($leader != null){
                                $row .= "<td>{$leader->getNameForForms()}</td>";
                            }
                            else{
                                $row .= "<td></td>";
                            }
                        }
                        $pdf = $rep->getPDF();
                        $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                        if(count($pdf) > 0){
                            $date = new DateTime($pdf[0]['timestamp']);
                            $pdfDate = (count($pdf) > 0) ? $date->format('Y-m-d') : "";
                        }                                                
                        $row .= "<td align='center'>{$pdfDate}</td>
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
                            if($extra->getId() == "title"){
                                if($extra->getText() == ""){                                
                                    $incomplete = true;
                                }                            
                            }
                            if($extra->getId() == "summary"){
                                $summary = $extra->getBlobValue();                     
                                $summary = ltrim($summary, "&lt;p&gt");
                                $summary = rtrim($summary, "&lt;/p&gt;");                     
                                $row .= "<td>".$summary."</td>";                
                            }else if($extra->getId() == "partners"){
                                $arr = array();    
                                if(is_array($extra->getBlobValue())){                           
                                    foreach($extra->getBlobValue() as $partner){
                                            array_push($arr, $partner['organizationname']);                             
                                    }
                                    $row .= "<td>".implode(",<br/>",$arr)."</td>";
                                }else{
                                    $row = "<td></td>";                                
                                }
                            }else if(strpos($extra->getId(), "copis") !== false){
                                $arr = array();   
                                if(is_array($extra->getBlobValue())){                            
                                    foreach($extra->getBlobValue() as $copis){
                                        array_push($arr, $copis['name'].", ".$copis['email']);                             
                                    }
                                    $row .= "<td>".implode(",<br/>",$arr)."</td>";
                                }else{
                                    $row = "<td></td>";                                
                                }
                            }else if(strpos($extra->getId(), "theme") !== false){
                                preg_match_all('#\((.*?)\)#', $extra->getText(), $abr);
                                $row .= "<td>".implode(',<br>',$abr[1])."</td>";
                            }else{
                                $row .= "<td>{$extra->getText()}</td>";
                            }
                        }
                        if(!$incomplete){
                            $this->html .= $row;                        
                        }
                    }
                $this->html .= "</tr>";
                }
                
                else{
                    $pdf = $first->getPDF();
                    $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                    $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                    $this->html .= "<td align='center'>{$pdfDate}</td>
                                    <td>{$pdfButton}</td>";
                    foreach($this->extraCols as $extra){
                        $section = new EditableReportSection();
                        $section->setParent($first);
                        $extra->setParent($section);
                        $extra->setPersonId($first->person->getId());
                        if($first->project != null){
                            $extra->setProjectId($first->project->getId());
                        }
                        else{
                            $extra->setProjectId(0);
                        }
                        if($extra->getId() == "summary"){            
                        } else if($extra->getId() == "partners"){
                            $arr = array();                               
                            foreach($extra->getBlobValue() as $partner){
                                array_push($arr, $partner['organizationname']);                             
                            }
                            $this->html .= "<td>".implode(",<br/>",$arr)."</td>";
                        }else if(strpos($extra->getId(), "copis") !== false){
                            $arr = array();                               
                            foreach($extra->getBlobValue() as $copis){
                                array_push($arr, $copis['name'].", ".$copis['email']);                             
                            }
                            $this->html .= "<td>".implode(",<br/>",$arr)."</td>";
                        }else if(strpos($extra->getId(), "theme") !== false){
                            preg_match_all('#\((.*?)\)#', $extra->getText(), $abr);
                            $this->html .= "<td>".implode('<br>',$abr[1])."</td>";
                        }else{
                            $this->html .= "<td>{$extra->getText()}</td>";
                        }
                    }
                }  
                $this->html .= "</tr>";
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
                    {'type': 'string', 'targets': 0 }
                ],
                'dom': 'Blfrtip',
                'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>";
    }
}
?>
