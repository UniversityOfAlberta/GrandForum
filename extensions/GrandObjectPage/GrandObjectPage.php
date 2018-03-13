<?php
    autoload_register('GrandObjectPage');
    autoload_register('GrandObjectPage/TabbedPage');
    
    require_once("Backbone/BackbonePage.php");
    require_once("PersonPage.php");
    require_once("ProjectPage.php");
    require_once("ThemePage.php");
    require_once("MaterialPage.php");
    require_once("ManagePeople/ManagePeople.php");
    require_once("ManageProducts/ManageProducts.php");
    require_once("ManagePeopleLog.php");
    require_once("Products/Products.php");
    require_once("Contributions/Contributions.php");
    if($config->getValue('networkName') == "FES"){
        // Only show this for FES (for now)
        require_once("Collaborations/Collaboration.php");
    }
    require_once("Bibliography/Bibliography.php");
    require_once("AddMultimediaPage.php");
    
    $wgHooks['AlternateEdit'][] = 'noEdit';
    $wgHooks['UnknownAction'][] = 'noCreate';
    
    function noEdit($editpage){
        global $wgArticle;
        wfRunHooks('ArticleViewHeader', array($wgArticle, "", ""));
        return true;
    }
    
    function noCreate($action, $article){
        if($action == "createFromTemplate"){
            wfRunHooks('ArticleViewHeader', array($article, "", "")); 
        }
        return true;
    }

?>
