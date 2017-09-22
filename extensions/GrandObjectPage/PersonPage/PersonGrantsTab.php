<?php

class PersonGrantsTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonGrantsTab($person, $visibility){
        parent::AbstractTab("Funding");
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
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$startRange}' size='8' /></td>
                                <td><input type='datepicker' name='endRange' value='{$endRange}' size='8' /></td>
                                <td><input type='button' value='Update' /></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."'
                            });
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                        </script>
                        </div>";
        $this->html .= "
            <div class='grantAccordion'>
                <h3><a href='#'>Grant Accts</a></h3>
                <div>
                {$this->generateUofAGrantTable()}
                </div>
            </div>
            <div class='grantAccordion'>
                <h3><a href='#'>Awards</a></h3>
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
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        $grantAwards = $this->person->getGrantAwardsBetween($startRange, $endRange);
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
        $startRange = (isset($_GET['startRange'])) ? $_GET['startRange'] : CYCLE_START;
        $endRange   = (isset($_GET['endRange']))   ? $_GET['endRange']   : CYCLE_END;
        $grants = $this->person->getGrantsBetween($startRange, $endRange);
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
