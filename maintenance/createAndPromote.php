<?php

/**
 * Maintenance script to create an account and grant it administrator rights
 *
 * @file
 * @ingroup Maintenance
 * @author Rob Church <robchur@gmail.com>
 */

$options = array( 'help', 'sysop', 'bureaucrat' );
require_once( 'commandLine.inc' );

if( isset( $options['help'] ) ) {
	showHelp();
	exit( 1 );
}

if( count( $args ) < 2 ) {
	echo( "Please provide a username and password for the new account.\n" );
	die( 1 );
}

$username = $args[0];
$password = $args[1];
if( count( $args ) == 3)
	$email = $args[2];

echo( wfWikiID() . ": Creating and promoting User:{$username}..." );

# Validate username and check it doesn't exist
$user = User::newFromName( $username );
if( !is_object( $user ) ) {
	echo( "invalid username.\n" );
	die( 1 );
} elseif( 0 != $user->idForName() ) {
	echo( "account exists.\n" );
	die( 1 );
}

# Insert the account into the database
$user->addToDatabase();
$user->setPassword( $password );
if( count( $args ) == 3 )
	$user->setEmail($email);
$user->saveSettings();

# Promote user
if( isset( $option['sysop'] ) )
$user->addGroup( 'sysop' );
if( isset( $option['bureaucrat'] ) )
	$user->addGroup( 'bureaucrat' );

# Increment site_stats.ss_users
$ssu = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
$ssu->doUpdate();

echo( "done.\n" );

function showHelp() {
	echo( <<<EOT
Create a new user account with optional administrator rights

USAGE: php createAndPromote.php [--bureaucrat|--sysop|--help] <username> <password> <email>

	--bureaucrat
		Grant the account bureaucrat rights
	--sysop
		Grant the account sysop rights
	--help
		Show this help information

EOT
	);
}
