<?php

class PersonAcknowledgementTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonAcknowledgementTab($person, $visibility){
        parent::AbstractTab("Acknowledgements");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->showAcks($this->person, $this->visibility);
        return $this->html;
    }
    
    /*
     * Displays the list of Acknowledgements for this user
     */
    function showAcks($person, $visibility){
        global $wgOut, $wgScriptPath, $wgServer, $wgUser, $config;
        $me = Person::newFromId($wgUser->getId());
        if($visibility['isMe'] || $me->isRoleAtLeast(STAFF)){
            $acks = $person->getAcknowledgements();
            $this->html .= "<script type='text/javascript'>
                                    $(document).ready(function(){
	                                    $('.indexTable').dataTable({'iDisplayLength': 100,
	                                                                'aLengthMenu': [[-1], ['All']]});
                                    });
                                </script>";
	        $this->html .= "<table class='indexTable' frame='box' rules='all'>
	                            <thead>
	                                <tr>
	                                    <th>Name</th>
	                                    <th>University</th>
	                                    <th>Date</th>
	                                    <th>PDF</th>
	                                </tr>
	                            </thead>
	                            <tbody>\n";
            foreach($acks as $ack){
                $this->html .= "<tr>
                                <td><a href='{$person->getUrl()}' target='_blank'>{$person->getReversedName()}</a></td>
                                <td>{$ack->getUniversity()}</td>
                                <td align='center'>{$ack->getDate()}</td>
                                <td align='center'><a href='{$ack->getUrl()}'>Download PDF</a></td>
                             </tr>\n";
            }
            foreach($person->getHQP(true) as $hqp){
                $found = false;
                foreach($hqp->getAcknowledgements() as $ack){
                    if($ack->getSupervisor() == $person->getName() || 
                       $ack->getSupervisor() == $person->getNameForForms()){
                        $found = true;
                        $this->html .= "<tr>
                                            <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a></td>
                                            <td>{$ack->getUniversity()}</td>
                                            <td align='center'>{$ack->getDate()}</td>
                                            <td align='center'><a href='{$ack->getUrl()}'>Download PDF</a></td>
                                         </tr>\n";
                        break;
                    }
                }
                if(!$found){
                    $university = $hqp->getUniversity();
                    $uni = $university['university'];
                    $this->html .= "<tr>
                                        <td><a href='{$hqp->getUrl()}' target='_blank'>{$hqp->getReversedName()}</a></td>
                                        <td>$uni</td>
                                        <td align='center'></td>
                                        <td></td>
                                     </tr>\n";
                }
            }
            $this->html .= "</tbody></table><br />
                            <h2>Instructions</h2>
                            <p>If you do not see a PDF for yourself or your HQP in the above table, then you have not submitted an acknowledgement to the Network Manager.  First you should read the <a href='$wgServer$wgScriptPath/data/{$config->getValue('networkName')} NCE Network Agreement (Section 3 highlighted).pdf'><b>{$config->getValue('networkName')} Network Agreement</b></a> and then fill out the <a href='$wgServer$wgScriptPath/data/GRAND Network Agreement Appendix A.doc'><b>Acknowledgement Form</b></a>.  If you are a from the University of Alberta, then you should fill out the <a href='$wgServer$wgScriptPath/data/{$config->getValue('networkName')} U Alberta Researcher Acknowledgement.doc'><b>University of Alberta Acknowledgement Form</b></a> instead.  To submit an acknowledgement form, email a completed PDF to <a href='mailto:adrian_sheppard@gnwc.ca'>Adrian Sheppard</a>.  Once the acknowledgement is accepted, it should appear in the above table.</p>";
        }
    }
}
?>
