-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 01, 2014 at 02:19 PM
-- Server version: 5.5.10
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `agewell_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `grand_acknowledgements`
--

CREATE TABLE IF NOT EXISTS `grand_acknowledgements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(256) NOT NULL,
  `university` varchar(256) NOT NULL,
  `date` varchar(256) NOT NULL,
  `supervisor` varchar(256) NOT NULL,
  `md5` varchar(256) NOT NULL,
  `pdf` longblob NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `md5` (`md5`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_champion_partners`
--

CREATE TABLE IF NOT EXISTS `grand_champion_partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `partner` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL,
  `department` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions`
--

CREATE TABLE IF NOT EXISTS `grand_contributions` (
  `id` int(11) NOT NULL,
  `rev_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `users` text NOT NULL,
  `description` text NOT NULL,
  `year` year(4) NOT NULL DEFAULT '0000',
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rev_id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions_partners`
--

CREATE TABLE IF NOT EXISTS `grand_contributions_partners` (
  `contribution_id` int(11) NOT NULL,
  `partner` varchar(512) NOT NULL,
  `type` varchar(16) NOT NULL,
  `subtype` varchar(256) NOT NULL,
  `cash` int(11) NOT NULL,
  `kind` int(11) NOT NULL,
  `unknown` tinyint(1) NOT NULL,
  PRIMARY KEY (`contribution_id`,`partner`),
  KEY `type` (`type`),
  KEY `subtype` (`subtype`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions_projects`
--

CREATE TABLE IF NOT EXISTS `grand_contributions_projects` (
  `contribution_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`contribution_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_disciplines`
--

CREATE TABLE IF NOT EXISTS `grand_disciplines` (
  `id` int(11) NOT NULL,
  `discipline` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_disciplines_map`
--

CREATE TABLE IF NOT EXISTS `grand_disciplines_map` (
  `department` varchar(256) NOT NULL,
  `discipline` int(11) NOT NULL,
  PRIMARY KEY (`department`,`discipline`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_ethics`
--

CREATE TABLE IF NOT EXISTS `grand_ethics` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `completed_tutorial` tinyint(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_eval`
--

CREATE TABLE IF NOT EXISTS `grand_eval` (
  `user_id` int(11) NOT NULL,
  `sub_id` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `year` year(4) NOT NULL DEFAULT '0000',
  PRIMARY KEY (`user_id`,`sub_id`,`type`,`year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_eval_conflicts`
--

CREATE TABLE IF NOT EXISTS `grand_eval_conflicts` (
  `eval_id` int(11) NOT NULL,
  `sub_id` int(11) NOT NULL,
  `type` enum('NI','PROJECT','LOI') NOT NULL,
  `year` year(4) NOT NULL,
  `conflict` tinyint(1) NOT NULL DEFAULT '0',
  `user_conflict` tinyint(1) NOT NULL DEFAULT '0',
  `preference` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eval_id`,`sub_id`,`type`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_feature_votes`
--

CREATE TABLE IF NOT EXISTS `grand_feature_votes` (
  `votes_p_id` int(11) NOT NULL,
  `votes_u_id` int(11) NOT NULL,
  `votes_approve` tinyint(1) NOT NULL,
  `votes_disapprove` tinyint(1) NOT NULL,
  KEY `votes_p_id` (`votes_p_id`,`votes_u_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_hqp_months`
--

CREATE TABLE IF NOT EXISTS `grand_hqp_months` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `months` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_ignored_duplicates`
--

CREATE TABLE IF NOT EXISTS `grand_ignored_duplicates` (
  `id1` int(11) NOT NULL,
  `id2` int(11) NOT NULL,
  `type` varchar(256) NOT NULL,
  PRIMARY KEY (`id1`,`id2`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_list_request`
--

CREATE TABLE IF NOT EXISTS `grand_list_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requesting_user` int(11) NOT NULL,
  `project` varchar(255) NOT NULL,
  `user` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `created` tinyint(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `ignore` (`ignore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_loi`
--

CREATE TABLE IF NOT EXISTS `grand_loi` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` year(4) NOT NULL DEFAULT '2013',
  `revision` tinyint(4) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '',
  `full_name` varchar(255) DEFAULT NULL,
  `type` enum('Full Project','Subproject') NOT NULL DEFAULT 'Full Project',
  `related_loi` varchar(64) DEFAULT NULL,
  `description` text,
  `lead` text,
  `colead` text,
  `champion` text,
  `primary_challenge` text,
  `secondary_challenge` text,
  `loi_pdf` varchar(255) DEFAULT NULL,
  `supplemental_pdf` varchar(255) DEFAULT NULL,
  `manager_comments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials`
--

CREATE TABLE IF NOT EXISTS `grand_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL,
  `type` varchar(256) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `media` varchar(1024) NOT NULL,
  `mediaLocal` varchar(1024) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `description` text NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `title` (`title`(767)),
  KEY `type` (`type`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_keywords`
--

CREATE TABLE IF NOT EXISTS `grand_materials_keywords` (
  `material_id` int(11) NOT NULL,
  `keyword` varchar(256) NOT NULL,
  PRIMARY KEY (`material_id`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_people`
--

CREATE TABLE IF NOT EXISTS `grand_materials_people` (
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`material_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_projects`
--

CREATE TABLE IF NOT EXISTS `grand_materials_projects` (
  `material_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`material_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_milestones`
--

CREATE TABLE IF NOT EXISTS `grand_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` bigint(20) NOT NULL COMMENT 'This is used for when the milestone is first created.',
  `milestone_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('New','Revised','Continuing','Closed','Abandoned') NOT NULL,
  `description` text NOT NULL,
  `assessment` text NOT NULL,
  `comment` text NOT NULL,
  `edited_by` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `projected_end_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `milestone_id` (`milestone_id`),
  KEY `project_id` (`project_id`),
  KEY `identifier` (`identifier`),
  KEY `edited_by` (`edited_by`),
  KEY `status` (`status`),
  KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_milestones_people`
--

CREATE TABLE IF NOT EXISTS `grand_milestones_people` (
  `milestone_id` int(11) NOT NULL COMMENT 'As in `grand_milestones`.id',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`milestone_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_movedOn`
--

CREATE TABLE IF NOT EXISTS `grand_movedOn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `where` text NOT NULL,
  `studies` tinytext NOT NULL,
  `employer` tinytext NOT NULL,
  `city` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_changed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_notifications`
--

CREATE TABLE IF NOT EXISTS `grand_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator` int(11) NOT NULL COMMENT 'The user who created the request',
  `user_id` int(11) NOT NULL COMMENT 'The user this request is for',
  `name` varchar(256) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(512) NOT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator` (`creator`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_partners`
--

CREATE TABLE IF NOT EXISTS `grand_partners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `organization` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prov_or_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `organization` (`organization`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pdf_index`
--

CREATE TABLE IF NOT EXISTS `grand_pdf_index` (
  `report_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `sub_id` int(11) NOT NULL,
  `type` enum('PROJECT','PERSON') NOT NULL DEFAULT 'PROJECT',
  `nr_download` int(10) unsigned NOT NULL DEFAULT '0',
  `last_download` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`,`sub_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pdf_report`
--

CREATE TABLE IF NOT EXISTS `grand_pdf_report` (
  `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT 'user_id of the submitter',
  `generation_user_id` int(11) NOT NULL,
  `submission_user_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `submitted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Flag whether this report was deemed as submitted.',
  `special` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `auto` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `token` char(32) CHARACTER SET ascii NOT NULL COMMENT 'Token used to retrieve reports.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `errors` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of errors in the PDF',
  `len_pdf` int(10) unsigned NOT NULL COMMENT 'Length of the PDF (bytes)',
  `hash_data` char(40) CHARACTER SET ascii NOT NULL COMMENT 'SHA1 of the raw data',
  `hash_pdf` char(40) CHARACTER SET ascii NOT NULL COMMENT 'SHA1 of the PDF',
  `error_data` blob NOT NULL COMMENT 'Serialized associative array of errors found',
  `data` longblob NOT NULL COMMENT 'Serialized PHP array',
  `html` longtext NOT NULL,
  `pdf` longblob NOT NULL,
  UNIQUE KEY `report_id` (`report_id`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`),
  KEY `type` (`type`),
  KEY `submission_user_id` (`submission_user_id`),
  KEY `generation_user_id` (`generation_user_id`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores (all) reports generated as PDF files.';

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll`
--

CREATE TABLE IF NOT EXISTS `grand_poll` (
  `poll_id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `poll_name` varchar(1024) NOT NULL,
  PRIMARY KEY (`poll_id`),
  KEY `collection_id` (`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_collection`
--

CREATE TABLE IF NOT EXISTS `grand_poll_collection` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `collection_name` varchar(1024) NOT NULL,
  `self_vote` tinyint(1) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `time_limit` int(11) NOT NULL,
  PRIMARY KEY (`collection_id`),
  KEY `author` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_groups`
--

CREATE TABLE IF NOT EXISTS `grand_poll_groups` (
  `group_name` varchar(256) NOT NULL,
  `collection_id` int(11) NOT NULL,
  PRIMARY KEY (`group_name`,`collection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_options`
--

CREATE TABLE IF NOT EXISTS `grand_poll_options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(255) NOT NULL,
  `poll_id` int(11) NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_votes`
--

CREATE TABLE IF NOT EXISTS `grand_poll_votes` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`vote_id`),
  KEY `user_id` (`user_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_positions`
--

CREATE TABLE IF NOT EXISTS `grand_positions` (
  `position_id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(256) NOT NULL,
  `order` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`position_id`),
  UNIQUE KEY `position` (`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_postings`
--

CREATE TABLE IF NOT EXISTS `grand_postings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last-changed timestamp',
  `start` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Stard date.',
  `end` date NOT NULL DEFAULT '0000-00-00' COMMENT 'End date.',
  `title` blob NOT NULL,
  `url` varchar(1024) NOT NULL,
  `descr` blob NOT NULL COMMENT 'Text of the posting',
  PRIMARY KEY (`id`),
  KEY `start` (`start`,`end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Postings relevant to the GRAND community';

-- --------------------------------------------------------

--
-- Table structure for table `grand_products`
--

CREATE TABLE IF NOT EXISTS `grand_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `title` varchar(256) NOT NULL,
  `date` date NOT NULL,
  `venue` varchar(256) NOT NULL,
  `status` varchar(256) NOT NULL,
  `authors` text NOT NULL,
  `data` text NOT NULL,
  `date_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `weasel_words` text,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `type` (`type`),
  KEY `category` (`category`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_products_reported`
--

CREATE TABLE IF NOT EXISTS `grand_products_reported` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `reported_type` varchar(16) NOT NULL,
  `year` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_product_authors`
--

CREATE TABLE IF NOT EXISTS `grand_product_authors` (
  `author` varchar(256) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`author`,`product_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_product_projects`
--

CREATE TABLE IF NOT EXISTS `grand_product_projects` (
  `product_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project`
--

CREATE TABLE IF NOT EXISTS `grand_project` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `phase` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_challenges`
--

CREATE TABLE IF NOT EXISTS `grand_project_challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_champions`
--

CREATE TABLE IF NOT EXISTS `grand_project_champions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `champion_org` varchar(255) DEFAULT '',
  `champion_title` varchar(255) DEFAULT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_descriptions`
--

CREATE TABLE IF NOT EXISTS `grand_project_descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `evolution_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `themes` text,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text,
  `problem` text,
  `solution` text,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `evolution_id` (`evolution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_evolution`
--

CREATE TABLE IF NOT EXISTS `grand_project_evolution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `new_id` int(11) NOT NULL,
  `action` enum('CREATE','MERGE','DELETE','EVOLVE') NOT NULL,
  `clear` tinyint(1) NOT NULL,
  `effective_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `last_id` (`last_id`),
  KEY `project_id` (`project_id`),
  KEY `new_id` (`new_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_leaders`
--

CREATE TABLE IF NOT EXISTS `grand_project_leaders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `type` enum('leader','co-leader','manager') NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_members`
--

CREATE TABLE IF NOT EXISTS `grand_project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`),
  KEY `project_id` (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_status`
--

CREATE TABLE IF NOT EXISTS `grand_project_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evolution_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `status` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `bigbet` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `evolution_id` (`evolution_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_recorded_images`
--

CREATE TABLE IF NOT EXISTS `grand_recorded_images` (
  `id` varchar(512) NOT NULL,
  `image` longblob NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_recordings`
--

CREATE TABLE IF NOT EXISTS `grand_recordings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storyToken` varchar(256) NOT NULL,
  `user_id` int(11) NOT NULL,
  `story` longblob NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `person` (`user_id`),
  KEY `storyToken` (`storyToken`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_relations`
--

CREATE TABLE IF NOT EXISTS `grand_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1` int(11) NOT NULL COMMENT 'as in user1 relates to user2',
  `user2` int(11) NOT NULL COMMENT 'as in user1 relates to user2',
  `type` enum('Works With','Supervises') NOT NULL,
  `projects` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user1`,`user2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_reporting_year_ticket`
--

CREATE TABLE IF NOT EXISTS `grand_reporting_year_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `report_type` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `ticket` varchar(256) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_backup`
--

CREATE TABLE IF NOT EXISTS `grand_report_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report` varchar(256) NOT NULL,
  `time` varchar(256) NOT NULL,
  `person_id` int(11) NOT NULL,
  `backup` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_blobs`
--

CREATE TABLE IF NOT EXISTS `grand_report_blobs` (
  `blob_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `edited_by` int(11) NOT NULL,
  `year` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Reporting cycle',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User ID',
  `proj_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Project ID',
  `rp_type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Report type',
  `rp_section` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Report section',
  `rp_item` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Section item',
  `rp_subitem` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Section subitem',
  `changed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last-change timestamp',
  `blob_type` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Type of blob data',
  `data` longblob NOT NULL COMMENT 'Blob contents',
  PRIMARY KEY (`blob_id`),
  KEY `year` (`year`,`user_id`,`proj_id`,`rp_type`,`rp_section`,`rp_item`,`rp_subitem`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_blobs_impersonated`
--

CREATE TABLE IF NOT EXISTS `grand_report_blobs_impersonated` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'This is the last user who edited this blob',
  `previous_value` longblob NOT NULL,
  `current_value` longblob NOT NULL,
  `last_edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `blob_id` (`blob_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_researcher_cv`
--

CREATE TABLE IF NOT EXISTS `grand_researcher_cv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` year(4) DEFAULT NULL,
  `researcher_name` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_review_results`
--

CREATE TABLE IF NOT EXISTS `grand_review_results` (
  `user_id` int(11) DEFAULT NULL,
  `type` enum('CNI','PNI','LOI','Project') NOT NULL DEFAULT 'PNI',
  `year` year(4) DEFAULT NULL,
  `allocated_amount` int(11) DEFAULT NULL,
  `allocated_amount2` int(11) DEFAULT NULL,
  `allocated_amount3` int(11) DEFAULT NULL,
  `overall_score` varchar(32) DEFAULT NULL,
  `send_email` tinyint(1) NOT NULL DEFAULT '1',
  `email_sent` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `user_id` (`user_id`,`type`,`year`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_roles`
--

CREATE TABLE IF NOT EXISTS `grand_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role` varchar(32) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user_id`,`role`),
  KEY `role` (`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_role_request`
--

CREATE TABLE IF NOT EXISTS `grand_role_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `effective_date` text NOT NULL,
  `staff` int(11) NOT NULL,
  `requesting_user` int(11) NOT NULL,
  `current_role` varchar(2048) DEFAULT NULL,
  `role` varchar(2048) NOT NULL,
  `comment` text NOT NULL,
  `other` text NOT NULL,
  `user` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `created` tinyint(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `ignore` (`ignore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_themes`
--

CREATE TABLE IF NOT EXISTS `grand_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acronym` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `phase` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `phase` (`phase`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_theme_leaders`
--

CREATE TABLE IF NOT EXISTS `grand_theme_leaders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme` int(11) NOT NULL,
  `co_lead` varchar(16) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_theses`
--

CREATE TABLE IF NOT EXISTS `grand_theses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`publication_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_travel_forms`
--

CREATE TABLE IF NOT EXISTS `grand_travel_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `year` year(4) NOT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `gender` enum('M','F') DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone_number` varchar(128) DEFAULT NULL,
  `dob` varchar(16) DEFAULT NULL,
  `type` enum('plane','train') NOT NULL DEFAULT 'plane',
  `leaving_from` varchar(255) DEFAULT NULL,
  `going_to` varchar(255) DEFAULT NULL,
  `departure_date` varchar(16) DEFAULT NULL,
  `departure_time` varchar(64) DEFAULT NULL,
  `return_date` varchar(16) DEFAULT NULL,
  `return_time` varchar(64) DEFAULT NULL,
  `preferred_seat` enum('Aisle','Middle','Window') DEFAULT NULL,
  `preferred_carrier` varchar(255) DEFAULT NULL,
  `frequent_flyer` varchar(64) DEFAULT NULL,
  `hotel_checkin` varchar(16) DEFAULT NULL,
  `hotel_checkout` varchar(16) DEFAULT NULL,
  `roommate_preference` varchar(255) DEFAULT NULL,
  `comments` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_universities`
--

CREATE TABLE IF NOT EXISTS `grand_universities` (
  `university_id` int(11) NOT NULL AUTO_INCREMENT,
  `university_name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL,
  PRIMARY KEY (`university_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_request`
--

CREATE TABLE IF NOT EXISTS `grand_user_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `staff` int(11) NOT NULL,
  `requesting_user` int(11) NOT NULL,
  `wpName` varchar(255) NOT NULL,
  `wpEmail` varchar(255) NOT NULL,
  `wpRealName` varchar(255) NOT NULL,
  `wpUserType` varchar(255) NOT NULL,
  `wpNS` text NOT NULL,
  `created` tinyint(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `ignore` (`ignore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_university`
--

CREATE TABLE IF NOT EXISTS `grand_user_university` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `position_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `university_id` (`university_id`),
  KEY `position_id` (`position_id`),
  KEY `end_date` (`end_date`),
  KEY `start_date` (`start_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_extranamespaces`
--

CREATE TABLE IF NOT EXISTS `mw_an_extranamespaces` (
  `nsId` int(11) NOT NULL AUTO_INCREMENT,
  `nsName` varchar(50) NOT NULL,
  `nsUser` int(11) DEFAULT NULL,
  `public` tinyint(1) NOT NULL,
  PRIMARY KEY (`nsId`),
  UNIQUE KEY `nsName` (`nsName`),
  UNIQUE KEY `nsUser` (`nsUser`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pagepermissions`
--

CREATE TABLE IF NOT EXISTS `mw_an_pagepermissions` (
  `page_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`page_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pageratings`
--

CREATE TABLE IF NOT EXISTS `mw_an_pageratings` (
  `page_id` int(8) NOT NULL,
  `user_id` int(5) NOT NULL,
  `visit_count` int(11) NOT NULL DEFAULT '1',
  `rating` int(11) DEFAULT NULL,
  `rating_time` timestamp NULL DEFAULT NULL,
  `comment` text,
  UNIQUE KEY `page_id` (`page_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pagestorate`
--

CREATE TABLE IF NOT EXISTS `mw_an_pagestorate` (
  `id` int(11) NOT NULL COMMENT 'Either page_id or nsId',
  `type` set('ns','page') NOT NULL,
  UNIQUE KEY `id` (`id`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_page_visits`
--

CREATE TABLE IF NOT EXISTS `mw_an_page_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `page_id` int(8) NOT NULL,
  `page_namespace` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_name` (`user_name`),
  KEY `page_id` (`page_id`),
  KEY `page_namespace` (`page_namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_text_replacement`
--

CREATE TABLE IF NOT EXISTS `mw_an_text_replacement` (
  `match_text` varchar(255) NOT NULL,
  `replacement` varchar(255) NOT NULL,
  PRIMARY KEY (`match_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_upload_permissions`
--

CREATE TABLE IF NOT EXISTS `mw_an_upload_permissions` (
  `upload_name` varchar(255) NOT NULL,
  `nsName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`upload_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_upload_perm_temp`
--

CREATE TABLE IF NOT EXISTS `mw_an_upload_perm_temp` (
  `upload_name` varchar(255) NOT NULL,
  `nsName` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`upload_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_vtracker_diff_results`
--

CREATE TABLE IF NOT EXISTS `mw_an_vtracker_diff_results` (
  `rev_id_old` int(11) NOT NULL,
  `rev_id_new` int(11) NOT NULL,
  `result` text NOT NULL,
  `peakMemUsage` bigint(20) NOT NULL,
  `runningTime` int(11) NOT NULL,
  PRIMARY KEY (`rev_id_old`,`rev_id_new`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_archive`
--

CREATE TABLE IF NOT EXISTS `mw_archive` (
  `ar_namespace` int(11) NOT NULL DEFAULT '0',
  `ar_title` varbinary(255) NOT NULL DEFAULT '',
  `ar_text` mediumblob NOT NULL,
  `ar_comment` tinyblob NOT NULL,
  `ar_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ar_user_text` varbinary(255) NOT NULL,
  `ar_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ar_minor_edit` tinyint(4) NOT NULL DEFAULT '0',
  `ar_flags` tinyblob NOT NULL,
  `ar_rev_id` int(10) unsigned DEFAULT NULL,
  `ar_text_id` int(10) unsigned DEFAULT NULL,
  `ar_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ar_len` int(10) unsigned DEFAULT NULL,
  `ar_page_id` int(10) unsigned DEFAULT NULL,
  `ar_parent_id` int(10) unsigned DEFAULT NULL,
  KEY `name_title_timestamp` (`ar_namespace`,`ar_title`,`ar_timestamp`),
  KEY `usertext_timestamp` (`ar_user_text`,`ar_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_category`
--

CREATE TABLE IF NOT EXISTS `mw_category` (
  `cat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat_title` varbinary(255) NOT NULL,
  `cat_pages` int(11) NOT NULL DEFAULT '0',
  `cat_subcats` int(11) NOT NULL DEFAULT '0',
  `cat_files` int(11) NOT NULL DEFAULT '0',
  `cat_hidden` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_title` (`cat_title`),
  KEY `cat_pages` (`cat_pages`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_categorylinks`
--

CREATE TABLE IF NOT EXISTS `mw_categorylinks` (
  `cl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `cl_to` varbinary(255) NOT NULL DEFAULT '',
  `cl_sortkey` varbinary(70) NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cl_from` (`cl_from`,`cl_to`),
  KEY `cl_sortkey` (`cl_to`,`cl_sortkey`,`cl_from`),
  KEY `cl_timestamp` (`cl_to`,`cl_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_change_tag`
--

CREATE TABLE IF NOT EXISTS `mw_change_tag` (
  `ct_rc_id` int(11) DEFAULT NULL,
  `ct_log_id` int(11) DEFAULT NULL,
  `ct_rev_id` int(11) DEFAULT NULL,
  `ct_tag` varbinary(255) NOT NULL,
  `ct_params` blob,
  UNIQUE KEY `change_tag_rc_tag` (`ct_rc_id`,`ct_tag`),
  UNIQUE KEY `change_tag_log_tag` (`ct_log_id`,`ct_tag`),
  UNIQUE KEY `change_tag_rev_tag` (`ct_rev_id`,`ct_tag`),
  KEY `change_tag_tag_id` (`ct_tag`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_externallinks`
--

CREATE TABLE IF NOT EXISTS `mw_externallinks` (
  `el_from` int(10) unsigned NOT NULL DEFAULT '0',
  `el_to` blob NOT NULL,
  `el_index` blob NOT NULL,
  KEY `el_from` (`el_from`,`el_to`(40)),
  KEY `el_to` (`el_to`(60),`el_from`),
  KEY `el_index` (`el_index`(60))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_externallinks_all`
--

CREATE TABLE IF NOT EXISTS `mw_externallinks_all` (
  `page_id` int(8) unsigned NOT NULL,
  `rev_id` int(8) unsigned NOT NULL,
  `url` blob NOT NULL,
  PRIMARY KEY (`page_id`,`url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_filearchive`
--

CREATE TABLE IF NOT EXISTS `mw_filearchive` (
  `fa_id` int(11) NOT NULL AUTO_INCREMENT,
  `fa_name` varbinary(255) NOT NULL DEFAULT '',
  `fa_archive_name` varbinary(255) DEFAULT '',
  `fa_storage_group` varbinary(16) DEFAULT NULL,
  `fa_storage_key` varbinary(64) DEFAULT '',
  `fa_deleted_user` int(11) DEFAULT NULL,
  `fa_deleted_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted_reason` blob,
  `fa_size` int(10) unsigned DEFAULT '0',
  `fa_width` int(11) DEFAULT '0',
  `fa_height` int(11) DEFAULT '0',
  `fa_metadata` mediumblob,
  `fa_bits` int(11) DEFAULT '0',
  `fa_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `fa_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') DEFAULT 'unknown',
  `fa_minor_mime` varbinary(32) DEFAULT 'unknown',
  `fa_description` tinyblob,
  `fa_user` int(10) unsigned DEFAULT '0',
  `fa_user_text` varbinary(255) DEFAULT NULL,
  `fa_timestamp` binary(14) DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fa_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fa_id`),
  KEY `fa_name` (`fa_name`,`fa_timestamp`),
  KEY `fa_storage_group` (`fa_storage_group`,`fa_storage_key`),
  KEY `fa_deleted_timestamp` (`fa_deleted_timestamp`),
  KEY `fa_user_timestamp` (`fa_user_text`,`fa_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_hitcounter`
--

CREATE TABLE IF NOT EXISTS `mw_hitcounter` (
  `hc_id` int(10) unsigned NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=25000;

-- --------------------------------------------------------

--
-- Table structure for table `mw_image`
--

CREATE TABLE IF NOT EXISTS `mw_image` (
  `img_name` varbinary(255) NOT NULL DEFAULT '',
  `img_size` int(10) unsigned NOT NULL DEFAULT '0',
  `img_width` int(11) NOT NULL DEFAULT '0',
  `img_height` int(11) NOT NULL DEFAULT '0',
  `img_metadata` mediumblob NOT NULL,
  `img_bits` int(11) NOT NULL DEFAULT '0',
  `img_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `img_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') NOT NULL DEFAULT 'unknown',
  `img_minor_mime` varbinary(32) NOT NULL DEFAULT 'unknown',
  `img_description` tinyblob NOT NULL,
  `img_user` int(10) unsigned NOT NULL DEFAULT '0',
  `img_user_text` varbinary(255) NOT NULL,
  `img_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `img_sha1` varbinary(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`img_name`),
  KEY `img_usertext_timestamp` (`img_user_text`,`img_timestamp`),
  KEY `img_size` (`img_size`),
  KEY `img_timestamp` (`img_timestamp`),
  KEY `img_sha1` (`img_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_imagelinks`
--

CREATE TABLE IF NOT EXISTS `mw_imagelinks` (
  `il_from` int(10) unsigned NOT NULL DEFAULT '0',
  `il_to` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `il_from` (`il_from`,`il_to`),
  UNIQUE KEY `il_to` (`il_to`,`il_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_interwiki`
--

CREATE TABLE IF NOT EXISTS `mw_interwiki` (
  `iw_prefix` varbinary(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `iw_prefix` (`iw_prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_ipblocks`
--

CREATE TABLE IF NOT EXISTS `mw_ipblocks` (
  `ipb_id` int(11) NOT NULL AUTO_INCREMENT,
  `ipb_address` tinyblob NOT NULL,
  `ipb_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by` int(10) unsigned NOT NULL DEFAULT '0',
  `ipb_by_text` varbinary(255) NOT NULL DEFAULT '',
  `ipb_reason` tinyblob NOT NULL,
  `ipb_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ipb_auto` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_anon_only` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_create_account` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_enable_autoblock` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_expiry` varbinary(14) NOT NULL DEFAULT '',
  `ipb_range_start` tinyblob NOT NULL,
  `ipb_range_end` tinyblob NOT NULL,
  `ipb_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_block_email` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_allow_usertalk` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ipb_id`),
  UNIQUE KEY `ipb_address` (`ipb_address`(255),`ipb_user`,`ipb_auto`,`ipb_anon_only`),
  KEY `ipb_user` (`ipb_user`),
  KEY `ipb_range` (`ipb_range_start`(8),`ipb_range_end`(8)),
  KEY `ipb_timestamp` (`ipb_timestamp`),
  KEY `ipb_expiry` (`ipb_expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_job`
--

CREATE TABLE IF NOT EXISTS `mw_job` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int(11) NOT NULL,
  `job_title` varbinary(255) NOT NULL,
  `job_params` blob NOT NULL,
  PRIMARY KEY (`job_id`),
  KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_langlinks`
--

CREATE TABLE IF NOT EXISTS `mw_langlinks` (
  `ll_from` int(10) unsigned NOT NULL DEFAULT '0',
  `ll_lang` varbinary(20) NOT NULL DEFAULT '',
  `ll_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `ll_from` (`ll_from`,`ll_lang`),
  KEY `ll_lang` (`ll_lang`,`ll_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_logging`
--

CREATE TABLE IF NOT EXISTS `mw_logging` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varbinary(10) NOT NULL DEFAULT '',
  `log_action` varbinary(10) NOT NULL DEFAULT '',
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_user` int(10) unsigned NOT NULL DEFAULT '0',
  `log_namespace` int(11) NOT NULL DEFAULT '0',
  `log_title` varbinary(255) NOT NULL DEFAULT '',
  `log_comment` varbinary(255) NOT NULL DEFAULT '',
  `log_params` blob NOT NULL,
  `log_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `type_time` (`log_type`,`log_timestamp`),
  KEY `user_time` (`log_user`,`log_timestamp`),
  KEY `page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  KEY `times` (`log_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_math`
--

CREATE TABLE IF NOT EXISTS `mw_math` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_outputhash` varbinary(16) NOT NULL,
  `math_html_conservativeness` tinyint(4) NOT NULL,
  `math_html` blob,
  `math_mathml` blob,
  UNIQUE KEY `math_inputhash` (`math_inputhash`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_objectcache`
--

CREATE TABLE IF NOT EXISTS `mw_objectcache` (
  `keyname` varbinary(255) NOT NULL DEFAULT '',
  `value` mediumblob,
  `exptime` datetime DEFAULT NULL,
  PRIMARY KEY (`keyname`),
  KEY `exptime` (`exptime`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_oldimage`
--

CREATE TABLE IF NOT EXISTS `mw_oldimage` (
  `oi_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_archive_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_size` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_width` int(11) NOT NULL DEFAULT '0',
  `oi_height` int(11) NOT NULL DEFAULT '0',
  `oi_bits` int(11) NOT NULL DEFAULT '0',
  `oi_description` tinyblob NOT NULL,
  `oi_user` int(10) unsigned NOT NULL DEFAULT '0',
  `oi_user_text` varbinary(255) NOT NULL,
  `oi_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `oi_metadata` mediumblob NOT NULL,
  `oi_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE') DEFAULT NULL,
  `oi_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart') NOT NULL DEFAULT 'unknown',
  `oi_minor_mime` varbinary(32) NOT NULL DEFAULT 'unknown',
  `oi_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `oi_sha1` varbinary(32) NOT NULL DEFAULT '',
  KEY `oi_usertext_timestamp` (`oi_user_text`,`oi_timestamp`),
  KEY `oi_name_timestamp` (`oi_name`,`oi_timestamp`),
  KEY `oi_name_archive_name` (`oi_name`,`oi_archive_name`(14)),
  KEY `oi_sha1` (`oi_sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page`
--

CREATE TABLE IF NOT EXISTS `mw_page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_namespace` int(11) NOT NULL,
  `page_title` varbinary(255) NOT NULL,
  `page_restrictions` tinyblob NOT NULL,
  `page_counter` bigint(20) unsigned NOT NULL DEFAULT '0',
  `page_is_redirect` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `page_is_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `page_random` double unsigned NOT NULL,
  `page_touched` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `page_latest` int(10) unsigned NOT NULL,
  `page_len` int(10) unsigned NOT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `name_title` (`page_namespace`,`page_title`),
  KEY `page_random` (`page_random`),
  KEY `page_len` (`page_len`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_pagelinks`
--

CREATE TABLE IF NOT EXISTS `mw_pagelinks` (
  `pl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `pl_namespace` int(11) NOT NULL DEFAULT '0',
  `pl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `pl_from` (`pl_from`,`pl_namespace`,`pl_title`),
  UNIQUE KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_pagelinks_all`
--

CREATE TABLE IF NOT EXISTS `mw_pagelinks_all` (
  `page_id` int(8) unsigned NOT NULL,
  `rev_id` int(8) unsigned NOT NULL,
  `namespace` int(11) NOT NULL,
  `title` varbinary(255) NOT NULL,
  `initially_broken` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`,`namespace`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page_props`
--

CREATE TABLE IF NOT EXISTS `mw_page_props` (
  `pp_page` int(11) NOT NULL,
  `pp_propname` varbinary(60) NOT NULL,
  `pp_value` blob NOT NULL,
  UNIQUE KEY `pp_page_propname` (`pp_page`,`pp_propname`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page_restrictions`
--

CREATE TABLE IF NOT EXISTS `mw_page_restrictions` (
  `pr_page` int(11) NOT NULL,
  `pr_type` varbinary(60) NOT NULL,
  `pr_level` varbinary(60) NOT NULL,
  `pr_cascade` tinyint(4) NOT NULL,
  `pr_user` int(11) DEFAULT NULL,
  `pr_expiry` varbinary(14) DEFAULT NULL,
  `pr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`pr_id`),
  UNIQUE KEY `pr_pagetype` (`pr_page`,`pr_type`),
  KEY `pr_typelevel` (`pr_type`,`pr_level`),
  KEY `pr_level` (`pr_level`),
  KEY `pr_cascade` (`pr_cascade`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_parent_of`
--

CREATE TABLE IF NOT EXISTS `mw_parent_of` (
  `sen_id` int(12) unsigned NOT NULL,
  `parent_id` int(12) unsigned NOT NULL,
  `favored` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sen_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_protected_titles`
--

CREATE TABLE IF NOT EXISTS `mw_protected_titles` (
  `pt_namespace` int(11) NOT NULL,
  `pt_title` varbinary(255) NOT NULL,
  `pt_user` int(10) unsigned NOT NULL,
  `pt_reason` tinyblob,
  `pt_timestamp` binary(14) NOT NULL,
  `pt_expiry` varbinary(14) NOT NULL DEFAULT '',
  `pt_create_perm` varbinary(60) NOT NULL,
  UNIQUE KEY `pt_namespace_title` (`pt_namespace`,`pt_title`),
  KEY `pt_timestamp` (`pt_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycache`
--

CREATE TABLE IF NOT EXISTS `mw_querycache` (
  `qc_type` varbinary(32) NOT NULL,
  `qc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qc_namespace` int(11) NOT NULL DEFAULT '0',
  `qc_title` varbinary(255) NOT NULL DEFAULT '',
  KEY `qc_type` (`qc_type`,`qc_value`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycachetwo`
--

CREATE TABLE IF NOT EXISTS `mw_querycachetwo` (
  `qcc_type` varbinary(32) NOT NULL,
  `qcc_value` int(10) unsigned NOT NULL DEFAULT '0',
  `qcc_namespace` int(11) NOT NULL DEFAULT '0',
  `qcc_title` varbinary(255) NOT NULL DEFAULT '',
  `qcc_namespacetwo` int(11) NOT NULL DEFAULT '0',
  `qcc_titletwo` varbinary(255) NOT NULL DEFAULT '',
  KEY `qcc_type` (`qcc_type`,`qcc_value`),
  KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycache_info`
--

CREATE TABLE IF NOT EXISTS `mw_querycache_info` (
  `qci_type` varbinary(32) NOT NULL DEFAULT '',
  `qci_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  UNIQUE KEY `qci_type` (`qci_type`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_recentchanges`
--

CREATE TABLE IF NOT EXISTS `mw_recentchanges` (
  `rc_id` int(11) NOT NULL AUTO_INCREMENT,
  `rc_timestamp` varbinary(14) NOT NULL DEFAULT '',
  `rc_cur_time` varbinary(14) NOT NULL DEFAULT '',
  `rc_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_user_text` varbinary(255) NOT NULL,
  `rc_namespace` int(11) NOT NULL DEFAULT '0',
  `rc_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_comment` varbinary(255) NOT NULL DEFAULT '',
  `rc_minor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_bot` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_cur_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_this_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_last_oldid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_ns` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_moved_to_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_patrolled` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_ip` varbinary(40) NOT NULL DEFAULT '',
  `rc_old_len` int(11) DEFAULT NULL,
  `rc_new_len` int(11) DEFAULT NULL,
  `rc_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rc_logid` int(10) unsigned NOT NULL DEFAULT '0',
  `rc_log_type` varbinary(255) DEFAULT NULL,
  `rc_log_action` varbinary(255) DEFAULT NULL,
  `rc_params` blob,
  PRIMARY KEY (`rc_id`),
  KEY `rc_timestamp` (`rc_timestamp`),
  KEY `rc_namespace_title` (`rc_namespace`,`rc_title`),
  KEY `rc_cur_id` (`rc_cur_id`),
  KEY `new_name_timestamp` (`rc_new`,`rc_namespace`,`rc_timestamp`),
  KEY `rc_ip` (`rc_ip`),
  KEY `rc_ns_usertext` (`rc_namespace`,`rc_user_text`),
  KEY `rc_user_text` (`rc_user_text`,`rc_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_redirect`
--

CREATE TABLE IF NOT EXISTS `mw_redirect` (
  `rd_from` int(10) unsigned NOT NULL DEFAULT '0',
  `rd_namespace` int(11) NOT NULL DEFAULT '0',
  `rd_title` varbinary(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`rd_from`),
  KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_revision`
--

CREATE TABLE IF NOT EXISTS `mw_revision` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rev_page` int(10) unsigned NOT NULL,
  `rev_text_id` int(10) unsigned NOT NULL,
  `rev_comment` tinyblob NOT NULL,
  `rev_user` int(10) unsigned NOT NULL DEFAULT '0',
  `rev_user_text` varbinary(255) NOT NULL DEFAULT '',
  `rev_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `rev_minor_edit` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rev_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rev_len` int(10) unsigned DEFAULT NULL,
  `rev_parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rev_id`),
  UNIQUE KEY `rev_page_id` (`rev_page`,`rev_id`),
  KEY `rev_timestamp` (`rev_timestamp`),
  KEY `page_timestamp` (`rev_page`,`rev_timestamp`),
  KEY `user_timestamp` (`rev_user`,`rev_timestamp`),
  KEY `usertext_timestamp` (`rev_user_text`,`rev_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=1024;

-- --------------------------------------------------------

--
-- Table structure for table `mw_searchindex`
--

CREATE TABLE IF NOT EXISTS `mw_searchindex` (
  `si_page` int(10) unsigned NOT NULL,
  `si_title` varchar(255) NOT NULL DEFAULT '',
  `si_text` mediumtext NOT NULL,
  UNIQUE KEY `si_page` (`si_page`),
  FULLTEXT KEY `si_title` (`si_title`),
  FULLTEXT KEY `si_text` (`si_text`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_sentence`
--

CREATE TABLE IF NOT EXISTS `mw_sentence` (
  `sen_id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `rev_id` int(8) unsigned NOT NULL,
  `page_id` int(8) unsigned NOT NULL,
  `ref_sen_id` int(12) unsigned DEFAULT NULL,
  `similarity` float unsigned NOT NULL,
  `original` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `favored` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `content` blob NOT NULL,
  `text_offset` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  PRIMARY KEY (`sen_id`),
  UNIQUE KEY `rev_sen` (`rev_id`,`text_offset`,`length`),
  KEY `rev_id` (`rev_id`),
  KEY `page_id` (`page_id`),
  KEY `page_rev_id` (`page_id`,`rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_session_data`
--

CREATE TABLE IF NOT EXISTS `mw_session_data` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `page` varchar(255) NOT NULL,
  `handle` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Multiple contexts in a same User:Page map',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` longblob NOT NULL COMMENT 'The actual session data',
  UNIQUE KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`,`page`),
  KEY `page` (`page`),
  KEY `handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_site_stats`
--

CREATE TABLE IF NOT EXISTS `mw_site_stats` (
  `ss_row_id` int(10) unsigned NOT NULL,
  `ss_total_views` bigint(20) unsigned DEFAULT '0',
  `ss_total_edits` bigint(20) unsigned DEFAULT '0',
  `ss_good_articles` bigint(20) unsigned DEFAULT '0',
  `ss_total_pages` bigint(20) DEFAULT '-1',
  `ss_users` bigint(20) DEFAULT '-1',
  `ss_active_users` bigint(20) DEFAULT '-1',
  `ss_admins` int(11) DEFAULT '-1',
  `ss_images` int(11) DEFAULT '0',
  UNIQUE KEY `ss_row_id` (`ss_row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_structuralchanges`
--

CREATE TABLE IF NOT EXISTS `mw_structuralchanges` (
  `rev_id` int(11) NOT NULL,
  `structure_inserted` int(11) NOT NULL,
  `structure_deleted` int(11) NOT NULL,
  `structure_changed` int(11) NOT NULL,
  `structure_moved` int(11) NOT NULL,
  PRIMARY KEY (`rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_tag_summary`
--

CREATE TABLE IF NOT EXISTS `mw_tag_summary` (
  `ts_rc_id` int(11) DEFAULT NULL,
  `ts_log_id` int(11) DEFAULT NULL,
  `ts_rev_id` int(11) DEFAULT NULL,
  `ts_tags` blob NOT NULL,
  UNIQUE KEY `tag_summary_rc_id` (`ts_rc_id`),
  UNIQUE KEY `tag_summary_log_id` (`ts_log_id`),
  UNIQUE KEY `tag_summary_rev_id` (`ts_rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_templatelinks`
--

CREATE TABLE IF NOT EXISTS `mw_templatelinks` (
  `tl_from` int(10) unsigned NOT NULL DEFAULT '0',
  `tl_namespace` int(11) NOT NULL DEFAULT '0',
  `tl_title` varbinary(255) NOT NULL DEFAULT '',
  UNIQUE KEY `tl_from` (`tl_from`,`tl_namespace`,`tl_title`),
  UNIQUE KEY `tl_namespace` (`tl_namespace`,`tl_title`,`tl_from`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_text`
--

CREATE TABLE IF NOT EXISTS `mw_text` (
  `old_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_text` mediumblob NOT NULL,
  `old_flags` tinyblob NOT NULL,
  PRIMARY KEY (`old_id`)
) ENGINE=InnoDB DEFAULT CHARSET=binary MAX_ROWS=10000000 AVG_ROW_LENGTH=10240;

-- --------------------------------------------------------

--
-- Table structure for table `mw_trackbacks`
--

CREATE TABLE IF NOT EXISTS `mw_trackbacks` (
  `tb_id` int(11) NOT NULL AUTO_INCREMENT,
  `tb_page` int(11) DEFAULT NULL,
  `tb_title` varbinary(255) NOT NULL,
  `tb_url` blob NOT NULL,
  `tb_ex` blob,
  `tb_name` varbinary(255) DEFAULT NULL,
  PRIMARY KEY (`tb_id`),
  KEY `tb_page` (`tb_page`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_transcache`
--

CREATE TABLE IF NOT EXISTS `mw_transcache` (
  `tc_url` varbinary(255) NOT NULL,
  `tc_contents` blob,
  `tc_time` int(11) NOT NULL,
  UNIQUE KEY `tc_url_idx` (`tc_url`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_updatelog`
--

CREATE TABLE IF NOT EXISTS `mw_updatelog` (
  `ul_key` varbinary(255) NOT NULL,
  PRIMARY KEY (`ul_key`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user`
--

CREATE TABLE IF NOT EXISTS `mw_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varbinary(255) NOT NULL DEFAULT '',
  `user_real_name` varbinary(255) NOT NULL DEFAULT '',
  `user_twitter` varbinary(255) NOT NULL,
  `url` varbinary(1024) NOT NULL,
  `user_public_profile` blob NOT NULL,
  `user_private_profile` blob NOT NULL,
  `user_gender` varbinary(32) NOT NULL,
  `user_nationality` varbinary(64) NOT NULL,
  `user_password` tinyblob NOT NULL,
  `user_newpassword` tinyblob NOT NULL,
  `user_newpass_time` binary(14) DEFAULT NULL,
  `user_email` tinyblob NOT NULL,
  `user_options` blob NOT NULL,
  `user_touched` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_email_authenticated` binary(14) DEFAULT NULL,
  `user_email_token` binary(32) DEFAULT NULL,
  `user_email_token_expires` binary(14) DEFAULT NULL,
  `user_registration` binary(14) DEFAULT NULL,
  `user_editcount` int(11) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `user_email_token` (`user_email_token`),
  KEY `deleted` (`deleted`)
) ENGINE=InnoDB  DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_aliases`
--

CREATE TABLE IF NOT EXISTS `mw_user_aliases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `alias` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_groups`
--

CREATE TABLE IF NOT EXISTS `mw_user_groups` (
  `ug_user` int(10) unsigned NOT NULL DEFAULT '0',
  `ug_group` varbinary(32) NOT NULL,
  UNIQUE KEY `ug_user_group` (`ug_user`,`ug_group`),
  KEY `ug_group` (`ug_group`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_newtalk`
--

CREATE TABLE IF NOT EXISTS `mw_user_newtalk` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_ip` varbinary(40) NOT NULL DEFAULT '',
  `user_last_timestamp` binary(14) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  KEY `user_id` (`user_id`),
  KEY `user_ip` (`user_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_valid_tag`
--

CREATE TABLE IF NOT EXISTS `mw_valid_tag` (
  `vt_tag` varbinary(255) NOT NULL,
  PRIMARY KEY (`vt_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_auth_ids`
--

CREATE TABLE IF NOT EXISTS `mw_virtu_auth_ids` (
  `pub_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_experience`
--

CREATE TABLE IF NOT EXISTS `mw_virtu_experience` (
  `user_id` int(11) NOT NULL,
  `exp` int(11) DEFAULT NULL,
  `connections` int(11) DEFAULT NULL,
  `products` int(11) DEFAULT NULL,
  `num_products` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_pub_auths`
--

CREATE TABLE IF NOT EXISTS `mw_virtu_pub_auths` (
  `pub_id` int(11) NOT NULL,
  `auth_ids` char(250) DEFAULT NULL,
  PRIMARY KEY (`pub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_watchlist`
--

CREATE TABLE IF NOT EXISTS `mw_watchlist` (
  `wl_user` int(10) unsigned NOT NULL,
  `wl_namespace` int(11) NOT NULL DEFAULT '0',
  `wl_title` varbinary(255) NOT NULL DEFAULT '',
  `wl_notificationtimestamp` varbinary(14) DEFAULT NULL,
  UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  KEY `namespace_title` (`wl_namespace`,`wl_title`)
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_actor`
--

CREATE TABLE IF NOT EXISTS `sociql_actor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_fk` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `real_name` varchar(30) DEFAULT NULL,
  `query` text NOT NULL,
  `actor_id` varchar(20) NOT NULL DEFAULT '',
  `url` varchar(255) DEFAULT NULL,
  `url_required_prop` varchar(20) DEFAULT NULL,
  `map_x_prop` int(11) DEFAULT NULL,
  `map_y_prop` int(11) DEFAULT NULL,
  `ont_entity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_map_actors`
--

CREATE TABLE IF NOT EXISTS `sociql_map_actors` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `map` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_ent`
--

CREATE TABLE IF NOT EXISTS `sociql_ontology_ent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '1',
  `upper_entity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_prop`
--

CREATE TABLE IF NOT EXISTS `sociql_ontology_prop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `entity_fk` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_rel`
--

CREATE TABLE IF NOT EXISTS `sociql_ontology_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_entity` int(11) NOT NULL,
  `to_entity` int(11) NOT NULL,
  `type` varchar(1) NOT NULL DEFAULT 'A',
  `name` varchar(20) DEFAULT NULL,
  `upper_level` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_property`
--

CREATE TABLE IF NOT EXISTS `sociql_property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_fk` int(11) NOT NULL DEFAULT '0',
  `relation_fk` int(11) DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `real_name` varchar(20) NOT NULL DEFAULT '',
  `query` text NOT NULL,
  `queriable` smallint(6) NOT NULL DEFAULT '1',
  `optimizable` tinyint(1) NOT NULL DEFAULT '1',
  `table_name` varchar(50) NOT NULL,
  `type` varchar(11) NOT NULL DEFAULT 'nominal',
  `sortable` smallint(6) NOT NULL DEFAULT '1',
  `significant` smallint(6) NOT NULL DEFAULT '0',
  `sparql` varchar(255) DEFAULT NULL,
  `fb_disj_query` text,
  `ont_property` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_relation`
--

CREATE TABLE IF NOT EXISTS `sociql_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `property1_fk` int(11) NOT NULL DEFAULT '0',
  `real_name1` varchar(20) NOT NULL DEFAULT '',
  `property2_fk` int(11) NOT NULL DEFAULT '0',
  `real_name2` varchar(20) NOT NULL DEFAULT '',
  `query` text NOT NULL,
  `direction` smallint(6) NOT NULL DEFAULT '2',
  `fb_disj_query` text,
  `cardinality` varchar(3) NOT NULL DEFAULT 'N-N',
  `ont_relation` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_requiredprop`
--

CREATE TABLE IF NOT EXISTS `sociql_requiredprop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_fk` int(11) NOT NULL DEFAULT '0',
  `requiredset_fk` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_requiredset`
--

CREATE TABLE IF NOT EXISTS `sociql_requiredset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actor_fk` int(11) NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_same`
--

CREATE TABLE IF NOT EXISTS `sociql_same` (
  `reason_id` int(11) NOT NULL DEFAULT '0',
  `facebook_id` varchar(20) DEFAULT NULL,
  `dbpedia_id` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_site`
--

CREATE TABLE IF NOT EXISTS `sociql_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `endpoint` varchar(255) DEFAULT NULL,
  `max_store` int(11) NOT NULL DEFAULT '-1',
  `type` varchar(20) NOT NULL DEFAULT 'sql',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `prefixes` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `survey_events`
--

CREATE TABLE IF NOT EXISTS `survey_events` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `event_info` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_results`
--

CREATE TABLE IF NOT EXISTS `survey_results` (
  `user_id` int(10) unsigned NOT NULL,
  `consent` tinyint(1) NOT NULL DEFAULT '0',
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `discipline` text,
  `use_forum_data` tinyint(1) NOT NULL DEFAULT '1',
  `use_survey_data` tinyint(1) NOT NULL DEFAULT '1',
  `grand_connections` text,
  `experience` text,
  `experience2` text,
  `additional_comments` text,
  `receive_results` tinyint(1) NOT NULL DEFAULT '1',
  `current_tab` smallint(6) NOT NULL DEFAULT '0',
  `submitted` tinyint(1) DEFAULT '0',
  `completed` varchar(255) DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_messages`
--

CREATE TABLE IF NOT EXISTS `wikidev_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL,
  `body` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `subject` text NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `mid_header` text NOT NULL,
  `refid_header` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_projects`
--

CREATE TABLE IF NOT EXISTS `wikidev_projects` (
  `projectid` int(11) NOT NULL AUTO_INCREMENT,
  `mailListName` varchar(255) NOT NULL,
  PRIMARY KEY (`projectid`,`mailListName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_unsubs`
--

CREATE TABLE IF NOT EXISTS `wikidev_unsubs` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
