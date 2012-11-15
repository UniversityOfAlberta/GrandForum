<?php
require_once('commandLine.inc');

global $wgDBname;
DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_projects_themes`", true);

$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'mw_an_extranamespaces' and column_name = 'themes' and table_schema = '$wgDBname'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `mw_an_extranamespaces`
                            DROP `themes`", true);
}

$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'mw_an_extranamespaces' and column_name = 'fullName' and table_schema = '$wgDBname'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `mw_an_extranamespaces`
                            DROP `fullName`", true);
}

DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_project_status`", true);
DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_project_evolution`", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_project_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_project_evolution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `new_id` int(11) NOT NULL,
  `action` enum('CREATE','MERGE','DELETE','EVOLVE') NOT NULL,
  `effective_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1", true);


DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` CHANGE `id` `id` INT( 11 ) NOT NULL", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` DROP PRIMARY KEY", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` ADD PRIMARY KEY ( `id` )", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT", true);

for($i = 138; $i <= 200; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`) 
                      VALUES ('-1', '{$i}', 'CREATE', '2010-08-01 00:00:00', '2010-08-01 00:00:00')", true);
    if($i == 172 || $i == 176){
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Active', 'Administrative')", true);
    }
    else{
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Active', 'Research')", true);
    }
}
for($i = 202; $i <= 204; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1', '{$i}', 'CREATE', '2011-08-01 00:00:00', '2011-08-01 00:00:00')", true);
    DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Active', 'Research')", true);
}
for($i = 248; $i <= 260; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1', '{$i}', 'CREATE', '2011-12-01 00:00:00', '2011-12-01 00:00:00')", true);
    DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Proposed', 'Research')", true);
    if($i == 248 || $i == 252 || $i == 254){
        DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$i}', '{$i}', 'EVOLVE', '2012-08-01 00:00:00', '2012-08-01 00:00:00')", true);
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Active', 'Research')", true);
    }
    else if($i == 250 || $i == 258){
        DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$i}', '{$i}', 'DELETE', '2012-08-01 00:00:00', '2012-08-01 00:00:00')", true);
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('{$i}', 'Completed', 'Research')", true);
    }
}
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1', '268', 'CREATE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_status` (`project_id`, `status`, `type`)
                          VALUES ('268', 'Active', 'Research')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('256', '268', 'MERGE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('260', '268', 'MERGE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);

?>
