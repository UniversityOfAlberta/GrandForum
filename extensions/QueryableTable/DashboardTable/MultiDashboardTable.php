<?php

class MultiDashboardTable {
    
    var $dashboards = array();
    var $sheetNames = array();
    
    function __construct(){
        $argv = func_get_args();
        switch(func_num_args()){
            case 0:
                self::EmptyMultiDashboardTable();
                break;
            case 2:
                self::InitializedMultiDashboardTable($argv[0], $argv[1]);
                break;
        }
    }
    
    private function EmptyMultiDashboardTable(){
        $this->dashboards = array();
        $this->names = array();
    }
    
    function InitializedMultiDashboardTable($dashboards, $names){
        $this->dashboards = $dashboards;
        $this->names = $names;
    }
    
    function add($dashboard, $name){
        $this->dashboards[] = $dashboard;
        $this->sheetNames[] = $name;
    }
    
    function getDashboards(){
        return $this->dashboards;
    }
    
    function getDashboard($i){
        return $this->dashboards[min($i,$this->nDashboard()-1)];
    }
    
    function nDashboards(){
        return count($this->dashboards);
    }
    
    function render(){
        $ret = "<div class='multiBudget'>";
        foreach($this->dashboards as $key => $dashboard){
            if($key == 0 && $this->nDashboards() > 1){
                // First
                $ret .= "<div>";
                $ret .= "<a class='button disabledButton'>&lt;</a>&nbsp;
                         <a class='button' onClick='$(this).parent().hide();$(this).parent().next().show();'>&gt;</a>";
            }
            else if($key > 0 && $key < $this->nDashboards() - 1){
                // Middle
                $ret .= "<div style='display:none;'>";
                $ret .= "<a class='button' onClick='$(this).parent().hide();$(this).parent().prev().show();'>&lt;</a>&nbsp;
                         <a class='button' onClick='$(this).parent().hide();$(this).parent().next().show();'>&gt;</a>";
            }
            else if($key == 0 && $this->nDashboards() == 1){
                // Only Dashboard
                $ret .= "<div>";
                $ret .= "<a class='button disabledButton'>&lt;</a>&nbsp;
                         <a class='button disabledButton'>&gt;</a>";
            }
            else{
                // Last
                $ret .= "<div style='display:none;'>";
                $ret .= "<a class='button' onClick='$(this).parent().hide();$(this).parent().prev().show();'>&lt;</a>&nbsp;
                         <a class='button disabledButton'>&gt;</a>";
            }
            $ret .= "<h3 style='margin-left:30px;display:inline;'>{$this->sheetNames[$key]}</h3>";
            $ret .= "<div style='margin-top:3px;'>{$dashboard->render()}</div>";
            $ret .= "</div>";
        }
        $ret .= "</div>";
        return $ret;
    }
    
}

?>
