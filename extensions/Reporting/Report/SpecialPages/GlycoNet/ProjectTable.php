<?php

autoload_register('Reporting/Report/SpecialPages/GlycoNet/Tabs');

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ProjectTable'] = 'ProjectTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProjectTable'] = $dir . 'ProjectTable.i18n.php';
$wgSpecialPageGroups['ProjectTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ProjectTable::createSubTabs';

function runProjectTable($par) {
    ProjectTable::execute($par);
}

class ProjectTable extends SpecialPage{

    function ProjectTable() {
        SpecialPage::__construct("ProjectTable", null, false, 'runProjectTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        if(isset($_GET['project'])){
            $this->project();
        }
        else {
            $this->table();
        }
    }
    
    function table(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $projects = Project::getAllProjectsEver();
        $wgOut->addHTML("<table id='projectTable' class='wikitable' width='100%'>
                            <thead>
                                <tr>
                                    <th>Acronym</th>
                                    <th>Name</th>
                                    <th>Leaders</th>
                                    <th>Theme</th>
                                </tr>
                            </thead>
                            <tbody>");
        foreach($projects as $project){
            $leaders = array();
            $themes = array();
            if(!$project->isDeleted()){
                foreach($project->getAllPeople(PL) as $leader){
                    $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
                }
            }
            else{
                foreach($project->getAllPeopleOn(PL, $project->getEffectiveDate()) as $leader){
                    $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
                }
            }
            foreach($project->getChallenges() as $challenge){
                $themes[] = ($challenge->getAcronym() != "") ? "<a href='{$challenge->getUrl()}'>{$challenge->getName()} ({$challenge->getAcronym()})</a>" : "";
            }
            $wgOut->addHTML("<tr>
                                <td><span style='display:none;'>{$project->getName()}</span><a href='{$wgServer}{$wgScriptPath}/index.php/Special:ProjectTable?project={$project->getId()}'>{$project->getName()}</a></td>
                                <td>{$project->getFullName()}</td>
                                <td>".implode(", ", $leaders)."</td>
                                <td>".implode(", ", $themes)."</td>
                             </tr>");
        }
        $wgOut->addHTML("</tbody>
                    </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#projectTable').dataTable({
                                        iDisplayLength: 100, 
                                        autoWidth: false,
                                        dom: 'Blfrtip',
                                        columnDefs: [
                                           {type: 'natural', targets: 0}
                                        ],
                                        buttons: [
                                            'excel', 'pdf'
                                        ]
                                     });
        </script>");
    }
    
    function project(){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $project = Project::newFromId($_GET['project']);
        if($project != null && $project->getId() != 0){
            $wgOut->setPageTitle($project->getName());
            $tabbedPage = new TabbedPage("project");
            
            $start = substr($project->getCreated(), 0, 4);
            $end = substr($project->getDeleted(), 0, 4);

            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Application 1", "APPLICATION"));
            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Application 2", "APPLICATION2"));
            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Scientific Reviews", "SCIENTIFIC_REVIEWS"));
            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Business Assessment", "BUSINESS_ASSESSMENT"));
            
            if($end == "0000"){
                $end = date('Y');
            }
            for($year=$end; $year >= $start; $year--){
                $tabbedPage->addTab(new ProjectPDFTab($project, "$year", array(RP_PROGRESS, 'RP_MILE_REPORT'), $year));
            }
            $tabbedPage->addTab(new ProjectProductsTab($project));
            $tabbedPage->addTab(new ProjectMilestonesROTab($project, array('edit' => false)));
            //$tabbedPage->addTab(new ProjectGlyconetBudgetTab($project));
            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Budget", "BUDGET"));
            $tabbedPage->addTab(new ProjectCRMTab($project, "CRM"));
            $tabbedPage->addTab(new ProjectBDTab($project));
            $tabbedPage->addTab(new ProjectUploadPDFTab($project, "Additional Info", "ADDITIONAL", true));
            $tabbedPage->showPage();
            if(isset($_POST['submit'])){
                redirect("{$wgServer}{$wgScriptPath}/index.php/Special:ProjectTable?project={$project->getId()}");
            }
        }
        else{
            $wgOut->setPageTitle("Project Not Found");
        }
        $wgOut->addHTML("<script type='text/javascript'>
            $('#bodyContent > h1').html('<a href=\"' + wgServer + wgScriptPath + '/index.php/Special:ProjectTable\">Project Table</a> &gt; ' + $('#bodyContent > h1').text());
            $('#bodyContent > h1').show();
            $('#project h1').hide();
            $('form').attr('action', $('form').attr('action') + '?project={$project->getId()}');
            $('#milestones .button').remove();
        </script>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        $person = Person::newFromWgUser();
        
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "BusinessDevelopment")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("BD", "$wgServer$wgScriptPath/index.php/Special:Report?report=BusinessDevelopment", $selected);
            
            $selected = @($wgTitle->getText() == "ProjectTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Projects", "$wgServer$wgScriptPath/index.php/Special:ProjectTable", $selected);
        }
        return true;
    }

}

?>
