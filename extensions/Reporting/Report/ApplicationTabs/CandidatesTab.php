<?php

class CandidatesTab extends AbstractTab {

    function __construct(){
        parent::__construct("HQP Candidates");
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        $candidates = Person::getAllCandidates(HQP);
        $this->html = "<table id='HQPApplicationTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th>Registration Date</th>
                    <th>University</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>";
        foreach($candidates as $candidate){
            $this->html .= "<tr>";
            $candidate->getName();
            $this->html .= "<td align='right'>{$candidate->getFirstName()}</td>";
            $this->html .= "<td>{$candidate->getLastName()}</td>";
            $this->html .= "<td><a href='mailto:{$candidate->getEmail()}'>{$candidate->getEmail()}</a></td>";
            $this->html .= "<td>".time2date($candidate->getRegistration(), 'Y-m-d')."</td>";
            $this->html .= "<td>{$candidate->getUni()}</td>";
            $this->html .= "<td>{$candidate->getPosition()}</td>";
            $this->html .= "</tr>";
        }
        $this->html .= "</tbody></table>";
        
        $this->html .= "<script type='text/javascript'>
            $('#HQPApplicationTable').dataTable({'iDisplayLength': 100, autoWidth: false});
        </script>";
    }
}
?>
