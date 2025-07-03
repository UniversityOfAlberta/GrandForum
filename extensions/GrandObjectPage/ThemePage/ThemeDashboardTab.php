<?php

class ThemeDashboardTab extends AbstractTab {

    var $theme;
    var $visibility;

    function __construct($theme, $visibility){
        parent::__construct("Dashboard");
        $this->theme = $theme;
        $this->visibility = $visibility;
        if(isset($_GET['showDashboard'])){
            echo $this->showDashboard($this->theme, $this->visibility);
            exit;
        }
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return;
        }
        $this->html .= "<div id='ajax_dashboard'><br /><span class='throbber'></span></div>";
        $this->html .= "<script type='text/javascript'>
            $.get('{$this->theme->getUrl()}?showDashboard', function(response){
                $('#ajax_dashboard').html(response);
            });
        </script>";
        return $this->html;
    }
    
    function showDashboard($theme, $visibility){
        global $wgOut, $config;
        $me = Person::newFromWgUser();
        $html = "";
        if($me->isLoggedIn()){
            /*$html .= "<script type='text/javascript'>
                $(document).ready(function(){
                    $('#dashboardAccordion').accordion({autoHeight: false,
                                                        collapsible: true});
                });
            </script>";
            $html .= "<h2>Dashboard</h2>";
            $html .= "<div id='dashboardAccordion'>";
            $html .= "<h3><a href='#'>Overall</a></h3>";
            $html .= "<div style='overflow: auto;'>";*/
            $multiDashboard = new MultiDashboardTable();
            $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $theme);
            $multiDashboard->add($dashboard, "Overall");
            foreach($theme->getProjects() as $project){
                $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $project);
                $multiDashboard->add($dashboard, $project->getName());
            }
            $html .= $multiDashboard->render(false, false);
            /*$html .= "</div>";
            $startYear = YEAR;
            $phaseDates = $config->getValue("projectPhaseDates");
            for($i=$startYear; $i >= substr($phaseDates[1], 0, 4); $i--){
                $html .= "<h3><a href='#'>".$i."</a></h3>";
                $html .= "<div style='overflow: auto;'>";
                
                $multiDashboard = new MultiDashboardTable();
                $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $theme, $i.'-01-01', $i.'-12-31');
                $multiDashboard->add($dashboard, "Overall");
                foreach($theme->getProjects() as $project){
                    $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $project, $i.'-01-01', $i.'-12-31');
                    $multiDashboard->add($dashboard, $project->getName());
                }
                $html .= $multiDashboard->render(false, false);
                
                $html .= "</div>";
            }
            $html .="</div>";*/
        }
        return $html;
    }

}    
    
?>
