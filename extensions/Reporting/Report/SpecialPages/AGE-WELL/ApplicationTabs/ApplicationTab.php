<?php

class ApplicationTab extends AbstractTab {

    var $rp;
    var $people;

    function ApplicationTab($rp, $people){
        $me = Person::newFromWgUser();
        $this->rp = $rp;
        $newPeople = array();
        foreach($people as $person){
            $newPeople[$person->getId()] = $person;
        }
        $this->people = $newPeople;
        if(is_array($this->rp)){
            $report = new DummyReport($this->rp[0], $me);
        }
        else{
            $report = new DummyReport($this->rp, $me);
        }
        parent::AbstractTab($report->name);
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $rpId = (is_array($this->rp)) ? $this->rp[0] : $this->rp;
        
        if(is_array($this->rp)){
            $report = array();
            foreach($this->rp as $rp){
                $report[] = new DummyReport($rp, $me);
            }
        }
        else{
            $report = new DummyReport($this->rp, $me);
        }
        
        $this->html = "<table id='application_{$rpId}' frame='box' rules='all'>";
        $this->html .= "<thead>";
        if(is_array($report)){
            $this->html .= "<tr><th></th>";
            foreach($report as $rep){
                $this->html .= "<th colspan='2'>{$rep->name}</th>";
            }
            $this->html .= "</tr>";
        }
        $this->html .= "<tr>
                            <th width='50%'>Name</th>";
        if(is_array($report)){
            foreach($report as $rep){
                $this->html .= "<th>Generation Date</th>
                                <th width='1%'>PDF&nbsp;Download</th>";
            }
        }
        else{
            $this->html .= "<th>Generation Date</th>
                            <th width='1%'>PDF&nbsp;Download</th>";
        }
        $this->html .= "</tr>
                        </thead>
                        <tbody>";
        
        foreach($this->people as $person){
            if(is_array($report)){
                foreach($report as $rep){
                    $rep->person = $person;
                }
                $first = $report[0];
            }
            else{
                $report->person = $person;
                $first = $report;
            }
            if($first->hasStarted()){
                $this->html .= "<tr>
                    <td>{$person->getName()}</td>";
                if(is_array($report)){
                    foreach($report as $rep){
                        $pdf = $rep->getPDF();
                        $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                        $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                        $this->html .= "<td align='center'>{$pdfDate}</td>
                                        <td>{$pdfButton}</td>";
                    }
                }
                else{
                    $pdf = $first->getPDF();
                    $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                    $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                    $this->html .= "<td align='center'>{$pdfDate}</td>
                                    <td>{$pdfButton}</td>";
                }  
                $this->html .= "</tr>";
            }
        }
        $this->html .= "</tbody>
                        </table>";
        $this->html .= "<script type='text/javascript'>
            $('#application_{$rpId}').dataTable({autoWidth: false});
        </script>";
    }
}
?>
