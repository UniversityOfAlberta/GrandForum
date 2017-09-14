<?php

class PersonGrantsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonGrantsTab($person, $visibility){
        parent::AbstractTab("Grants");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $config;
        $wgOut->addScript(
                "<script type='text/javascript'>
                    $(document).ready(function(){
                        $('.grantAccordion').accordion({autoHeight: false, collapsible: true, active:false});
                    });
                </script>"
            );
        //$this->html = $this->generateUofAGrantTable();
        //return $this->html;
        $this->html ="
            <div class='grantAccordion'>
                <h3><a href='#'>UoA Grants 3.0</a></h3>
                <div>
                {$this->generateUofAGrantTable()}
                </div>
            </div>
            <div class='grantAccordion'>
                <h3><a href='#'>NSERC</a></h3>
                <div>
                {$this->generateGrantTable()}
                </div>
            </div>";
        return $this->html;
     }

    function generateGrantTable(){
        if(!$this->visibility['isMe']){
            return "";
        }
        $grantAwards = GrantAward::getAllGrantAwards(0, 999999999, $this->person);
        $string = "<table id='grants_table' frame='box' rules='all'>
                    <thead>
                        <tr>
                            <th style='white-space:nowrap;'>Name</th>
                            <th style='white-space:nowrap;'>Fiscal Year</th>
                            <th style='white-space:nowrap;'>Competition Year</th>
                            <th style='white-space:nowrap;'>Amount</th>
                        </tr>
                    </thead><tbody>";
        foreach($grantAwards as $grantAward){
            $partners = $grantAward->getPartners();
            $string .= "<tr><td><a href='{$grantAward->getUrl()}'>{$grantAward->application_title}</a></td>
                            <td style='white-space:nowrap;'>{$grantAward->fiscal_year}</td>
                            <td style='white-space:nowrap;'>{$grantAward->competition_year}</td>
                            <td align=right>$".number_format($grantAward->amount)."</td></tr>";}
            $string .= "</table></tbody><script type='text/javascript'>
                $('#grants_table').dataTable({'iDisplayLength': 25});
            </script>";
        return $string;
    }
    
    function generateUofAGrantTable(){
        if(!$this->visibility['isMe']){
                return "";
        }
        $grants = $this->person->getGrants();
        $string = "<table id='grants_table2' frame='box' rules='all'>
                    <thead><tr><th style='white-space:nowrap;'>Name</th>
                    <th style='white-space:nowrap;'>Sponsor</th>
                    <th style='white-space:nowrap;'>Start Date</th>
                    <th style='white-space:nowrap;'>End Date</th>
                    <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($grants as $grant){
            $grantAward = $grant->getGrantAward();
            $grantAwardText = "";
            if($grantAward != null){
                $grantAwardText = "<br />Grant Award: <a href='{$grantAward->getUrl()}'>{$grantAward->application_title}</a>";
            }
            $string .= "<tr><td><a href='{$grant->getUrl()}'>{$grant->getTitle()}</a><br />{$grant->getDescription()}{$grantAwardText}</td>
                                <td>{$grant->getSponsor()}</td>
                                <td style='white-space:nowrap;'>".time2date($grant->getStartDate(), "Y-m-d")."</td>
                                <td style='white-space:nowrap;'>".time2date($grant->getEndDate(), "Y-m-d")."</td>
                                 <td align=right>$".number_format($grant->getTotal())."</td></tr>";}
        $string .= "</table></tbody><script type='text/javascript'>
            $('#grants_table2').dataTable({'iDisplayLength': 25});
        </script>";
        return $string;
    }
}    
?>
