<?php
    autoload_register('GrandObjectPage');
    autoload_register('GrandObjectPage/TabbedPage');
    
    require_once("BackbonePage.php");
    require_once("PersonPage.php");
    require_once("ProjectPage.php");
    require_once("PublicationPage.php");
    require_once("ContributionPage.php");
    require_once("MaterialPage.php");
    //require_once("FormPage.php");
    require_once("AddPublicationPage.php");
    require_once("AddContributionPage.php");
    require_once("AddMultimediaStoryPage.php");
    //require_once("AddFormPage.php");
    //require_once("MyProjects.php");
    require_once("EditRelations.php");
    require_once("MailingLists.php");
    require_once("Products.php");
    
    $wgHooks['AlternateEdit'][] = 'noEdit';
    $wgHooks['UnknownAction'][] = 'noCreate';
    
    function noEdit(&$editpage){
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
