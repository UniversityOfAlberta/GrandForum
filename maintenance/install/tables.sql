-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 07, 2025 at 08:42 PM
-- Server version: 8.0.43-0ubuntu0.24.04.2
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `forum_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `grand_acknowledgements`
--

CREATE TABLE `grand_acknowledgements` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(256) NOT NULL,
  `university` varchar(256) NOT NULL,
  `date` varchar(256) NOT NULL,
  `supervisor` varchar(256) NOT NULL,
  `md5` varchar(256) NOT NULL,
  `pdf` longblob NOT NULL,
  `uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_action_plan`
--

CREATE TABLE `grand_action_plan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL,
  `type` text NOT NULL,
  `fitbit` text NOT NULL,
  `goals` text NOT NULL,
  `barriers` text NOT NULL,
  `plan` text NOT NULL,
  `time` text NOT NULL,
  `when` text NOT NULL,
  `dates` text NOT NULL,
  `confidence` int NOT NULL,
  `tracker` text NOT NULL,
  `components` text NOT NULL,
  `submitted` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_activities`
--

CREATE TABLE `grand_activities` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `project_id` text NOT NULL,
  `order` int NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_allocations`
--

CREATE TABLE `grand_allocations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `year` int NOT NULL,
  `amount` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_alumni`
--

CREATE TABLE `grand_alumni` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `recruited` varchar(64) NOT NULL,
  `recruited_country` varchar(64) NOT NULL,
  `alumni` varchar(32) NOT NULL,
  `alumni_country` varchar(64) NOT NULL,
  `alumni_sector` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_avoid_categories`
--

CREATE TABLE `grand_avoid_categories` (
  `id` int NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `level` int NOT NULL,
  `parent` int NOT NULL,
  `alias_database_name` text NOT NULL,
  `tags` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_avoid_expert_event`
--

CREATE TABLE `grand_avoid_expert_event` (
  `id` int NOT NULL,
  `name_of_expert` text NOT NULL,
  `expert_field` text NOT NULL,
  `zoomlink` text NOT NULL,
  `date_of_event` datetime NOT NULL,
  `active` tinyint(1) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `currently_on` tinyint(1) NOT NULL,
  `date_for_questions` datetime NOT NULL,
  `theme` text NOT NULL,
  `host` text NOT NULL,
  `description` text NOT NULL,
  `event` text NOT NULL,
  `details` text NOT NULL,
  `end_of_event` datetime NOT NULL,
  `location` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_avoid_resources`
--

CREATE TABLE `grand_avoid_resources` (
  `id` int NOT NULL,
  `ParentAgency` text NOT NULL,
  `PublicName_Program` text NOT NULL,
  `ResourceAgencyNum` text NOT NULL,
  `AgencyDescription` text NOT NULL,
  `HoursOfOperation` text NOT NULL,
  `Eligibility` text NOT NULL,
  `LanguagesOffered` text NOT NULL,
  `LanguagesOfferedList` text NOT NULL,
  `ApplicationProcess` text NOT NULL,
  `Coverage` text NOT NULL,
  `CoverageAreaText` text NOT NULL,
  `PhysicalAddress1` text NOT NULL,
  `PhysicalAddress2` text NOT NULL,
  `PhysicalCity` text NOT NULL,
  `PhysicalCounty` text NOT NULL,
  `PhysicalStateProvince` text NOT NULL,
  `PhysicalPostalCode` text NOT NULL,
  `MailingAttentionName` text NOT NULL,
  `MailingAddress1` text NOT NULL,
  `MailingAddress2` text NOT NULL,
  `MailingCity` text NOT NULL,
  `MailingStateProvince` text NOT NULL,
  `MailingPostalCode` text NOT NULL,
  `DisabilitiesAccess` text NOT NULL,
  `Phone1Name` text NOT NULL,
  `Phone1Number` text NOT NULL,
  `Phone1Description` text NOT NULL,
  `PhoneNumberBusinessLine` text NOT NULL,
  `PhoneTollFree` text NOT NULL,
  `PhoneFax` text NOT NULL,
  `EmailAddressMain` text NOT NULL,
  `WebsiteAddress` text NOT NULL,
  `Custom_Facebook` text NOT NULL,
  `Custom_Instagram` text NOT NULL,
  `Custom_LinkedIn` text NOT NULL,
  `Custom_Twitter` text NOT NULL,
  `Custom_YouTube` text NOT NULL,
  `Categories` text NOT NULL,
  `LastVerifiedOn` text NOT NULL,
  `Split` text NOT NULL,
  `PublicName` text NOT NULL,
  `Category` text NOT NULL,
  `SubCategory` text NOT NULL,
  `SubSubCategory` text NOT NULL,
  `TaxonomyTerms` text NOT NULL,
  `lat` varchar(255) NOT NULL,
  `lon` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_bibliography`
--

CREATE TABLE `grand_bibliography` (
  `id` int NOT NULL,
  `title` varchar(1024) NOT NULL,
  `description` longtext NOT NULL,
  `person_id` int NOT NULL,
  `editors` varchar(255) NOT NULL,
  `products` longtext NOT NULL,
  `thread_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_boards`
--

CREATE TABLE `grand_boards` (
  `id` int NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_bsi_postings`
--

CREATE TABLE `grand_bsi_postings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `visibility` varchar(16) NOT NULL,
  `language` varchar(32) NOT NULL,
  `title` varchar(300) NOT NULL,
  `title_fr` varchar(300) NOT NULL,
  `article_link` varchar(256) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `summary` text NOT NULL,
  `summary_fr` text NOT NULL,
  `type` varchar(64) NOT NULL,
  `about` text NOT NULL,
  `skills` text NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `positions` int NOT NULL,
  `positions_text` text NOT NULL,
  `discipline` text NOT NULL,
  `partner_name` varchar(64) NOT NULL,
  `city` varchar(64) NOT NULL,
  `province` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL,
  `image_caption` varchar(128) NOT NULL,
  `image_caption_fr` varchar(128) NOT NULL,
  `deleted_text` text NOT NULL,
  `preview_code` varchar(32) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_ccv`
--

CREATE TABLE `grand_ccv` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `ccv` longtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_clipboard`
--

CREATE TABLE `grand_clipboard` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `json_objs` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_collaborations`
--

CREATE TABLE `grand_collaborations` (
  `id` int NOT NULL,
  `organization_name` varchar(256) NOT NULL,
  `year` int NOT NULL,
  `end_year` int NOT NULL,
  `sector` varchar(128) NOT NULL,
  `number` int NOT NULL,
  `country` varchar(64) NOT NULL,
  `planning` tinyint(1) NOT NULL,
  `design` tinyint(1) NOT NULL,
  `analysis` tinyint(1) NOT NULL,
  `dissemination` tinyint(1) NOT NULL,
  `user` tinyint(1) NOT NULL,
  `other` text NOT NULL,
  `person_name` varchar(64) NOT NULL,
  `position` varchar(64) NOT NULL,
  `email` varchar(256) NOT NULL,
  `cash` decimal(65,2) NOT NULL,
  `inkind` decimal(65,2) NOT NULL,
  `projected_cash` decimal(65,2) NOT NULL,
  `projected_inkind` decimal(65,2) NOT NULL,
  `existed` varchar(16) NOT NULL,
  `extra` text NOT NULL,
  `knowledge_user` tinyint(1) NOT NULL,
  `leverage` tinyint(1) NOT NULL,
  `access_id` int NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_collaboration_files`
--

CREATE TABLE `grand_collaboration_files` (
  `id` int NOT NULL,
  `collaboration_id` int NOT NULL,
  `file` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_collaboration_projects`
--

CREATE TABLE `grand_collaboration_projects` (
  `collaboration_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_conference_attendance`
--

CREATE TABLE `grand_conference_attendance` (
  `id` int NOT NULL,
  `person_id` int NOT NULL,
  `date` date NOT NULL,
  `conference` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `title` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions`
--

CREATE TABLE `grand_contributions` (
  `id` int NOT NULL,
  `rev_id` int NOT NULL,
  `name` varchar(256) NOT NULL,
  `users` text NOT NULL,
  `description` text NOT NULL,
  `institution` text NOT NULL,
  `province` text NOT NULL,
  `access_id` int NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions_partners`
--

CREATE TABLE `grand_contributions_partners` (
  `contribution_id` int NOT NULL,
  `partner` varchar(512) NOT NULL,
  `contact` varchar(1024) NOT NULL,
  `signatory` varchar(8) NOT NULL DEFAULT '',
  `industry` varchar(64) NOT NULL,
  `country` varchar(64) NOT NULL DEFAULT '',
  `prov` varchar(64) NOT NULL DEFAULT '',
  `city` varchar(64) NOT NULL DEFAULT '',
  `level` varchar(64) NOT NULL,
  `type` varchar(16) NOT NULL,
  `amounts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contributions_projects`
--

CREATE TABLE `grand_contributions_projects` (
  `contribution_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_contribution_edits`
--

CREATE TABLE `grand_contribution_edits` (
  `id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_crm_contact`
--

CREATE TABLE `grand_crm_contact` (
  `id` int NOT NULL,
  `title` varchar(256) NOT NULL,
  `owner` int NOT NULL,
  `details` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_crm_opportunity`
--

CREATE TABLE `grand_crm_opportunity` (
  `id` int NOT NULL,
  `contact` int NOT NULL,
  `owner` int NOT NULL,
  `description` text NOT NULL,
  `category` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_crm_projects`
--

CREATE TABLE `grand_crm_projects` (
  `id` int NOT NULL,
  `contact_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_crm_task`
--

CREATE TABLE `grand_crm_task` (
  `id` int NOT NULL,
  `opportunity` int NOT NULL,
  `assignee` int NOT NULL,
  `task` text NOT NULL,
  `due_date` datetime NOT NULL,
  `transactions` text NOT NULL,
  `priority` varchar(16) DEFAULT NULL,
  `status` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_data_collection`
--

CREATE TABLE `grand_data_collection` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `page` varchar(256) NOT NULL,
  `data` text NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_delegate`
--

CREATE TABLE `grand_delegate` (
  `id` int NOT NULL,
  `delegate` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_diversity`
--

CREATE TABLE `grand_diversity` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `language` varchar(2) NOT NULL,
  `submitted` tinyint(1) DEFAULT NULL,
  `decline` tinyint(1) NOT NULL,
  `reason` text NOT NULL,
  `gender` text NOT NULL,
  `orientation` text NOT NULL,
  `indigenous` varchar(32) NOT NULL,
  `disability` varchar(32) NOT NULL,
  `disability_visibility` text NOT NULL,
  `minority` varchar(32) NOT NULL,
  `race` text NOT NULL,
  `language_minority` text,
  `immigration` varchar(128) NOT NULL,
  `stem` text NOT NULL,
  `implemented` text NOT NULL,
  `training_taken` text NOT NULL,
  `prevents_training` text NOT NULL,
  `training` varchar(50) NOT NULL,
  `improve` text NOT NULL,
  `statement` varchar(50) NOT NULL,
  `principles_describe` text NOT NULL,
  `principles` varchar(50) NOT NULL,
  `least_respected` text NOT NULL,
  `respected` text NOT NULL,
  `space` varchar(50) NOT NULL,
  `valued` varchar(50) NOT NULL,
  `true_self` varchar(50) NOT NULL,
  `indigenous_apply` text NOT NULL,
  `age` varchar(50) NOT NULL,
  `affiliation` varchar(50) NOT NULL,
  `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_diversity_2018`
--

CREATE TABLE `grand_diversity_2018` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `language` varchar(2) NOT NULL,
  `decline` tinyint(1) NOT NULL,
  `reason` text NOT NULL,
  `gender` text NOT NULL,
  `orientation` text NOT NULL,
  `birth` varchar(32) NOT NULL,
  `indigenous` varchar(32) NOT NULL,
  `disability` varchar(32) NOT NULL,
  `disability_visibility` varchar(32) NOT NULL,
  `minority` varchar(32) NOT NULL,
  `race` text NOT NULL,
  `racialized` varchar(32) NOT NULL,
  `immigration` varchar(128) NOT NULL,
  `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_elite_postings`
--

CREATE TABLE `grand_elite_postings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `visibility` varchar(32) NOT NULL,
  `language` varchar(32) NOT NULL,
  `title` varchar(300) NOT NULL,
  `title_fr` varchar(300) NOT NULL,
  `article_link` varchar(256) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `summary` text NOT NULL,
  `summary_fr` text NOT NULL,
  `image` mediumtext NOT NULL,
  `image_caption` varchar(128) NOT NULL,
  `image_caption_fr` varchar(128) NOT NULL,
  `type` varchar(32) NOT NULL,
  `extra` mediumtext NOT NULL,
  `comments` text NOT NULL,
  `preview_code` varchar(32) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_ethics`
--

CREATE TABLE `grand_ethics` (
  `user_id` int UNSIGNED NOT NULL,
  `completed_tutorial` tinyint(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_eval`
--

CREATE TABLE `grand_eval` (
  `user_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `sub2_id` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `year` year NOT NULL DEFAULT '0000'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_eval_conflicts`
--

CREATE TABLE `grand_eval_conflicts` (
  `eval_id` int NOT NULL,
  `sub_id` int NOT NULL,
  `type` enum('NI','PROJECT','LOI') NOT NULL,
  `year` year NOT NULL,
  `conflict` tinyint(1) NOT NULL DEFAULT '0',
  `user_conflict` tinyint(1) NOT NULL DEFAULT '0',
  `preference` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_event_postings`
--

CREATE TABLE `grand_event_postings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `visibility` varchar(16) NOT NULL,
  `language` varchar(32) NOT NULL,
  `title` varchar(300) NOT NULL,
  `title_fr` varchar(300) NOT NULL,
  `article_link` varchar(1024) NOT NULL,
  `website` varchar(256) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `summary` text NOT NULL,
  `summary_fr` text NOT NULL,
  `address` varchar(70) NOT NULL,
  `city` varchar(70) NOT NULL,
  `province` varchar(70) NOT NULL,
  `country` varchar(70) NOT NULL,
  `image_caption` varchar(500) NOT NULL,
  `image_caption_fr` varchar(500) NOT NULL,
  `enable_registration` tinyint(1) NOT NULL,
  `enable_materials` tinyint(1) NOT NULL,
  `extra` text,
  `preview_code` varchar(32) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_event_registration`
--

CREATE TABLE `grand_event_registration` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `email` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `role` varchar(64) NOT NULL,
  `webpage` varchar(256) NOT NULL,
  `twitter` varchar(128) NOT NULL,
  `receive_information` tinyint(1) NOT NULL,
  `join_newsletter` tinyint(1) NOT NULL,
  `create_profile` tinyint(1) NOT NULL,
  `similar_events` tinyint(1) NOT NULL,
  `misc` mediumtext NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_feature_votes`
--

CREATE TABLE `grand_feature_votes` (
  `votes_p_id` int NOT NULL,
  `votes_u_id` int NOT NULL,
  `votes_approve` tinyint(1) NOT NULL,
  `votes_disapprove` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_fitbit_data`
--

CREATE TABLE `grand_fitbit_data` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `steps` int NOT NULL,
  `distance` int NOT NULL,
  `active` int NOT NULL,
  `sleep` int NOT NULL,
  `water` int NOT NULL,
  `fibre` float NOT NULL,
  `protein` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_freeze`
--

CREATE TABLE `grand_freeze` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `feature` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_gamification`
--

CREATE TABLE `grand_gamification` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(64) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_gs_citations`
--

CREATE TABLE `grand_gs_citations` (
  `user_id` int NOT NULL,
  `year` varchar(4) NOT NULL,
  `count` int NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_ignored_duplicates`
--

CREATE TABLE `grand_ignored_duplicates` (
  `id1` int NOT NULL,
  `id2` int NOT NULL,
  `type` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_illegal_authors`
--

CREATE TABLE `grand_illegal_authors` (
  `id` int NOT NULL,
  `author` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_job_postings`
--

CREATE TABLE `grand_job_postings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `visibility` varchar(16) NOT NULL,
  `email_sent` tinyint(1) NOT NULL,
  `job_title` varchar(128) NOT NULL,
  `job_title_fr` varchar(128) NOT NULL,
  `deadline_type` varchar(16) NOT NULL,
  `deadline_date` datetime NOT NULL,
  `start_date_type` varchar(32) NOT NULL,
  `start_date` datetime NOT NULL,
  `tenure` varchar(8) NOT NULL,
  `rank` varchar(32) NOT NULL,
  `language` varchar(32) NOT NULL,
  `rank_other` varchar(32) NOT NULL,
  `position_type` varchar(32) NOT NULL,
  `research_fields` text NOT NULL,
  `research_fields_fr` text NOT NULL,
  `keywords` text NOT NULL,
  `keywords_fr` text NOT NULL,
  `contact` varchar(128) NOT NULL,
  `source_link` varchar(256) NOT NULL,
  `summary` text NOT NULL,
  `summary_fr` text NOT NULL,
  `preview_code` varchar(32) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_journals`
--

CREATE TABLE `grand_journals` (
  `id` int NOT NULL,
  `year` int NOT NULL,
  `short_title` varchar(64) NOT NULL,
  `iso_abbrev` varchar(128) NOT NULL,
  `title` varchar(256) NOT NULL,
  `issn` varchar(9) NOT NULL,
  `eissn` varchar(9) NOT NULL,
  `description` varchar(128) NOT NULL,
  `ranking_numerator` int NOT NULL,
  `ranking_denominator` int NOT NULL,
  `impact_factor` decimal(8,5) NOT NULL,
  `cited_half_life` decimal(8,5) NOT NULL,
  `eigenfactor` decimal(8,5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_lims_contact`
--

CREATE TABLE `grand_lims_contact` (
  `id` int NOT NULL,
  `title` varchar(256) NOT NULL,
  `owner` int NOT NULL,
  `details` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_lims_files`
--

CREATE TABLE `grand_lims_files` (
  `id` int NOT NULL,
  `opportunity_id` int NOT NULL,
  `filename` varchar(128) NOT NULL,
  `type` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_lims_opportunity`
--

CREATE TABLE `grand_lims_opportunity` (
  `id` int NOT NULL,
  `contact` int NOT NULL,
  `owner` int NOT NULL,
  `project` int NOT NULL,
  `user_type` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(64) NOT NULL,
  `products` text NOT NULL,
  `surveyed` varchar(8) NOT NULL,
  `responded` varchar(8) NOT NULL,
  `satisfaction` varchar(3) NOT NULL,
  `status` varchar(16) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_lims_projects`
--

CREATE TABLE `grand_lims_projects` (
  `id` int NOT NULL,
  `contact_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_lims_task`
--

CREATE TABLE `grand_lims_task` (
  `id` int NOT NULL,
  `opportunity` int NOT NULL,
  `assignee` int NOT NULL,
  `task` text NOT NULL,
  `due_date` datetime NOT NULL,
  `comments` text NOT NULL,
  `status` varchar(64) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_list_request`
--

CREATE TABLE `grand_list_request` (
  `id` int NOT NULL,
  `requesting_user` int NOT NULL,
  `project` varchar(255) NOT NULL,
  `user` int NOT NULL,
  `type` varchar(16) NOT NULL,
  `created` tinyint(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_loi`
--

CREATE TABLE `grand_loi` (
  `id` int UNSIGNED NOT NULL,
  `year` year NOT NULL DEFAULT '2013',
  `revision` tinyint NOT NULL,
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
  `manager_comments` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_managed_people`
--

CREATE TABLE `grand_managed_people` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `managed_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials`
--

CREATE TABLE `grand_materials` (
  `id` int NOT NULL,
  `title` varchar(1024) NOT NULL,
  `type` varchar(256) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `media` varchar(1024) NOT NULL,
  `mediaLocal` varchar(1024) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `description` text NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_keywords`
--

CREATE TABLE `grand_materials_keywords` (
  `material_id` int NOT NULL,
  `keyword` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_people`
--

CREATE TABLE `grand_materials_people` (
  `material_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_materials_projects`
--

CREATE TABLE `grand_materials_projects` (
  `material_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_milestones`
--

CREATE TABLE `grand_milestones` (
  `id` int NOT NULL,
  `activity_id` int NOT NULL,
  `milestone_id` int NOT NULL,
  `order` int NOT NULL,
  `project_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `leader` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` varchar(16) NOT NULL,
  `modification` varchar(16) NOT NULL,
  `description` text NOT NULL,
  `end_user` varchar(32) NOT NULL,
  `comment` text NOT NULL,
  `people` text NOT NULL,
  `edited_by` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `quarters` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `projected_end_date` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_milestones_people`
--

CREATE TABLE `grand_milestones_people` (
  `milestone_id` int NOT NULL COMMENT 'As in `grand_milestones`.id',
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_movedOn`
--

CREATE TABLE `grand_movedOn` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `where` text NOT NULL,
  `studies` tinytext NOT NULL,
  `employer` tinytext NOT NULL,
  `city` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `employment_type` varchar(64) NOT NULL,
  `effective_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_changed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_news_postings`
--

CREATE TABLE `grand_news_postings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `visibility` varchar(16) NOT NULL,
  `language` varchar(32) NOT NULL,
  `title` varchar(300) NOT NULL,
  `title_fr` varchar(300) NOT NULL,
  `article_link` varchar(256) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `summary` text NOT NULL,
  `summary_fr` text NOT NULL,
  `author` varchar(128) NOT NULL,
  `source_name` varchar(128) NOT NULL,
  `source_link` varchar(256) NOT NULL,
  `image_caption` varchar(500) NOT NULL,
  `image_caption_fr` varchar(500) NOT NULL,
  `enable_registration` tinyint(1) NOT NULL,
  `enable_materials` tinyint(1) NOT NULL,
  `preview_code` varchar(32) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_notifications`
--

CREATE TABLE `grand_notifications` (
  `id` int NOT NULL,
  `creator` int NOT NULL COMMENT 'The user who created the request',
  `user_id` int NOT NULL COMMENT 'The user this request is for',
  `name` varchar(256) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(512) NOT NULL,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_partners`
--

CREATE TABLE `grand_partners` (
  `id` int UNSIGNED NOT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `prov_or_state` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `country` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pdf_index`
--

CREATE TABLE `grand_pdf_index` (
  `report_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `sub_id` int NOT NULL,
  `type` enum('PROJECT','PERSON') NOT NULL DEFAULT 'PROJECT',
  `nr_download` int UNSIGNED NOT NULL DEFAULT '0',
  `last_download` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pdf_report`
--

CREATE TABLE `grand_pdf_report` (
  `report_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL COMMENT 'user_id of the submitter',
  `proj_id` int NOT NULL,
  `generation_user_id` int NOT NULL,
  `submission_user_id` int NOT NULL,
  `year` int NOT NULL,
  `type` varchar(64) NOT NULL,
  `submitted` tinyint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Flag whether this report was deemed as submitted.',
  `special` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `auto` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `token` varchar(40) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `errors` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Number of errors in the PDF',
  `len_pdf` int UNSIGNED NOT NULL COMMENT 'Length of the PDF (bytes)',
  `hash_data` char(40) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'SHA1 of the raw data',
  `hash_pdf` char(40) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL COMMENT 'SHA1 of the PDF',
  `error_data` blob NOT NULL COMMENT 'Serialized associative array of errors found',
  `data` longblob NOT NULL COMMENT 'Serialized PHP array',
  `html` longtext NOT NULL,
  `pdf` longblob NOT NULL,
  `encrypted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Stores (all) reports generated as PDF files.';

-- --------------------------------------------------------

--
-- Table structure for table `grand_person_crdc`
--

CREATE TABLE `grand_person_crdc` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_person_keywords`
--

CREATE TABLE `grand_person_keywords` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `keyword` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pmm_files`
--

CREATE TABLE `grand_pmm_files` (
  `id` int UNSIGNED NOT NULL,
  `opportunity_id` int DEFAULT NULL,
  `filename` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` mediumtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pmm_task`
--

CREATE TABLE `grand_pmm_task` (
  `id` int UNSIGNED NOT NULL,
  `opportunity` int DEFAULT NULL,
  `task` text COLLATE utf8mb4_unicode_ci,
  `due_date` datetime DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `task_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pmm_task_assignees`
--

CREATE TABLE `grand_pmm_task_assignees` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int UNSIGNED DEFAULT NULL,
  `assignee` int DEFAULT NULL,
  `status` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` mediumtext COLLATE utf8mb4_unicode_ci,
  `reviewer` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_pmm_task_assignees_comments`
--

CREATE TABLE `grand_pmm_task_assignees_comments` (
  `id` int UNSIGNED NOT NULL,
  `task_id` int UNSIGNED DEFAULT NULL,
  `assignee_id` int DEFAULT NULL,
  `sender_id` int UNSIGNED DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll`
--

CREATE TABLE `grand_poll` (
  `poll_id` int NOT NULL,
  `collection_id` int NOT NULL,
  `poll_name` varchar(1024) NOT NULL,
  `choices` int NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_collection`
--

CREATE TABLE `grand_poll_collection` (
  `collection_id` int NOT NULL,
  `author_id` int NOT NULL,
  `collection_name` varchar(1024) NOT NULL,
  `description` longtext NOT NULL,
  `self_vote` tinyint(1) NOT NULL,
  `timestamp` int NOT NULL,
  `time_limit` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_groups`
--

CREATE TABLE `grand_poll_groups` (
  `group_name` varchar(256) NOT NULL,
  `collection_id` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_options`
--

CREATE TABLE `grand_poll_options` (
  `option_id` int NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `poll_id` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_poll_votes`
--

CREATE TABLE `grand_poll_votes` (
  `vote_id` int NOT NULL,
  `user_id` int NOT NULL,
  `option_id` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_positions`
--

CREATE TABLE `grand_positions` (
  `position_id` int NOT NULL,
  `position` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `order` int NOT NULL,
  `default` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_posting_images`
--

CREATE TABLE `grand_posting_images` (
  `id` int NOT NULL,
  `tbl` varchar(32) NOT NULL,
  `posting_id` int NOT NULL,
  `index` int NOT NULL,
  `mime` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_posts`
--

CREATE TABLE `grand_posts` (
  `id` int NOT NULL,
  `thread_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` longtext NOT NULL,
  `search` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_products`
--

CREATE TABLE `grand_products` (
  `id` int NOT NULL,
  `category` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `type` varchar(64) NOT NULL,
  `title` varchar(256) NOT NULL,
  `date` date NOT NULL,
  `venue` varchar(256) NOT NULL,
  `status` varchar(256) NOT NULL,
  `authors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data` longtext NOT NULL,
  `date_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `access_id` int NOT NULL,
  `created_by` int NOT NULL,
  `access` varchar(256) NOT NULL DEFAULT 'Forum',
  `ccv_id` varchar(256) NOT NULL,
  `bibtex_id` varchar(256) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_product_authors`
--

CREATE TABLE `grand_product_authors` (
  `author` varchar(128) NOT NULL,
  `product_id` int NOT NULL,
  `order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_product_projects`
--

CREATE TABLE `grand_product_projects` (
  `product_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_product_tags`
--

CREATE TABLE `grand_product_tags` (
  `id` int NOT NULL,
  `tag` varchar(128) NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project`
--

CREATE TABLE `grand_project` (
  `id` int NOT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `phase` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_projects`
--

CREATE TABLE `grand_projects` (
  `id` int NOT NULL,
  `visibility` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_challenges`
--

CREATE TABLE `grand_project_challenges` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_champions`
--

CREATE TABLE `grand_project_champions` (
  `id` int UNSIGNED NOT NULL,
  `project_id` int UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `champion_org` varchar(255) DEFAULT '',
  `champion_title` varchar(255) DEFAULT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_contact`
--

CREATE TABLE `grand_project_contact` (
  `id` int NOT NULL,
  `proj_id` int NOT NULL,
  `type` varchar(64) NOT NULL,
  `line1` varchar(256) NOT NULL,
  `line2` varchar(256) NOT NULL,
  `line3` varchar(256) NOT NULL,
  `line4` varchar(256) NOT NULL,
  `line5` varchar(256) NOT NULL,
  `city` varchar(256) NOT NULL,
  `code` varchar(16) NOT NULL,
  `country` varchar(64) NOT NULL,
  `province` varchar(64) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `primary_indicator` tinyint(1) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `fax` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  `twitter` varchar(128) NOT NULL,
  `facebook` varchar(128) NOT NULL,
  `linkedin` varchar(128) NOT NULL,
  `youtube` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_descriptions`
--

CREATE TABLE `grand_project_descriptions` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `evolution_id` int NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `short_name` varchar(128) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` text,
  `long_description` text NOT NULL,
  `member_status` varchar(255) NOT NULL DEFAULT 'Member',
  `faculty_list` varchar(32) NOT NULL DEFAULT '',
  `website` varchar(256) NOT NULL,
  `dept_website` varchar(256) NOT NULL,
  `email` varchar(64) NOT NULL,
  `use_generic` tinyint(1) NOT NULL DEFAULT '0',
  `admin_email` varchar(64) NOT NULL,
  `admin_use_generic` tinyint(1) NOT NULL DEFAULT '0',
  `tech_email` varchar(64) NOT NULL,
  `tech_use_generic` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_evolution`
--

CREATE TABLE `grand_project_evolution` (
  `id` int NOT NULL,
  `last_id` int NOT NULL,
  `project_id` int NOT NULL,
  `new_id` int NOT NULL,
  `action` enum('CREATE','MERGE','DELETE','EVOLVE') NOT NULL,
  `clear` tinyint(1) NOT NULL,
  `effective_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_leaders`
--

CREATE TABLE `grand_project_leaders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `type` enum('leader','co-leader','manager') NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_members`
--

CREATE TABLE `grand_project_members` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_programs`
--

CREATE TABLE `grand_project_programs` (
  `id` int NOT NULL,
  `proj_id` int NOT NULL,
  `name` varchar(128) NOT NULL,
  `url` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_project_status`
--

CREATE TABLE `grand_project_status` (
  `id` int NOT NULL,
  `evolution_id` int NOT NULL,
  `project_id` int NOT NULL,
  `status` varchar(32) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `type` varchar(32) NOT NULL,
  `private` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_provinces`
--

CREATE TABLE `grand_provinces` (
  `id` int NOT NULL,
  `province` varchar(256) NOT NULL,
  `color` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_recorded_images`
--

CREATE TABLE `grand_recorded_images` (
  `id` varchar(512) NOT NULL,
  `image` longblob NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_recordings`
--

CREATE TABLE `grand_recordings` (
  `id` int NOT NULL,
  `storyToken` varchar(256) NOT NULL,
  `user_id` int NOT NULL,
  `story` longblob NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_relations`
--

CREATE TABLE `grand_relations` (
  `id` int NOT NULL,
  `user1` int NOT NULL COMMENT 'as in user1 relates to user2',
  `user2` int NOT NULL COMMENT 'as in user1 relates to user2',
  `type` varchar(32) NOT NULL,
  `projects` text NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_backup`
--

CREATE TABLE `grand_report_backup` (
  `id` int NOT NULL,
  `report` varchar(256) NOT NULL,
  `time` varchar(256) NOT NULL,
  `person_id` int NOT NULL,
  `backup` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_blobs`
--

CREATE TABLE `grand_report_blobs` (
  `blob_id` int UNSIGNED NOT NULL,
  `edited_by` int NOT NULL,
  `year` smallint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Reporting cycle',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'User ID',
  `proj_id` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Project ID',
  `rp_type` varchar(64) NOT NULL,
  `rp_section` varchar(64) NOT NULL,
  `rp_item` varchar(64) NOT NULL,
  `rp_subitem` varchar(64) NOT NULL,
  `changed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last-change timestamp',
  `blob_type` smallint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Type of blob data',
  `data` longblob NOT NULL COMMENT 'Blob contents',
  `md5` varchar(32) NOT NULL,
  `encrypted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_report_blobs_impersonated`
--

CREATE TABLE `grand_report_blobs_impersonated` (
  `id` int NOT NULL,
  `blob_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'This is the last user who edited this blob',
  `previous_value` longblob NOT NULL,
  `current_value` longblob NOT NULL,
  `last_edited` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_roles`
--

CREATE TABLE `grand_roles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` varchar(32) NOT NULL,
  `title` varchar(256) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_role_projects`
--

CREATE TABLE `grand_role_projects` (
  `role_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_role_subtype`
--

CREATE TABLE `grand_role_subtype` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `sub_role` varchar(256) NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_rss_articles`
--

CREATE TABLE `grand_rss_articles` (
  `id` int NOT NULL,
  `feed` int NOT NULL,
  `rss_id` varchar(128) NOT NULL,
  `url` varchar(1024) NOT NULL,
  `title` varchar(256) NOT NULL,
  `date` datetime NOT NULL,
  `description` text NOT NULL,
  `people` text NOT NULL,
  `projects` text NOT NULL,
  `keywords` text NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_rss_feeds`
--

CREATE TABLE `grand_rss_feeds` (
  `id` int NOT NULL,
  `url` varchar(256) NOT NULL,
  `filter` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_themes`
--

CREATE TABLE `grand_themes` (
  `id` int NOT NULL,
  `acronym` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `resources` text NOT NULL,
  `wiki` text NOT NULL,
  `phase` int NOT NULL,
  `color` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_theme_leaders`
--

CREATE TABLE `grand_theme_leaders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `theme` int NOT NULL,
  `co_lead` varchar(16) NOT NULL,
  `coordinator` varchar(16) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_theses`
--

CREATE TABLE `grand_theses` (
  `id` int NOT NULL,
  `moved_on` int NOT NULL,
  `user_id` int NOT NULL,
  `publication_id` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `grand_threads`
--

CREATE TABLE `grand_threads` (
  `id` int NOT NULL,
  `board_id` int NOT NULL,
  `stickied` tinyint(1) NOT NULL,
  `user_id` int NOT NULL,
  `users` text NOT NULL,
  `title` varchar(1024) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `roles` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_top_products`
--

CREATE TABLE `grand_top_products` (
  `id` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `obj_id` int NOT NULL,
  `product_type` varchar(32) NOT NULL,
  `product_id` int NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_universities`
--

CREATE TABLE `grand_universities` (
  `university_id` int NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `short_name` varchar(32) NOT NULL,
  `province_id` int NOT NULL,
  `latitude` varchar(32) NOT NULL,
  `longitude` varchar(32) NOT NULL,
  `order` int NOT NULL,
  `default` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_uofa_news`
--

CREATE TABLE `grand_uofa_news` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(256) NOT NULL,
  `url` varchar(256) NOT NULL,
  `first_sentences` text NOT NULL,
  `img` varchar(256) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_addresses`
--

CREATE TABLE `grand_user_addresses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(64) NOT NULL,
  `line1` varchar(256) NOT NULL,
  `line2` varchar(256) NOT NULL,
  `line3` varchar(256) NOT NULL,
  `line4` varchar(256) NOT NULL,
  `line5` varchar(256) NOT NULL,
  `city` varchar(256) NOT NULL,
  `code` varchar(16) NOT NULL,
  `country` varchar(64) NOT NULL,
  `province` varchar(64) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `primary_indicator` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_languages`
--

CREATE TABLE `grand_user_languages` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `language` varchar(64) NOT NULL,
  `can_read` tinyint(1) NOT NULL,
  `can_write` tinyint(1) NOT NULL,
  `can_speak` tinyint(1) NOT NULL,
  `can_understand` tinyint(1) NOT NULL,
  `can_review` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_metrics`
--

CREATE TABLE `grand_user_metrics` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `gs_citation_count` int NOT NULL,
  `gs_hindex_5_years` float NOT NULL,
  `gs_i10_index_5_years` float NOT NULL,
  `gs_hindex` float NOT NULL,
  `gs_i10_index` float NOT NULL,
  `scopus_document_count` int NOT NULL,
  `scopus_cited_by_count` int NOT NULL,
  `scopus_citation_count` int NOT NULL,
  `scopus_h_index` float NOT NULL,
  `scopus_coauthor_count` int NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_request`
--

CREATE TABLE `grand_user_request` (
  `id` int NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `staff` int NOT NULL,
  `requesting_user` int NOT NULL,
  `wpName` varchar(255) NOT NULL,
  `wpEmail` varchar(255) NOT NULL,
  `wpSendEmail` varchar(64) NOT NULL,
  `wpRealName` varchar(255) NOT NULL,
  `wpFirstName` varchar(128) NOT NULL,
  `wpMiddleName` varchar(128) NOT NULL,
  `wpLastName` varchar(128) NOT NULL,
  `wpUserType` varchar(255) NOT NULL,
  `wpUserSubType` varchar(255) NOT NULL,
  `wpNS` text NOT NULL,
  `relation` varchar(64) NOT NULL,
  `university` varchar(256) NOT NULL,
  `faculty` varchar(256) NOT NULL,
  `department` varchar(256) NOT NULL,
  `position` varchar(256) NOT NULL,
  `linkedin` varchar(1024) DEFAULT NULL,
  `nationality` varchar(64) NOT NULL,
  `employment` varchar(64) NOT NULL,
  `recruitment` varchar(64) NOT NULL,
  `recruitment_country` varchar(64) NOT NULL,
  `start_date` varchar(256) NOT NULL,
  `end_date` varchar(256) NOT NULL,
  `candidate` int NOT NULL,
  `extra` text,
  `created` tinyint(1) NOT NULL,
  `ignore` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_telephone`
--

CREATE TABLE `grand_user_telephone` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` varchar(64) NOT NULL,
  `country_code` varchar(32) NOT NULL,
  `area_code` varchar(32) NOT NULL,
  `number` varchar(32) NOT NULL,
  `extension` varchar(32) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `primary_indicator` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grand_user_university`
--

CREATE TABLE `grand_user_university` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `university_id` int NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `position_id` int NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_actor`
--

CREATE TABLE `mw_actor` (
  `actor_id` bigint UNSIGNED NOT NULL,
  `actor_user` int UNSIGNED DEFAULT NULL,
  `actor_name` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_extranamespaces`
--

CREATE TABLE `mw_an_extranamespaces` (
  `nsId` int NOT NULL,
  `nsName` varchar(50) NOT NULL,
  `nsUser` int DEFAULT NULL,
  `public` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pagepermissions`
--

CREATE TABLE `mw_an_pagepermissions` (
  `page_id` int NOT NULL,
  `group_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pageratings`
--

CREATE TABLE `mw_an_pageratings` (
  `page_id` int NOT NULL,
  `user_id` int NOT NULL,
  `visit_count` int NOT NULL DEFAULT '1',
  `rating` int DEFAULT NULL,
  `rating_time` timestamp NULL DEFAULT NULL,
  `comment` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_pagestorate`
--

CREATE TABLE `mw_an_pagestorate` (
  `id` int NOT NULL COMMENT 'Either page_id or nsId',
  `type` set('ns','page') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_page_visits`
--

CREATE TABLE `mw_an_page_visits` (
  `id` int NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `page_id` int NOT NULL,
  `page_namespace` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_text_replacement`
--

CREATE TABLE `mw_an_text_replacement` (
  `match_text` varchar(255) NOT NULL,
  `replacement` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_upload_permissions`
--

CREATE TABLE `mw_an_upload_permissions` (
  `upload_name` varchar(255) NOT NULL,
  `nsName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_upload_perm_temp`
--

CREATE TABLE `mw_an_upload_perm_temp` (
  `upload_name` varchar(255) NOT NULL,
  `nsName` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_an_vtracker_diff_results`
--

CREATE TABLE `mw_an_vtracker_diff_results` (
  `rev_id_old` int NOT NULL,
  `rev_id_new` int NOT NULL,
  `result` text NOT NULL,
  `peakMemUsage` bigint NOT NULL,
  `runningTime` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_archive`
--

CREATE TABLE `mw_archive` (
  `ar_id` int UNSIGNED NOT NULL,
  `ar_namespace` int NOT NULL DEFAULT '0',
  `ar_title` varbinary(255) NOT NULL DEFAULT '',
  `ar_comment_id` bigint UNSIGNED NOT NULL,
  `ar_actor` bigint UNSIGNED NOT NULL,
  `ar_timestamp` binary(14) NOT NULL,
  `ar_minor_edit` tinyint NOT NULL DEFAULT '0',
  `ar_rev_id` int UNSIGNED NOT NULL,
  `ar_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `ar_len` int UNSIGNED DEFAULT NULL,
  `ar_page_id` int UNSIGNED DEFAULT NULL,
  `ar_parent_id` int UNSIGNED DEFAULT NULL,
  `ar_sha1` varbinary(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_bot_passwords`
--

CREATE TABLE `mw_bot_passwords` (
  `bp_user` int UNSIGNED NOT NULL,
  `bp_app_id` varbinary(32) NOT NULL,
  `bp_password` tinyblob NOT NULL,
  `bp_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `bp_restrictions` blob NOT NULL,
  `bp_grants` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_category`
--

CREATE TABLE `mw_category` (
  `cat_id` int UNSIGNED NOT NULL,
  `cat_title` varbinary(255) NOT NULL,
  `cat_pages` int NOT NULL DEFAULT '0',
  `cat_subcats` int NOT NULL DEFAULT '0',
  `cat_files` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_categorylinks`
--

CREATE TABLE `mw_categorylinks` (
  `cl_from` int UNSIGNED NOT NULL DEFAULT '0',
  `cl_to` varbinary(255) NOT NULL DEFAULT '',
  `cl_sortkey` varbinary(230) NOT NULL DEFAULT '',
  `cl_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cl_sortkey_prefix` varbinary(255) NOT NULL DEFAULT '',
  `cl_collation` varbinary(32) NOT NULL DEFAULT '',
  `cl_type` enum('page','subcat','file') NOT NULL DEFAULT 'page'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_change_tag`
--

CREATE TABLE `mw_change_tag` (
  `ct_id` int UNSIGNED NOT NULL,
  `ct_rc_id` int DEFAULT NULL,
  `ct_log_id` int UNSIGNED DEFAULT NULL,
  `ct_rev_id` int UNSIGNED DEFAULT NULL,
  `ct_params` blob,
  `ct_tag_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_change_tag_def`
--

CREATE TABLE `mw_change_tag_def` (
  `ctd_id` int UNSIGNED NOT NULL,
  `ctd_name` varbinary(255) NOT NULL,
  `ctd_user_defined` tinyint(1) NOT NULL,
  `ctd_count` bigint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_comment`
--

CREATE TABLE `mw_comment` (
  `comment_id` bigint UNSIGNED NOT NULL,
  `comment_hash` int NOT NULL,
  `comment_text` blob NOT NULL,
  `comment_data` blob
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_content`
--

CREATE TABLE `mw_content` (
  `content_id` bigint UNSIGNED NOT NULL,
  `content_size` int UNSIGNED NOT NULL,
  `content_sha1` varbinary(32) NOT NULL,
  `content_model` smallint UNSIGNED NOT NULL,
  `content_address` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_content_models`
--

CREATE TABLE `mw_content_models` (
  `model_id` int NOT NULL,
  `model_name` varbinary(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_externallinks`
--

CREATE TABLE `mw_externallinks` (
  `el_id` int UNSIGNED NOT NULL,
  `el_from` int UNSIGNED NOT NULL DEFAULT '0',
  `el_to` blob NOT NULL,
  `el_index` blob NOT NULL,
  `el_index_60` varbinary(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_externallinks_all`
--

CREATE TABLE `mw_externallinks_all` (
  `page_id` int UNSIGNED NOT NULL,
  `rev_id` int UNSIGNED NOT NULL,
  `url` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_filearchive`
--

CREATE TABLE `mw_filearchive` (
  `fa_id` int UNSIGNED NOT NULL,
  `fa_name` varbinary(255) NOT NULL DEFAULT '',
  `fa_archive_name` varbinary(255) DEFAULT '',
  `fa_storage_group` varbinary(16) DEFAULT NULL,
  `fa_storage_key` varbinary(64) DEFAULT '',
  `fa_deleted_user` int DEFAULT NULL,
  `fa_deleted_timestamp` binary(14),
  `fa_deleted_reason_id` bigint UNSIGNED NOT NULL,
  `fa_size` int UNSIGNED DEFAULT '0',
  `fa_width` int DEFAULT '0',
  `fa_height` int DEFAULT '0',
  `fa_metadata` mediumblob,
  `fa_bits` int DEFAULT '0',
  `fa_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `fa_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') DEFAULT 'unknown',
  `fa_minor_mime` varbinary(100) DEFAULT 'unknown',
  `fa_description_id` bigint UNSIGNED NOT NULL,
  `fa_actor` bigint UNSIGNED NOT NULL,
  `fa_timestamp` binary(14),
  `fa_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `fa_sha1` varbinary(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_googlelogin_allowed_domains`
--

CREATE TABLE `mw_googlelogin_allowed_domains` (
  `gl_allowed_domain_id` int UNSIGNED NOT NULL,
  `gl_allowed_domain` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_image`
--

CREATE TABLE `mw_image` (
  `img_name` varbinary(255) NOT NULL DEFAULT '',
  `img_size` int UNSIGNED NOT NULL DEFAULT '0',
  `img_width` int NOT NULL DEFAULT '0',
  `img_height` int NOT NULL DEFAULT '0',
  `img_metadata` mediumblob NOT NULL,
  `img_bits` int NOT NULL DEFAULT '0',
  `img_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `img_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') NOT NULL DEFAULT 'unknown',
  `img_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
  `img_description_id` bigint UNSIGNED NOT NULL,
  `img_actor` bigint UNSIGNED NOT NULL,
  `img_timestamp` binary(14) NOT NULL,
  `img_sha1` varbinary(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_imagelinks`
--

CREATE TABLE `mw_imagelinks` (
  `il_from` int UNSIGNED NOT NULL DEFAULT '0',
  `il_to` varbinary(255) NOT NULL DEFAULT '',
  `il_from_namespace` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_interwiki`
--

CREATE TABLE `mw_interwiki` (
  `iw_prefix` varbinary(32) NOT NULL,
  `iw_url` blob NOT NULL,
  `iw_local` tinyint(1) NOT NULL,
  `iw_trans` tinyint NOT NULL DEFAULT '0',
  `iw_api` blob NOT NULL,
  `iw_wikiid` varbinary(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_ipblocks`
--

CREATE TABLE `mw_ipblocks` (
  `ipb_id` int UNSIGNED NOT NULL,
  `ipb_address` tinyblob NOT NULL,
  `ipb_user` int UNSIGNED NOT NULL DEFAULT '0',
  `ipb_by` int UNSIGNED NOT NULL DEFAULT '0',
  `ipb_by_text` varbinary(255) NOT NULL DEFAULT '',
  `ipb_by_actor` bigint UNSIGNED NOT NULL DEFAULT '0',
  `ipb_reason_id` bigint UNSIGNED NOT NULL,
  `ipb_timestamp` binary(14) NOT NULL,
  `ipb_auto` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_anon_only` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_create_account` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_enable_autoblock` tinyint(1) NOT NULL DEFAULT '1',
  `ipb_expiry` varbinary(14) NOT NULL,
  `ipb_range_start` tinyblob NOT NULL,
  `ipb_range_end` tinyblob NOT NULL,
  `ipb_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_block_email` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_allow_usertalk` tinyint(1) NOT NULL DEFAULT '0',
  `ipb_parent_block_id` int UNSIGNED DEFAULT NULL,
  `ipb_sitewide` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_ipblocks_restrictions`
--

CREATE TABLE `mw_ipblocks_restrictions` (
  `ir_ipb_id` int UNSIGNED NOT NULL,
  `ir_type` tinyint NOT NULL,
  `ir_value` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_ip_changes`
--

CREATE TABLE `mw_ip_changes` (
  `ipc_rev_id` int UNSIGNED NOT NULL DEFAULT '0',
  `ipc_rev_timestamp` binary(14) NOT NULL,
  `ipc_hex` varbinary(35) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_iwlinks`
--

CREATE TABLE `mw_iwlinks` (
  `iwl_from` int UNSIGNED NOT NULL DEFAULT '0',
  `iwl_prefix` varbinary(32) NOT NULL DEFAULT '',
  `iwl_title` varbinary(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_job`
--

CREATE TABLE `mw_job` (
  `job_id` int UNSIGNED NOT NULL,
  `job_cmd` varbinary(60) NOT NULL DEFAULT '',
  `job_namespace` int NOT NULL,
  `job_title` varbinary(255) NOT NULL,
  `job_params` mediumblob NOT NULL,
  `job_timestamp` binary(14) DEFAULT NULL,
  `job_random` int UNSIGNED NOT NULL DEFAULT '0',
  `job_token` varbinary(32) NOT NULL DEFAULT '',
  `job_token_timestamp` binary(14) DEFAULT NULL,
  `job_sha1` varbinary(32) NOT NULL DEFAULT '',
  `job_attempts` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_l10n_cache`
--

CREATE TABLE `mw_l10n_cache` (
  `lc_lang` varbinary(35) NOT NULL,
  `lc_key` varbinary(255) NOT NULL,
  `lc_value` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_langlinks`
--

CREATE TABLE `mw_langlinks` (
  `ll_from` int UNSIGNED NOT NULL DEFAULT '0',
  `ll_lang` varbinary(35) NOT NULL DEFAULT '',
  `ll_title` varbinary(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_linktarget`
--

CREATE TABLE `mw_linktarget` (
  `lt_id` bigint UNSIGNED NOT NULL,
  `lt_namespace` int NOT NULL,
  `lt_title` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_logging`
--

CREATE TABLE `mw_logging` (
  `log_id` int UNSIGNED NOT NULL,
  `log_type` varbinary(32) NOT NULL,
  `log_action` varbinary(32) NOT NULL,
  `log_timestamp` binary(14) NOT NULL DEFAULT '19700101000000',
  `log_namespace` int NOT NULL DEFAULT '0',
  `log_title` varbinary(255) NOT NULL DEFAULT '',
  `log_comment_id` bigint UNSIGNED NOT NULL,
  `log_params` blob NOT NULL,
  `log_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `log_actor` bigint UNSIGNED NOT NULL,
  `log_page` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_log_search`
--

CREATE TABLE `mw_log_search` (
  `ls_field` varbinary(32) NOT NULL,
  `ls_value` varbinary(255) NOT NULL,
  `ls_log_id` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_math`
--

CREATE TABLE `mw_math` (
  `math_inputhash` varbinary(16) NOT NULL,
  `math_outputhash` varbinary(16) NOT NULL,
  `math_html_conservativeness` tinyint NOT NULL,
  `math_html` blob,
  `math_mathml` blob
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_module_deps`
--

CREATE TABLE `mw_module_deps` (
  `md_module` varbinary(255) NOT NULL,
  `md_skin` varbinary(32) NOT NULL,
  `md_deps` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_objectcache`
--

CREATE TABLE `mw_objectcache` (
  `keyname` varbinary(255) NOT NULL DEFAULT '',
  `value` mediumblob,
  `exptime` binary(14) NOT NULL,
  `modtoken` varbinary(17) NOT NULL DEFAULT '00000000000000000',
  `flags` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_oldimage`
--

CREATE TABLE `mw_oldimage` (
  `oi_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_archive_name` varbinary(255) NOT NULL DEFAULT '',
  `oi_size` int UNSIGNED NOT NULL DEFAULT '0',
  `oi_width` int NOT NULL DEFAULT '0',
  `oi_height` int NOT NULL DEFAULT '0',
  `oi_bits` int NOT NULL DEFAULT '0',
  `oi_description_id` bigint UNSIGNED NOT NULL,
  `oi_actor` bigint UNSIGNED NOT NULL,
  `oi_timestamp` binary(14) NOT NULL,
  `oi_metadata` mediumblob NOT NULL,
  `oi_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `oi_major_mime` enum('unknown','application','audio','image','text','video','message','model','multipart','chemical') NOT NULL DEFAULT 'unknown',
  `oi_minor_mime` varbinary(100) NOT NULL DEFAULT 'unknown',
  `oi_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `oi_sha1` varbinary(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_openid_connect`
--

CREATE TABLE `mw_openid_connect` (
  `oidc_user` int UNSIGNED NOT NULL,
  `oidc_subject` tinyblob NOT NULL,
  `oidc_issuer` tinyblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page`
--

CREATE TABLE `mw_page` (
  `page_id` int UNSIGNED NOT NULL,
  `page_namespace` int NOT NULL,
  `page_title` varbinary(255) NOT NULL,
  `page_is_redirect` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `page_is_new` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `page_random` double UNSIGNED NOT NULL,
  `page_touched` binary(14) NOT NULL,
  `page_latest` int UNSIGNED NOT NULL,
  `page_len` int UNSIGNED NOT NULL,
  `page_content_model` varbinary(32) DEFAULT NULL,
  `page_links_updated` varbinary(14) DEFAULT NULL,
  `page_lang` varbinary(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_pagelinks`
--

CREATE TABLE `mw_pagelinks` (
  `pl_from` int UNSIGNED NOT NULL DEFAULT '0',
  `pl_namespace` int NOT NULL DEFAULT '0',
  `pl_title` varbinary(255) NOT NULL DEFAULT '',
  `pl_from_namespace` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_pagelinks_all`
--

CREATE TABLE `mw_pagelinks_all` (
  `page_id` int UNSIGNED NOT NULL,
  `rev_id` int UNSIGNED NOT NULL,
  `namespace` int NOT NULL,
  `title` varbinary(255) NOT NULL,
  `initially_broken` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page_props`
--

CREATE TABLE `mw_page_props` (
  `pp_page` int UNSIGNED NOT NULL,
  `pp_propname` varbinary(60) NOT NULL,
  `pp_value` blob NOT NULL,
  `pp_sortkey` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_page_restrictions`
--

CREATE TABLE `mw_page_restrictions` (
  `pr_page` int UNSIGNED NOT NULL,
  `pr_type` varbinary(60) NOT NULL,
  `pr_level` varbinary(60) NOT NULL,
  `pr_cascade` tinyint NOT NULL,
  `pr_expiry` varbinary(14) DEFAULT NULL,
  `pr_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_parent_of`
--

CREATE TABLE `mw_parent_of` (
  `sen_id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED NOT NULL,
  `favored` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_protected_titles`
--

CREATE TABLE `mw_protected_titles` (
  `pt_namespace` int NOT NULL,
  `pt_title` varbinary(255) NOT NULL,
  `pt_user` int UNSIGNED NOT NULL,
  `pt_reason_id` bigint UNSIGNED NOT NULL,
  `pt_timestamp` binary(14) NOT NULL,
  `pt_expiry` varbinary(14) NOT NULL,
  `pt_create_perm` varbinary(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycache`
--

CREATE TABLE `mw_querycache` (
  `qc_type` varbinary(32) NOT NULL,
  `qc_value` int UNSIGNED NOT NULL DEFAULT '0',
  `qc_namespace` int NOT NULL DEFAULT '0',
  `qc_title` varbinary(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycachetwo`
--

CREATE TABLE `mw_querycachetwo` (
  `qcc_type` varbinary(32) NOT NULL,
  `qcc_value` int UNSIGNED NOT NULL DEFAULT '0',
  `qcc_namespace` int NOT NULL DEFAULT '0',
  `qcc_title` varbinary(255) NOT NULL DEFAULT '',
  `qcc_namespacetwo` int NOT NULL DEFAULT '0',
  `qcc_titletwo` varbinary(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_querycache_info`
--

CREATE TABLE `mw_querycache_info` (
  `qci_type` varbinary(32) NOT NULL DEFAULT '',
  `qci_timestamp` binary(14) NOT NULL DEFAULT '19700101000000'
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_recentchanges`
--

CREATE TABLE `mw_recentchanges` (
  `rc_id` int UNSIGNED NOT NULL,
  `rc_timestamp` binary(14) NOT NULL,
  `rc_actor` bigint UNSIGNED NOT NULL,
  `rc_namespace` int NOT NULL DEFAULT '0',
  `rc_title` varbinary(255) NOT NULL DEFAULT '',
  `rc_comment_id` bigint UNSIGNED NOT NULL,
  `rc_minor` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_bot` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_new` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_cur_id` int UNSIGNED NOT NULL DEFAULT '0',
  `rc_this_oldid` int UNSIGNED NOT NULL DEFAULT '0',
  `rc_last_oldid` int UNSIGNED NOT NULL DEFAULT '0',
  `rc_type` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_patrolled` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_ip` varbinary(40) NOT NULL DEFAULT '',
  `rc_old_len` int DEFAULT NULL,
  `rc_new_len` int DEFAULT NULL,
  `rc_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rc_logid` int UNSIGNED NOT NULL DEFAULT '0',
  `rc_log_type` varbinary(255) DEFAULT NULL,
  `rc_log_action` varbinary(255) DEFAULT NULL,
  `rc_params` blob,
  `rc_source` varbinary(16) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_redirect`
--

CREATE TABLE `mw_redirect` (
  `rd_from` int UNSIGNED NOT NULL DEFAULT '0',
  `rd_namespace` int NOT NULL DEFAULT '0',
  `rd_title` varbinary(255) NOT NULL DEFAULT '',
  `rd_interwiki` varbinary(32) DEFAULT NULL,
  `rd_fragment` varbinary(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_revision`
--

CREATE TABLE `mw_revision` (
  `rev_id` int UNSIGNED NOT NULL,
  `rev_page` int UNSIGNED NOT NULL,
  `rev_comment_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `rev_actor` bigint UNSIGNED NOT NULL DEFAULT '0',
  `rev_timestamp` binary(14) NOT NULL,
  `rev_minor_edit` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rev_deleted` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `rev_len` int UNSIGNED DEFAULT NULL,
  `rev_parent_id` int UNSIGNED DEFAULT NULL,
  `rev_sha1` varbinary(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB AVG_ROW_LENGTH=1024 DEFAULT CHARSET=binary MAX_ROWS=10000000;

-- --------------------------------------------------------

--
-- Table structure for table `mw_revision_comment_temp`
--

CREATE TABLE `mw_revision_comment_temp` (
  `revcomment_rev` int UNSIGNED NOT NULL,
  `revcomment_comment_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_searchindex`
--

CREATE TABLE `mw_searchindex` (
  `si_page` int UNSIGNED NOT NULL,
  `si_title` varchar(255) NOT NULL DEFAULT '',
  `si_text` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_sentence`
--

CREATE TABLE `mw_sentence` (
  `sen_id` int UNSIGNED NOT NULL,
  `rev_id` int UNSIGNED NOT NULL,
  `page_id` int UNSIGNED NOT NULL,
  `ref_sen_id` int UNSIGNED DEFAULT NULL,
  `similarity` float UNSIGNED NOT NULL,
  `original` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `favored` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `content` blob NOT NULL,
  `text_offset` int NOT NULL,
  `length` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_session_data`
--

CREATE TABLE `mw_session_data` (
  `session_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `page` varchar(255) NOT NULL,
  `handle` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Multiple contexts in a same User:Page map',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` longblob NOT NULL COMMENT 'The actual session data'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_sites`
--

CREATE TABLE `mw_sites` (
  `site_id` int UNSIGNED NOT NULL,
  `site_global_key` varbinary(64) NOT NULL,
  `site_type` varbinary(32) NOT NULL,
  `site_group` varbinary(32) NOT NULL,
  `site_source` varbinary(32) NOT NULL,
  `site_language` varbinary(35) NOT NULL,
  `site_protocol` varbinary(32) NOT NULL,
  `site_domain` varbinary(255) NOT NULL,
  `site_data` blob NOT NULL,
  `site_forward` tinyint(1) NOT NULL,
  `site_config` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_site_identifiers`
--

CREATE TABLE `mw_site_identifiers` (
  `si_site` int UNSIGNED NOT NULL,
  `si_type` varbinary(32) NOT NULL,
  `si_key` varbinary(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_site_stats`
--

CREATE TABLE `mw_site_stats` (
  `ss_row_id` int UNSIGNED NOT NULL,
  `ss_total_edits` bigint UNSIGNED DEFAULT NULL,
  `ss_good_articles` bigint UNSIGNED DEFAULT NULL,
  `ss_total_pages` bigint UNSIGNED DEFAULT NULL,
  `ss_users` bigint UNSIGNED DEFAULT NULL,
  `ss_active_users` bigint UNSIGNED DEFAULT NULL,
  `ss_images` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_slots`
--

CREATE TABLE `mw_slots` (
  `slot_revision_id` bigint UNSIGNED NOT NULL,
  `slot_role_id` smallint UNSIGNED NOT NULL,
  `slot_content_id` bigint UNSIGNED NOT NULL,
  `slot_origin` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_slot_roles`
--

CREATE TABLE `mw_slot_roles` (
  `role_id` int NOT NULL,
  `role_name` varbinary(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_structuralchanges`
--

CREATE TABLE `mw_structuralchanges` (
  `rev_id` int NOT NULL,
  `structure_inserted` int NOT NULL,
  `structure_deleted` int NOT NULL,
  `structure_changed` int NOT NULL,
  `structure_moved` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_templatelinks`
--

CREATE TABLE `mw_templatelinks` (
  `tl_from` int UNSIGNED NOT NULL DEFAULT '0',
  `tl_from_namespace` int NOT NULL DEFAULT '0',
  `tl_target_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_text`
--

CREATE TABLE `mw_text` (
  `old_id` int UNSIGNED NOT NULL,
  `old_text` mediumblob NOT NULL,
  `old_flags` tinyblob NOT NULL
) ENGINE=InnoDB AVG_ROW_LENGTH=10240 DEFAULT CHARSET=binary MAX_ROWS=10000000;

-- --------------------------------------------------------

--
-- Table structure for table `mw_trackbacks`
--

CREATE TABLE `mw_trackbacks` (
  `tb_id` int NOT NULL,
  `tb_page` int DEFAULT NULL,
  `tb_title` varbinary(255) NOT NULL,
  `tb_url` blob NOT NULL,
  `tb_ex` blob,
  `tb_name` varbinary(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_updatelog`
--

CREATE TABLE `mw_updatelog` (
  `ul_key` varbinary(255) NOT NULL,
  `ul_value` blob
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_uploadstash`
--

CREATE TABLE `mw_uploadstash` (
  `us_id` int UNSIGNED NOT NULL,
  `us_user` int UNSIGNED NOT NULL,
  `us_key` varbinary(255) NOT NULL,
  `us_orig_path` varbinary(255) NOT NULL,
  `us_path` varbinary(255) NOT NULL,
  `us_source_type` varbinary(50) DEFAULT NULL,
  `us_timestamp` binary(14) NOT NULL,
  `us_status` varbinary(50) NOT NULL,
  `us_size` int UNSIGNED NOT NULL,
  `us_sha1` varbinary(31) NOT NULL,
  `us_mime` varbinary(255) DEFAULT NULL,
  `us_media_type` enum('UNKNOWN','BITMAP','DRAWING','AUDIO','VIDEO','MULTIMEDIA','OFFICE','TEXT','EXECUTABLE','ARCHIVE','3D') DEFAULT NULL,
  `us_image_width` int UNSIGNED DEFAULT NULL,
  `us_image_height` int UNSIGNED DEFAULT NULL,
  `us_image_bits` smallint UNSIGNED DEFAULT NULL,
  `us_chunk_inx` int UNSIGNED DEFAULT NULL,
  `us_props` blob
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user`
--

CREATE TABLE `mw_user` (
  `user_id` int UNSIGNED NOT NULL,
  `user_name` varbinary(255) NOT NULL DEFAULT '',
  `user_real_name` varbinary(255) NOT NULL DEFAULT '',
  `first_name` varbinary(256) NOT NULL,
  `middle_name` varbinary(256) NOT NULL,
  `last_name` varbinary(256) NOT NULL,
  `prev_first_name` varbinary(256) NOT NULL,
  `prev_last_name` varbinary(256) NOT NULL,
  `employee_id` int NOT NULL,
  `honorific` varbinary(16) NOT NULL,
  `language` varbinary(32) NOT NULL,
  `user_type` varchar(32) NOT NULL,
  `user_twitter` varbinary(255) NOT NULL,
  `user_website` varbinary(1024) NOT NULL,
  `user_linkedin` varbinary(1024) NOT NULL,
  `user_facebook` varbinary(1024) NOT NULL,
  `user_google_scholar` varbinary(1024) NOT NULL,
  `user_orcid` varchar(1024) NOT NULL,
  `user_scopus` varchar(1024) NOT NULL,
  `user_researcherid` varchar(1024) NOT NULL,
  `user_office` varbinary(32) NOT NULL,
  `url` varbinary(1024) NOT NULL,
  `user_public_profile` text,
  `user_private_profile` text,
  `user_gender` varbinary(32) NOT NULL,
  `user_pronouns` varchar(32) NOT NULL,
  `user_birth_date` varbinary(32) NOT NULL,
  `user_indigenous_status` varbinary(32) NOT NULL,
  `user_minority_status` varbinary(32) NOT NULL,
  `user_disability_status` varbinary(32) NOT NULL,
  `user_ethnicity` varchar(64) NOT NULL,
  `user_nationality` varbinary(64) NOT NULL,
  `user_stakeholder` varbinary(64) NOT NULL,
  `user_ecr` varbinary(128) NOT NULL,
  `user_agencies` varchar(128) NOT NULL,
  `user_mitacs` varchar(128) NOT NULL,
  `user_crc` blob NOT NULL,
  `user_extra` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `user_password` tinyblob NOT NULL,
  `user_newpassword` tinyblob NOT NULL,
  `user_newpass_time` binary(14) DEFAULT NULL,
  `user_email` tinyblob NOT NULL,
  `user_touched` binary(14) NOT NULL,
  `user_token` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `user_email_authenticated` binary(14) DEFAULT NULL,
  `user_email_token` binary(32) DEFAULT NULL,
  `user_email_token_expires` binary(14) DEFAULT NULL,
  `user_registration` binary(14) DEFAULT NULL,
  `user_editcount` int UNSIGNED DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `candidate` int NOT NULL,
  `user_password_expires` varbinary(14) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_aliases`
--

CREATE TABLE `mw_user_aliases` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `alias` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_autocreate_serial`
--

CREATE TABLE `mw_user_autocreate_serial` (
  `uas_shard` int UNSIGNED NOT NULL,
  `uas_value` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_former_groups`
--

CREATE TABLE `mw_user_former_groups` (
  `ufg_user` int UNSIGNED NOT NULL DEFAULT '0',
  `ufg_group` varbinary(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_google_user`
--

CREATE TABLE `mw_user_google_user` (
  `user_googleid` decimal(25,0) UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_groups`
--

CREATE TABLE `mw_user_groups` (
  `ug_user` int UNSIGNED NOT NULL DEFAULT '0',
  `ug_group` varbinary(255) NOT NULL DEFAULT '',
  `ug_expiry` varbinary(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_newtalk`
--

CREATE TABLE `mw_user_newtalk` (
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_ip` varbinary(40) NOT NULL DEFAULT '',
  `user_last_timestamp` binary(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_user_properties`
--

CREATE TABLE `mw_user_properties` (
  `up_user` int UNSIGNED NOT NULL,
  `up_property` varbinary(255) NOT NULL,
  `up_value` blob
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_auth_ids`
--

CREATE TABLE `mw_virtu_auth_ids` (
  `pub_id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_experience`
--

CREATE TABLE `mw_virtu_experience` (
  `user_id` int NOT NULL,
  `exp` int DEFAULT NULL,
  `connections` int DEFAULT NULL,
  `products` int DEFAULT NULL,
  `num_products` int DEFAULT NULL,
  `status` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_virtu_pub_auths`
--

CREATE TABLE `mw_virtu_pub_auths` (
  `pub_id` int NOT NULL,
  `auth_ids` char(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mw_watchlist`
--

CREATE TABLE `mw_watchlist` (
  `wl_id` int UNSIGNED NOT NULL,
  `wl_user` int UNSIGNED NOT NULL,
  `wl_namespace` int NOT NULL DEFAULT '0',
  `wl_title` varbinary(255) NOT NULL DEFAULT '',
  `wl_notificationtimestamp` binary(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `mw_watchlist_expiry`
--

CREATE TABLE `mw_watchlist_expiry` (
  `we_item` int UNSIGNED NOT NULL,
  `we_expiry` binary(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=binary;

-- --------------------------------------------------------

--
-- Table structure for table `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_actor`
--

CREATE TABLE `sociql_actor` (
  `id` int NOT NULL,
  `site_fk` int NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `real_name` varchar(30) DEFAULT NULL,
  `query` text NOT NULL,
  `actor_id` varchar(20) NOT NULL DEFAULT '',
  `url` varchar(255) DEFAULT NULL,
  `url_required_prop` varchar(20) DEFAULT NULL,
  `map_x_prop` int DEFAULT NULL,
  `map_y_prop` int DEFAULT NULL,
  `ont_entity` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_map_actors`
--

CREATE TABLE `sociql_map_actors` (
  `user_id` int NOT NULL DEFAULT '0',
  `map` varchar(30) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_ent`
--

CREATE TABLE `sociql_ontology_ent` (
  `id` int NOT NULL,
  `name` varchar(20) NOT NULL,
  `level` int NOT NULL DEFAULT '1',
  `upper_entity` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_prop`
--

CREATE TABLE `sociql_ontology_prop` (
  `id` int NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `entity_fk` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_ontology_rel`
--

CREATE TABLE `sociql_ontology_rel` (
  `id` int NOT NULL,
  `from_entity` int NOT NULL,
  `to_entity` int NOT NULL,
  `type` varchar(1) NOT NULL DEFAULT 'A',
  `name` varchar(20) DEFAULT NULL,
  `upper_level` int DEFAULT NULL,
  `level` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_property`
--

CREATE TABLE `sociql_property` (
  `id` int NOT NULL,
  `actor_fk` int NOT NULL DEFAULT '0',
  `relation_fk` int DEFAULT '0',
  `name` varchar(20) NOT NULL DEFAULT '',
  `real_name` varchar(20) NOT NULL DEFAULT '',
  `query` text NOT NULL,
  `queriable` smallint NOT NULL DEFAULT '1',
  `optimizable` tinyint(1) NOT NULL DEFAULT '1',
  `table_name` varchar(50) NOT NULL,
  `type` varchar(11) NOT NULL DEFAULT 'nominal',
  `sortable` smallint NOT NULL DEFAULT '1',
  `significant` smallint NOT NULL DEFAULT '0',
  `sparql` varchar(255) DEFAULT NULL,
  `fb_disj_query` text,
  `ont_property` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_relation`
--

CREATE TABLE `sociql_relation` (
  `id` int NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  `property1_fk` int NOT NULL DEFAULT '0',
  `real_name1` varchar(20) NOT NULL DEFAULT '',
  `property2_fk` int NOT NULL DEFAULT '0',
  `real_name2` varchar(20) NOT NULL DEFAULT '',
  `query` text NOT NULL,
  `direction` smallint NOT NULL DEFAULT '2',
  `fb_disj_query` text,
  `cardinality` varchar(3) NOT NULL DEFAULT 'N-N',
  `ont_relation` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_requiredprop`
--

CREATE TABLE `sociql_requiredprop` (
  `id` int NOT NULL,
  `property_fk` int NOT NULL DEFAULT '0',
  `requiredset_fk` int NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_requiredset`
--

CREATE TABLE `sociql_requiredset` (
  `id` int NOT NULL,
  `actor_fk` int NOT NULL DEFAULT '0',
  `name` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_same`
--

CREATE TABLE `sociql_same` (
  `reason_id` int NOT NULL DEFAULT '0',
  `facebook_id` varchar(20) DEFAULT NULL,
  `dbpedia_id` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sociql_site`
--

CREATE TABLE `sociql_site` (
  `id` int NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  `endpoint` varchar(255) DEFAULT NULL,
  `max_store` int NOT NULL DEFAULT '-1',
  `type` varchar(20) NOT NULL DEFAULT 'sql',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `prefixes` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_messages`
--

CREATE TABLE `wikidev_messages` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `body` text NOT NULL,
  `author` varchar(100) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `subject` text NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `mid_header` text NOT NULL,
  `refid_header` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_projects`
--

CREATE TABLE `wikidev_projects` (
  `projectid` int NOT NULL,
  `mailListName` varchar(255) NOT NULL,
  `public` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_projects_rules`
--

CREATE TABLE `wikidev_projects_rules` (
  `id` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `project_id` int NOT NULL,
  `value` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `wikidev_unsubs`
--

CREATE TABLE `wikidev_unsubs` (
  `user_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grand_acknowledgements`
--
ALTER TABLE `grand_acknowledgements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `md5` (`md5`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_action_plan`
--
ALTER TABLE `grand_action_plan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `grand_activities`
--
ALTER TABLE `grand_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order` (`order`);

--
-- Indexes for table `grand_allocations`
--
ALTER TABLE `grand_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `year` (`year`);

--
-- Indexes for table `grand_alumni`
--
ALTER TABLE `grand_alumni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_avoid_categories`
--
ALTER TABLE `grand_avoid_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_avoid_expert_event`
--
ALTER TABLE `grand_avoid_expert_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_avoid_resources`
--
ALTER TABLE `grand_avoid_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_bibliography`
--
ALTER TABLE `grand_bibliography`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`),
  ADD KEY `title` (`title`(255)),
  ADD KEY `thread_id` (`thread_id`);

--
-- Indexes for table `grand_boards`
--
ALTER TABLE `grand_boards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_bsi_postings`
--
ALTER TABLE `grand_bsi_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_ccv`
--
ALTER TABLE `grand_ccv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_clipboard`
--
ALTER TABLE `grand_clipboard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_collaborations`
--
ALTER TABLE `grand_collaborations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_name` (`organization_name`(255)),
  ADD KEY `year` (`year`),
  ADD KEY `knowledge_user` (`knowledge_user`),
  ADD KEY `access_id` (`access_id`),
  ADD KEY `leverage` (`leverage`);

--
-- Indexes for table `grand_collaboration_files`
--
ALTER TABLE `grand_collaboration_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collaboration_id` (`collaboration_id`);

--
-- Indexes for table `grand_collaboration_projects`
--
ALTER TABLE `grand_collaboration_projects`
  ADD PRIMARY KEY (`collaboration_id`,`project_id`);

--
-- Indexes for table `grand_conference_attendance`
--
ALTER TABLE `grand_conference_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `grand_contributions`
--
ALTER TABLE `grand_contributions`
  ADD PRIMARY KEY (`rev_id`),
  ADD KEY `id` (`id`),
  ADD KEY `access_id` (`access_id`);

--
-- Indexes for table `grand_contributions_partners`
--
ALTER TABLE `grand_contributions_partners`
  ADD PRIMARY KEY (`contribution_id`,`partner`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `grand_contributions_projects`
--
ALTER TABLE `grand_contributions_projects`
  ADD PRIMARY KEY (`contribution_id`,`project_id`);

--
-- Indexes for table `grand_contribution_edits`
--
ALTER TABLE `grand_contribution_edits`
  ADD PRIMARY KEY (`id`,`user_id`);

--
-- Indexes for table `grand_crm_contact`
--
ALTER TABLE `grand_crm_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `title` (`title`(255));

--
-- Indexes for table `grand_crm_opportunity`
--
ALTER TABLE `grand_crm_opportunity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact` (`contact`),
  ADD KEY `owner` (`owner`);

--
-- Indexes for table `grand_crm_projects`
--
ALTER TABLE `grand_crm_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_crm_task`
--
ALTER TABLE `grand_crm_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunity` (`opportunity`),
  ADD KEY `assignee` (`assignee`);

--
-- Indexes for table `grand_data_collection`
--
ALTER TABLE `grand_data_collection`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `page` (`page`);

--
-- Indexes for table `grand_delegate`
--
ALTER TABLE `grand_delegate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delegate` (`delegate`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_diversity`
--
ALTER TABLE `grand_diversity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_diversity_2018`
--
ALTER TABLE `grand_diversity_2018`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_elite_postings`
--
ALTER TABLE `grand_elite_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_ethics`
--
ALTER TABLE `grand_ethics`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `grand_eval`
--
ALTER TABLE `grand_eval`
  ADD PRIMARY KEY (`user_id`,`sub_id`,`sub2_id`,`type`,`year`);

--
-- Indexes for table `grand_eval_conflicts`
--
ALTER TABLE `grand_eval_conflicts`
  ADD PRIMARY KEY (`eval_id`,`sub_id`,`type`,`year`);

--
-- Indexes for table `grand_event_postings`
--
ALTER TABLE `grand_event_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_event_registration`
--
ALTER TABLE `grand_event_registration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `grand_feature_votes`
--
ALTER TABLE `grand_feature_votes`
  ADD KEY `votes_p_id` (`votes_p_id`,`votes_u_id`);

--
-- Indexes for table `grand_fitbit_data`
--
ALTER TABLE `grand_fitbit_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `grand_freeze`
--
ALTER TABLE `grand_freeze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `feature` (`feature`);

--
-- Indexes for table `grand_gamification`
--
ALTER TABLE `grand_gamification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`);

--
-- Indexes for table `grand_gs_citations`
--
ALTER TABLE `grand_gs_citations`
  ADD PRIMARY KEY (`user_id`,`year`);

--
-- Indexes for table `grand_ignored_duplicates`
--
ALTER TABLE `grand_ignored_duplicates`
  ADD PRIMARY KEY (`id1`,`id2`,`type`);

--
-- Indexes for table `grand_illegal_authors`
--
ALTER TABLE `grand_illegal_authors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_job_postings`
--
ALTER TABLE `grand_job_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_title` (`job_title`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `job_title_fr` (`job_title_fr`);

--
-- Indexes for table `grand_journals`
--
ALTER TABLE `grand_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issn` (`issn`),
  ADD KEY `eissn` (`eissn`);

--
-- Indexes for table `grand_lims_contact`
--
ALTER TABLE `grand_lims_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `title` (`title`),
  ADD KEY `owner` (`owner`);

--
-- Indexes for table `grand_lims_files`
--
ALTER TABLE `grand_lims_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunity_id` (`opportunity_id`);

--
-- Indexes for table `grand_lims_opportunity`
--
ALTER TABLE `grand_lims_opportunity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact` (`contact`),
  ADD KEY `owner` (`owner`),
  ADD KEY `project` (`project`);

--
-- Indexes for table `grand_lims_projects`
--
ALTER TABLE `grand_lims_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_lims_task`
--
ALTER TABLE `grand_lims_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunity` (`opportunity`),
  ADD KEY `assignee` (`assignee`);

--
-- Indexes for table `grand_list_request`
--
ALTER TABLE `grand_list_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created` (`created`),
  ADD KEY `ignore` (`ignore`);

--
-- Indexes for table `grand_loi`
--
ALTER TABLE `grand_loi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_managed_people`
--
ALTER TABLE `grand_managed_people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `managed_id` (`managed_id`);

--
-- Indexes for table `grand_materials`
--
ALTER TABLE `grand_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `title` (`title`(767)),
  ADD KEY `type` (`type`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `grand_materials_keywords`
--
ALTER TABLE `grand_materials_keywords`
  ADD PRIMARY KEY (`material_id`,`keyword`);

--
-- Indexes for table `grand_materials_people`
--
ALTER TABLE `grand_materials_people`
  ADD PRIMARY KEY (`material_id`,`user_id`);

--
-- Indexes for table `grand_materials_projects`
--
ALTER TABLE `grand_materials_projects`
  ADD PRIMARY KEY (`material_id`,`project_id`);

--
-- Indexes for table `grand_milestones`
--
ALTER TABLE `grand_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `milestone_id` (`milestone_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `edited_by` (`edited_by`),
  ADD KEY `status` (`status`),
  ADD KEY `title` (`title`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `order` (`order`);

--
-- Indexes for table `grand_milestones_people`
--
ALTER TABLE `grand_milestones_people`
  ADD PRIMARY KEY (`milestone_id`,`user_id`);

--
-- Indexes for table `grand_movedOn`
--
ALTER TABLE `grand_movedOn`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_news_postings`
--
ALTER TABLE `grand_news_postings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_notifications`
--
ALTER TABLE `grand_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `time` (`time`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_partners`
--
ALTER TABLE `grand_partners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization` (`organization`,`type`);

--
-- Indexes for table `grand_pdf_index`
--
ALTER TABLE `grand_pdf_index`
  ADD UNIQUE KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`,`sub_id`,`type`);

--
-- Indexes for table `grand_pdf_report`
--
ALTER TABLE `grand_pdf_report`
  ADD UNIQUE KEY `report_id` (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token` (`token`),
  ADD KEY `type` (`type`),
  ADD KEY `submission_user_id` (`submission_user_id`),
  ADD KEY `generation_user_id` (`generation_user_id`),
  ADD KEY `year` (`year`),
  ADD KEY `proj_id` (`proj_id`);

--
-- Indexes for table `grand_person_crdc`
--
ALTER TABLE `grand_person_crdc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `grand_person_keywords`
--
ALTER TABLE `grand_person_keywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `keyword` (`keyword`);

--
-- Indexes for table `grand_pmm_files`
--
ALTER TABLE `grand_pmm_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunity_id` (`opportunity_id`);

--
-- Indexes for table `grand_pmm_task`
--
ALTER TABLE `grand_pmm_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `opportunity` (`opportunity`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_pmm_task_assignees`
--
ALTER TABLE `grand_pmm_task_assignees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_id_2` (`task_id`,`assignee`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `assignee` (`assignee`);

--
-- Indexes for table `grand_pmm_task_assignees_comments`
--
ALTER TABLE `grand_pmm_task_assignees_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `grand_poll`
--
ALTER TABLE `grand_poll`
  ADD PRIMARY KEY (`poll_id`),
  ADD KEY `collection_id` (`collection_id`);

--
-- Indexes for table `grand_poll_collection`
--
ALTER TABLE `grand_poll_collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `author` (`author_id`);

--
-- Indexes for table `grand_poll_groups`
--
ALTER TABLE `grand_poll_groups`
  ADD PRIMARY KEY (`group_name`,`collection_id`);

--
-- Indexes for table `grand_poll_options`
--
ALTER TABLE `grand_poll_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- Indexes for table `grand_poll_votes`
--
ALTER TABLE `grand_poll_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD KEY `user_id` (`user_id`,`option_id`);

--
-- Indexes for table `grand_positions`
--
ALTER TABLE `grand_positions`
  ADD PRIMARY KEY (`position_id`),
  ADD KEY `position` (`position`);

--
-- Indexes for table `grand_posting_images`
--
ALTER TABLE `grand_posting_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tbl` (`tbl`),
  ADD KEY `posting_id` (`posting_id`),
  ADD KEY `index` (`index`);

--
-- Indexes for table `grand_posts`
--
ALTER TABLE `grand_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `user_id` (`user_id`);
ALTER TABLE `grand_posts` ADD FULLTEXT KEY `search` (`search`);

--
-- Indexes for table `grand_products`
--
ALTER TABLE `grand_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `title` (`title`),
  ADD KEY `type` (`type`),
  ADD KEY `category` (`category`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `access_id` (`access_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `ccv_id` (`ccv_id`),
  ADD KEY `bibtex_id` (`bibtex_id`),
  ADD KEY `access` (`access`);
ALTER TABLE `grand_products` ADD FULLTEXT KEY `description` (`description`);

--
-- Indexes for table `grand_product_authors`
--
ALTER TABLE `grand_product_authors`
  ADD PRIMARY KEY (`author`,`product_id`),
  ADD KEY `order` (`order`);

--
-- Indexes for table `grand_product_projects`
--
ALTER TABLE `grand_product_projects`
  ADD PRIMARY KEY (`product_id`,`project_id`);

--
-- Indexes for table `grand_product_tags`
--
ALTER TABLE `grand_product_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `grand_project`
--
ALTER TABLE `grand_project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `grand_projects`
--
ALTER TABLE `grand_projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_project_challenges`
--
ALTER TABLE `grand_project_challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `grand_project_champions`
--
ALTER TABLE `grand_project_champions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_project_contact`
--
ALTER TABLE `grand_project_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proj_id` (`proj_id`);

--
-- Indexes for table `grand_project_descriptions`
--
ALTER TABLE `grand_project_descriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `evolution_id` (`evolution_id`);

--
-- Indexes for table `grand_project_evolution`
--
ALTER TABLE `grand_project_evolution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_id` (`last_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `new_id` (`new_id`);

--
-- Indexes for table `grand_project_leaders`
--
ALTER TABLE `grand_project_leaders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_project_members`
--
ALTER TABLE `grand_project_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_project_programs`
--
ALTER TABLE `grand_project_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proj_id` (`proj_id`);

--
-- Indexes for table `grand_project_status`
--
ALTER TABLE `grand_project_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evolution_id` (`evolution_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `grand_provinces`
--
ALTER TABLE `grand_provinces`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_recorded_images`
--
ALTER TABLE `grand_recorded_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person` (`user_id`);

--
-- Indexes for table `grand_recordings`
--
ALTER TABLE `grand_recordings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person` (`user_id`),
  ADD KEY `storyToken` (`storyToken`);

--
-- Indexes for table `grand_relations`
--
ALTER TABLE `grand_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user1`,`user2`);

--
-- Indexes for table `grand_report_backup`
--
ALTER TABLE `grand_report_backup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `person_id` (`person_id`);

--
-- Indexes for table `grand_report_blobs`
--
ALTER TABLE `grand_report_blobs`
  ADD PRIMARY KEY (`blob_id`),
  ADD KEY `md5` (`md5`),
  ADD KEY `year` (`year`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `proj_id` (`proj_id`),
  ADD KEY `rp_type` (`rp_type`),
  ADD KEY `rp_section` (`rp_section`),
  ADD KEY `rp_item` (`rp_item`),
  ADD KEY `rp_subitem` (`rp_subitem`);

--
-- Indexes for table `grand_report_blobs_impersonated`
--
ALTER TABLE `grand_report_blobs_impersonated`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blob_id` (`blob_id`);

--
-- Indexes for table `grand_roles`
--
ALTER TABLE `grand_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user_id`,`role`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `grand_role_projects`
--
ALTER TABLE `grand_role_projects`
  ADD PRIMARY KEY (`role_id`,`project_id`);

--
-- Indexes for table `grand_role_subtype`
--
ALTER TABLE `grand_role_subtype`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_rss_articles`
--
ALTER TABLE `grand_rss_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feed` (`feed`),
  ADD KEY `rss_id` (`rss_id`);

--
-- Indexes for table `grand_rss_feeds`
--
ALTER TABLE `grand_rss_feeds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_themes`
--
ALTER TABLE `grand_themes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phase` (`phase`);

--
-- Indexes for table `grand_theme_leaders`
--
ALTER TABLE `grand_theme_leaders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_theses`
--
ALTER TABLE `grand_theses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grand_threads`
--
ALTER TABLE `grand_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `title` (`title`(255)),
  ADD KEY `board_id` (`board_id`),
  ADD KEY `stickied` (`stickied`);

--
-- Indexes for table `grand_top_products`
--
ALTER TABLE `grand_top_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `obj_id` (`obj_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `grand_universities`
--
ALTER TABLE `grand_universities`
  ADD PRIMARY KEY (`university_id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indexes for table `grand_uofa_news`
--
ALTER TABLE `grand_uofa_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);
ALTER TABLE `grand_uofa_news` ADD FULLTEXT KEY `title` (`title`);

--
-- Indexes for table `grand_user_addresses`
--
ALTER TABLE `grand_user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_user_languages`
--
ALTER TABLE `grand_user_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_user_metrics`
--
ALTER TABLE `grand_user_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_user_request`
--
ALTER TABLE `grand_user_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created` (`created`),
  ADD KEY `ignore` (`ignore`);

--
-- Indexes for table `grand_user_telephone`
--
ALTER TABLE `grand_user_telephone`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `grand_user_university`
--
ALTER TABLE `grand_user_university`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `university_id` (`university_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `end_date` (`end_date`),
  ADD KEY `start_date` (`start_date`);

--
-- Indexes for table `mw_actor`
--
ALTER TABLE `mw_actor`
  ADD PRIMARY KEY (`actor_id`),
  ADD UNIQUE KEY `actor_name` (`actor_name`),
  ADD UNIQUE KEY `actor_user` (`actor_user`);

--
-- Indexes for table `mw_an_extranamespaces`
--
ALTER TABLE `mw_an_extranamespaces`
  ADD PRIMARY KEY (`nsId`),
  ADD UNIQUE KEY `nsName` (`nsName`),
  ADD UNIQUE KEY `nsUser` (`nsUser`);

--
-- Indexes for table `mw_an_pagepermissions`
--
ALTER TABLE `mw_an_pagepermissions`
  ADD PRIMARY KEY (`page_id`,`group_id`);

--
-- Indexes for table `mw_an_pageratings`
--
ALTER TABLE `mw_an_pageratings`
  ADD UNIQUE KEY `page_id` (`page_id`,`user_id`);

--
-- Indexes for table `mw_an_pagestorate`
--
ALTER TABLE `mw_an_pagestorate`
  ADD UNIQUE KEY `id` (`id`,`type`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `mw_an_page_visits`
--
ALTER TABLE `mw_an_page_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_name` (`user_name`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `page_namespace` (`page_namespace`);

--
-- Indexes for table `mw_an_text_replacement`
--
ALTER TABLE `mw_an_text_replacement`
  ADD PRIMARY KEY (`match_text`);

--
-- Indexes for table `mw_an_upload_permissions`
--
ALTER TABLE `mw_an_upload_permissions`
  ADD PRIMARY KEY (`upload_name`);

--
-- Indexes for table `mw_an_upload_perm_temp`
--
ALTER TABLE `mw_an_upload_perm_temp`
  ADD PRIMARY KEY (`upload_name`);

--
-- Indexes for table `mw_an_vtracker_diff_results`
--
ALTER TABLE `mw_an_vtracker_diff_results`
  ADD PRIMARY KEY (`rev_id_old`,`rev_id_new`);

--
-- Indexes for table `mw_archive`
--
ALTER TABLE `mw_archive`
  ADD PRIMARY KEY (`ar_id`),
  ADD UNIQUE KEY `ar_revid_uniq` (`ar_rev_id`),
  ADD KEY `ar_actor_timestamp` (`ar_actor`,`ar_timestamp`),
  ADD KEY `ar_name_title_timestamp` (`ar_namespace`,`ar_title`,`ar_timestamp`);

--
-- Indexes for table `mw_bot_passwords`
--
ALTER TABLE `mw_bot_passwords`
  ADD PRIMARY KEY (`bp_user`,`bp_app_id`);

--
-- Indexes for table `mw_category`
--
ALTER TABLE `mw_category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `cat_title` (`cat_title`),
  ADD KEY `cat_pages` (`cat_pages`);

--
-- Indexes for table `mw_categorylinks`
--
ALTER TABLE `mw_categorylinks`
  ADD PRIMARY KEY (`cl_from`,`cl_to`),
  ADD KEY `cl_timestamp` (`cl_to`,`cl_timestamp`),
  ADD KEY `cl_sortkey` (`cl_to`,`cl_type`,`cl_sortkey`,`cl_from`),
  ADD KEY `cl_collation_ext` (`cl_collation`,`cl_to`,`cl_type`,`cl_from`);

--
-- Indexes for table `mw_change_tag`
--
ALTER TABLE `mw_change_tag`
  ADD PRIMARY KEY (`ct_id`),
  ADD UNIQUE KEY `ct_rc_tag_id` (`ct_rc_id`,`ct_tag_id`),
  ADD UNIQUE KEY `ct_log_tag_id` (`ct_log_id`,`ct_tag_id`),
  ADD UNIQUE KEY `ct_rev_tag_id` (`ct_rev_id`,`ct_tag_id`),
  ADD KEY `ct_tag_id_id` (`ct_tag_id`,`ct_rc_id`,`ct_rev_id`,`ct_log_id`);

--
-- Indexes for table `mw_change_tag_def`
--
ALTER TABLE `mw_change_tag_def`
  ADD PRIMARY KEY (`ctd_id`),
  ADD UNIQUE KEY `ctd_name` (`ctd_name`),
  ADD KEY `ctd_count` (`ctd_count`),
  ADD KEY `ctd_user_defined` (`ctd_user_defined`);

--
-- Indexes for table `mw_comment`
--
ALTER TABLE `mw_comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `comment_hash` (`comment_hash`);

--
-- Indexes for table `mw_content`
--
ALTER TABLE `mw_content`
  ADD PRIMARY KEY (`content_id`);

--
-- Indexes for table `mw_content_models`
--
ALTER TABLE `mw_content_models`
  ADD PRIMARY KEY (`model_id`),
  ADD UNIQUE KEY `model_name` (`model_name`);

--
-- Indexes for table `mw_externallinks`
--
ALTER TABLE `mw_externallinks`
  ADD PRIMARY KEY (`el_id`),
  ADD KEY `el_from` (`el_from`,`el_to`(40)),
  ADD KEY `el_to` (`el_to`(60),`el_from`),
  ADD KEY `el_index` (`el_index`(60)),
  ADD KEY `el_index_60` (`el_index_60`,`el_id`),
  ADD KEY `el_from_index_60` (`el_from`,`el_index_60`,`el_id`);

--
-- Indexes for table `mw_externallinks_all`
--
ALTER TABLE `mw_externallinks_all`
  ADD PRIMARY KEY (`page_id`,`url`(255));

--
-- Indexes for table `mw_filearchive`
--
ALTER TABLE `mw_filearchive`
  ADD PRIMARY KEY (`fa_id`),
  ADD KEY `fa_name` (`fa_name`,`fa_timestamp`),
  ADD KEY `fa_storage_group` (`fa_storage_group`,`fa_storage_key`),
  ADD KEY `fa_deleted_timestamp` (`fa_deleted_timestamp`),
  ADD KEY `fa_sha1` (`fa_sha1`(10)),
  ADD KEY `fa_actor_timestamp` (`fa_actor`,`fa_timestamp`);

--
-- Indexes for table `mw_googlelogin_allowed_domains`
--
ALTER TABLE `mw_googlelogin_allowed_domains`
  ADD PRIMARY KEY (`gl_allowed_domain_id`),
  ADD KEY `gl_allowed_domain` (`gl_allowed_domain`);

--
-- Indexes for table `mw_image`
--
ALTER TABLE `mw_image`
  ADD PRIMARY KEY (`img_name`),
  ADD KEY `img_size` (`img_size`),
  ADD KEY `img_timestamp` (`img_timestamp`),
  ADD KEY `img_sha1` (`img_sha1`),
  ADD KEY `img_media_mime` (`img_media_type`,`img_major_mime`,`img_minor_mime`),
  ADD KEY `img_actor_timestamp` (`img_actor`,`img_timestamp`);

--
-- Indexes for table `mw_imagelinks`
--
ALTER TABLE `mw_imagelinks`
  ADD PRIMARY KEY (`il_from`,`il_to`),
  ADD KEY `il_backlinks_namespace` (`il_from_namespace`,`il_to`,`il_from`),
  ADD KEY `il_to` (`il_to`,`il_from`);

--
-- Indexes for table `mw_interwiki`
--
ALTER TABLE `mw_interwiki`
  ADD PRIMARY KEY (`iw_prefix`);

--
-- Indexes for table `mw_ipblocks`
--
ALTER TABLE `mw_ipblocks`
  ADD PRIMARY KEY (`ipb_id`),
  ADD UNIQUE KEY `ipb_address_unique` (`ipb_address`(255),`ipb_user`,`ipb_auto`),
  ADD KEY `ipb_user` (`ipb_user`),
  ADD KEY `ipb_range` (`ipb_range_start`(8),`ipb_range_end`(8)),
  ADD KEY `ipb_timestamp` (`ipb_timestamp`),
  ADD KEY `ipb_expiry` (`ipb_expiry`),
  ADD KEY `ipb_parent_block_id` (`ipb_parent_block_id`);

--
-- Indexes for table `mw_ipblocks_restrictions`
--
ALTER TABLE `mw_ipblocks_restrictions`
  ADD PRIMARY KEY (`ir_ipb_id`,`ir_type`,`ir_value`),
  ADD KEY `ir_type_value` (`ir_type`,`ir_value`);

--
-- Indexes for table `mw_ip_changes`
--
ALTER TABLE `mw_ip_changes`
  ADD PRIMARY KEY (`ipc_rev_id`),
  ADD KEY `ipc_rev_timestamp` (`ipc_rev_timestamp`),
  ADD KEY `ipc_hex_time` (`ipc_hex`,`ipc_rev_timestamp`);

--
-- Indexes for table `mw_iwlinks`
--
ALTER TABLE `mw_iwlinks`
  ADD PRIMARY KEY (`iwl_from`,`iwl_prefix`,`iwl_title`),
  ADD KEY `iwl_prefix_title_from` (`iwl_prefix`,`iwl_title`,`iwl_from`),
  ADD KEY `iwl_prefix_from_title` (`iwl_prefix`,`iwl_from`,`iwl_title`);

--
-- Indexes for table `mw_job`
--
ALTER TABLE `mw_job`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `job_cmd` (`job_cmd`,`job_namespace`,`job_title`),
  ADD KEY `job_timestamp` (`job_timestamp`),
  ADD KEY `job_sha1` (`job_sha1`),
  ADD KEY `job_cmd_token` (`job_cmd`,`job_token`,`job_random`),
  ADD KEY `job_cmd_token_id` (`job_cmd`,`job_token`,`job_id`);

--
-- Indexes for table `mw_l10n_cache`
--
ALTER TABLE `mw_l10n_cache`
  ADD PRIMARY KEY (`lc_lang`,`lc_key`);

--
-- Indexes for table `mw_langlinks`
--
ALTER TABLE `mw_langlinks`
  ADD PRIMARY KEY (`ll_from`,`ll_lang`),
  ADD KEY `ll_lang` (`ll_lang`,`ll_title`);

--
-- Indexes for table `mw_linktarget`
--
ALTER TABLE `mw_linktarget`
  ADD PRIMARY KEY (`lt_id`),
  ADD UNIQUE KEY `lt_namespace_title` (`lt_namespace`,`lt_title`);

--
-- Indexes for table `mw_logging`
--
ALTER TABLE `mw_logging`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `log_page_id_time` (`log_page`,`log_timestamp`),
  ADD KEY `log_actor_type_time` (`log_actor`,`log_type`,`log_timestamp`),
  ADD KEY `log_type_action` (`log_type`,`log_action`,`log_timestamp`),
  ADD KEY `log_type_time` (`log_type`,`log_timestamp`),
  ADD KEY `log_actor_time` (`log_actor`,`log_timestamp`),
  ADD KEY `log_page_time` (`log_namespace`,`log_title`,`log_timestamp`),
  ADD KEY `log_times` (`log_timestamp`);

--
-- Indexes for table `mw_log_search`
--
ALTER TABLE `mw_log_search`
  ADD PRIMARY KEY (`ls_field`,`ls_value`,`ls_log_id`),
  ADD KEY `ls_log_id` (`ls_log_id`);

--
-- Indexes for table `mw_math`
--
ALTER TABLE `mw_math`
  ADD UNIQUE KEY `math_inputhash` (`math_inputhash`);

--
-- Indexes for table `mw_module_deps`
--
ALTER TABLE `mw_module_deps`
  ADD PRIMARY KEY (`md_module`,`md_skin`);

--
-- Indexes for table `mw_objectcache`
--
ALTER TABLE `mw_objectcache`
  ADD PRIMARY KEY (`keyname`),
  ADD KEY `exptime` (`exptime`);

--
-- Indexes for table `mw_oldimage`
--
ALTER TABLE `mw_oldimage`
  ADD KEY `oi_name_timestamp` (`oi_name`,`oi_timestamp`),
  ADD KEY `oi_name_archive_name` (`oi_name`,`oi_archive_name`(14)),
  ADD KEY `oi_sha1` (`oi_sha1`),
  ADD KEY `oi_actor_timestamp` (`oi_actor`,`oi_timestamp`),
  ADD KEY `oi_timestamp` (`oi_timestamp`);

--
-- Indexes for table `mw_openid_connect`
--
ALTER TABLE `mw_openid_connect`
  ADD PRIMARY KEY (`oidc_user`),
  ADD KEY `openid_connect_subject` (`oidc_subject`(50),`oidc_issuer`(50));

--
-- Indexes for table `mw_page`
--
ALTER TABLE `mw_page`
  ADD PRIMARY KEY (`page_id`),
  ADD UNIQUE KEY `page_name_title` (`page_namespace`,`page_title`),
  ADD KEY `page_random` (`page_random`),
  ADD KEY `page_len` (`page_len`),
  ADD KEY `page_redirect_namespace_len` (`page_is_redirect`,`page_namespace`,`page_len`);

--
-- Indexes for table `mw_pagelinks`
--
ALTER TABLE `mw_pagelinks`
  ADD PRIMARY KEY (`pl_from`,`pl_namespace`,`pl_title`),
  ADD KEY `pl_backlinks_namespace` (`pl_from_namespace`,`pl_namespace`,`pl_title`,`pl_from`),
  ADD KEY `pl_namespace` (`pl_namespace`,`pl_title`,`pl_from`);

--
-- Indexes for table `mw_pagelinks_all`
--
ALTER TABLE `mw_pagelinks_all`
  ADD PRIMARY KEY (`page_id`,`namespace`,`title`);

--
-- Indexes for table `mw_page_props`
--
ALTER TABLE `mw_page_props`
  ADD PRIMARY KEY (`pp_page`,`pp_propname`),
  ADD UNIQUE KEY `pp_propname_page` (`pp_propname`,`pp_page`),
  ADD UNIQUE KEY `pp_propname_sortkey_page` (`pp_propname`,`pp_sortkey`,`pp_page`);

--
-- Indexes for table `mw_page_restrictions`
--
ALTER TABLE `mw_page_restrictions`
  ADD PRIMARY KEY (`pr_id`),
  ADD UNIQUE KEY `pr_pagetype` (`pr_page`,`pr_type`),
  ADD KEY `pr_typelevel` (`pr_type`,`pr_level`),
  ADD KEY `pr_level` (`pr_level`),
  ADD KEY `pr_cascade` (`pr_cascade`);

--
-- Indexes for table `mw_parent_of`
--
ALTER TABLE `mw_parent_of`
  ADD PRIMARY KEY (`sen_id`,`parent_id`);

--
-- Indexes for table `mw_protected_titles`
--
ALTER TABLE `mw_protected_titles`
  ADD PRIMARY KEY (`pt_namespace`,`pt_title`),
  ADD KEY `pt_timestamp` (`pt_timestamp`);

--
-- Indexes for table `mw_querycache`
--
ALTER TABLE `mw_querycache`
  ADD KEY `qc_type` (`qc_type`,`qc_value`);

--
-- Indexes for table `mw_querycachetwo`
--
ALTER TABLE `mw_querycachetwo`
  ADD KEY `qcc_type` (`qcc_type`,`qcc_value`),
  ADD KEY `qcc_title` (`qcc_type`,`qcc_namespace`,`qcc_title`),
  ADD KEY `qcc_titletwo` (`qcc_type`,`qcc_namespacetwo`,`qcc_titletwo`);

--
-- Indexes for table `mw_querycache_info`
--
ALTER TABLE `mw_querycache_info`
  ADD PRIMARY KEY (`qci_type`);

--
-- Indexes for table `mw_recentchanges`
--
ALTER TABLE `mw_recentchanges`
  ADD PRIMARY KEY (`rc_id`),
  ADD KEY `rc_timestamp` (`rc_timestamp`),
  ADD KEY `rc_cur_id` (`rc_cur_id`),
  ADD KEY `rc_ip` (`rc_ip`),
  ADD KEY `rc_name_type_patrolled_timestamp` (`rc_namespace`,`rc_type`,`rc_patrolled`,`rc_timestamp`),
  ADD KEY `rc_ns_actor` (`rc_namespace`,`rc_actor`),
  ADD KEY `rc_actor` (`rc_actor`,`rc_timestamp`),
  ADD KEY `rc_namespace_title_timestamp` (`rc_namespace`,`rc_title`,`rc_timestamp`),
  ADD KEY `rc_this_oldid` (`rc_this_oldid`),
  ADD KEY `rc_new_name_timestamp` (`rc_new`,`rc_namespace`,`rc_timestamp`);

--
-- Indexes for table `mw_redirect`
--
ALTER TABLE `mw_redirect`
  ADD PRIMARY KEY (`rd_from`),
  ADD KEY `rd_ns_title` (`rd_namespace`,`rd_title`,`rd_from`);

--
-- Indexes for table `mw_revision`
--
ALTER TABLE `mw_revision`
  ADD PRIMARY KEY (`rev_id`),
  ADD KEY `rev_timestamp` (`rev_timestamp`),
  ADD KEY `rev_actor_timestamp` (`rev_actor`,`rev_timestamp`,`rev_id`),
  ADD KEY `rev_page_actor_timestamp` (`rev_page`,`rev_actor`,`rev_timestamp`),
  ADD KEY `rev_page_timestamp` (`rev_page`,`rev_timestamp`);

--
-- Indexes for table `mw_revision_comment_temp`
--
ALTER TABLE `mw_revision_comment_temp`
  ADD PRIMARY KEY (`revcomment_rev`,`revcomment_comment_id`),
  ADD UNIQUE KEY `revcomment_rev` (`revcomment_rev`);

--
-- Indexes for table `mw_searchindex`
--
ALTER TABLE `mw_searchindex`
  ADD UNIQUE KEY `si_page` (`si_page`);
ALTER TABLE `mw_searchindex` ADD FULLTEXT KEY `si_title` (`si_title`);
ALTER TABLE `mw_searchindex` ADD FULLTEXT KEY `si_text` (`si_text`);

--
-- Indexes for table `mw_sentence`
--
ALTER TABLE `mw_sentence`
  ADD PRIMARY KEY (`sen_id`),
  ADD UNIQUE KEY `rev_sen` (`rev_id`,`text_offset`,`length`),
  ADD KEY `rev_id` (`rev_id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `page_rev_id` (`page_id`,`rev_id`);

--
-- Indexes for table `mw_session_data`
--
ALTER TABLE `mw_session_data`
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `user_id` (`user_id`,`page`),
  ADD KEY `page` (`page`),
  ADD KEY `handle` (`handle`);

--
-- Indexes for table `mw_sites`
--
ALTER TABLE `mw_sites`
  ADD PRIMARY KEY (`site_id`),
  ADD UNIQUE KEY `site_global_key` (`site_global_key`),
  ADD KEY `site_type` (`site_type`),
  ADD KEY `site_group` (`site_group`),
  ADD KEY `site_source` (`site_source`),
  ADD KEY `site_language` (`site_language`),
  ADD KEY `site_protocol` (`site_protocol`),
  ADD KEY `site_domain` (`site_domain`),
  ADD KEY `site_forward` (`site_forward`);

--
-- Indexes for table `mw_site_identifiers`
--
ALTER TABLE `mw_site_identifiers`
  ADD PRIMARY KEY (`si_type`,`si_key`),
  ADD KEY `si_site` (`si_site`),
  ADD KEY `si_key` (`si_key`);

--
-- Indexes for table `mw_site_stats`
--
ALTER TABLE `mw_site_stats`
  ADD PRIMARY KEY (`ss_row_id`);

--
-- Indexes for table `mw_slots`
--
ALTER TABLE `mw_slots`
  ADD PRIMARY KEY (`slot_revision_id`,`slot_role_id`),
  ADD KEY `slot_revision_origin_role` (`slot_revision_id`,`slot_origin`,`slot_role_id`);

--
-- Indexes for table `mw_slot_roles`
--
ALTER TABLE `mw_slot_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `mw_structuralchanges`
--
ALTER TABLE `mw_structuralchanges`
  ADD PRIMARY KEY (`rev_id`);

--
-- Indexes for table `mw_templatelinks`
--
ALTER TABLE `mw_templatelinks`
  ADD PRIMARY KEY (`tl_from`,`tl_target_id`),
  ADD KEY `tl_target_id` (`tl_target_id`,`tl_from`),
  ADD KEY `tl_backlinks_namespace_target_id` (`tl_from_namespace`,`tl_target_id`,`tl_from`);

--
-- Indexes for table `mw_text`
--
ALTER TABLE `mw_text`
  ADD PRIMARY KEY (`old_id`);

--
-- Indexes for table `mw_trackbacks`
--
ALTER TABLE `mw_trackbacks`
  ADD PRIMARY KEY (`tb_id`),
  ADD KEY `tb_page` (`tb_page`);

--
-- Indexes for table `mw_updatelog`
--
ALTER TABLE `mw_updatelog`
  ADD PRIMARY KEY (`ul_key`);

--
-- Indexes for table `mw_uploadstash`
--
ALTER TABLE `mw_uploadstash`
  ADD PRIMARY KEY (`us_id`),
  ADD UNIQUE KEY `us_key` (`us_key`),
  ADD KEY `us_user` (`us_user`),
  ADD KEY `us_timestamp` (`us_timestamp`);

--
-- Indexes for table `mw_user`
--
ALTER TABLE `mw_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD KEY `user_email_token` (`user_email_token`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `user_email` (`user_email`(50)),
  ADD KEY `candidate` (`candidate`);
ALTER TABLE `mw_user` ADD FULLTEXT KEY `user_public_profile` (`user_public_profile`);
ALTER TABLE `mw_user` ADD FULLTEXT KEY `user_private_profile` (`user_private_profile`);

--
-- Indexes for table `mw_user_aliases`
--
ALTER TABLE `mw_user_aliases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `alias` (`alias`);

--
-- Indexes for table `mw_user_autocreate_serial`
--
ALTER TABLE `mw_user_autocreate_serial`
  ADD PRIMARY KEY (`uas_shard`);

--
-- Indexes for table `mw_user_former_groups`
--
ALTER TABLE `mw_user_former_groups`
  ADD PRIMARY KEY (`ufg_user`,`ufg_group`);

--
-- Indexes for table `mw_user_google_user`
--
ALTER TABLE `mw_user_google_user`
  ADD PRIMARY KEY (`user_googleid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_id_2` (`user_id`);

--
-- Indexes for table `mw_user_groups`
--
ALTER TABLE `mw_user_groups`
  ADD PRIMARY KEY (`ug_user`,`ug_group`),
  ADD KEY `ug_group` (`ug_group`),
  ADD KEY `ug_expiry` (`ug_expiry`);

--
-- Indexes for table `mw_user_newtalk`
--
ALTER TABLE `mw_user_newtalk`
  ADD KEY `un_user_id` (`user_id`),
  ADD KEY `un_user_ip` (`user_ip`);

--
-- Indexes for table `mw_user_properties`
--
ALTER TABLE `mw_user_properties`
  ADD PRIMARY KEY (`up_user`,`up_property`),
  ADD KEY `up_property` (`up_property`);

--
-- Indexes for table `mw_virtu_experience`
--
ALTER TABLE `mw_virtu_experience`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `mw_virtu_pub_auths`
--
ALTER TABLE `mw_virtu_pub_auths`
  ADD PRIMARY KEY (`pub_id`);

--
-- Indexes for table `mw_watchlist`
--
ALTER TABLE `mw_watchlist`
  ADD PRIMARY KEY (`wl_id`),
  ADD UNIQUE KEY `wl_user` (`wl_user`,`wl_namespace`,`wl_title`),
  ADD KEY `wl_user_notificationtimestamp` (`wl_user`,`wl_notificationtimestamp`),
  ADD KEY `wl_namespace_title` (`wl_namespace`,`wl_title`);

--
-- Indexes for table `mw_watchlist_expiry`
--
ALTER TABLE `mw_watchlist_expiry`
  ADD PRIMARY KEY (`we_item`),
  ADD KEY `we_expiry` (`we_expiry`);

--
-- Indexes for table `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `sociql_actor`
--
ALTER TABLE `sociql_actor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_map_actors`
--
ALTER TABLE `sociql_map_actors`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `sociql_ontology_ent`
--
ALTER TABLE `sociql_ontology_ent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_ontology_prop`
--
ALTER TABLE `sociql_ontology_prop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_ontology_rel`
--
ALTER TABLE `sociql_ontology_rel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_property`
--
ALTER TABLE `sociql_property`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_relation`
--
ALTER TABLE `sociql_relation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_requiredprop`
--
ALTER TABLE `sociql_requiredprop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_requiredset`
--
ALTER TABLE `sociql_requiredset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sociql_site`
--
ALTER TABLE `sociql_site`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wikidev_messages`
--
ALTER TABLE `wikidev_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wikidev_projects`
--
ALTER TABLE `wikidev_projects`
  ADD PRIMARY KEY (`projectid`,`mailListName`);

--
-- Indexes for table `wikidev_projects_rules`
--
ALTER TABLE `wikidev_projects_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `wikidev_unsubs`
--
ALTER TABLE `wikidev_unsubs`
  ADD PRIMARY KEY (`user_id`,`project_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grand_acknowledgements`
--
ALTER TABLE `grand_acknowledgements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_action_plan`
--
ALTER TABLE `grand_action_plan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_activities`
--
ALTER TABLE `grand_activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_allocations`
--
ALTER TABLE `grand_allocations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_alumni`
--
ALTER TABLE `grand_alumni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_avoid_categories`
--
ALTER TABLE `grand_avoid_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_avoid_expert_event`
--
ALTER TABLE `grand_avoid_expert_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_avoid_resources`
--
ALTER TABLE `grand_avoid_resources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_bibliography`
--
ALTER TABLE `grand_bibliography`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_boards`
--
ALTER TABLE `grand_boards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_bsi_postings`
--
ALTER TABLE `grand_bsi_postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_ccv`
--
ALTER TABLE `grand_ccv`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_clipboard`
--
ALTER TABLE `grand_clipboard`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_collaborations`
--
ALTER TABLE `grand_collaborations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_collaboration_files`
--
ALTER TABLE `grand_collaboration_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_conference_attendance`
--
ALTER TABLE `grand_conference_attendance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_contributions`
--
ALTER TABLE `grand_contributions`
  MODIFY `rev_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_crm_contact`
--
ALTER TABLE `grand_crm_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_crm_opportunity`
--
ALTER TABLE `grand_crm_opportunity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_crm_projects`
--
ALTER TABLE `grand_crm_projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_crm_task`
--
ALTER TABLE `grand_crm_task`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_data_collection`
--
ALTER TABLE `grand_data_collection`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_delegate`
--
ALTER TABLE `grand_delegate`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_diversity`
--
ALTER TABLE `grand_diversity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_diversity_2018`
--
ALTER TABLE `grand_diversity_2018`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_elite_postings`
--
ALTER TABLE `grand_elite_postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_ethics`
--
ALTER TABLE `grand_ethics`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_event_postings`
--
ALTER TABLE `grand_event_postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_event_registration`
--
ALTER TABLE `grand_event_registration`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_fitbit_data`
--
ALTER TABLE `grand_fitbit_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_freeze`
--
ALTER TABLE `grand_freeze`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_gamification`
--
ALTER TABLE `grand_gamification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_illegal_authors`
--
ALTER TABLE `grand_illegal_authors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_job_postings`
--
ALTER TABLE `grand_job_postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_journals`
--
ALTER TABLE `grand_journals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_lims_contact`
--
ALTER TABLE `grand_lims_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_lims_files`
--
ALTER TABLE `grand_lims_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_lims_opportunity`
--
ALTER TABLE `grand_lims_opportunity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_lims_projects`
--
ALTER TABLE `grand_lims_projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_lims_task`
--
ALTER TABLE `grand_lims_task`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_list_request`
--
ALTER TABLE `grand_list_request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_loi`
--
ALTER TABLE `grand_loi`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_managed_people`
--
ALTER TABLE `grand_managed_people`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_materials`
--
ALTER TABLE `grand_materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_milestones`
--
ALTER TABLE `grand_milestones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_movedOn`
--
ALTER TABLE `grand_movedOn`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_news_postings`
--
ALTER TABLE `grand_news_postings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_notifications`
--
ALTER TABLE `grand_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_partners`
--
ALTER TABLE `grand_partners`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_pdf_report`
--
ALTER TABLE `grand_pdf_report`
  MODIFY `report_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_person_crdc`
--
ALTER TABLE `grand_person_crdc`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_person_keywords`
--
ALTER TABLE `grand_person_keywords`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_pmm_files`
--
ALTER TABLE `grand_pmm_files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_pmm_task`
--
ALTER TABLE `grand_pmm_task`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_pmm_task_assignees`
--
ALTER TABLE `grand_pmm_task_assignees`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_pmm_task_assignees_comments`
--
ALTER TABLE `grand_pmm_task_assignees_comments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_poll`
--
ALTER TABLE `grand_poll`
  MODIFY `poll_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_poll_collection`
--
ALTER TABLE `grand_poll_collection`
  MODIFY `collection_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_poll_options`
--
ALTER TABLE `grand_poll_options`
  MODIFY `option_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_poll_votes`
--
ALTER TABLE `grand_poll_votes`
  MODIFY `vote_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_positions`
--
ALTER TABLE `grand_positions`
  MODIFY `position_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_posting_images`
--
ALTER TABLE `grand_posting_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_posts`
--
ALTER TABLE `grand_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_products`
--
ALTER TABLE `grand_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_product_tags`
--
ALTER TABLE `grand_product_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_projects`
--
ALTER TABLE `grand_projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_challenges`
--
ALTER TABLE `grand_project_challenges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_champions`
--
ALTER TABLE `grand_project_champions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_contact`
--
ALTER TABLE `grand_project_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_descriptions`
--
ALTER TABLE `grand_project_descriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_evolution`
--
ALTER TABLE `grand_project_evolution`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_leaders`
--
ALTER TABLE `grand_project_leaders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_members`
--
ALTER TABLE `grand_project_members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_programs`
--
ALTER TABLE `grand_project_programs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_project_status`
--
ALTER TABLE `grand_project_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_provinces`
--
ALTER TABLE `grand_provinces`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_recordings`
--
ALTER TABLE `grand_recordings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_relations`
--
ALTER TABLE `grand_relations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_report_backup`
--
ALTER TABLE `grand_report_backup`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_report_blobs`
--
ALTER TABLE `grand_report_blobs`
  MODIFY `blob_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_report_blobs_impersonated`
--
ALTER TABLE `grand_report_blobs_impersonated`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_roles`
--
ALTER TABLE `grand_roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_role_subtype`
--
ALTER TABLE `grand_role_subtype`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_rss_articles`
--
ALTER TABLE `grand_rss_articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_rss_feeds`
--
ALTER TABLE `grand_rss_feeds`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_themes`
--
ALTER TABLE `grand_themes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_theme_leaders`
--
ALTER TABLE `grand_theme_leaders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_theses`
--
ALTER TABLE `grand_theses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_threads`
--
ALTER TABLE `grand_threads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_top_products`
--
ALTER TABLE `grand_top_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_universities`
--
ALTER TABLE `grand_universities`
  MODIFY `university_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_uofa_news`
--
ALTER TABLE `grand_uofa_news`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_addresses`
--
ALTER TABLE `grand_user_addresses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_languages`
--
ALTER TABLE `grand_user_languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_metrics`
--
ALTER TABLE `grand_user_metrics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_request`
--
ALTER TABLE `grand_user_request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_telephone`
--
ALTER TABLE `grand_user_telephone`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grand_user_university`
--
ALTER TABLE `grand_user_university`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_actor`
--
ALTER TABLE `mw_actor`
  MODIFY `actor_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_an_extranamespaces`
--
ALTER TABLE `mw_an_extranamespaces`
  MODIFY `nsId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_an_page_visits`
--
ALTER TABLE `mw_an_page_visits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_archive`
--
ALTER TABLE `mw_archive`
  MODIFY `ar_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_category`
--
ALTER TABLE `mw_category`
  MODIFY `cat_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_change_tag`
--
ALTER TABLE `mw_change_tag`
  MODIFY `ct_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_change_tag_def`
--
ALTER TABLE `mw_change_tag_def`
  MODIFY `ctd_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_comment`
--
ALTER TABLE `mw_comment`
  MODIFY `comment_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_content`
--
ALTER TABLE `mw_content`
  MODIFY `content_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_content_models`
--
ALTER TABLE `mw_content_models`
  MODIFY `model_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_externallinks`
--
ALTER TABLE `mw_externallinks`
  MODIFY `el_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_filearchive`
--
ALTER TABLE `mw_filearchive`
  MODIFY `fa_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_googlelogin_allowed_domains`
--
ALTER TABLE `mw_googlelogin_allowed_domains`
  MODIFY `gl_allowed_domain_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_ipblocks`
--
ALTER TABLE `mw_ipblocks`
  MODIFY `ipb_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_job`
--
ALTER TABLE `mw_job`
  MODIFY `job_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_linktarget`
--
ALTER TABLE `mw_linktarget`
  MODIFY `lt_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_logging`
--
ALTER TABLE `mw_logging`
  MODIFY `log_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_page`
--
ALTER TABLE `mw_page`
  MODIFY `page_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_page_restrictions`
--
ALTER TABLE `mw_page_restrictions`
  MODIFY `pr_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_recentchanges`
--
ALTER TABLE `mw_recentchanges`
  MODIFY `rc_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_revision`
--
ALTER TABLE `mw_revision`
  MODIFY `rev_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_sentence`
--
ALTER TABLE `mw_sentence`
  MODIFY `sen_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_session_data`
--
ALTER TABLE `mw_session_data`
  MODIFY `session_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_sites`
--
ALTER TABLE `mw_sites`
  MODIFY `site_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_slot_roles`
--
ALTER TABLE `mw_slot_roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_text`
--
ALTER TABLE `mw_text`
  MODIFY `old_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_trackbacks`
--
ALTER TABLE `mw_trackbacks`
  MODIFY `tb_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_uploadstash`
--
ALTER TABLE `mw_uploadstash`
  MODIFY `us_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_user`
--
ALTER TABLE `mw_user`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_user_aliases`
--
ALTER TABLE `mw_user_aliases`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mw_watchlist`
--
ALTER TABLE `mw_watchlist`
  MODIFY `wl_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_actor`
--
ALTER TABLE `sociql_actor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_ontology_ent`
--
ALTER TABLE `sociql_ontology_ent`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_ontology_prop`
--
ALTER TABLE `sociql_ontology_prop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_ontology_rel`
--
ALTER TABLE `sociql_ontology_rel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_property`
--
ALTER TABLE `sociql_property`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_relation`
--
ALTER TABLE `sociql_relation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_requiredprop`
--
ALTER TABLE `sociql_requiredprop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_requiredset`
--
ALTER TABLE `sociql_requiredset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sociql_site`
--
ALTER TABLE `sociql_site`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wikidev_messages`
--
ALTER TABLE `wikidev_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wikidev_projects`
--
ALTER TABLE `wikidev_projects`
  MODIFY `projectid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wikidev_projects_rules`
--
ALTER TABLE `wikidev_projects_rules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grand_pmm_task`
--
ALTER TABLE `grand_pmm_task`
  ADD CONSTRAINT `grand_pmm_task_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `grand_project` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grand_pmm_task_assignees`
--
ALTER TABLE `grand_pmm_task_assignees`
  ADD CONSTRAINT `grand_pmm_task_assignees_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `grand_pmm_task` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grand_pmm_task_assignees_comments`
--
ALTER TABLE `grand_pmm_task_assignees_comments`
  ADD CONSTRAINT `grand_pmm_task_assignees_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `grand_pmm_task` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
