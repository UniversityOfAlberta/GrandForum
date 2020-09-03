<?php

class HQPFundingTab extends AbstractTab {

    var $person;
    var $visibility;

    function HQPFundingTab($person, $visibility){
        parent::AbstractTab("Funding History");
        $this->person = $person;
        $this->visibility = $visibility;
    }
    
    function canView(){
        $me = Person::newFromWgUser();
        if($this->person->isMe()){
            // User is themselves
            return true;
        }
        if($me->isRoleAtLeast(STAFF)){
            // User is at least Staff
            return true;
        }
        if($me->isRelatedToDuring($this->person, SUPERVISES, "0000-00-00", "2100-00-00") ||
           $me->isRelatedToDuring($this->person, CO_SUPERVISES, "0000-00-00", "2100-00-00")){
            // User has supervised the Person
            return true;
        }
        return false;
    }

    function generateBody(){
        global $wgServer, $wgScriptPath;
        if($this->canView()){
            $graddbs = $this->person->getGradDBFinancials();
            $this->html .= "<table id='fundingHistory' class='wikitable' width='100%'>
                            <thead>
                                <tr>
                                    <th>Supervisor</th>
                                    <th>Term(s)</th>
                                    <th>HQP Accepted</th>
                                    <th>Supervisor Accepted</th>
                                    <th>Contract</th>
                                </tr>
                            </thead>
                            <tbody>";
            foreach($graddbs as $graddb){
                $hqpAccepted = ($graddb->hasHQPAccepted()) ? $graddb->getHQPAccepted() : "";
                $supAccepted = ($graddb->hasSupAccepted()) ? $graddb->getSupAccepted() : "";
                $this->html .= "<tr>
                                    <td><a href='{$graddb->getSupervisor()->getUrl()}'>{$graddb->getSupervisor()->getReversedName()}</a></td>
                                    <td>{$graddb->getTerm()}</td>
                                    <td align='middle'>{$hqpAccepted}</td>
                                    <td align='middle'>{$supAccepted}</td>
                                    <td align='middle'><a class='button' target='_blank' href='{$wgServer}{$wgScriptPath}/index.php/Special:GradDB?pdf={$graddb->getMD5()}'>View Contract</a></td>
                                </tr>";
            }
            $this->html .= "</tbody>
                            </table>
                <script type='text/javascript'>
                    $('#fundingHistory').DataTable({
                        aLengthMenu: [
                            [25, 50, 100, 200, -1],
                            [25, 50, 100, 200, 'All']
                        ],
                        iDisplayLength: -1
                    });
                </script>";
        }
        return $this->html;
    }
    
}
?>
