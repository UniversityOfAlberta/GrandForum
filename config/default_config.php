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
    
    // Scopus Api Id
    $config->setValue("scopusApi", "");
    
    // Url for gscholar-rss
    $config->setValue("gscholar-rss", "");
    
    // API Key for gscholar-rss
    $config->setValue("gscholar-api", "");
    
    // Localization Cache Directory
    $config->setValue("localizationCache", "");
    
    // Default Mailing List Admins
    $config->setValue("listAdmins", array());
    
    // Default Mailing List Password
    $config->setValue("listAdminPassword", "");
    
    // Support Email Address
    $config->setValue("supportEmail", "");
    
    // Network Website
    $config->setValue("networkSite", "");
    
    // Shibboleth Logout URL
    $config->setValue("shibLogoutUrl", "");
    
    // Shibboleth default role
    $config->setValue("shibDefaultRole", "");
    
    // Whether to auto create a user from single sign on
    $config->setValue('shibCreateUser', false);
    
    // Skin
    $config->setValue("skin", "cavendish");
    
    // Logo path
    $config->setValue("logo", "skins/logos/logo.png");
    
    // Icon path (gray)
    $config->setValue("iconPath", "skins/icons/gray_dark/");
    
    // Icon path (highlighted)
    $config->setValue("iconPathHighlighted", "skins/icons/gray_dark/");
    
    // Top Header color for skin
    $config->setValue("topHeaderColor", "#777777");
    
    // Sidebar color for skin
    $config->setValue("sideColor", "#999999");
    
    // Highlight color for skin
    $config->setValue("highlightColor", "#555555");
    
    // Highlight color for skin
    $config->setValue("hyperlinkColor", "#555555");
    
    // Highlight color for fonts
    $config->setValue("highlightFontColor", "#FFFFFF");
    
    // Border color for the #bodyContent div
    $config->setValue("mainBorderColor", "#555555");
    
    // Highlight color for headers
    $config->setValue("headerColor", "#333333");
    
    // Global Message (ie. maintenance message)
    $config->setValue("globalMessage", "");
    
    // The terminology for project themes 
    $config->setValue("projectThemes", "Theme");
    
    // The terminology for administrative projects 
    $config->setValue("adminProjects", "Admin Project");
    
    // The terminology to use for "Product"
    $config->setValue("productsTerm", "Product");
    
    // The Default productVisibility
    $config->setValue("productVisibility", "Forum");
    
    // The terminology to use for "Sub-Role"
    $config->setValue("subRoleTerm", "Sub-Role");
    
    // The terminology to use for "Department"
    $config->setValue("deptsTerm", "Department");

    $config->setValue("nameFormat", "{%First} {%M.} {%Last}");
    
    // Whether to include the middle name in most places
    $config->setValue("includeMiddleName", false);
    
    $config->setValue("hqpRegisterEmailWhitelist", array(".ca"));
    
    // Whether to prevent access to certain pages when a guess
    // This is mostly a facade since it doesn't actually prevent access from the data, it just hides the links to the pages.
    $config->setValue("guestLockdown", false);
    
    // Whether or not to show outputs unrelated to projects or not.
    $config->setValue("showNonNetwork", true);
    
    // Whether to enable French/English options
    $config->setValue("bilingual", false);
    
    // Whether or not the gender field is enabled
    $config->setValue("genderEnabled", true);
    
    // Whether or not the Nationality field is enabled
    $config->setValue("nationalityEnabled", true);
    
    // Whether or not Early Career Research is enabled
    $config->setValue("ecrEnabled", false);
    
    // Whether or not Agencies field is enabled
    $config->setValue("agenciesEnabled", false);
    
    // Whether or not Canada Research Chair is enabled
    $config->setValue("crcEnabled", false);
    
    // Whether or not MITACS is enabled
    $config->setValue("mitacsEnabled", false);
    
    // Whether to have only the public profile field (true) or both public & private (false)
    $config->setValue("publicProfileOnly", false);
    
    // Whether or not Project Technology Evaluation/Adoption is enabled
    $config->setValue("projectTechEnabled", false);
    
    // Whether or not Project Technology Evaluation/Adoption is enabled
    $config->setValue("alumniEnabled", false);
    
    // Whether or not wiki features are enabled
    $config->setValue("wikiEnabled", true);
    
    // Whether or not Manage Products should be enabled
    $config->setValue("productsEnabled", true);
    
    // Whether or not Profiles should be enabled
    $config->setValue("profilesEnabled", true);
    
    // A list of api keys
    $config->setValue("apiKeys", array());
    
    // A whitelist of ip addresses so that certain ips can still get past the login wall
    $config->setValue("ipWhitelist", array());
    
    // Whether or not to show the Upload File in the sidebar
    $config->setValue("showUploadFile", true);
    
    // Whether or not to show the Other Tools in the sidebar
    $config->setValue("showOtherTools", true);
    
    // Whether to allow users to upload photos (if false, only Staff+ will be able to)
    $config->setValue("allowPhotoUpload", true);
    
    // Whether or not to enable projects
    $config->setValue("projectsEnabled", true);

    // Whether or not projects have 'long descriptions'
    $config->setValue("projectLongDescription", true);
    
    // Whether or not to enable contributions
    $config->setValue("contributionsEnabled", true);

    // Whether or not HQPs are public
    $config->setValue("hqpIsPublic", false);
    
    // Whether or not to include HQP Products for supervisors
    $config->setValue("includeHQPProducts", true);
    
    // Whether or not to allow projectTypes
    $config->setValue("projectTypes", false);
    
    // Whether or not to allow projectStatus
    $config->setValue("projectStatus", true);
    
    // Number of Project Top Products
    $config->setValue("nProjectTopProducts", 10);
    
    // Which extensions to enable
    $config->setValue("extensions", array(
        //'Shibboleth',
        'AccessControl',
        'Cache',
        'Messages',
        'TabUtils',
        'API',
        'GrandObjects',
        'UI',
        'Notification',
        'GrandObjectPage',
        //'Twitter',
        'MailingList',
        'AddMember',
        //'Register',
        'Poll',
        'QueryableTable',
        'IndexTables',
        'Reporting',
        //'DiversitySurvey',
        'NCETable',
        'GlobalSearch',
        'Impersonation',
        'Visualizations',
        'PublicVisualizations',
        'Duplicates',
        //'AllocatedBudgets',
        'ProjectEvolution',
        'CCVExport',
        //'CrossForumExport',
        'ReportIssue',
        //'ContactUs',
        'MyThreads',
        'Freeze',
        //'Postings',
        //'CRM',
        //'RSSAlerts',
        //'EventRegistration'
    ));
    
    $config->setValue("reportingExtras", array('CreatePDF'              => false,
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

    $config->setValue("projectPhaseDates", array(1 => "2015-03-31 00:00:00",
                                                 2 => "2015-04-01 00:00:00"));
                                                 
    $config->setValue("projectPhaseNames", array(1 => "Theme",
                                                 2 => "Theme"));
    
    // The types of relations which are enabld (Supervises/Mentors/Works With)    
    $config->setValue("relationTypes", array("Supervises",
                                             "Mentors",
                                             "Works With"));
    
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
     
    // The current Project Phase
    $config->setConst("PROJECT_PHASE", 2);
     
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
    $config->setValue("committees",
        array(
            "RMC" => "Research Management Committee"
        ));
     
    $config->setConst("INACTIVE",   "Inactive");
    $config->setConst("HQP",        "HQP");
    $config->setConst("PS",         "PS");
    $config->setConst("STUDENT",    "Student");
    $config->setConst("EXTERNAL",   "External");
    $config->setConst("AG",         "AdvisoryGroup");
    $config->setConst("NI",         "NI");
    $config->setConst("AR",         "AR");
    $config->setConst("CI",         "CI");
    $config->setConst("PA",         "PA");
    $config->setConst("PL",         "PL");
    $config->setConst("APL",        "APL");
    $config->setConst("TL",         "TL");
    $config->setConst("TC",         "TC");
    $config->setConst("COMMITTEE",  "Committee");
    $config->setConst("EVALUATOR",  "Evaluator");
    $config->setConst("CHAMP",      "Champion");
    $config->setConst("PARTNER",    "Partner");
    $config->setConst("ASD",        "ASD");
    $config->setConst("SD",         "SD");
    $config->setConst("STAFF",      "Staff");
    $config->setConst("MANAGER",    "Manager");
    $config->setConst("EDI",        "EDI");
    $config->setConst("ADMIN",      "Admin");
    
    $config->setValue("roleDefs", array(
        $config->getConst('INACTIVE')       => "Inactive",
        $config->getConst('HQP')            => "Highly Qualified Person",
        $config->getConst('PS')             => "Project Support",
        $config->getConst('EXTERNAL')       => "External",
        $config->getConst('AG')             => "Advisory Group",
        $config->getConst('NI')             => "Network Investigator",
        $config->getConst('AR')             => "Affiliated Researcher",
        $config->getConst('CI')             => "Co-Investigator",
        $config->getConst('CHAMP')          => "Champion",
        $config->getConst('PARTNER')        => "Partner",
        $config->getConst('PA')             => "Project Assistant",
        $config->getConst('PL')             => "Project Leader",
        $config->getConst('APL')            => "Admin Project Leader",
        $config->getConst('TL')             => "Theme Leader",
        $config->getConst('TC')             => "Work Package Coordinator",
        $config->getConst('EVALUATOR')      => "Evaluator",
        $config->getConst('ASD')            => "Associate Scientific Director",
        $config->getConst('SD')             => "Scientific Director",
        $config->getConst('STAFF')          => "Staff",
        $config->getConst('MANAGER')        => "Manager",
        $config->getConst('EDI')            => "EDI Office",
        $config->getConst('ADMIN')          => "Admin"));
        
    $config->setValue("subRoles", array());
    
    $config->setValue("roleAliases", array());
    
    $config->setValue("stakeholderCategories", array());
        
    $config->setValue("boardMods", array($config->getConst('STAFF'), 
                                         $config->getConst('MANAGER'),
                                         $config->getConst('ADMIN')));
    /* Other */
    $config->setValue("analyticsCode", "");
    
    $config->setValue("googleAPI", "");
?>
