<?php
$dir = __DIR__;

require_once("$dir/Classes/Patch.php");

// Hack to change to MYSQLI_ASSOC in doFetchRow
$objPatch = new Patch("$dir/includes/libs/rdbms/database/resultwrapper/MysqliResultWrapper.php");
$objPatch->redefineFunction("
    protected function doFetchRow() {
	\$array = \$this->result->fetch_array(MYSQLI_ASSOC); // Changed to MYSQLI_ASSOC
	\$this->checkFetchError();
	if ( \$array === null ) {
		return false;
	}
	return \$array;
}");
try{
    eval($objPatch->getCode());
} catch (Throwable $e) {
    //code to handle the exception or error
}

// Hack to add text/html to the email headers
$objPatch = new Patch("$dir/includes/user/User.php");
$objPatch->redefineFunction("
    public function sendMail( \$subject, \$body, \$from = null, \$replyto = null ) {
	    global \$wgAllowHTMLEmail;
		\$passwordSender = MediaWikiServices::getInstance()->getMainConfig()
			->get( MainConfigNames::PasswordSender );

		if ( \$from instanceof User ) {
			\$sender = MailAddress::newFromUser( \$from );
		} else {
			\$sender = new MailAddress( \$passwordSender,
				wfMessage( 'emailsender' )->inContentLanguage()->text() );
		}
		\$to = MailAddress::newFromUser( \$this );
        \$options = [
			'replyTo' => \$replyto,
		];
		if(\$wgAllowHTMLEmail){
		    \$options['contentType'] = 'text/html; charset=UTF-8';
		}
		return UserMailer::send( \$to, \$sender, \$subject, \$body, \$options );
	}");
try{
    eval($objPatch->getCode());
} catch (Throwable $e) {
    //code to handle the exception or error
}

?>
