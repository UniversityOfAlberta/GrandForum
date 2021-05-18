<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['SpecialEventRegistrationTable'] = 'SpecialEventRegistrationTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['SpecialEventRegistrationTable'] = $dir . 'SpecialEventRegistrationTable.i18n.php';
$wgSpecialPageGroups['SpecialEventRegistrationTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'SpecialEventRegistrationTable::createSubTabs';

class SpecialEventRegistrationTable extends SpecialPage{

    function SpecialEventRegistrationTable() {
        parent::__construct("SpecialEventRegistrationTable", STAFF.'+', true);
    }

    function execute($par){
        global $wgOut, $wgUser, $config, $wgServer, $wgScriptPath;
        $registrations = EventRegistration::getAllEventRegistrations();
        $wgOut->addHTML("<table id='eventsTable' class='wikitable' frame='box' rules='all' width='100%'>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th style='width:1%;'>Receive Information</th>
                    <th style='width:1%;'>Join Newsletter</th>
                    <th style='width:1%;'>Create Profile</th>
                    <th style='width:1%;'>Similar Events</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>");
        foreach($registrations as $registration){
            $receiveInformation = ($registration->receiveInformation) ? "&#10003;" : "";
            $joinNewsletter = ($registration->joinNewsletter) ? "&#10003;" : "";
            $createProfile = ($registration->createProfile) ? "&#10003;" : "";
            $similarEvents = ($registration->similarEvents) ? "&#10003;" : "";
            $wgOut->addHTML("<tr>
                <td>{$registration->getEvent()->title}</td>
                <td>{$registration->name}</td>
                <td>{$registration->email}</td>
                <td>{$registration->role}</td>
                <td align='center' style='font-size:2em;'>{$receiveInformation}</td>
                <td align='center' style='font-size:2em;'>{$joinNewsletter}</td>
                <td align='center' style='font-size:2em;'>{$createProfile}</td>
                <td align='center' style='font-size:2em;'>{$similarEvents}</td>
                <td align='center'>{$registration->created}</td>
            </tr>");
        }
        $wgOut->addHTML("</tbody></table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#eventsTable').DataTable({
                'autoWidth': false,
	            'aLengthMenu': [[-1], ['All']],
	            'order': [8, 'desc'],
	            'dom': 'Blfrtip',
	            'buttons': [
                    'excel', 'pdf'
                ]
            });
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        $person = Person::newFromWgUser($wgUser);
        if($person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "SpecialEventRegistrationTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Event Registraton Table", "$wgServer$wgScriptPath/index.php/Special:SpecialEventRegistrationTable", $selected);
        }
        return true;
    }

}

?>
