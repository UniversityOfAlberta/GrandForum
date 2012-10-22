<?php
/**
 * Change the password of a given user
 *
 * @file
 * @ingroup Maintenance
 *
 * @author Ævar Arnfjörð Bjarmason <avarab@gmail.com>
 * @copyright Copyright © 2005, Ævar Arnfjörð Bjarmason
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$optionsWithArgs = array( 'user', 'email' );
require_once 'commandLine.inc';

$USAGE =
	"Usage: php changeEmail.php [--user=user --email=email | --help]\n" .
	"\toptions:\n" .
	"\t\t--help      show this message\n" .
	"\t\t--user      the username to operate on\n" .
	"\t\t--email     the email to use\n";

if( in_array( '--help', $argv ) )
	wfDie( $USAGE );

$cp = new ChangeEmail( @$options['user'], @$options['email'] );
$cp->main();

/**
 * @ingroup Maintenance
 */
class ChangeEmail {
	var $dbw;
	var $user, $email;

	function ChangeEmail( $user, $email ) {
		global $USAGE;
		if( !strlen( $user ) or !strlen( $email ) ) {
			wfDie( $USAGE );
		}

		$this->user = User::newFromName( $user );
		if ( !$this->user->getId() ) {
			die ( "No such user: $user\n" );
		}

		$this->email = $email;

		$this->dbw = wfGetDB( DB_MASTER );
	}

	function main() {
		$this->user->setEmail( $this->email );
		$this->user->saveSettings();
	}
}
