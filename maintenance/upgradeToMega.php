<?php
    require_once('commandLine.inc');
    $out = "";
    echo "Creating New Tables and general cleanup...\n";
    execSQLStatement("DROP TABLE IF EXISTS `grand_hqp_months`", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_contributions` (
      `id` int(11) NOT NULL,
      `rev_id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(256) NOT NULL,
      `users` text NOT NULL,
      `projects` text NOT NULL,
      `partner_id` text NOT NULL,
      `type` varchar(256) NOT NULL,
      `cash` int(11) NOT NULL,
      `kind` int(11) NOT NULL,
      `description` text NOT NULL,
      `year` int(11) NOT NULL,
      `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`rev_id`),
      KEY `id` (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `milestone_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('New','Revised','Continuing','Closed','Abandoned') NOT NULL,
  `description` text NOT NULL,
  `assessment` text NOT NULL,
  `comment` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `projected_end_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `milestone_id` (`milestone_id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_project` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `fullName` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_hqp_months` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `months` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8", true);
   execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `project` varchar(32) NOT NULL,
  `project_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`project`),
  KEY `project` (`project`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
   execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_project_descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_project_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `themes` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_publications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `projects` varchar(256) NOT NULL,
  `type` varchar(32) NOT NULL,
  `title` varchar(256) NOT NULL,
  `date` date NOT NULL,
  `venue` varchar(256) NOT NULL,
  `status` varchar(256) NOT NULL,
  `authors` text NOT NULL,
  `data` text NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `type` (`type`),
  KEY `category` (`category`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1` int(11) NOT NULL COMMENT 'as in user1 relates to user2',
  `user2` int(11) NOT NULL COMMENT 'as in user1 relates to user2',
  `type` enum('Works With','Supervises') NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user1`,`user2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `role` varchar(32) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`role`),
  KEY `role` (`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_role_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requesting_user` varchar(255) NOT NULL,
  `role` varchar(1024) NOT NULL,
  `comment` text NOT NULL,
  `user` varchar(255) NOT NULL,
  `type` varchar(16) NOT NULL,
  `created` varchar(12) NOT NULL DEFAULT 'false',
  `ignore` varchar(12) NOT NULL DEFAULT 'false',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_theses` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `user_id` INT NOT NULL ,
    `publication_id` INT NOT NULL ,
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX ( `user_id` , `publication_id` )
    ) ENGINE = MYISAM", true);
    execSQLStatement("CREATE TABLE IF NOT EXISTS `grand_movedOn` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `user_id` INT NOT NULL ,
    `where` TEXT NOT NULL ,
    `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX (`user_id`)
    ) ENGINE = MYISAM", true);
    execSQLStateent("CREATE TABLE IF NOT EXISTS `grand_publication_notifications` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `user_id` int(11) NOT NULL,
      `who_changed` int(11) NOT NULL,
      `last_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `publication_id` int(11) NOT NULL,
      `message` VARCHAR(256) NOT NULL, 
      `active` tinyint(1) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `user_id` (`user_id`,`publication_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'PNI' WHERE `mw_an_extranamespaces`.`nsName` = 'NI'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'PNI'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'CNI' WHERE `mw_an_extranamespaces`.`nsName` = 'CR'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'CNI'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'HQP' WHERE `mw_an_extranamespaces`.`nsName` = 'Student'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'HQP'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'PNI_Talk' WHERE `mw_an_extranamespaces`.`nsName` = 'NI_Talk'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'PNI_Talk'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'CNI_Talk' WHERE `mw_an_extranamespaces`.`nsName` = 'CR_Talk'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'CNI_Talk'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `nsName` = 'HQP_Talk' WHERE `mw_an_extranamespaces`.`nsName` = 'Student_Talk'", true);
    execSQLStatement("UPDATE `mw_an_extranamespaces` SET `public` = '1' WHERE `mw_an_extranamespaces`.`nsName` = 'HQP_Talk'", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('222','Staff','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('223','Staff_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('224','RMC','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('225','RMC_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('226','Manager','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('227','Manager_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('228','BOD','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('229','BOD_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('230','Champion','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('231','Champion_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('232','Publication','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('233','Publication_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('234','Artifact','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('235','Artifact_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('236','Activity','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('237','Activity_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('238','Press','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('239','Press_Talk','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('240','Contribution','1')", true);
    execSQLStatement("INSERT INTO `mw_an_extranamespaces` (`nsId`,`nsName`,`public`) VALUES ('241','Contribution_Talk','1')", true);
    execSQLStatement("INSERT INTO `grand_roles` (`user`,`role`,`start_date`) VALUES ('4','Staff',CURRENT_TIMESTAMP)", true);
    execSQLStatement("INSERT INTO `mw_user_groups` (`ug_user`,`ug_group`) VALUES ('4','Staff')", true);
    execSQLStatement("INSERT INTO `grand_roles` (`user`,`role`,`start_date`) VALUES ('150','Manager',CURRENT_TIMESTAMP)", true);
    execSQLStatement("INSERT INTO `mw_user_groups` (`ug_user`,`ug_group`) VALUES ('150','Manager')");
    execSQLStatement("INSERT INTO `grand_roles` (`user`,`role`,`start_date`) VALUES ('157','Manager',CURRENT_TIMESTAMP)", true);
    execSQLStatement("INSERT INTO `mw_user_groups` (`ug_user`,`ug_group`) VALUES ('157','Manager')");
    execSQLStatement("INSERT INTO `grand_roles` (`user`,`role`,`start_date`) VALUES ('158','Manager',CURRENT_TIMESTAMP)", true);
    execSQLStatement("INSERT INTO `mw_user_groups` (`ug_user`,`ug_group`) VALUES ('158','Manager')");
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_group = ''", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name LIKE '% %'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName LIKE '% %'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '440'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name LIKE '%Tpze%'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName LIKE '%Tpze%'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '232'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name = 'Bardia'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName = 'Bardia'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '230'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name = 'Beth'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName = 'Beth'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '233'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name = 'Andrea'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName = 'Andrea'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '231'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name = 'Dinara'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName = 'Dinara'", true);
    execSQLStatement("DELETE FROM `mw_user_groups` WHERE ug_user = '586'", true);
    execSQLStatement("DELETE FROM `mw_user` WHERE user_name = 'Scott.Newsom'", true);
    execSQLStatement("DELETE FROM `mw_user_create_request` WHERE wpName = 'Scott.Newsom'", true);
    execSQLStatement("ALTER TABLE `grand_role_request` ADD `other` TEXT NOT NULL AFTER `comment`", true);
    execSQLStatement("ALTER TABLE `mw_user`  ADD `user_public_profile` TEXT NOT NULL AFTER `user_twitter`,  ADD `user_private_profile` TEXT NOT NULL AFTER `user_public_profile`", true);
    execSQLStatement("ALTER TABLE `mw_user` ADD `user_gender` VARCHAR( 32 ) NOT NULL AFTER `user_private_profile`", true);
    execSQLStatement("ALTER TABLE `mw_user` ADD `user_nationality` VARCHAR( 64 ) NOT NULL AFTER `user_gender`", true);
    execSQLStatement("ALTER TABLE `mw_user_create_request` ADD `last_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`", true);
    execSQLStatement("ALTER TABLE `mw_user_create_request` ADD `staff` VARCHAR( 256 ) NOT NULL AFTER `last_modified`", true);
    execSQLStatement("ALTER TABLE `grand_role_request` ADD `last_modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`", true);
    execSQLStatement("ALTER TABLE `grand_role_request` ADD `staff` VARCHAR( 256 ) NOT NULL AFTER `last_modified`", true);
    execSQLStatement("INSERT INTO `mw_user_aliases` (`user_id`,`alias`) VALUES ('54','E. G. Toms')");
    echo "Done!\n\n";
    /*
    echo "Creating SociQL Tables...\n";
    execSQLStatement("DROP TABLE IF EXISTS `sociql_actor`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_ontology_ent`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_ontology_prop`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_ontology_rel`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_property`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_relation`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_same`", true);
    execSQLStatement("DROP TABLE IF EXISTS `sociql_site`", true);
    execSQLStatement("CREATE TABLE `sociql_actor` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`site_fk` int( 11 ) NOT NULL DEFAULT '0',
`name` varchar( 20 ) NOT NULL DEFAULT '',
`real_name` varchar( 30 ) DEFAULT NULL ,
`query` text NOT NULL ,
`actor_id` varchar( 20 ) NOT NULL DEFAULT '',
`url` varchar( 255 ) DEFAULT NULL ,
`url_required_prop` varchar( 20 ) DEFAULT NULL ,
`map_x_prop` int( 11 ) DEFAULT NULL ,
`map_y_prop` int( 11 ) DEFAULT NULL ,
`ont_entity` int( 11 ) DEFAULT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_ontology_ent` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`name` varchar( 20 ) NOT NULL ,
`level` int( 11 ) NOT NULL DEFAULT '1',
`upper_entity` int( 11 ) DEFAULT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_ontology_prop` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`name` varchar( 20 ) DEFAULT NULL ,
`entity_fk` int( 11 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_ontology_rel` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`from_entity` int( 11 ) NOT NULL ,
`to_entity` int( 11 ) NOT NULL ,
`type` varchar( 1 ) NOT NULL DEFAULT 'A',
`name` varchar( 20 ) DEFAULT NULL ,
`upper_level` int( 11 ) DEFAULT NULL ,
`level` int( 11 ) DEFAULT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_property` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`actor_fk` int( 11 ) NOT NULL DEFAULT '0',
`relation_fk` int( 11 ) DEFAULT '0',
`name` varchar( 20 ) NOT NULL DEFAULT '',
`real_name` varchar( 20 ) NOT NULL DEFAULT '',
`query` text NOT NULL ,
`queriable` smallint( 6 ) NOT NULL DEFAULT '1',
`optimizable` tinyint( 1 ) NOT NULL DEFAULT '1',
`table_name` varchar( 50 ) NOT NULL ,
`type` varchar( 11 ) NOT NULL DEFAULT 'nominal',
`sortable` smallint( 6 ) NOT NULL DEFAULT '1',
`significant` smallint( 6 ) NOT NULL DEFAULT '0',
`sparql` varchar( 255 ) DEFAULT NULL ,
`fb_disj_query` text,
`ont_property` int( 11 ) DEFAULT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_relation` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`name` varchar( 20 ) NOT NULL DEFAULT '',
`property1_fk` int( 11 ) NOT NULL DEFAULT '0',
`real_name1` varchar( 20 ) NOT NULL DEFAULT '',
`property2_fk` int( 11 ) NOT NULL DEFAULT '0',
`real_name2` varchar( 20 ) NOT NULL DEFAULT '',
`query` text NOT NULL ,
`direction` smallint( 6 ) NOT NULL DEFAULT '2',
`fb_disj_query` text,
`cardinality` varchar( 3 ) NOT NULL DEFAULT 'N-N',
`ont_relation` int( 11 ) DEFAULT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_same` (
`reason_id` int( 11 ) NOT NULL DEFAULT '0',
`facebook_id` varchar( 20 ) DEFAULT NULL ,
`dbpedia_id` varchar( 255 ) DEFAULT NULL
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);
    execSQLStatement("CREATE TABLE `sociql_site` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`name` varchar( 20 ) NOT NULL DEFAULT '',
`endpoint` varchar( 255 ) DEFAULT NULL ,
`max_store` int( 11 ) NOT NULL DEFAULT '-1',
`type` varchar( 20 ) NOT NULL DEFAULT 'sql',
`username` varchar( 50 ) DEFAULT NULL ,
`password` varchar( 50 ) DEFAULT NULL ,
`prefixes` text,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8", true);

    execSQLStatement("INSERT INTO `sociql_actor`
SELECT *
FROM `grand_forum_test`.`sociql_actor`", true);
    execSQLStatement("INSERT INTO `sociql_ontology_ent`
SELECT *
FROM `grand_forum_test`.`sociql_ontology_ent`", true);
    execSQLStatement("INSERT INTO `sociql_ontology_prop`
SELECT *
FROM `grand_forum_test`.`sociql_ontology_prop`", true);
    execSQLStatement("INSERT INTO `sociql_ontology_rel`
SELECT *
FROM `grand_forum_test`.`sociql_ontology_rel`", true);
    execSQLStatement("INSERT INTO `sociql_property`
SELECT *
FROM `grand_forum_test`.`sociql_property`", true);
    execSQLStatement("INSERT INTO `sociql_relation`
SELECT *
FROM `grand_forum_test`.`sociql_relation`", true);
    execSQLStatement("INSERT INTO `sociql_same`
SELECT *
FROM `grand_forum_test`.`sociql_same`", true);
    execSQLStatement("INSERT INTO `sociql_site`
SELECT *
FROM `grand_forum_test`.`sociql_site`", true);

    execSQLStatement("UPDATE `sociql_actor` SET `url` = 'http://forum.grand-nce.ca/index.php/<type>:<username>' WHERE `sociql_actor`.`id` =1", true);
    execSQLStatement("UPDATE `sociql_actor` SET `url` = 'http://forum.grand-nce.ca/index.php/<type>:<username>' WHERE `sociql_actor`.`id` =6;", true);
    echo "Done!\n\n";
    */
    echo "Creating grand_partners...\n";
    exec("php upgradePartners.php");
    echo "Done!\n\n";
    echo "Replacing NI with PNI...";
    exec("php replaceText.php \"NI:\" \"PNI:\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_user_groups SET ug_group='PNI' WHERE ug_group='NI'", true);
    echo ".";
    exec("php replaceText.php \"{{PNI\" \"{{NI\"", $out);
    echo ".";
    exec("php replaceText.php \"GRAND:ALL_NI\" \"GRAND:ALL_PNI\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_templatelinks SET tl_title='PNI' WHERE tl_title='NI'", true);
    echo ".";
    execSQLStatement("UPDATE mw_user_create_request SET wpUserType='PNI' WHERE wpUserType='NI'", true);
    echo "\nDone!\n\n";
    echo "Replacing CR with CNI...";
    exec("php replaceText.php \"CR:\" \"CNI:\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_user_groups SET ug_group='CNI' WHERE ug_group='CR'", true);
    echo ".";
    exec("php replaceText.php \"{{CR\" \"{{CNI\"", $out);
    echo ".";
    exec("php replaceText.php \"GRAND:ALL_CR\" \"GRAND:ALL_CNI\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_templatelinks SET tl_title='CNI' WHERE tl_title='CR'", true);
    echo ".";
    execSQLStatement("UPDATE mw_user_create_request SET wpUserType='CNI' WHERE wpUserType='CR'", true);
    echo "\nDone!\n\n";
    echo "Replacing Student with HQP...";
    exec("php replaceText.php \"Student:\" \"HQP:\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_user_groups SET ug_group='HQP' WHERE ug_group='Student'", true);
    echo ".";
    exec("php replaceText.php \"{{Student\" \"{{HQP\"", $out);
    echo ".";
    exec("php replaceText.php \"GRAND:ALL_Student\" \"GRAND:ALL_HQP\"", $out);
    echo ".";
    execSQLStatement("UPDATE mw_templatelinks SET tl_title='HQP' WHERE tl_title='Student'", true);
    echo ".";
    execSQLStatement("UPDATE mw_user_create_request SET wpUserType='HQP' WHERE wpUserType='Student'", true);
    echo "\nDone!\n\n";
    
    echo "Upgrading Projects...\n";
    exec("php upgradeProjects.php", $out);
    echo "Done!\n\n";
    echo "Upgrading Projects Descriptions...\n";
    exec("php updateProjectDescriptions.php", $out);
    echo "Done!\n\n";
    echo "Upgrading User Groups...\n";
    exec("php upgradeUserGroups.php", $out);
    echo "Done!\n\n";
    echo "Upgrading Biographies...\n";
    exec("php upgradeBios.php", $out);
    echo "Done!\n\n";
    echo "Upgrading HQP Universities...\n";
    exec("php updateHQPUniversities.php", $out);
    echo "Done!\n\n";
    echo "Upgrading Publications...\n";
    exec("php upgradePublications.php", $out);
    echo "Done!\n\n";
    echo "Upgrading HQP Status...\n";
    exec("php upgradeHQPStatus.php", $out);
    echo "Done!\n\n";
    echo "Upgrading Milestones...\n";
    exec("php upgradeMilestones.php", $out);
    echo "Done!\n\n";
    echo "Upgrading Contributions...\n";
    exec("php upgradeContributions.php", $out);
    echo "Done!\n\n";
    echo "Moving Pages...\n";
    $pnis = Title::newFromID(206);
    $cnis = Title::newFromID(219);
    $hqps = Title::newFromID(2606);
    $projects = Title::newFromID(33);
    $themes = Title::newFromID(443);
    
    $pnis->moveTo(Title::makeTitle(122, "ALL_PNI"), '',false);
    $cnis->moveTo(Title::makeTitle(122, "ALL_CNI"), '',false);
    $hqps->moveTo(Title::makeTitle(122, "ALL_HQP"), '',false);
    
    $pnis = Title::newFromID(206);
    $cnis = Title::newFromID(219);
    $hqps = Title::newFromID(2606);
    $projects = Title::newFromID(33);
    $themes = Title::newFromID(443);
    
    $pniArticle = Article::newFromID($pnis->getArticleID());
    $cniArticle = Article::newFromID($cnis->getArticleID());
    $hqpArticle = Article::newFromID($hqps->getArticleID());
    $projectArticle = Article::newFromID($projects->getArticleID());
    $themeArticle = Article::newFromID($themes->getArticleID());
    
    $pniArticle->doEdit("", "");
    $cniArticle->doEdit("", "");
    $hqpArticle->doEdit("", "");
    $projectArticle->doEdit("", "");
    $themeArticle->doEdit("", "");
    
    echo "Done!\n\n";
    echo "Updating Search Index...\n";
    exec("php updateSearchIndex.php", $out);
    echo "Done!\n\n";
    
    echo "Upgrade Complete!\n";
    
    function execSQLStatement($sql, $update=false) {
		if($update == false){
			$dbr = wfGetDB(DB_SLAVE);
		}
		else {
			$dbr = wfGetDB(DB_MASTER);
			return $dbr->query($sql);
		}
		$result = $dbr->query($sql);
		$rows = null;
		if($update == false){
			$rows = array();
			while ($row = $dbr->fetchRow($result)) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

?>
