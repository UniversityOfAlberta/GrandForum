<?php

    /*
     * Variables
     */
    
    // The name of the Network
    $config->setValue("networkName", "NETWORK");
    
    // The name of the Site
    $config->setValue("siteName", "{$config->getValue("networkName")} Forum");
    
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
    
    // Default Mailing List Admins
    $config->setValue("listAdmins", array());
    
    // Default Mailing List Password
    $config->setValue("listAdminPassword", "");
    
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
    
    // The terminology for project themes 
    $config->setValue("projectThemes", "Theme");
    
    // The terminology for administrative projects 
    $config->setValue("adminProjects", "Admin Project");
    
    $config->setValue("nameFormat", "{%First} {%M.} {%Last}");
    
    // The terminology to use for "Product"
    $config->setValue("productsTerm", "Product");
   
    // Whether or not the forum only contains one university
    $config->setValue("singleUniversity",false);
 
    // Whether or not to allow bigBetProjects
    $config->setValue("bigBetProjects", false);
    
    // Whether or not to allow projectTypes
    $config->setValue("projectTypes", false);
    
    // Whether or not to allow projectStatus
    $config->setValue("projectStatus", true);
    
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
        'MailingList',
        'AddMember',
        //'AddHqp',
        'EditMember',
        'ManagePeople',
        'IndexTables',
        //'GradDB',
        'Reporting',
        'GlobalSearch',
        'Impersonation',
        'Visualizations',
        'Duplicates',
        'ProjectEvolution',
        'CCVExport',
        //'CrossForumExport',
        //'QASummary'
    ));
    
    $config->setValue("reportingExtras", array('EvaluationTable'        => false,
                                               'ReportStats'            => false,
                                               'CreatePDF'              => false,
                                               'ReportArchive'          => false,
                                               'ReviewResults'          => false,
                                               'AdminVisualizations'    => false));
    
    // What social links to have in the top header
    // should be an associative array with the index as the type of social network, and the value is the url
    // Options: twitter, linkedin, flickr, youtube
    $config->setValue("socialLinks", array());
    
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
    $config->setConst("CYCLE_START_MONTH", '-01-01');
    $config->setConst("CYCLE_START", $config->getConst('YEAR').$config->getConst('CYCLE_START_MONTH'));
    
    // Start of NCE reporting cycle
    $config->setConst("NCE_START_MONTH", '-04-01');
    $config->setConst("NCE_START", $config->getConst('YEAR').$config->getConst('NCE_START_MONTH'));
    
    // Start of reporting period
    $config->setConst("START_MONTH", '-09-01');
    $config->setConst("START", $config->getConst('YEAR').$config->getConst('START_MONTH'));
    
    // End of reporting period for HQP, NIs and Projects
    $config->setConst("END_MONTH", '-12-31');
    $config->setConst("END", $config->getConst('YEAR').$config->getConst('END_MONTH'));
    
    // End of internal reporting cycle (Used for range queries)
    $config->setConst("CYCLE_END_MONTH_ACTUAL", '-12-31');
    $config->setConst("CYCLE_END_ACTUAL", $config->getConst('YEAR').$config->getConst('CYCLE_END_MONTH_ACTUAL'));
    
    // End of internal reporting cycle (Used for range queries)
    $config->setConst("CYCLE_END_MONTH", '-01-15');
    $config->setConst("CYCLE_END", ($config->getConst('YEAR')+1).$config->getConst('CYCLE_END_MONTH'));
    
    // Production of NI and Project reports
    $config->setConst("PRODUCTION_MONTH", '-01-15');
    $config->setConst("PRODUCTION", ($config->getConst('YEAR')+1).$config->getConst('PRODUCTION_MONTH'));
    
    // RMC when evaluator reports can be revised
    $config->setConst("RMC_REVISED_MONTH", '-02-19');
    $config->setConst("RMC_REVISED", ($config->getConst('YEAR')+1).$config->getConst('RMC_REVISED_MONTH'));
    
    // RMC meeting for fund allocation
    $config->setConst("RMC_MEETING_MONTH", '-02-28');
    $config->setConst("RMC_MEETING", ($config->getConst('YEAR')+1).$config->getConst('RMC_MEETING_MONTH'));
    
    // End of NCE reporting cycle
    $config->setConst("NCE_END_MONTH", '-03-31');
    $config->setConst("NCE_END", ($config->getConst('YEAR')+1).$config->getConst('NCE_END_MONTH'));
    
    // Production of NCE report
    $config->setConst("NCE_PRODUCTION_MONTH", '-06-15');
    $config->setConst("NCE_PRODUCTION", ($config->getConst('YEAR')+1).$config->getConst('NCE_PRODUCTION_MONTH')); 
    
    /*
     * Roles
     * TODO: These should probably be moved into the DB at some point
     */
    $config->setConst("INACTIVE",   "Inactive");
    $config->setConst("HQP",        "HQP");
    $config->setConst("PS",         "PS");
    $config->setConst("EXTERNAL",   "External");
    $config->setConst("ACHAIR",     "AssocChair");
    $config->setConst("CHAIR",      "Chair");
    $config->setConst("ADEAN",      "AssocDean");
    $config->setConst("VDEAN",      "ViceDean");
    $config->setConst("DEAN",       "Dean");
    $config->setConst("DEANEA",     "DeanEA");
    $config->setConst("SRC",        "SRC");
    $config->setConst("EA",         "EA");
    $config->setConst("NI",         "NI");
    $config->setConst("AR",         "AR");
    $config->setConst("CI",         "CI");
    $config->setConst("PL",         "PL");
    $config->setConst("APL",        "APL");
    $config->setConst("TL",         "TL");
    $config->setConst("TC",         "TC");
    $config->setConst("HR",         "HR");
    $config->setConst("RMC",        "RMC");
    $config->setConst("EVALUATOR",  "Evaluator");
    $config->setConst("STAFF",      "Staff");
    $config->setConst("MANAGER",    "Manager");
    $config->setConst("ADMIN",    "Admin");
    
    $config->setValue("roleDefs", array(
        $config->getConst('INACTIVE')       => "Inactive",
        $config->getConst('HQP')            => "Highly Qualified Person",
        $config->getConst('PS')             => "Project Support",
        $config->getConst('EXTERNAL')       => "External",
        $config->getConst('ACHAIR')         => "Associate Chair",
        $config->getConst('CHAIR')          => "Chair",
        $config->getConst('SRC')            => "Scientific Research Committee",
        $config->getConst('EA')             => "Executive Assistant",
        $config->getConst('NI')             => "Network Investigator",
        $config->getConst('AR')             => "Affiliated Researcher",
        $config->getConst('CI')             => "Co-Investigator",
        $config->getConst('PL')             => "Project Leader",
        $config->getConst('APL')            => "Admin Project Leader",
        $config->getConst('TL')             => "Theme Leader",
        $config->getConst('TC')             => "Work Package Coordinator",
        $config->getConst('HR')             => "Human Resources",
        $config->getConst('RMC')            => "Research Management Comittee",
        $config->getConst('EVALUATOR')      => "Evaluator",
        $config->getConst('STAFF')          => "Staff",
        $config->getConst('MANAGER')        => "Manager",
        $config->getConst('ADMIN')          => "Admin"));
        
    $config->setValue("subRoles", array());
    
    $config->setValue("roleAliases", array());
        
    /* Other */
    $config->setValue("analyticsCode", "");
?>
