<?php
    autoload_register('GrandObjectPage');
    autoload_register('GrandObjectPage/TabbedPage');
    
    require_once("Backbone/BackbonePage.php");
    require_once("PersonPage.php");
    require_once("ProjectPage.php");
    //require_once("EditRelations.php");
    if(isExtensionEnabled('Products')){
        require_once("ThemePage.php");
        require_once("ContributionPage.php");
        require_once("MaterialPage.php");
        require_once("ManageProducts/ManageProducts.php");
        require_once("AddContributionPage.php");
        require_once("AddMultimediaPage.php");
        require_once("Products/Products.php");
    }
    
    $wgHooks['AlternateEdit'][] = 'noEdit';
    UnknownAction::createAction('noCreate');
    
    function noEdit($editpage){
        global $wgArticle;
        Hooks::run('ArticleViewHeader', array($wgArticle, "", ""));
        return true;
    }
    
    function noCreate($action, $article){
        if($action == "createFromTemplate"){
            Hooks::run('ArticleViewHeader', array($article, "", "")); 
        }
        return true;
    }

?>
