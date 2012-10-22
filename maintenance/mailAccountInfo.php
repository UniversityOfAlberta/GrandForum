<?php
require_once( 'commandLine.inc' );
$options = array('help');
if( isset( $options['help'] ) ) {
	showHelp();
	exit(1);
}

if( count( $args ) != 2){
	showHelp();
	exit(1);
}
$user_name = $args[0];
$password = $args[1];

$user = User::newFromName($user_name);

$userName = $user->getName();
$len = strlen($userName);
while($len < 25){
	$userName .= " ";
	$len++;
}

echo $userName." : ";
if($user->checkPassword($password)){ // Password is the correct password for the user

	$name = str_replace(".", " ", $user->getName());

	$to      = $user->getEmail();
	$subject = "Welcome to the GRAND Forum $name";
	$message = 
"Dear $name<br />
<br />
The GRAND forum is now up and running at <a href='http://forum.grand-nce.ca/index.php/Main_Page'>http://forum.grand-nce.ca/index.php/Main_Page</a>. You have an account in the system, with the credentials<br />
<br />
User Name: {$user->getName()}<br />
Password: $password<br />
<br />
It is recommended that as soon as you first login to <a href='http://forum.grand-nce.ca/index.php?title=Special:UserLogin&returnto=Main_Page'>the system</a> that you change your password. If you have any questions about the system, please contact us at <a href='mailto:grand-forum-help@hypatia.cs.ualberta.ca'>grand-forum-help@hypatia.cs.ualberta.ca</a>.<br />
<br />
Best Regards<br />
David Turner (GRAND forum developer)";

	$headers = 'From: grand-forum-help@hypatia.cs.ualberta.ca' . "\r\n" .
	    'Reply-To: grand-forum-help@hypatia.cs.ualberta.ca' . "\r\n" .
	    'Content-type:text/html;charset=iso-8859-1' . "" .
	    'X-Mailer: PHP/' . phpversion();
	mail($to, $subject, $message, $headers);
	echo "Success\n";
}
else { // Password was not the correct password for the user
	echo "Password did not match the one in the database.\n";
}

function showHelp() {
		echo( <<<EOT
Emails the user the given password for the GRAND forum.

USAGE: php mailAccountInfo.php [--help] <user_name> <password>

	--help
		Show this help information

EOT
	);
}

?>
