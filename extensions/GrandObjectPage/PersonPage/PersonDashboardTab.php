<?php

class PersonDashboardTab extends AbstractTab {

    var $person;
    var $visibility;

    function PersonDashboardTab($person, $visibility){
        parent::AbstractTab("Dashboard");
        $this->person = $person;
        $this->visibility = $visibility;
    }

    function generateBody(){
        $this->showDashboard($this->person, $this->visibility);
        return $this->html;
    }
    
    function showDashboard($person, $visibility){
        global $wgUser;
        if($wgUser->isLoggedIn()){
            $dashboard = null;
            $me = Person::newFromId($wgUser->getId());
            if($person->isRoleAtLeast(CNI) || ($person->isRole(INACTIVE) && $person->wasLastRoleAtLeast(CNI))){
                if($visibility['isMe'] || $me->isRoleAtLeast(STAFF)){
                    // Display Private Dashboard
                    $dashboard = new DashboardTable(NI_PRIVATE_PROFILE_STRUCTURE, $person);
                }
                else{
                    // Display Public Dashboard
                    $dashboard = new DashboardTable(NI_PUBLIC_PROFILE_STRUCTURE, $person);
                }
            }
            else if($person->isRole(HQP) || $person->isRole(EXTERNAL) || ($person->isRole(INACTIVE) && $person->wasLastRole(HQP))){
                $dashboard = new DashboardTable(HQP_PUBLIC_PROFILE_STRUCTURE, $person);
            }
            if($dashboard != null){
                $this->html = $dashboard->render();
            }
            $this->html .= "<script type='text/javascript'>
                var completedRows = $('table.dashboard td:contains((Completed))').parent();
                completedRows.hide();
                if(completedRows.length > 0){
                    var last = completedRows.last();
                    var colspan = completedRows.children().length;
                    var newRow = $('<tr><td style=\'cursor:pointer;\' align=\'center\' colspan=\'' + colspan + '\'><b>Show Completed Projects</b></td></tr>');
                    newRow.hover(function(){
                        $(this).css('background', '#DDDDDD');
                    }, function(){
                        $(this).css('background', '#FFFFFF');
                    });
                    newRow.click(function(){
                        completedRows.show();
                        newRow.hide();
                    });
                    last.after(newRow);
                }
            </script>";
        }
    }
}
?>
