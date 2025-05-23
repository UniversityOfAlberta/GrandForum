<?php
    autoload_register('GrandObjectPage');
    autoload_register('GrandObjectPage/TabbedPage');
    autoload_register('GrandObjectPage/PersonPage');
    autoload_register('GrandObjectPage/ProjectPage');
    
    require_once("Backbone/BackbonePage.php");
    require_once("ProductSummary.php");
    
    if($config->getValue("profilesEnabled")){
        require_once("PersonPage.php");
        require_once("ProjectPage.php");
        require_once("ThemePage.php");
        require_once("MilestonesLog.php");
    }
    else{
        // Profiles are disabled
        $wgHooks['ArticleViewHeader'][] = 'redirectProfile';
    }
    require_once("MaterialPage.php");
    require_once("ManagePeople/ManagePeople.php");
    if($config->getValue("productsEnabled")){
        require_once("ManageProducts/ManageProducts.php");
    }
    
    require_once("ManagePeopleLog.php");
    require_once("Products/Products.php");
    
    if($config->getValue("contributionsEnabled")){
        require_once("Contributions/Contributions.php");
    }
    if($config->getValue('networkName') == "FES" ||
       $config->getValue('networkName') == "NETWORK"){
        // Only show this for FES (for now)
        require_once("Collaborations/Collaboration.php");
        require_once("Projections.php");
    }
    if($config->getValue("productsEnabled")){
        require_once("Bibliography/Bibliography.php");
    }
    if(isExtensionEnabled("Postings")){
        require_once("NewsPosting/NewsPostingPage.php");
        require_once("EventPosting/EventPostingPage.php");
        if($config->getValue('networkName') == "AI4Society"){
            require_once("BSIPosting/BSIPostingPage.php");
        }
    }
    if($config->getValue('networkName') == "ELITE"){
        require_once("extensions/ELITE/ELITE.php");
    }
    if($config->getValue('networkName') == "GlycoNet"){
        require_once("extensions/GrandObjectPage/ManageSOP/ManageSOP.php");
    }
    if(isExtensionEnabled("CRM")){
        require_once("CRM/CRM.php");
        require_once("LIMS/LIMS.php");
    }
    //require_once("AddMultimediaPage.php");
    
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
    
    function redirectProfile($article, $outputDone, $pcache){
        global $wgServer, $wgScriptPath, $wgRoleValues;
        $nsText = ($article != null) ? str_replace("_", " ", $article->getTitle()->getNsText()) : "";
        if(!isset($wgRoleValues[$nsText])){
            // Namespace is not a role namespace
            return true;
        }
        redirect("$wgServer$wgScriptPath");
    }

?>
