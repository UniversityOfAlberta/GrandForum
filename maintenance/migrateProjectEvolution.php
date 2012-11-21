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

$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'grand_project' and column_name = 'deleted' and table_schema = '$wgDBname'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `grand_project`
                            DROP `deleted`", true);
}

$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'grand_project' and column_name = 'project_end_date' and table_schema = '$wgDBname'");

if(count($data) > 0){
    DBFunctions::execSQL("ALTER TABLE `grand_project`
                            DROP `project_end_date`", true);
}

DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_project_status`", true);
DBFunctions::execSQL("DROP TABLE IF EXISTS `grand_project_evolution`", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_project_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evolution_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `evolution_id` (`evolution_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1", true);
DBFunctions::execSQL("CREATE TABLE IF NOT EXISTS `grand_project_evolution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `new_id` int(11) NOT NULL,
  `action` enum('CREATE','MERGE','DELETE','EVOLVE') NOT NULL,
  `effective_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `last_id` (`last_id`),
  KEY `project_id` (`project_id`),
  KEY `new_id` (`new_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1", true);

$data = DBFunctions::execSQL("select * from information_schema.columns where table_name = 'grand_project_descriptions' and column_name = 'evolution_id' and table_schema = '$wgDBname'");

if(count($data) == 0){
    DBFunctions::execSQL("ALTER TABLE `grand_project_descriptions` ADD `evolution_id` INT NOT NULL AFTER `project_id`, ADD INDEX ( `evolution_id` ) ", true);
}

DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` CHANGE `id` `id` INT( 11 ) NOT NULL", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` DROP PRIMARY KEY", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` ADD PRIMARY KEY ( `id` )", true);
DBFunctions::execSQL("ALTER TABLE `grand_project_evolution` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT", true);

for($i = 138; $i <= 200; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`) 
                      VALUES ('-1','-1', '{$i}', 'CREATE', '2010-08-01 00:00:00', '2010-08-01 00:00:00')", true);
    $data = DBFunctions::execSQL("UPDATE `grand_project_descriptions` d, `grand_project_evolution` e
                                  SET d.evolution_id = e.id
                                  WHERE d.project_id = '{$i}'
                                  AND e.new_id = '{$i}'
                                  AND e.action = 'CREATE'", true);
    if($i == 172 || $i == 176){
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Active', 'Administrative')", true);
    }
    else{
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Active', 'Research')", true);
    }
}
for($i = 202; $i <= 204; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1','-1', '{$i}', 'CREATE', '2011-08-01 00:00:00', '2011-08-01 00:00:00')", true);
    $data = DBFunctions::execSQL("UPDATE `grand_project_descriptions` d, `grand_project_evolution` e
                                  SET d.evolution_id = e.id
                                  WHERE d.project_id = '{$i}'
                                  AND e.new_id = '{$i}'
                                  AND e.action = 'CREATE'", true);
    DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Active', 'Research')", true);
}
for($i = 248; $i <= 260; $i+=2){
    DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1','-1', '{$i}', 'CREATE', '2011-12-01 00:00:00', '2011-12-01 00:00:00')", true);
    $data = DBFunctions::execSQL("UPDATE `grand_project_descriptions` d, `grand_project_evolution` e
                                  SET d.evolution_id = e.id
                                  WHERE d.project_id = '{$i}'
                                  AND e.new_id = '{$i}'
                                  AND e.action = 'CREATE'", true);
    DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Proposed', 'Research')", true);
    $p = Project::newFromId($i);
    if($i == 248 || $i == 252 || $i == 254){
        DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$p->evolutionId}','{$i}', '{$i}', 'EVOLVE', '2012-08-01 00:00:00', '2012-08-01 00:00:00')", true);
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Active', 'Research')", true);
    }
    else if($i == 250 || $i == 258){
        DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$p->evolutionId}','{$i}', '{$i}', 'DELETE', '2012-08-01 00:00:00', '2012-08-01 00:00:00')", true);
        DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '{$i}', 'Ended', 'Research')", true);
    }
}
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('-1','-1', '268', 'CREATE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);
$data = DBFunctions::execSQL("UPDATE `grand_project_descriptions` d, `grand_project_evolution` e
                                  SET d.evolution_id = e.id
                                  WHERE d.project_id = '268'
                                  AND e.new_id = '268'
                                  AND e.action = 'CREATE'", true);
DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '268', 'Active', 'Research')", true);
$p256 = Project::newFromId(256);
$p260 = Project::newFromId(260);
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$p256->evolutionId}','256', '268', 'MERGE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                      VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '268', 'Active', 'Research')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_evolution` (`last_id`,`project_id`, `new_id`, `action`, `effective_date`, `date`)
                      VALUES ('{$p260->evolutionId}','260', '268', 'MERGE', '2012-08-16 09:19:31', '2012-08-16 09:19:31')", true);
DBFunctions::execSQL("INSERT INTO `grand_project_status` (`evolution_id`,`project_id`, `status`, `type`)
                          VALUES ((SELECT COUNT(*) FROM grand_project_evolution), '268', 'Active', 'Research')", true);
?>
