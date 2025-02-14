<?php

class PersonGrantsTab extends AbstractTab {

    var $person;
    var $visibility;
    var $startRange;
    var $endRange;

    function PersonGrantsTab($person, $visibility, $startRange=SOT, $endRange=CYCLE_END){
        parent::AbstractTab("Funding");
        $this->person = $person;
        $this->visibility = $visibility;
        $this->startRange = $startRange;
        $this->endRange = $endRange;
        $this->tooltip = "Contains two tables listing the faculty member's Grants, as shown in the UoA's Peoplesoft system, and the corresponding Awarded NSERC Applications between the specified start and end dates.";
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
        $me = Person::newFromWgUser();
        $this->html .= "<div id='{$this->id}'>
                        <table>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <td><input type='datepicker' name='startRange' value='{$this->startRange}' size='10' /></td>
                                <td><input type='datepicker' name='endRange' value='{$this->endRange}' size='10' /></td>
                                <td><input type='button' value='Update' /></td>
                                <td id='manage{$this->id}cell'></td>
                            </tr>
                        </table>
                        <script type='text/javascript'>
                            $('div#{$this->id} input[type=datepicker]').datepicker({
                                dateFormat: 'yy-mm-dd',
                                changeMonth: true,
                                changeYear: true,
                                yearRange: '1900:".(date('Y')+3)."',
                                onChangeMonthYear: function (year, month, inst) {
                                    var curDate = $(this).datepicker('getDate');
                                    if (curDate == null)
                                        return;
                                    if (curDate.getYear() != year || curDate.getMonth() != month - 1) {
                                        curDate.setYear(year);
                                        curDate.setMonth(month - 1);
                                        while(curDate.getMonth() != month -1){
                                            curDate.setDate(curDate.getDate() - 1);
                                        }
                                        $(this).datepicker('setDate', curDate);
                                        $(this).trigger('change');
                                    }
                                }
                            });
                            $('div#{$this->id} input[type=button]').click(function(){
                                var startRange = $('div#{$this->id} input[name=startRange]').val();
                                var endRange = $('div#{$this->id} input[name=endRange]').val();
                                document.location = '{$this->person->getUrl()}?tab={$this->id}&startRange=' + startRange + '&endRange=' + endRange;
                            });
                            $(document).ready(function(){
                                $('#manage{$this->id}').clone().appendTo('#manage{$this->id}cell');
                            });
                        </script>
                        </div>";
        $this->html .= "
            <div class='grantAccordion'>
                <h3><a href='#'>Revenue Accts</a></h3>
                <div>
                {$this->generateUofAGrantTable()}
                </div>
            </div>";
        /*$this->html .= "
            <div class='grantAccordion'>
                <h3><a href='#'>Awarded NSERC Applications</a></h3>
                <div>
                {$this->generateGrantTable()}
                </div>
            </div>";*/
        if($me->isAllowedToEdit($this->person)){
            $this->html .= "<br /><a id='manage{$this->id}' href='$wgServer$wgScriptPath/index.php/Special:GrantPage' class='button'>Manage Funding</a>";
        }
        return $this->html;
     }

    function generateGrantTable(){
        if(!$this->visibility['isMe']){
            return "";
        }

        $grantAwards = $this->person->getGrantAwardsBetween($this->startRange, $this->endRange);
        $string = "<table id='grants_table' frame='box' rules='all'>
                    <thead>
                        <tr>
                            <th style='white-space:nowrap;' width='50%'>Name</th>
                            <th style='white-space:nowrap;'>Program</th>
                            <th style='white-space:nowrap;'>Timeframe</th>
                            <th style='white-space:nowrap;'>Competition Year</th>
                            <th style='white-space:nowrap;'>Amount</th>
                        </tr>
                    </thead><tbody>";
        foreach($grantAwards as $grantAward){
            $partners = $grantAward->getPartners();
            $string .= "<tr><td><a href='{$grantAward->getUrl()}'>{$grantAward->application_title}</a></td>
                            <td>{$grantAward->program_name}</td>
                            <td style='white-space:nowrap;'>{$grantAward->start_year} - {$grantAward->end_year}</td>
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

        $grants = $this->person->getGrantsBetween($this->startRange, $this->endRange);
        $string = "<table id='grants_table2' frame='box' rules='all'>
                    <thead><tr><th style='white-space:nowrap;'>Name</th>
                    <th style='white-space:nowrap;'>Sponsor</th>
                    <th style='white-space:nowrap;'>Start Date</th>
                    <th style='white-space:nowrap;'>End Date</th>
                    <th style='white-space:nowrap;'>Total</th></tr></thead><tbody>";
        foreach($grants as $grant){
            $grantAwardText = "";
            /*$grantAward = $grant->getGrantAward();
            if($grantAward != null){
                $grantAwardText = "<br />Grant Award: <a href='{$grantAward->getUrl()}'>{$grantAward->application_title}</a>";
            }*/
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
