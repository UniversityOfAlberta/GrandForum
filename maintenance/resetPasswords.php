<?php
require_once('commandLine.inc');

use MediaWiki\MediaWikiServices;

$wgUser = User::newFromId(1);

$rows = DBFunctions::select(array('mw_user'),
                            array('*'));
foreach($rows as $row){
    $passwd = PasswordFactory::generateRandomPasswordString(16);
    $passwd = MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString();
    echo "{$row['user_name']}\n";
    DBFunctions::update('mw_user',
                        array('user_password' => MediaWikiServices::getInstance()->getPasswordFactory()->newFromPlaintext($passwd)->toString()),
                        array('user_id' => EQ($row['user_id'])));
}

?>

