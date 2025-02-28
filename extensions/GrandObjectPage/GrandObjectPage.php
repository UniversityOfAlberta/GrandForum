<?php
    autoload_register('GrandObjectPage');
    autoload_register('GrandObjectPage/TabbedPage');
    
    require_once("Backbone/BackbonePage.php");
    require_once("PersonPage.php");
    require_once("ManagePeople/ManagePeople.php");
    require_once("ManageProducts/ManageProducts.php");
    require_once("ProductHistories/ProductHistories.php");
    require_once("GrantPage/GrantPage.php");
    require_once("GrantAwardPage/GrantAwardPage.php");
    require_once("Keywords/Keywords.php");
    require_once("Products/Products.php");
    
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
