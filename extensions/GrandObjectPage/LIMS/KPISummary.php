<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['KPISummary'] = 'KPISummary'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['KPISummary'] = $dir . 'KPISummary.i18n.php';
$wgSpecialPageGroups['KPISummary'] = 'other-tools';

$wgHooks['SubLevelTabs'][] = 'KPISummary::createSubTabs';

function runKPISummary($par) {
    KPISummary::execute($par);
}

class KPISummary extends SpecialPage{

	function KPISummary() {
		SpecialPage::__construct("KPISummary", MANAGER.'+', true, 'runKPISummary');
	}

	function execute($par){
		global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
	    $adminProjects = array();
	    $projects = Project::getAllProjectsDuring("0000-00-00", EOT);
	    $startYear = date('Y');
	    foreach($projects as $project){
	        if($project->getType() == "Administrative"){
	            $adminProjects[] = $project;
	            $startYear = min($startYear, date('Y', strtotime($project->getCreated()) - (3 * 30 * 24 * 60 * 60)));
	        }
	    }
	    
	    $wgOut->addScript("<script type='text/javascript'>
            $(document).ready(function(){
                $('#kpiAccordion').accordion({autoHeight: false,
                                                 collapsible: true});
            });
        </script>");
        $wgOut->addHTML("<div id='kpiAccordion'>");
        $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Roll-over kpi in April
        $phaseDates = $config->getValue("projectPhaseDates");
        for($i=$endYear; $i >= $startYear; $i--){
            $fullYear = ProjectKPITab::getKPITemplate();
            foreach(ProjectKPITab::$qMap as $q => $quarter){
                switch($q){
                    case 1:
                        $date = "{$i}-04-01";
                        $enddate = "{$i}-07-01";
                        break;
                    case 2:
                        $date = "{$i}-07-01";
                        $enddate = "{$i}-10-01";
                        break;
                    case 3:
                        $date = "{$i}-10-01";
                        $enddate = ($i+1)."-01-01";
                        break;
                    case 4:
                        $date = ($i+1)."-01-01";
                        $enddate = ($i+1)."-04-01";
                        break;
                }
                if(date('Y-m-d') < $date){
                    continue;
                }
                $wgOut->addHTML("<h3><a href='#'>".$i."/".substr($i+1,2,2)." Q{$q}</a></h3>");
                $wgOut->addHTML("<div style='overflow: auto;'>");
                
                // KPI
                $kpiSummary = ProjectKPITab::getKPITemplate();
                foreach($adminProjects as $project){
                    if(substr($project->getCreated(), 0, 10) <= $enddate){
                        list($kpi, $md5) = ProjectKPITab::getKPI($project, "KPI_{$i}_Q{$q}", $date, $enddate);
                        $kpi->xls[41][2]->value = $kpi->xls[41][2]->value/count($adminProjects);
                        $kpiSummary = ProjectKPITab::addKPI($kpiSummary, $kpi);
                    }
                }
                if($kpiSummary != null){
                    $wgOut->addHTML("<div id='KPI_{$i}_Q{$q}'>{$kpiSummary->render()}</div><br />");
                }
                
                $fullYear = ProjectKPITab::addKPI($fullYear, $kpiSummary);

                $wgOut->addHTML("<a class='externalLink' style='cursor:pointer;' id='download_KPI_{$i}_Q{$q}'>Download KPI</a>
                    <script type='text/javascript'>
                        $('#download_KPI_{$i}_Q{$q}').click(function(){
                            window.open('data:application/vnd.ms-excel;base64,' + base64Conversion($('#KPI_{$i}_Q{$q} table')[0].outerHTML));
                        });
                    </script>
                </div>");
            }
            
            $wgOut->addHTML("<h3><a href='#'>".$i."/".substr($i+1,2,2)." Full Year</a></h3>");
            $wgOut->addHTML("<div style='overflow: auto;'>");
            $wgOut->addHTML("<div id='KPI_{$i}'>{$fullYear->render()}</div><br />");
            $wgOut->addHTML("<a class='externalLink' style='cursor:pointer;' id='download_KPI_{$i}_Q{$q}'>Download KPI</a>
                    <script type='text/javascript'>
                        $('#download_KPI_{$i}_Q{$q}').click(function(){
                            window.open('data:application/vnd.ms-excel;base64,' + base64Conversion($('#KPI_{$i} table')[0].outerHTML));
                        });
                    </script>
                </div>");
            
        }
        $wgOut->addHTML("</div>");
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
	    $person = Person::newFromWgUser($wgUser);
	    if($person->isRoleAtLeast(MANAGER)){
	        $selected = @($wgTitle->getText() == "KPISummary") ? "selected" : false;
	        $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("CFI-MSI", "$wgServer$wgScriptPath/index.php/Special:KPISummary", $selected);
	    }
	    return true;
    }
}
?>
