<?php

class ApplicationTab extends AbstractTab {

    var $rp;
    var $people;

    function ApplicationTab($rp, $people){
        $me = Person::newFromWgUser();
        $this->rp = $rp;
        $this->people = $people;
        $report = new DummyReport($this->rp, $me);
        parent::AbstractTab($report->name);
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        $this->html = "<table id='application_{$this->rp}' frame='box' rules='all'>";
        $this->html .= "<thead>";
        $this->html .= "<tr>
                            <th width='50%'>Name</th>
                            <th>Generation Date</th>
                            <th width='1%'>PDF&nbsp;Download</th>
                        </tr>
                        </thead>
                        <tbody>";
        $report = new DummyReport($this->rp, $me);
        foreach($this->people as $person){
            $report->person = $person;
            if($report->hasStarted()){
                $pdf = $report->getPDF();
                $pdfButton = (count($pdf) > 0) ? "<a class='button' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>Download</a>" : "";
                $pdfDate = (count($pdf) > 0) ? "{$pdf[0]['timestamp']}" : "";
                $this->html .= "<tr>
                    <td>{$person->getName()}</td>
                    <td align='center'>{$pdfDate}</td>
                    <td>{$pdfButton}</td>
                </tr>";
            }
        }
        $this->html .= "</tbody>
                        </table>";
        $this->html .= "<script type='text/javascript'>
            $('#application_{$this->rp}').dataTable({autoWidth: false});
        </script>";
    }
}
?>
