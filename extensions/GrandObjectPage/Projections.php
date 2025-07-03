<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Projections'] = 'Projections'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Projections'] = $dir . 'Projections.i18n.php';
$wgSpecialPageGroups['Projections'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'Projections::createSubTabs';

function runProjections($par) {
  Projections::execute($par);
}

class Projections extends SpecialPage{

	function __construct() {
		SpecialPage::__construct("Projections", MANAGER.'+', true, 'runProjections');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
		$this->getOutput()->setPageTitle("Projections");
	    $projects = Project::getAllProjectsEver();
	    $phaseDates = $config->getValue("projectPhaseDates");
	    $year = date('Y', strtotime($phaseDates[PROJECT_PHASE]) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y', time() - (4 * 30 * 24 * 60 * 60));
        
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('.wikitable').dataTable({
                                                'iDisplayLength': 100, 
                                                'autoWidth': false,
                                                'scrollX': true,
                                                'dom': 'Blfrtip',
                                                'buttons': [
                                                    'excel'
                                                ]
                                             });
                    $('#projectionsAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $wgOut->addHTML("<div id='projectionsAccordion'>");
        $tab = new ProjectFESProjectionsTab(null, null);
        
        $structure = Product::structure();
        $nCols = 0;
        $productTypes = array();
        foreach($structure['categories'] as $cat => $category){
            $types = $category['types'];
            foreach($types as $type => $data){
                if($type == "Misc"){
                    continue;
                }
                $productTypes[] = $type;
            }
            $productTypes[] = "Other {$cat} Spec";
            $productTypes[] = "Other {$cat}";
        }
        
        for($y=$today; $y >= $year; $y--){
            $wgOut->addHTML("<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>");
            $wgOut->addHTML("<div style='overflow: auto;'>");
            $wgOut->addHTML("<table class='wikitable' frame='box' rules='all'>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th colspan='12'>Recruitment</th>
                                        <th colspan='".count($productTypes)."'>".Inflect::pluralize($config->getValue('productsTerm'))."</th>
                                    </tr>
                                    <tr>
                                        <th>Project</th>
                                        <th>Undergrad</th>
                                        <th>MSc</th>
                                        <th>PhD</th>
                                        <th>PDF</th>
                                        <th>Research Associate</th>
                                        <th>Technician</th>
                                        <th>Other HQP Spec</th>
                                        <th>Other HQP</th>
                                        <th>Admin Staff Spec</th>
                                        <th>Admin Staff</th>
                                        <th>Other Spec</th>
                                        <th>Other</th>");
            foreach($productTypes as $type){
                $wgOut->addHTML("<th>{$type}</th>");
            }
            $wgOut->addHTML("       </tr>
                                </thead>
                                <tbody>");
            $totals = array();
            foreach($projects as $project){
                $tab->project = $project;
                $cols = array();
                $cols['Undergrad'] = $tab->getBlobData("Undergrad", $y);
                $cols['MSc'] = $tab->getBlobData("MSc", $y);
                $cols['PhD'] = $tab->getBlobData("PhD", $y);
                $cols['PDF'] = $tab->getBlobData("PDF", $y);
                $cols['Research Associate'] = $tab->getBlobData("Research Associate", $y);
                $cols['Technician'] = $tab->getBlobData("Technician", $y);
                $cols['Other HQP Spec'] = $tab->getBlobData("Other HQP Spec", $y);
                $cols['Other HQP'] = $tab->getBlobData("Other HQP", $y);
                $cols['Administrative Staff Spec'] = $tab->getBlobData("Administrative Staff Spec", $y);
                $cols['Administrative Staff'] = $tab->getBlobData("Administrative Staff", $y);
                $cols['Other Spec'] = $tab->getBlobData("Other Spec", $y);
                $cols['Other'] = $tab->getBlobData("Other", $y);
                foreach($productTypes as $type){
                    $cols[$type] = $tab->getBlobData($type, $y);
                }
                $wgOut->addHTML("<tr><td style='white-space:nowrap;'><b>{$project->getName()}</b></td>");
                foreach($cols as $key => $col){
                    if(is_numeric($col)){
                        $wgOut->addHTML("<td align='right'>{$col}</td>");
                        @$totals[$key] += $col;
                    }
                    else{
                        $wgOut->addHTML("<td>{$col}</td>");
                        $totals[$key] = @$totals[$key];
                    }
                }
                $wgOut->addHTML("</tr>");
            }
            $wgOut->addHTML("</tbody>
                             <tfoot>
                                <tr><th style='text-align:right;'>Total:</th>");
            foreach($totals as $total){
                if(is_numeric($total)){
                    $wgOut->addHTML("<td align='right'><b>{$total}</b></td>");
                }
                else{
                    $wgOut->addHTML("<td></td>");
                }
            }
            $wgOut->addHTML("   </tr>
                             </tfoot></table>");
	        $wgOut->addHTML("</div>");
	    }
	    $wgOut->addHTML("</div>");
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "Projections") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Projections", "$wgServer$wgScriptPath/index.php/Special:Projections", $selected);
	    }
	    return true;
    }
}
?>
