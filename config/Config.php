<?php

    /*
     * Variables
     */

    // The name of the Network
    $config->setValue("networkName", "CSGARS");
    
    // The name of the Site
    $config->setValue("siteName", "Graduate-Application Review System ({$config->getValue("networkName")})");
    
    // The path for the Forum
    $config->setValue("path", "");
    
    // The domain for the Forum (used for things like mailing list addresses)
    $config->setValue("domain", "");
    
    // DB Type (ie. mysql)
    $config->setValue("dbType", "mysql");
    
    // DB Server (ie. localhost)
    $config->setValue("dbServer", "localhost:8889");
    
    // DB Name
    $config->setValue("dbName", "forum");
    
    // DB Test Name
    $config->setValue("dbTestName", "");
    
    // DB User
    $config->setValue("dbUser", "root");
    
    // DB Password
    $config->setValue("dbPassword", "root");
    
    // Localization Cache Directory
    $config->setValue("localizationCache", "/Applications/MAMP/cache");
    
    // Default Mailing List Admins
    $config->setValue("listAdmins", array(""));
    
    // Default Mailing List Password
    $config->setValue("listAdminPassword", "");
    
    // Support Email Address
    $config->setValue("supportEmail", "support@ssrg5.cs.ualberta.ca");
    
    // Network Website
    $config->setValue("networkSite", "http://www.ssrg5.cs.ualberta.ca");
    
    // Shibboleth Logout Url
    $config->setValue("shibLogoutUrl", "");
    
    // Shibboleth Login Url
    $config->setValue("shibLoginUrl", "");
    
    // Shibboleth default role
    $config->setValue("shibDefaultRole", "OT");
    
    // Skin
    $config->setValue("skin", "cavendish2");
    
    // Logo path
    $config->setValue("logo", "skins/logos/ualberta_logo.png");
    
    // Icon path (gray)
    $config->setValue("iconPath", "skins/icons/white_mix/");
    
    // Icon path (highlighted)
    $config->setValue("iconPathHighlighted", "skins/icons/ot/");
    
    // Highlight color for skin
    $config->setValue("highlightColor", "#079651");
    $config->setValue("highlightColor1", "#048849");
    $config->setValue("highlightColor2", "#00713B");
    $config->setValue("highlightFontColor", "#FFFFFF");
    $config->setValue("inputColor", "#006434");
    
    // Highlight color for headers
    $config->setValue("headerColor", "#3D4A43");
    
    // Global Message (ie. maintenance message)
    $config->setValue("globalMessage", "");
    
    // The terminology for project themes 
    $config->setValue("projectThemes", "Theme");

    // The terminology to use for "Product"
    $config->setValue("productsTerm", "Output");

    
    // The terminology for administrative projects 
    $config->setValue("adminProjects", "Admin Project");
    
    $config->setValue("nameFormat", "{%First} {%M.} {%Last}");
    
    // Whether or not to enable projects
    $config->setValue("projectsEnabled", false);
    
    // Whether or not to allow bigBetProjects
    $config->setValue("bigBetProjects", false);
    
    // Whether or not to allow projectTypes
    $config->setValue("projectTypes", false);
    
    // Whether or not to allow projectStatus
    $config->setValue("projectStatus", true);
    
    // Which extensions to enable
    $config->setValue("extensions", array(
        'Shibboleth',
        'AccessControl',
        'Cache',
        'Messages',
        'TabUtils',
        'API',
        'GrandObjects',
        'UI',
        'Notification',
        'GrandObjectPage',
        //'Cal',
        //'TempEd',
        //'TextReplace',
        //'Twitter',
        //'MailingList',
        'AddMember',
        //'EditMember',
        //'HQPRegister',
        //'Poll',
        'QueryableTable',
        'IndexTables',
        'Reporting',
        //'NCETable',
        //'EmptyEmailList',
        'GlobalSearch',
        'Impersonation',
        'Visualizations',
        //'PublicVisualizations',
        //'Survey',
        'Duplicates',
        //'Acknowledgements',
        //'ProjectEvolution',
        //'ScreenCapture',
        //'Solr',
        //'TravelForm',
        //'AdvancedSearch',
        'CCVExport',
        //'MyThreads',
	'Sops',
	'PdfConversion',
	'AdminTabs',
	'Courses'
    ));
    
    $config->setValue("reportingExtras", array('EvaluationTable'        => false,
                                               'ReportStats'            => false,
                                               'CreatePDF'              => false,
                                               'ReportArchive'          => false,
                                               'ReviewResults'          => false,
                                               'LoiProposals'           => false,
                                               'AdminVisualizations'    => false));
    
    // What social links to have in the top header
    // should be an associative array with the index as the type of social network, and the value is the url
    // Options: twitter, linkedin, flickr, youtube
    $config->setValue("socialLinks", array());
    
    // The dates that each phase started
    $config->setValue("projectPhaseDates", array(1 => "2015-04-01 00:00:00"));
    
    $config->setValue("relationTypes", array("Supervises"));
    
    /*
     * PDF Config
     */
     
    // The font for generated PDF documents
    //$config->setValue("pdfFont", "Times New Roman");
    
    // The font for generated PDF documents
    //$config->setValue("pdfFontSize", "12");
    
    // The font for generated PDF documents
    $config->setValue("pdfMargins", array('top'     => 1,
                                          'right'   => 1,
                                          'bottom'  => 1,
                                          'left'    => 1));
    
    /*
     * Constants
     */
     
    $config->setConst("DEMO", true);
     
    // The current Project Phase
    $config->setConst("PROJECT_PHASE", 1);
     
    // The current cycle year
    $config->setConst("YEAR", 2017);

    // Start of internal reporting cycle (Used for range queries)
    $config->setConst("CYCLE_START_MONTH", '-00-00');
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
    $config->setValue("committees",
        array());
     
    $config->setConst("INACTIVE",   "User");
    $config->setConst("HQP",        "HQP");
    $config->setConst("STUDENT",    "Student");
    $config->setConst("EXTERNAL",   "External");
    $config->setConst("NI",         "Student");
    $config->setConst("AR",         "AR");
    $config->setConst("CI",         "OT");
    $config->setConst("PL",         "PL");
    $config->setConst("APL",        "APL");
    $config->setConst("TL",         "TL");
    $config->setConst("EVALUATOR",  "Faculty");
    $config->setConst("CHAMP",      "Collaborator");
    $config->setConst("ASD",        "ASD");
    $config->setConst("SD",         "SD");
    $config->setConst("STAFF",      "Staff");
    $config->setConst("MANAGER",    "Reviewer");
    $config->setConst("ADMIN",      "Admin");
    
    $config->setValue("roleDefs", array(
        $config->getConst('INACTIVE')       => "User",
        $config->getConst('CI')             => "Applicant",
        $config->getConst('HQP')             => "HQP",
        $config->getConst('STAFF')          => "Staff",
	$config->getConst('EVALUATOR')	    => "Faculty",
        $config->getConst('MANAGER')        => "Reviewer",
        $config->getConst('ADMIN')          => "Admin"));
        
    $config->setValue("wgRoles", array(
        $config->getConst('CI'),
        $config->getConst('HQP'),
        $config->getConst('STAFF'),
	$config->getConst('EVALUATOR'), 
        $config->getConst('MANAGER'),
        $config->getConst('ADMIN')
    ));

    $config->setValue("wgAllRoles", array(
        $config->getConst('CI'),
        $config->getConst('HQP'),
        $config->getConst('STAFF'),
	$config->getConst('EVALUATOR'), 
        $config->getConst('MANAGER'),
        $config->getConst('ADMIN')
    ));
    
    $config->setValue("subRoles", array());
    
    $config->setValue("roleAliases", array());
    
    /* Other */
    $config->setValue("analyticsCode", "");
    
    //setcookie('sideToggled', 'out', time()+3600);
    //$_COOKIE['sideToggled'] = 'out';
?>
