<?php
    /*
     * Variables
     */
    // The name of the Network
    $config->setValue("networkName", "Faculty of Science");
    
    // The name of the Site
    $config->setValue("siteName", "{$config->getValue("networkName")} Forum (ruby_test)");
    
    // The path for the Forum
    $config->setValue("path", "/fos_test");
    
    // The domain for the Forum (used for things like mailing list addresses)
    $config->setValue("domain", "grand.cs.ualberta.ca");
    
    // DB Type (ie. mysql)
    $config->setValue("dbType", "mysql");
    
    // DB Server (ie. localhost)
    $config->setValue("dbServer", "localhost");
    
    // DB Name
    $config->setValue("dbName", "ualberta_fec_new");
    
    // DB Test Name
    $config->setValue("dbTestName", "ualberta_behat");
    
    // DB User
    $config->setValue("dbUser", "dwt");
    
    // DB Password
    $config->setValue("dbPassword", "ZjiTYF7nW5yqxn1tw9UhC73K");
    
    // Localization Cache Directory
    $config->setValue("localizationCache", "/local/data/www-root/cache/iqst");
    
    // Default Mailing List Admins
    $config->setValue("listAdmins", array("dwt@ualberta.ca"
    ));
    
    // Default Mailing List Password
    $config->setValue("listAdminPassword", "BigLasagna");
    
    // Support Email Address
    $config->setValue("supportEmail", "dwt@ualberta.ca");
    
    // Network Website
    $config->setValue("networkSite", "https://www.cs.ualberta.ca/");
    
    // Logo path
    $config->setValue("logo", "skins/logos/ualberta_logo.png");
    
    // Icon path (gray)
    $config->setValue("iconPath", "skins/icons/gray_dark/");
    
    // Icon path (highlighted)
    $config->setValue("iconPathHighlighted", "skins/icons/ualberta/");
    
    // Highlight color for skin
    $config->setValue("highlightColor", "#007d43");
    
    // Highlight color for headers
    $config->setValue("headerColor", "#333333");
    
    // Global Message (ie. maintenance message)
    $config->setValue("globalMessage", "");
    
    // The terminology for project themes 
    $config->setValue("projectThemes", "Theme");
    
    // The terminology for administrative projects 
    $config->setValue("adminProjects", "Crosscutting Activities");
    
    // The terminology to use for "Products"
    $config->setValue("productsTerm", "Output");
    
    // Whether or not to enable projects
    $config->setValue("projectsEnabled", false);
    
    // Whether or not forum has one university
    $config->setValue("singleUniversity", true);

    // Whether or not to allow bigBetProjects
    $config->setValue("bigBetProjects", false);
    
    // Whether or not to allow projectTypes
    $config->setValue("projectTypes", false);
    
    // Whether or not to allow projectStatus
    $config->setValue("projectStatus", true);
    
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
        'Cal',
        'TempEd',
        //'TextReplace',
        //'Twitter',
       // 'MailingList',
        //'FeatureRequest',
        'AddMember',
        'AddHqp',
        //'EditMember',
        //'HQPRegister',
        //'Poll',
        'QueryableTable',
        'IndexTables',
        'NCETable',
        'Reporting',
        //'EmptyEmailList',
        'GlobalSearch',
        'Impersonation',
        'Visualizations',
        //'PublicVisualizations',
        //'Survey',
        'Duplicates',
        //'Acknowledgements',
        //'AllocatedBudgets',
        'ProjectEvolution',
        //'ScreenCapture',
        //'Solr',
        //'TravelForm',
        //'AdvancedSearch',
        'CCVExport'
    ));
    
    $config->setValue("reportingExtras", array('EvaluationTable'        => false,
                                               'ReportStats'            => false,
                                               'CreatePDF'              => false,
                                               'ReportArchive'          => false,
                                               'ReviewerConflicts'      => false,
                                               'ReviewResults'          => false,
                                               'SanityChecks'           => false,
                                               'AdminVisualizations'    => false));
    
    // What social links to have in the top header
    // should be an associative array with the index as the type of social network, and the value is the url
    // Options: twitter, linkedin, flickr, youtube
    $config->setValue("socialLinks", array());
    
    // The dates that each phase started
    $config->setValue("projectPhaseDates", array(1 => "2015-04-01 00:00:00"));
    
    /*
     * PDF Config
     */
    
    // The font for generated PDF documents
    $config->setValue("pdfMargins", array('top'     => 2,
                                          'right'   => 2,
                                          'bottom'  => 2,
                                          'left'    => 2));
    
    /*
     * Constants
     */
     
    // The current Project Phase
    $config->setConst("PROJECT_PHASE", 1);
     
    // The current cycle year
    $config->setConst("YEAR", 2016);
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
    $config->setConst("INACTIVE",   "Inactive");
    $config->setConst("HQP",        "HQP");
    $config->setConst("STUDENT",    "Student");
    $config->setConst("EXTERNAL",   "External");
    $config->setConst("ISAC",       "Chair");
    $config->setConst("IAC",        "IAC");
    $config->setConst("CAC",        "CAC");
    $config->setConst("NCE",        "NCE Rep");
    $config->setConst("NI",         "FacultyNI");
    $config->setConst("AR",         "Faculty");
    $config->setConst("CI",         "Faculty");
    $config->setConst("PL",         "PL");
    $config->setConst("TL",         "WPL");
    $config->setConst("TC",         "WPC");
    $config->setConst("RMC",        "FEC");
    $config->setConst("HQPAC",      "HQPAC");
    $config->setConst("EVALUATOR",  "Evaluator");
    $config->setConst("CF",         "CF");
    $config->setConst("BOD",        "BOD");
    $config->setConst("CHAMP",      "Collaborator");
    $config->setConst("SD",         "SD");
    $config->setConst("STAFF",      "Staff");
    $config->setConst("MANAGER",    "Manager");
    $config->setConst("ADMIN",      "Admin");
    
    $config->setValue("roleDefs", array(
        $config->getConst('INACTIVE')       => "Inactive",
        $config->getConst('HQP')            => "Highly Qualified Personnel",
        $config->getConst('EXTERNAL')       => "External",
        $config->getConst('ISAC')           => "Chair",
        $config->getConst('IAC')            => "Industry Advisory Committee",
        $config->getConst('CAC')            => "Consumer Advisory Committee",
        $config->getConst('NCE')            => "NCE Rep",
        $config->getConst('NI')             => "Faculty",
        $config->getConst('AR')             => "Faculty",
        $config->getConst('CI')             => "Faculty",
        $config->getConst('CHAMP')          => "Collaborator",
        $config->getConst('PL')             => "Project Leader",
        $config->getConst('TL')             => "Workpackage Leader",
        $config->getConst('TC')             => "Workpackage Coordinator",
        $config->getConst('RMC')            => "Faculty Evaluation Committee",
        $config->getConst('HQPAC')          => "HQP Advisory Committee",
        $config->getConst('EVALUATOR')      => "Evaluator",
        $config->getConst("CF")             => "Core Facility",
        $config->getConst('BOD')            => "Board of Directors",
        $config->getConst('SD')             => "Scientific Director",
        $config->getConst('STAFF')          => "Staff",
        $config->getConst('MANAGER')        => "Manager",
        $config->getConst('ADMIN')          => "Admin"));
    
    $config->setValue("wgRoles", array(
        $config->getConst('HQP'), 
        //$config->getConst('EXTERNAL'), 
        //$config->getConst('AR'),
        $config->getConst('CI'),
        //$config->getConst('CHAMP'),
        $config->getConst('ISAC'),
        //$config->getConst('IAC'),
        //$config->getConst('CAC'),
        //$config->getConst('NCE'), 
        $config->getConst('RMC'),
        //$config->getConst('HQPAC'),
        //$config->getConst('CF'),
        //$config->getConst('BOD'),
        //$config->getConst('SD'), 
        $config->getConst('STAFF'),
        $config->getConst('MANAGER'),
        $config->getConst('ADMIN')
    ));
    $config->setValue("wgAllRoles", array(
       // $config->getConst('HQP'), 
       // $config->getConst('STUDENT'),
       // $config->getConst('EXTERNAL'), 
        $config->getConst('ISAC'),
       // $config->getConst('IAC'),
       // $config->getConst('CAC'),
       // $config->getConst('NCE'), 
        $config->getConst('CI'), 
       // $config->getConst('AR'), 
       // $config->getConst('CI'),
       // $config->getConst('PL'),
       // $config->getConst('TL'),
        $config->getConst('RMC'),
        //$config->getConst('HQPAC'),
        //$config->getConst('EVALUATOR'),
        //$config->getConst('CF'),
       // $config->getConst('BOD'),
       // $config->getConst('CHAMP'),
       // $config->getConst('SD'), 
       // $config->getConst('STAFF'), 
       // $config->getConst('MANAGER'),
       // $config->getConst('ADMIN')
    ));
    
    /* Other */
    $config->setValue("analyticsCode", "");
?>
