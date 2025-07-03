<?php
require_once('FitbitStats.php');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['FitbitStats'] = 'FitbitStats'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['AdminUsageStats'] = $dir . 'FitbitStats.i18n.php';
$wgSpecialPageGroups['FitbitStats'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'FitbitStats::createSubTabs';

class FitbitStats extends SpecialPage {

    function __construct() {
        SpecialPage::__construct("FitbitStats", STAFF.'+', true);
    }

    function execute($par){
        global $wgUser, $wgOut, $wgServer, $wgScriptPath, $wgTitle;
        $this->getOutput()->setPageTitle("Fitbit Stats");
        $data = DBFunctions::select(array('grand_fitbit_data'),
                                    array('*'));
        $wgOut->addHTML("<table id='table' class='wikitable'>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Steps</th>
                                    <th>Distance (km)</th>
                                    <th>Active (m)</th>
                                    <th>Sleep (h)</th>
                                    <th>Water (mL)</th>
                                    <th>Fibre (g)</th>
                                    <th>Protein (g)</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($data as $row){
            if($this->exclude($row['user_id'])){ continue; }
            $person = Person::newFromId($row['user_id']);
            $wgOut->addHTML("<tr>
                                <td>{$person->getNameForForms()}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['steps']}</td>
                                <td>{$row['distance']}</td>
                                <td>{$row['active']}</td>
                                <td>".number_format($row['sleep']/1000/60/60, 2)."</td>
                                <td>{$row['water']}</td>
                                <td>{$row['fibre']}</td>
                                <td>{$row['protein']}</td>
                             </tr>");
        }
        $wgOut->addHTML("   </tbody>
                         </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#table').DataTable({
                aLengthMenu: [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']],
                iDisplayLength: -1,
                'dom': 'Blfrtip',
                'buttons': [
                    'excel'
                ]
            });
        </script>");
    }
    
    function exclude($userId){
        $person = Person::newFromId($userId);
        if($person->getId() == 0){ return true; }
        $postal_code = AdminDataCollection::getBlobValue(BLOB_TEXT, YEAR, "RP_AVOID", "AVOID_Questions_tab0", "POSTAL", $person->getId());
        if($person->isRoleAtLeast(STAFF) || $postal_code == "CFN"){
            return true;
        }
        return false;
    }

    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "FitbitStats") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Fitbit", "{$wgServer}{$wgScriptPath}/index.php/Special:FitbitStats", $selected);
        }
        return true;
    }

}

?>
