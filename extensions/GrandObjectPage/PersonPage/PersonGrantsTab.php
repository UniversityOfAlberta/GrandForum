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
        $this->html = $this->generateUofAGrantTable();
        return $this->html;
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
        $contributions = $this->person->getContributions();
        $string = "<table id='grants_table' frame='box' rules='all'>
                    <thead><tr><th style='white-space:nowrap;'>Name</th>
                    <th style='white-space:nowrap;'>Partner</th>
                    <th style='white-space:nowrap;'>Start Date</th>
                    <th style='white-space:nowrap;'>End Date</th>
                    <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($contributions as $contribution){
            $partners = $contribution->getPartners();
            if($contribution->project_id != ""){
                continue;
            }
            $string .= "<tr><td><a href='{$contribution->getURL()}'>{$contribution->getName()}</a></td>
                            <td></td>
                            <td style='white-space:nowrap;'>".time2date($contribution->getStartDate(), "Y-m-d")."</td>
                            <td style='white-space:nowrap;'>".time2date($contribution->getEndDate(), "Y-m-d")."</td>
                            <td align=right>$".number_format($contribution->getTotal())."</td></tr>";}
            $string .= "</table></tbody><script type='text/javascript'>
                $('#grants_table').dataTable();
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
            $string .= "<tr><td><a href='{$grant->getUrl()}'>{$grant->getTitle()}</a><br />{$grant->getDescription()}</td>
                                <td>{$grant->getSponsor()}</td>
                                <td style='white-space:nowrap;'>".time2date($grant->getStartDate(), "Y-m-d")."</td>
                                <td style='white-space:nowrap;'>".time2date($grant->getEndDate(), "Y-m-d")."</td>
                                 <td align=right>$".number_format($grant->getTotal())."</td></tr>";}
        $string .= "</table></tbody><script type='text/javascript'>
            $('#grants_table2').dataTable();
        </script>";
        return $string;
    }
}    
?>
