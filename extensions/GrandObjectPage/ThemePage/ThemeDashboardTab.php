<?php

class ThemeDashboardTab extends AbstractTab {

    var $theme;
    var $visibility;

    function ThemeDashboardTab($theme, $visibility){
        parent::AbstractTab("Dashboard");
        $this->theme = $theme;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return;
        }
        $this->showDashboard($this->theme, $this->visibility);
        return $this->html;
    }
    
    function showDashboard($theme, $visibility){
        global $wgOut, $config;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            /*$wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#dashboardAccordion').accordion({autoHeight: false,
                                                        collapsible: true});
                });
            </script>");
            $this->html .= "<h2>Dashboard</h2>";
            $this->html .= "<div id='dashboardAccordion'>";
            $this->html .= "<h3><a href='#'>Overall</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";*/
            $multiDashboard = new MultiDashboardTable();
            $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $theme);
            $multiDashboard->add($dashboard, "Overall");
            foreach($theme->getProjects() as $project){
                $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $project);
                $multiDashboard->add($dashboard, $project->getName());
            }
            $this->html .= $multiDashboard->render(false, false);
            /*$this->html .= "</div>";
            $startYear = YEAR;
            $phaseDates = $config->getValue("projectPhaseDates");
            for($i=$startYear; $i >= substr($phaseDates[1], 0, 4); $i--){
                $this->html .= "<h3><a href='#'>".$i."</a></h3>";
                $this->html .= "<div style='overflow: auto;'>";
                
                $multiDashboard = new MultiDashboardTable();
                $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $theme, $i.'-01-01', $i.'-12-31');
                $multiDashboard->add($dashboard, "Overall");
                foreach($theme->getProjects() as $project){
                    $dashboard = new DashboardTable(THEME_PUBLIC_STRUCTURE, $project, $i.'-01-01', $i.'-12-31');
                    $multiDashboard->add($dashboard, $project->getName());
                }
                $this->html .= $multiDashboard->render(false, false);
                
                $this->html .= "</div>";
            }
            $this->html .="</div>";*/
        }
    }

}    
    
?>
