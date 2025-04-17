<?php

    /*
     * Variables
     */
    
    // The name of the Network
    $config->setValue("networkName", "NETWORK");
    
    // The name of the Site
    $config->setValue("siteName", "{$config->getValue("networkName")} Forum");
    
    // Value for wgServer
    $config->setValue("server", "");
    
    // The path for the Forum
    $config->setValue("path", "");
    
    // The domain for the Forum (used for things like mailing list addresses)
    $config->setValue("domain", "");
    
    // DB Type (ie. mysql)
    $config->setValue("dbType", "mysql");
    
    // DB Server (ie. localhost)
    $config->setValue("dbServer", "localhost");
    
    // DB Name
    $config->setValue("dbName", "");
    
    // DB Test Name
    $config->setValue("dbTestName", "");
    
    // DB User
    $config->setValue("dbUser", "");
    
    // DB Password
    $config->setValue("dbPassword", "");

    // ORCID Client ID
    $config->setValue("orcidId", "");
    
    // ORCID Secret Key
    $config->setValue("orcidSecret", "");
    
    // Localization Cache Directory
    $config->setValue("localizationCache", "");
    
    // The location of the encryption key
    $config->setValue("encryptionKey", "");
    
    // Support Email Address
    $config->setValue("supportEmail", "");
    
    // Network Website
    $config->setValue("networkSite", "");
    
    // Logo path
    $config->setValue("logo", "skins/logos/logo.png");
    
    // Icon path (gray)
    $config->setValue("iconPath", "skins/icons/gray_dark/");
    
    // Icon path (highlighted)
    $config->setValue("iconPathHighlighted", "skins/icons/gray_dark/");
    
    // Highlight color for skin
    $config->setValue("highlightColor", "#555555");
    
    // Highlight color for headers
    $config->setValue("headerColor", "#333333");
    
    // Global Message (ie. maintenance message)
    $config->setValue("globalMessage", "");
    
    $config->setValue("nameFormat", "{%First} {%M.} {%Last}");
    
    // The terminology to use for "Product"
    $config->setValue("productsTerm", "Product");
    
    // Whether to auto create a user from single sign on
    $config->setValue('shibCreateUser', false);
    
    // Which extensions to enable
    $config->setValue("extensions", array(
        'AccessControl',
        'Cache',
        'Messages',
        'TabUtils',
        'API',
        'GrandObjects',
        'UI',
        'Notification',
        'GrandObjectPage',
        'AddMember',
        'AddHqp',
        'EditMember',
        'ManagePeople',
        'IndexTables',
        'Reporting',
        'GlobalSearch',
        'Impersonation',
        'Visualizations',
        'CCVExport',
        //'QASummary'
    ));
    
    // Associative array of other Forum instances that this one can import from
    $config->setValue("crossForumUrls", array("AGE-WELL"   => "https://forum.agewell-nce.ca/index.php/Special:CrossForumExport",
                                              "AI4Society" => "https://ai4society.ca/index.php/Special:CrossForumExport",
                                              "CFN"        => "https://forum.cfn-nce.ca/index.php/Special:CrossForumExport",
                                              "FES"        => "https://forum.futureenergysystems.ca/index.php/Special:CrossForumExport",
                                              "UofA FoS"   => "https://forum-fos.ualberta.ca/index.php/Special:CrossForumExport",
                                              "GlycoNet"   => "https://forum.glyconet.ca/index.php/Special:CrossForumExport"));
    
    /*
     * PDF Config
     */
     
    // The font for generated PDF documents
    $config->setValue("pdfFont", "helvetica");
    
    // The font for generated PDF documents
    $config->setValue("pdfFontSize", "10");
    
    // The font for generated PDF documents
    $config->setValue("pdfMargins", array('top'     => 0.75,
                                          'right'   => 0.75,
                                          'bottom'  => 0.50,
                                          'left'    => 0.75));
    
    /*
     * Constants
     */
     
    $config->setConst("DEMO", false);
     
    // The current cycle year
    $config->setConst("YEAR", date('Y'));

    // Start of internal reporting cycle (Used for range queries)
    $config->setConst("CYCLE_START_MONTH", '-07-01');
    $config->setConst("CYCLE_START", ($config->getConst('YEAR')-1).$config->getConst('CYCLE_START_MONTH'));
    
    // Start of reporting period
    $config->setConst("START_MONTH", '-07-01');
    $config->setConst("START", ($config->getConst('YEAR')-1).$config->getConst('START_MONTH'));
    
    // End of reporting period for HQP, NIs and Projects
    $config->setConst("END_MONTH", '-06-30');
    $config->setConst("END", $config->getConst('YEAR').$config->getConst('END_MONTH'));
    
    // End of internal reporting cycle (Used for range queries)
    $config->setConst("CYCLE_END_MONTH", '-06-30');
    $config->setConst("CYCLE_END", ($config->getConst('YEAR')).$config->getConst('CYCLE_END_MONTH'));
    
    /*
     * Roles
     * TODO: These should probably be moved into the DB at some point
     */
    $config->setConst("INACTIVE",   "Inactive");
    $config->setConst("HQP",        "HQP");
    $config->setConst("EXTERNAL",   "External");
    $config->setConst("ACHAIR",     "AssocChair");
    $config->setConst("CHAIR",      "Chair");
    $config->setConst("ADEAN",      "AssocDean");
    $config->setConst("VDEAN",      "ViceDean");
    $config->setConst("DEAN",       "Dean");
    $config->setConst("DEANEA",     "DeanEA");
    $config->setConst("EA",         "EA");
    $config->setConst("NI",         "NI");
    $config->setConst("AR",         "AR");
    $config->setConst("CI",         "CI");
    $config->setConst("HR",         "HR");
    $config->setConst("FEC",        "FEC");
    $config->setConst("STAFF",      "Staff");
    $config->setConst("MANAGER",    "Manager");
    $config->setConst("ADMIN",      "Admin");
    
    $config->setValue("roleDefs", array(
        $config->getConst('INACTIVE')       => "Inactive",
        $config->getConst('HQP')            => "Highly Qualified Person",
        $config->getConst('EXTERNAL')       => "External",
        $config->getConst('ACHAIR')         => "Associate Chair",
        $config->getConst('CHAIR')          => "Chair",
        $config->getConst('EA')             => "Executive Assistant",
        $config->getConst('NI')             => "Network Investigator",
        $config->getConst('AR')             => "Affiliated Researcher",
        $config->getConst('CI')             => "Co-Investigator",
        $config->getConst('HR')             => "Human Resources",
        $config->getConst('FEC')            => "Faculty Evaluation Committee",
        $config->getConst('STAFF')          => "Staff",
        $config->getConst('MANAGER')        => "Manager",
        $config->getConst('ADMIN')          => "Admin"));
        
    $config->setValue("subRoles", array());
    
    $config->setValue("roleAliases", array());
        
    /* Other */
    $config->setValue("analyticsCode", "");
?>
